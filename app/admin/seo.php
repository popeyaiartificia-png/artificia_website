<?php
declare(strict_types=1);

// SEO desk (Orkay-style): Meta manager, Schema overview, and the robots.txt / llms.txt screen.

/** Meta manager filters: key => [label, test on a page row]. */
function meta_filters(): array
{
    $len = fn(string $s) => mb_strlen(heading_plain($s));
    return [
        'no-keyword' => ['No focus keyword', fn($p) => trim($p['focus_keyword']) === ''],
        'no-description' => ['Missing description', fn($p) => trim($p['meta_description']) === ''],
        'too-long' => ['Title or description too long', fn($p) => $len($p['meta_title'] ?: $p['title']) > SEO_TITLE_MAX || $len($p['meta_description']) > SEO_DESC_MAX],
        'noindex' => ['Hidden from Google', fn($p) => (bool) $p['noindex']],
    ];
}

const META_FIELDS = ['focus_keyword', 'meta_title', 'h1', 'meta_description'];

/**
 * Saves the Meta manager's changed rows. A published page keeps its required fields (the editor's publish rule).
 * @return array{0: int, 1: array<int,string>} saved count, page id => error
 */
function meta_save(PDO $db, array $rows, int $userId): array
{
    $saved = 0;
    $errors = [];
    foreach ($rows as $id => $row) {
        $page = is_array($row) ? page_find($db, (int) $id) : null;
        if (!$page) {
            continue;
        }
        $change = [];
        foreach (META_FIELDS as $f) {
            $v = is_string($row[$f] ?? null) ? trim($row[$f]) : $page[$f];
            if ($v !== $page[$f]) {
                $change[$f] = $v;
            }
        }
        $noindex = empty($row['noindex']) ? 0 : 1;
        if ($noindex !== (int) $page['noindex']) {
            $change['noindex'] = $noindex;
        }
        if (!$change) {
            continue;
        }
        $merged = $change + $page;
        $missing = $page['status'] === 'published'
            ? array_filter(['h1' => 'H1', 'meta_title' => 'meta title', 'meta_description' => 'meta description'], fn($f) => trim((string) $merged[$f]) === '', ARRAY_FILTER_USE_KEY)
            : [];
        try {
            if ($missing) {
                throw new InvalidArgumentException('A published page needs its ' . implode(' and ', $missing) . '. Fill it in, or unpublish the page first.');
            }
            page_save($db, ['id' => (int) $id] + $change, $userId);
            $saved++;
        } catch (InvalidArgumentException $e) {
            $errors[(int) $id] = $e->getMessage();
        }
    }
    return [$saved, $errors];
}

function admin_meta(PDO $db, string $method): HttpResponse
{
    $user = auth_require('seo', $db);
    $errors = [];
    $draft = [];
    if ($method === 'POST') {
        $rows = is_array($_POST['rows'] ?? null) ? $_POST['rows'] : [];
        [$saved, $errors] = meta_save($db, $rows, (int) $user['id']);
        if (!$errors) {
            $back = '/admin/seo' . (($_SERVER['QUERY_STRING'] ?? '') !== '' ? '?' . $_SERVER['QUERY_STRING'] : '');
            return admin_redirect($back, $saved ? "Saved $saved " . ($saved === 1 ? 'page' : 'pages') . '. Live now.' : 'Nothing had changed.');
        }
        $draft = array_intersect_key($rows, $errors);
        $_SESSION['flash'] = ['msg' => count($errors) . ' not saved: see the rows marked in red.' . ($saved ? " $saved saved." : ''), 'kind' => 'error'];
    }
    // ponytail: one form for every page; past ~150 pages PHP's max_input_vars (1000) truncates the post, so paginate then.
    $pages = $db->query('SELECT id, kind, slug, title, h1, meta_title, meta_description, focus_keyword, noindex, status FROM pages ORDER BY kind, slug = \'home\' DESC, slug')->fetchAll();
    $filters = meta_filters();
    $show = admin_get_str('show');
    $q = mb_strtolower(trim(admin_get_str('q')));
    $shown = array_filter($pages, fn($p) => (!isset($filters[$show]) || $filters[$show][1]($p))
        && ($q === '' || str_contains(mb_strtolower($p['title'] . ' ' . page_path($p)), $q)));
    $vars = ['title' => 'Meta manager', 'pages' => $shown, 'total' => count($pages), 'filters' => $filters, 'show' => isset($filters[$show]) ? $show : '', 'q' => $q, 'errors' => $errors, 'draft' => $draft];
    return new HttpResponse($errors ? 422 : 200, admin_view('seo-meta', $vars, $user));
}

function admin_seo_schema(PDO $db, string $method): HttpResponse
{
    $user = auth_require('seo', $db);
    $settings = settings($db);
    $base = config()['base_url'];
    $rows = [];
    foreach ($db->query("SELECT * FROM pages WHERE status = 'published' ORDER BY kind, slug = 'home' DESC, slug")->fetchAll() as $page) {
        $page['sections'] = json_decode((string) $page['sections'], true) ?: [];
        $graph = schema_for_page($page, $settings, $base);
        $rows[] = ['page' => $page, 'url' => $base . page_path($page), 'graph' => $graph,
            'types' => array_merge(...array_map(fn($n) => (array) ($n['@type'] ?? '?'), $graph ?: [[]])) ?: []];
    }
    return new HttpResponse(200, admin_view('seo-schema', ['title' => 'Schema', 'rows' => $rows], $user));
}

const SEO_FILE_KEYS = ['robots_mode', 'robots_block_ai', 'robots_custom', 'llms_mode', 'llms_intro', 'llms_extra', 'llms_custom'];

/**
 * Checks the robots.txt / llms.txt form. A custom robots.txt that hides the whole site needs the confirm tick.
 * @return array{0: array<string,string>, 1: array<string,string>} values, field => error
 */
function seo_files_check(array $in): array
{
    $v = [];
    foreach (SEO_FILE_KEYS as $k) {
        $v[$k] = trim(str_replace("\r", '', is_string($in[$k] ?? null) ? $in[$k] : ''));
    }
    $v['robots_mode'] = $v['robots_mode'] === 'custom' ? 'custom' : 'auto';
    $v['llms_mode'] = $v['llms_mode'] === 'custom' ? 'custom' : 'auto';
    $v['robots_block_ai'] = $v['robots_block_ai'] !== '' ? '1' : '0';
    $errors = [];
    foreach (['robots_custom', 'llms_intro', 'llms_extra', 'llms_custom'] as $k) {
        if (mb_strlen($v[$k]) > 20000) {
            $errors[$k] = 'This box is too long (at most 20,000 characters).';
        }
    }
    if ($v['robots_mode'] === 'custom' && $v['robots_custom'] === '') {
        $errors['robots_custom'] = 'Type the custom robots.txt, or choose Automatic.';
    } elseif ($v['robots_mode'] === 'custom' && robots_blocks_site($v['robots_custom']) && empty($in['robots_confirm'])) {
        $errors['robots_custom'] = 'This file has "Disallow: /", which hides the whole site from that crawler. Tick the box below to save it anyway.';
    }
    if ($v['llms_mode'] === 'custom' && $v['llms_custom'] === '') {
        $errors['llms_custom'] = 'Type the custom llms.txt, or choose Automatic.';
    }
    return [$v, $errors];
}

function admin_seo_files(PDO $db, string $method): HttpResponse
{
    $user = auth_require('seo', $db);
    $base = config()['base_url'];
    $values = array_intersect_key(settings($db), array_flip(SEO_FILE_KEYS)) + ['robots_mode' => 'auto', 'llms_mode' => 'auto'] + array_fill_keys(SEO_FILE_KEYS, '');
    $errors = [];
    if ($method === 'POST') {
        [$values, $errors] = seo_files_check($_POST);
        if (!$errors) {
            foreach ($values as $k => $v) {
                setting_set($db, $k, $v);
            }
            return admin_redirect('/admin/seo/files', 'Saved. robots.txt and llms.txt serve the new text now.');
        }
        $_SESSION['flash'] = ['msg' => reset($errors), 'kind' => 'error'];
    }
    if ($values['robots_custom'] === '') { // Custom starts from what Automatic says now
        $values['robots_custom'] = trim(robots_txt('production', $base, ['robots_block_ai' => $values['robots_block_ai']]));
    }
    $vars = ['title' => 'robots.txt & llms.txt', 'values' => $values, 'errors' => $errors, 'env' => config()['env'],
        'robots' => robots_txt('production', $base, $values), 'llms' => llms_txt($db, $base, $values)];
    return new HttpResponse($errors ? 422 : 200, admin_view('seo-files', $vars, $user));
}
