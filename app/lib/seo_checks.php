<?php
declare(strict_types=1);

const SEO_TITLE_MIN = 30;
const SEO_TITLE_MAX = 60;
const SEO_DESC_MIN = 70;
const SEO_DESC_MAX = 160;
const SEO_MIN_WORDS = 300;
const SEO_MIN_LINKS_OUT = 2;
const SEO_MIN_LINKS_IN = 1;
const SEO_SLUG_MAX = 60;
const SEO_DUPLICATE_OVERLAP = 0.5;

/** check key => short problem label (the dashboard's health rows and the editor's score pills). */
const SEO_CHECK_LABELS = [
    'title' => 'title too short or too long', 'title_duplicate' => 'title used by another page',
    'description' => 'description missing or the wrong length', 'description_duplicate' => 'description used by another page',
    'h1' => 'not exactly one H1', 'alt' => 'images missing alt text', 'words' => 'too little text',
    'links_out' => 'few links to other pages', 'links_in' => 'no other page links here',
    'slug' => 'address hard to read', 'duplicate' => 'near-duplicate of another page', 'schema' => 'schema problem',
    'keyword' => 'focus keyword missing from title, H1 or description',
];

/** check key => neutral name for the editor's score card, where passing checks are listed too. */
const SEO_CHECK_NAMES = [
    'title' => 'Title length', 'title_duplicate' => 'Title is unique', 'description' => 'Description length',
    'description_duplicate' => 'Description is unique', 'h1' => 'One H1', 'alt' => 'Image alt text', 'words' => 'Enough text',
    'links_out' => 'Links to other pages', 'links_in' => 'Linked from other pages', 'slug' => 'Readable address',
    'duplicate' => 'Not a near-duplicate', 'schema' => 'Valid schema', 'keyword' => 'Focus keyword used',
];

/** Visible words of rendered HTML: svg, script and style contents and all tags removed. */
function seo_words(string $html): array
{
    $text = preg_replace(['#<(svg|script|style)\b.*?</\1>#is', '/<[^>]*>/'], ' ', $html);
    return preg_split('/\s+/u', trim(html_entity_decode((string) $text, ENT_QUOTES | ENT_HTML5, 'UTF-8')), -1, PREG_SPLIT_NO_EMPTY) ?: [];
}

/** Distinct site paths the HTML links to (single leading /, no #fragment or ?query, not /assets/ or /uploads/), minus $self. */
function seo_links(string $html, string $self): array
{
    preg_match_all('/<a\b[^>]*?\shref="([^"]*)"/i', $html, $m);
    $out = [];
    foreach ($m[1] as $href) {
        $href = html_entity_decode($href, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if (!preg_match('#^/(?!/)#', $href)) {
            continue;
        }
        $path = preg_split('/[?#]/', $href)[0];
        $path = $path === '/' ? '/' : rtrim($path, '/');
        if ($path !== $self && !str_starts_with($path, '/assets/') && !str_starts_with($path, '/uploads/')) {
            $out[$path] = true;
        }
    }
    return array_keys($out);
}

/**
 * Every page and post (drafts too) with its issues, most issues first.
 * shortcut: renders every page on each call (fine for ~20 pages, ~0.3 s); cache per page once the site passes ~100 pages.
 * @return list<array{page: array, issues: list<array{check: string, message: string}>}>
 */
function seo_checks(PDO $db, array $settings): array
{
    $prev = db_swap($db); // sections read media and posts through db(); render from the database we were given
    try {
        return seo_analyse($db, $settings);
    } finally {
        db_swap($prev);
    }
}

/** The issues of one page, judged against the whole site. */
function seo_page_issues(PDO $db, array $settings, int $pageId): array
{
    foreach (seo_checks($db, $settings) as $row) {
        if ((int) $row['page']['id'] === $pageId) {
            return $row['issues'];
        }
    }
    return [];
}

function seo_analyse(PDO $db, array $settings): array
{
    $base = config()['base_url'];
    $info = [];
    foreach ($db->query('SELECT * FROM pages ORDER BY id')->fetchAll() as $page) {
        $page['sections'] = json_decode((string) $page['sections'], true) ?: [];
        $path = page_path($page);
        $html = render_page_body($page, $settings);
        $words = seo_words($html);
        $shingles = [];
        for ($i = 0, $n = count($words) - 4; $i < $n; $i++) {
            $shingles[mb_strtolower(implode(' ', array_slice($words, $i, 5)))] = true;
        }
        $info[$page['id']] = [
            'page' => $page, 'path' => $path, 'html' => $html, 'words' => count($words), 'shingles' => $shingles,
            'links' => seo_links($html, $path),
            'title' => trim(heading_plain($page['meta_title'] ?: $page['title'])),
            'desc' => trim(heading_plain($page['meta_description'] ?: strip_tags((string) $page['excerpt']))),
            // Only live, indexable pages count as "another page" for duplicates and links in.
            'counts' => $page['status'] === 'published' && !$page['noindex'],
        ];
    }

    $rows = [];
    foreach ($info as $id => $me) {
        $page = $me['page'];
        $others = array_filter($info, fn($o) => $o['counts'] && $o['page']['id'] !== $id);
        $issues = [];
        $add = function (string $check, string $message) use (&$issues) {
            $issues[] = ['check' => $check, 'message' => $message];
        };

        foreach (['title' => [SEO_TITLE_MIN, SEO_TITLE_MAX, 'Title'], 'description' => [SEO_DESC_MIN, SEO_DESC_MAX, 'Description']] as $key => [$min, $max, $label]) {
            $len = mb_strlen($me[$key === 'title' ? 'title' : 'desc']);
            if ($len < $min || $len > $max) {
                $add($key, "$label is $len characters. Make it $min to $max" . ($len === 0 ? ' (it is empty).' : '.'));
            }
            $dupe = $me[$key === 'title' ? 'title' : 'desc'];
            foreach ($dupe === '' ? [] : $others as $o) {
                if (mb_strtolower($o[$key === 'title' ? 'title' : 'desc']) === mb_strtolower($dupe)) {
                    $add($key . '_duplicate', "$label is the same as {$o['path']}. Give this page its own.");
                    break;
                }
            }
        }

        $h1s = preg_match_all('/<h1\b/i', $me['html']);
        if ($h1s !== 1) {
            $add('h1', "The page has $h1s H1 headings. It needs exactly one.");
        }
        preg_match_all('/<img\b[^>]*>/i', $me['html'], $imgs);
        $noAlt = count(array_filter($imgs[0], fn($tag) => !preg_match('/\balt="\s*[^"\s][^"]*"/i', $tag)));
        if ($noAlt) {
            $add('alt', "$noAlt image" . ($noAlt === 1 ? ' has' : 's have') . ' no alt text. Add it in the Media library.');
        }
        if ($me['words'] < SEO_MIN_WORDS) {
            $add('words', "The page has {$me['words']} words. Aim for at least " . SEO_MIN_WORDS . '.');
        }
        if (count($me['links']) < SEO_MIN_LINKS_OUT) {
            $add('links_out', 'The page links to ' . count($me['links']) . ' other pages. Link to at least ' . SEO_MIN_LINKS_OUT . ' in the text or cards.');
        }
        if ($page['slug'] !== 'home') {
            $in = array_filter($others, fn($o) => in_array($me['path'], $o['links'], true));
            if (count($in) < SEO_MIN_LINKS_IN) {
                $add('links_in', 'No published page links here. Link to it from a related page.');
            }
            $digits = array_filter(explode('/', $page['slug']), 'ctype_digit');
            if (mb_strlen($page['slug']) > SEO_SLUG_MAX || $digits) {
                $add('slug', 'The address is ' . ($digits ? 'a bare number in one part' : mb_strlen($page['slug']) . ' characters') . '. Use short readable words, at most ' . SEO_SLUG_MAX . ' characters.');
            }
        }
        foreach ($others as $o) {
            $union = count($me['shingles'] + $o['shingles']);
            $overlap = $union ? count(array_intersect_key($me['shingles'], $o['shingles'])) / $union : 0;
            if ($overlap >= SEO_DUPLICATE_OVERLAP) {
                $add('duplicate', "Mostly the same text as {$o['path']} (" . (int) round($overlap * 100) . '% overlap). Rewrite the intro, use cases and FAQs for this audience.');
            }
        }

        $keyword = mb_strtolower(trim((string) ($page['focus_keyword'] ?? '')));
        if ($keyword !== '') {
            $where = ['title' => $me['title'], 'H1' => heading_plain((string) ($page['h1'] ?: $page['title'])), 'description' => $me['desc']];
            $missing = array_keys(array_filter($where, fn($t) => !str_contains(mb_strtolower($t), $keyword)));
            if ($missing) {
                $add('keyword', 'The focus keyword "' . trim((string) $page['focus_keyword']) . '" is not in the ' . implode(' or ', $missing) . '.');
            }
        }

        try {
            $graph = schema_for_page($page, $settings, $base);
            json_encode($graph, JSON_THROW_ON_ERROR);
            foreach ($graph as $node) {
                if (!is_array($node) || empty($node['@type'])) {
                    throw new RuntimeException('A schema block has no @type.');
                }
            }
        } catch (Throwable $t) {
            $add('schema', 'The structured data is broken (' . $t->getMessage() . '). Check the FAQ and course sections.');
        }

        $rows[] = ['page' => [
            'id' => (int) $id, 'kind' => $page['kind'], 'slug' => $page['slug'], 'title' => $page['title'],
            'status' => $page['status'], 'path' => $me['path'],
        ], 'issues' => $issues];
    }
    usort($rows, fn($a, $b) => count($b['issues']) <=> count($a['issues']) ?: strcmp($a['page']['path'], $b['page']['path']));
    return $rows;
}
