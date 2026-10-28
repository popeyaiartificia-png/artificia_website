<?php
declare(strict_types=1);

// Admin pages and posts: lists, new, the section editor, preview, duplicate and templates. Both roles use all of it.
// Editor field names: sections[i][type], sections[i][<field>], sections[i][<list>][j][<sub>]. admin.js leaves the
// indexes sparse after moves and removals; PHP keeps the posted order, which is the order that gets saved.

const EDITOR_PER_PAGE = 50;
const EDITOR_FIXED_SLUGS = ['home', 'blog']; // the router serves these two at fixed addresses

/** A posted string; arrays and other non-strings (only a tampered form sends them) become ''. */
function admin_post_str(string $key): string
{
    $v = $_POST[$key] ?? '';
    return is_string($v) ? $v : '';
}

function admin_get_str(string $key): string
{
    $v = $_GET[$key] ?? '';
    return is_string($v) ? trim($v) : '';
}

function admin_edit_url(array $page): string
{
    return ($page['kind'] === 'post' ? '/admin/posts/' : '/admin/pages/') . $page['id'];
}

/** "28/10/26, 3:40 PM IST" from a DB timestamp, labelled with PHP's time zone; '' for none. */
function admin_date(?string $ts): string
{
    return $ts ? date('d/m/y, g:i A T', strtotime($ts)) : '';
}

function admin_not_found(array $user): HttpResponse
{
    return new HttpResponse(404, admin_view('notfound', ['title' => 'Not found'], $user));
}

// ---------- Form building ----------

/** One registry field as a form control. $value is the stored value (string, int media id or list rows). */
function editor_field(PDO $db, array $def, string $name, string $id, mixed $value): string
{
    $label = e($def['label']) . (!empty($def['required']) ? ' <span class="req">required</span>' : '');
    $lab = '<label for="' . $id . '" id="' . $id . '-l">' . $label . '</label>';
    $str = is_scalar($value) ? (string) $value : '';
    switch ($def['type']) {
        case 'list':
            return '<div class="cell cell--wide">' . editor_list_field($db, $def, $name, $id, is_array($value) ? $value : []) . '</div>';
        case 'image':
            $mid = positive_int($value);
            $row = $mid ? media_row($db, $mid) : null;
            return '<div class="cell cell--wide"><div class="field img-field" role="group" aria-labelledby="' . $id . '-l">'
                . '<span class="label" id="' . $id . '-l">' . $label . '</span><div class="img-field__row">'
                . '<span class="img-field__thumb"><img data-media-preview="#' . $id . '" src="' . e($row ? media_url($db, $mid, 480) : 'data:,') . '" alt="' . e($row['alt'] ?? '') . '"' . ($row ? '' : ' hidden') . '></span>'
                . '<input type="hidden" id="' . $id . '" name="' . $name . '" value="' . ($row ? $mid : '') . '">'
                . '<button type="button" class="btn btn--sm" data-media-pick="#' . $id . '">Choose image</button>'
                . '<button type="button" class="btn btn--ghost btn--sm" data-media-clear="#' . $id . '">Clear</button>'
                . '</div></div></div>';
        case 'select':
            $opts = '<option value="">' . (!empty($def['required']) ? 'Choose&hellip;' : 'Default') . '</option>';
            foreach ($def['options'] as $o) {
                $opts .= '<option value="' . e($o) . '"' . ($o === $str ? ' selected' : '') . '>' . e(ucfirst(str_replace('-', ' ', $o))) . '</option>';
            }
            return '<div class="cell"><div class="field">' . $lab . '<select id="' . $id . '" name="' . $name . '">' . $opts . '</select></div></div>';
        case 'textarea':
        case 'richtext':
            $rich = $def['type'] === 'richtext';
            return '<div class="cell cell--wide"><div class="field">' . $lab . '<textarea id="' . $id . '" name="' . $name . '" rows="' . ($rich ? 8 : 3) . '"'
                . ($rich ? ' data-richtext' : '') . '>' . e($str) . '</textarea></div></div>';
        default:
            $ph = isset($def['default']) ? ' placeholder="' . e($def['default']) . '"' : '';
            return '<div class="cell"><div class="field">' . $lab . '<input type="text" id="' . $id . '" name="' . $name . '" value="' . e($str) . '"' . $ph . '></div></div>';
    }
}

/** A list field: its items, a <template> admin.js clones for "Add item" (__j__ becomes a fresh index), and the add button. */
function editor_list_field(PDO $db, array $def, string $name, string $id, array $items): string
{
    $rows = '';
    foreach (array_values($items) as $j => $item) {
        $rows .= editor_list_item($db, $def, "{$name}[$j]", "$id-$j", is_array($item) ? $item : [], $j + 1);
    }
    return '<fieldset class="list-field" data-list><legend>' . e($def['label']) . (!empty($def['required']) ? ' <span class="req">required</span>' : '') . '</legend>'
        . '<div class="list-field__items" data-items>' . $rows . '</div>'
        . '<template data-item-tpl>' . editor_list_item($db, $def, "{$name}[__j__]", "$id-__j__", [], 0) . '</template>'
        . '<button type="button" class="btn btn--ghost btn--sm" data-item-add>' . icon('plus') . 'Add item</button></fieldset>';
}

function editor_list_item(PDO $db, array $def, string $name, string $id, array $item, int $n): string
{
    $fields = '';
    foreach ($def['fields'] as $sub => $sdef) {
        $fields .= editor_field($db, $sdef, "{$name}[$sub]", "$id-$sub", $item[$sub] ?? null);
    }
    return '<div class="list-item" data-item><div class="list-item__head"><span class="list-item__title">Item <span data-num>' . $n . '</span></span>'
        . '<button type="button" class="btn btn--danger btn--sm" data-item-remove data-confirm="Click again to remove">Remove</button></div>'
        . '<div class="form-grid">' . $fields . '</div></div>';
}

/** One collapsible section card. $errors are this section's publish problems (they open the card). */
function editor_section_card(PDO $db, array $section, int $i, array $errors = [], bool $open = false): string
{
    $def = section_registry()[$section['type']];
    $name = "sections[$i]";
    $id = "f-s$i";
    $fields = $summary = '';
    foreach ($def['fields'] as $f => $fdef) {
        $v = $section['fields'][$f] ?? null;
        $fields .= editor_field($db, $fdef, "{$name}[$f]", "$id-$f", $v);
        if ($summary === '' && $fdef['type'] === 'text' && is_string($v)) {
            $summary = heading_plain(trim($v));
        }
    }
    $open = $open || $errors;
    return '<div class="sec-card' . ($errors ? ' has-error' : '') . '" data-section>'
        . '<input type="hidden" name="' . $name . '[type]" value="' . e($section['type']) . '">'
        . '<div class="sec-card__head"><button type="button" class="sec-card__toggle" data-sec-toggle aria-expanded="' . ($open ? 'true' : 'false') . '" aria-controls="' . $id . '__panel">'
        . '<span class="sec-card__num" data-num>' . ($i + 1) . '</span><span class="sec-card__type">' . e($def['label']) . '</span>'
        . '<span class="sec-card__sum">' . e(mb_strimwidth($summary, 0, 70, '…')) . '</span></button>'
        . '<div class="sec-card__tools">'
        . '<button type="button" class="btn btn--ghost btn--sm" data-sec-move="up" aria-label="Move this section up">Up</button>'
        . '<button type="button" class="btn btn--ghost btn--sm" data-sec-move="down" aria-label="Move this section down">Down</button>'
        . '<button type="button" class="btn btn--danger btn--sm" data-sec-remove data-confirm="Click again to remove">Remove</button></div></div>'
        . '<div class="sec-card__body" id="' . $id . '__panel"' . ($open ? '' : ' hidden') . '>'
        . ($errors ? '<ul class="err-list">' . implode('', array_map(fn($m) => '<li>' . e($m) . '</li>', $errors)) . '</ul>' : '')
        . '<div class="form-grid">' . $fields . '</div></div></div>';
}

/** Fields a template can mark page-specific: "<sectionIndex>.<field>" => readable label. */
function editor_template_fields(array $sections): array
{
    $registry = section_registry();
    $out = [];
    foreach ($sections as $i => $s) {
        $def = $registry[$s['type']] ?? null;
        foreach ($def['fields'] ?? [] as $f => $fdef) {
            if ($fdef['type'] !== 'select') {
                $out["$i.$f"] = 'Section ' . ($i + 1) . " ({$def['label']}): {$fdef['label']}";
            }
        }
    }
    return $out;
}

// ---------- Saving ----------

/** sections[i][...] as posted => [['type', 'fields']] in posted order. page_save cleans the fields. */
function editor_sections_from_post(mixed $posted): array
{
    $out = [];
    foreach (is_array($posted) ? $posted : [] as $s) {
        if (is_array($s)) {
            $type = $s['type'] ?? null;
            unset($s['type']);
            $out[] = ['type' => $type, 'fields' => $s];
        }
    }
    return $out;
}

/** Empty fields are left out (the form posts '' for every blank box), so a saved page keeps the shape the seed pages have. */
function editor_drop_empty(array $fields): array
{
    foreach ($fields as $k => $v) {
        if ($v === '') {
            unset($fields[$k]);
        } elseif (is_array($v)) { // a list: its rows are field arrays
            $fields[$k] = array_map(fn($row) => is_array($row) ? editor_drop_empty($row) : $row, $v);
        }
    }
    return $fields;
}

/** The editor form as a page array for page_save (no status, no published_at: the controller decides those). */
function editor_page_from_post(array $page): array
{
    $sections = sections_normalize(editor_sections_from_post($_POST['sections'] ?? []));
    foreach ($sections as &$s) {
        $s['fields'] = editor_drop_empty($s['fields']);
    }
    $in = ['id' => $page['id'], 'kind' => $page['kind'], 'noindex' => !empty($_POST['noindex']), 'sections' => $sections];
    foreach (['title', 'slug', 'h1', 'meta_title', 'meta_description', 'og_image_id', 'template_id'] as $k) {
        $in[$k] = trim(admin_post_str($k));
    }
    if ($page['kind'] === 'page' && in_array($page['slug'], EDITOR_FIXED_SLUGS, true)) {
        $in['slug'] = $page['slug'];
    }
    if (positive_int($page['template_id'])) { // once linked, a template's unique-field rule can't be switched off from the form
        $in['template_id'] = $page['template_id'];
    }
    if ($page['kind'] === 'post') {
        $in['excerpt'] = admin_post_str('excerpt');
    }
    return $in;
}

/**
 * Messages from page_publish_errors() / page_save() sorted by where the editor shows them:
 * fields (name => first message), sections (index => messages without the "Section n (Type): " prefix), all (every message, for the summary).
 */
function editor_errors(array $messages): array
{
    $out = ['fields' => [], 'sections' => [], 'all' => $messages];
    $prefixes = ['The page heading (h1)' => 'h1', 'The meta title' => 'meta_title', 'The meta description' => 'meta_description',
        'The slug' => 'slug', "A blog post's web address" => 'slug'];
    foreach (array_keys(PAGE_TEXT_LIMITS) as $col) {
        $prefixes["The $col "] = $col; // "The meta_title is too long ..."
    }
    foreach ($messages as $msg) {
        if (preg_match('/^Section (\d+) \((.*?)\): (.*)$/s', $msg, $m)) {
            $out['sections'][(int) $m[1] - 1][] = $m[3];
            continue;
        }
        foreach ($prefixes as $prefix => $field) {
            if (str_starts_with($msg, $prefix)) {
                $out['fields'][$field] ??= $msg;
                break;
            }
        }
    }
    return $out;
}

/** Renders the editor. $page is the saved row (status, address, links); $form the values shown in the fields. */
function editor_render(PDO $db, array $user, array $page, array $form, int $status, array $messages = [], ?string $lead = null, bool $dirty = false): HttpResponse
{
    $errors = editor_errors($messages);
    if ($messages) {
        $_SESSION['flash'] = ['msg' => $lead ?? $messages[0], 'kind' => 'error'];
    }
    $asked = admin_post_str('tab') ?: admin_get_str('tab'); // a re-shown form keeps the tab it was posted from
    $tab = in_array($asked, ['seo', 'settings'], true) ? $asked : 'content';
    $f = $errors['fields'];
    if ($errors['sections']) {
        $tab = 'content';
    } elseif (isset($f['h1']) || isset($f['meta_title']) || isset($f['meta_description'])) {
        $tab = 'seo';
    } elseif (isset($f['slug'])) {
        $tab = 'settings';
    }
    $by = null;
    if ($page['updated_by']) {
        $stmt = $db->prepare('SELECT email FROM users WHERE id = ?');
        $stmt->execute([$page['updated_by']]);
        $by = $stmt->fetchColumn() ?: null;
    }
    $vars = [
        'title' => ($page['kind'] === 'post' ? 'Edit post: ' : 'Edit page: ') . heading_plain($page['title']),
        'db' => $db, 'page' => $page, 'form' => $form, 'errors' => $errors, 'lead' => $lead, 'tab' => $tab, 'dirty' => $dirty, 'by' => $by,
        'templates' => $db->query('SELECT id, name FROM templates ORDER BY name')->fetchAll(),
    ];
    return new HttpResponse($status, admin_view('page-edit', $vars, $user));
}

function admin_page_edit(PDO $db, string $method, int $id): HttpResponse
{
    return editor_handle($db, $method, $id, 'page');
}

function admin_post_edit(PDO $db, string $method, int $id): HttpResponse
{
    return editor_handle($db, $method, $id, 'post');
}

/**
 * GET shows the editor. POST saves: "publish" checks the publish rules first. A draft that fails them is saved as a draft
 * with the reasons shown; a live page that fails them is left as it is (the typed values stay in the form). "draft"
 * saves as (or turns the page into) a draft. No action (Enter, Cmd/Ctrl+S) keeps the current status.
 * R28: a live page whose address changes gets a 301 from the old address; if that fails the page is still saved and the editor sees why.
 */
function editor_handle(PDO $db, string $method, int $id, string $kind): HttpResponse
{
    $user = auth_require('seo', $db);
    $page = page_find($db, $id);
    if (!$page) {
        return admin_not_found($user);
    }
    if ($page['kind'] !== $kind) {
        return admin_redirect(admin_edit_url($page));
    }
    if ($method !== 'POST') {
        return editor_render($db, $user, $page, $page, 200);
    }

    $form = editor_page_from_post($page);
    $wasLive = $page['status'] === 'published';
    $action = admin_post_str('action');
    $publish = $action === 'publish' || ($action !== 'draft' && $wasLive);
    $problems = $publish ? page_publish_errors($db, $form) : [];
    $noun = $kind === 'post' ? 'post' : 'page';
    if ($wasLive && !$publish && $kind === 'page' && in_array($page['slug'], EDITOR_FIXED_SLUGS, true)) {
        return editor_render($db, $user, $page, $form, 422, ['This page lives at a fixed address (' . page_path($page) . ") and can't be unpublished. Use Update to save your changes."], null, true);
    }
    $n = count($problems) === 1 ? '1 thing' : count($problems) . ' things';
    if ($problems && $wasLive) {
        return editor_render($db, $user, $page, $form, 422, $problems, "Not updated: fix $n first. The live $noun has not changed.", true);
    }
    $form['status'] = $publish && !$problems ? 'published' : 'draft';
    try {
        page_save($db, $form, (int) $user['id']);
    } catch (InvalidArgumentException $e) {
        return editor_render($db, $user, $page, $form, 422, [$e->getMessage(), ...$problems], null, true);
    }
    $saved = page_find($db, $id);
    if ($problems) {
        return editor_render($db, $user, $saved, $saved, 422, $problems, "Saved as a draft. Fix $n to publish it.");
    }

    $type = 'ok';
    $msg = match (true) {
        $publish && $wasLive => "Updated - the live $noun shows it now.",
        $publish => "Published - the $noun is live.",
        $wasLive => "Unpublished - the $noun is a draft and its address now answers 404.",
        default => 'Draft saved.',
    };
    if ($wasLive && $publish && $saved['slug'] !== $page['slug']) {
        $from = page_path($page);
        $to = page_path($saved);
        try {
            // A rename back (A to B, then B to A) leaves an A to B row that the page now hides; drop it so B to A isn't a loop.
            $db->prepare("DELETE FROM redirects WHERE from_path = ? AND match_type = 'exact'")->execute([$to]);
            redirect_save($db, ['from_path' => $from, 'to_path' => $to, 'match_type' => 'exact', 'code' => 301]);
            $msg .= " $from now redirects to $to.";
        } catch (InvalidArgumentException $e) {
            [$msg, $type] = ["Saved, but $from could not be redirected to $to: " . $e->getMessage(), 'error'];
        }
    }
    $tab = admin_post_str('tab');
    return admin_redirect(admin_edit_url($saved) . (in_array($tab, ['seo', 'settings'], true) ? "?tab=$tab" : ''), $msg, $type);
}

function admin_preview(PDO $db, string $method, int $id): HttpResponse
{
    $user = auth_require('seo', $db);
    $page = page_find($db, $id);
    // No GA4 or Meta Pixel: editors' previews must not count as visits.
    $settings = array_diff_key(settings($db), array_flip(['ga4_id', 'meta_pixel_id']));
    return $page ? new HttpResponse(200, render_page($page, $settings)) : admin_not_found($user);
}

/** A blank section card for admin.js "Add section". ?i= is the index its field names use. */
function admin_section_form(PDO $db, string $method): HttpResponse
{
    auth_require('seo', $db);
    $type = admin_get_str('type');
    $i = admin_get_str('i') ?: '0';
    if (!isset(section_registry()[$type]) || !ctype_digit($i) || strlen($i) > 9) {
        return new HttpResponse(404, 'Unknown section type.', null, 'text/plain; charset=utf-8');
    }
    return new HttpResponse(200, editor_section_card($db, ['type' => $type, 'fields' => []], (int) $i, [], true));
}

/** The first free "<base>", "<base>-2", ... address. */
function editor_free_slug(PDO $db, string $base): string
{
    $stmt = $db->prepare('SELECT 1 FROM pages WHERE slug = ?');
    for ($n = 1; ; $n++) {
        $slug = $n === 1 ? $base : "$base-$n";
        $stmt->execute([$slug]);
        if (!$stmt->fetchColumn()) {
            return $slug;
        }
    }
}

function admin_page_duplicate(PDO $db, string $method, int $id): HttpResponse
{
    $user = auth_require('seo', $db);
    $page = page_find($db, $id);
    if (!$page) {
        return admin_not_found($user);
    }
    try {
        $copy = page_duplicate($db, $id, editor_free_slug($db, $page['slug'] . '-copy'), (int) $user['id']);
    } catch (InvalidArgumentException $e) {
        return admin_redirect(admin_edit_url($page), $e->getMessage(), 'error');
    }
    $new = page_find($db, $copy);
    return admin_redirect(admin_edit_url($new), 'Copy saved as a draft at ' . page_path($new) . '. Give it its own address under Settings.');
}

function admin_page_template(PDO $db, string $method, int $id): HttpResponse
{
    $user = auth_require('seo', $db);
    $page = page_find($db, $id);
    if (!$page) {
        return admin_not_found($user);
    }
    $posted = is_array($_POST['unique'] ?? null) ? array_filter($_POST['unique'], 'is_string') : [];
    $unique = array_values(array_intersect($posted, array_keys(editor_template_fields($page['sections']))));
    $name = trim(admin_post_str('name'));
    try {
        template_from_page($db, $id, $name, $unique);
    } catch (InvalidArgumentException $e) {
        return admin_redirect(admin_edit_url($page) . '?tab=settings', $e->getMessage(), 'error');
    }
    return admin_redirect('/admin/templates', "Template \"$name\" saved. Start a page from it with New page.");
}

// ---------- Lists and new ----------

function admin_pages(PDO $db, string $method): HttpResponse
{
    return editor_list($db, 'page');
}

function admin_posts(PDO $db, string $method): HttpResponse
{
    return editor_list($db, 'post');
}

/** Pages or posts, newest edit first, filtered by ?q= (title or address) and ?status=, 50 to a screen (?p=). */
function editor_list(PDO $db, string $kind): HttpResponse
{
    $user = auth_require('seo', $db);
    $q = admin_get_str('q');
    $status = in_array(admin_get_str('status'), ['published', 'draft'], true) ? admin_get_str('status') : '';
    $where = 'p.kind = :kind';
    $args = ['kind' => $kind];
    if ($status !== '') {
        $where .= ' AND p.status = :status';
        $args['status'] = $status;
    }
    if ($q !== '') {
        $where .= ' AND (p.title LIKE :q1 OR p.slug LIKE :q2)';
        $args['q1'] = '%' . addcslashes($q, '%_\\') . '%';
        $args['q2'] = '%' . addcslashes((string) preg_replace('#^/(blog/)?#', '', $q), '%_\\') . '%';
    }
    $stmt = $db->prepare("SELECT COUNT(*) FROM pages p WHERE $where");
    $stmt->execute($args);
    $total = (int) $stmt->fetchColumn();
    $pages = max(1, (int) ceil($total / EDITOR_PER_PAGE));
    $p = min($pages, max(1, (int) admin_get_str('p')));
    $stmt = $db->prepare("SELECT p.id, p.kind, p.slug, p.title, p.status, p.noindex, p.updated_at, u.email AS editor
        FROM pages p LEFT JOIN users u ON u.id = p.updated_by WHERE $where
        ORDER BY p.updated_at DESC, p.id DESC LIMIT " . EDITOR_PER_PAGE . ' OFFSET ' . (($p - 1) * EDITOR_PER_PAGE));
    $stmt->execute($args);
    $counts = $db->query('SELECT kind, COUNT(*) FROM pages GROUP BY kind')->fetchAll(PDO::FETCH_KEY_PAIR);
    $vars = ['title' => $kind === 'post' ? 'Posts' : 'Pages', 'kind' => $kind, 'rows' => $stmt->fetchAll(), 'total' => $total,
        'q' => $q, 'status' => $status, 'p' => $p, 'pages' => $pages, 'counts' => $counts];
    return new HttpResponse(200, admin_view('pages', $vars, $user));
}

function admin_page_new(PDO $db, string $method): HttpResponse
{
    return editor_new($db, $method, 'page');
}

function admin_post_new(PDO $db, string $method): HttpResponse
{
    return editor_new($db, $method, 'post');
}

/** Title + address (+ a template for pages) => a new draft, then the editor. A new post starts with one text block. */
function editor_new(PDO $db, string $method, string $kind): HttpResponse
{
    $user = auth_require('seo', $db);
    $form = ['title' => '', 'slug' => '', 'template_id' => admin_get_str('template')];
    $errors = ['fields' => [], 'all' => []];
    if ($method === 'POST') {
        $form = ['title' => trim(admin_post_str('title')), 'slug' => trim(admin_post_str('slug')), 'template_id' => admin_post_str('template_id')];
        $tid = $kind === 'page' ? positive_int($form['template_id']) : null;
        try {
            if ($form['title'] === '') {
                throw new InvalidArgumentException("The title is required: it names the $kind in the admin and in Google.");
            }
            $id = $tid
                ? page_from_template($db, $tid, $form['slug'], $form['title'], (int) $user['id'])
                : page_save($db, ['kind' => $kind, 'title' => $form['title'], 'slug' => $form['slug'], 'status' => 'draft',
                    'sections' => $kind === 'post' ? [['type' => 'rich_text', 'fields' => []]] : []], (int) $user['id']);
            return admin_redirect(admin_edit_url(['kind' => $kind, 'id' => $id]), 'Draft created. Add its content, then publish.');
        } catch (InvalidArgumentException $e) {
            $errors = editor_errors([$e->getMessage()]);
            $_SESSION['flash'] = ['msg' => $e->getMessage(), 'kind' => 'error'];
        }
    }
    $templates = $kind === 'page' ? $db->query('SELECT id, name, sections FROM templates ORDER BY name')->fetchAll() : [];
    $vars = ['title' => $kind === 'post' ? 'New post' : 'New page', 'kind' => $kind, 'form' => $form, 'errors' => $errors, 'templates' => $templates];
    return new HttpResponse($errors['all'] ? 422 : 200, admin_view('page-new', $vars, $user));
}

function admin_templates(PDO $db, string $method): HttpResponse
{
    $user = auth_require('seo', $db);
    $rows = $db->query('SELECT t.id, t.name, t.sections, t.unique_fields, t.created_at, COUNT(p.id) AS used
        FROM templates t LEFT JOIN pages p ON p.template_id = t.id GROUP BY t.id ORDER BY t.name')->fetchAll();
    return new HttpResponse(200, admin_view('templates', ['title' => 'Templates', 'rows' => $rows], $user));
}
