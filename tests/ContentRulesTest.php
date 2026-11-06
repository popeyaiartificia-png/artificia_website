<?php
declare(strict_types=1);

const SEED_DIR = __DIR__ . '/../content/seed';
const SEED_BANNED = ['\bbest\b', '#\s?1\b', '\bno\.?\s?1\b', 'lowest', 'seats? (left|available)', '\bplacements?\b', 'internship',
    'guarantee', '₹', '\bRs\.?\s', '\bINR\b', 'valued at', '\bLPA\b', '\bModi\b', 'Altman'];
const SEED_APPROVED_NUMBERS = ['92%', '65%', '500%', '4×'];

/** Every seed page, keyed by slug. */
function seed_pages(): array
{
    $pages = [];
    foreach (glob(SEED_DIR . '/pages/*.json') as $file) {
        $page = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
        $pages[$page['slug']] = $page;
    }
    $launch = ['about', 'ai-masterclass', 'ai-masterclass/business-owners', 'ai-masterclass/students', 'ai-masterclass/working-professionals',
        'blog', 'case-studies/orkay-tiles', 'contact', 'corporate-ai-training', 'corporate-ai-training/healthcare',
        'corporate-ai-training/manufacturing', 'home', 'privacy', 'services', 'services/content-marketing', 'services/gohighlevel',
        'services/rag-custom-automation', 'services/website-development', 'terms'];
    $slugs = array_keys($pages);
    sort($slugs);
    eq($slugs, $launch, 'the launch pages (spec section 7)');
    return $pages;
}

/** Every string value inside $value. */
function seed_strings(mixed $value): array
{
    $strings = [];
    $value = (array) $value;
    array_walk_recursive($value, function ($v) use (&$strings) {
        if (is_string($v)) {
            $strings[] = $v;
        }
    });
    return $strings;
}

/** Every JSON file under content/seed, decoded, keyed by path relative to content/seed. */
function seed_json_files(): array
{
    $files = [];
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(SEED_DIR, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) {
        if ($f->getExtension() === 'json') {
            $files[substr($f->getPathname(), strlen(SEED_DIR) + 1)] = json_decode((string) file_get_contents($f->getPathname()), true, 512, JSON_THROW_ON_ERROR);
        }
    }
    ok(count($files) > 3, 'seed JSON files found');
    return $files;
}

function test_seed_has_no_banned_words(): void
{
    $re = '/' . implode('|', SEED_BANNED) . '/iu';
    foreach (seed_json_files() as $name => $data) {
        foreach (seed_strings($data) as $s) {
            // The owner approved these two public course fees on 2 Nov 2026.
            if (($data['slug'] ?? '') === 'ai-masterclass') {
                $s = str_replace(['₹20,000', '₹40,000'], '', $s);
            }
            ok(!preg_match($re, $s, $m), "$name: \"" . ($m[0] ?? '') . "\" in: $s");
        }
    }
}

function test_seed_numbers_are_approved(): void
{
    foreach (seed_json_files() as $name => $data) {
        $slug = is_array($data) ? (string) ($data['slug'] ?? '') : '';
        foreach (seed_strings($data) as $s) {
            preg_match_all('/\d+\s?%|\d+\s?(?:x\b|×)/iu', $s, $m); // "×" is not a word character, so it takes no \b
            foreach ($m[0] as $n) {
                ok(in_array(str_replace([' ', 'x', 'X'], ['', '×', '×'], $n), SEED_APPROVED_NUMBERS, true), "$name: unapproved number \"$n\" in: $s");
            }
            ok(!preg_match('/\b100\+/', $s) || str_starts_with($slug, 'corporate-ai-training'), "$name: \"100+\" belongs only on corporate training pages: $s");
        }
    }
}

function test_corporate_pages_state_no_fixed_duration(): void
{
    $checked = array_filter(seed_pages(), fn($slug) => str_starts_with($slug, 'corporate-ai-training'), ARRAY_FILTER_USE_KEY);
    foreach (json_decode((string) file_get_contents(SEED_DIR . '/templates.json'), true, 512, JSON_THROW_ON_ERROR) as $t) {
        if ($t['name'] === 'Corporate by industry') {
            $checked['template: ' . $t['name']] = $t;
        }
    }
    eq(count($checked), 4, 'three corporate training pages and their template');
    foreach ($checked as $label => $data) {
        foreach (seed_strings($data) as $s) {
            ok(!preg_match('/60[- ]day|\d+\s+sessions/i', $s, $m), "$label: fixed duration \"" . ($m[0] ?? '') . "\" in: $s");
        }
    }
}

function test_seed_pages_pass_publish_rules(): void
{
    $db = test_db();
    seed_import($db, SEED_DIR, temp_dir());
    $ids = $db->query('SELECT id FROM pages')->fetchAll(PDO::FETCH_COLUMN);
    eq(count($ids), count(seed_pages()), 'every seed page imported');
    foreach ($ids as $id) {
        $page = page_find($db, (int) $id);
        eq($page['status'], 'published', $page['slug']);
        eq(page_publish_errors($db, $page), [], $page['slug']);
    }
}

/** Images a page shows: each filled image field, with a hero's image or drawing counted once. Each file must exist. */
function seed_image_count(array $sections): int
{
    $registry = section_registry();
    $count = function (array $fields, array $defs) use (&$count): int {
        $n = 0;
        foreach ($defs as $key => $def) {
            $v = $fields[$key] ?? null;
            if ($def['type'] === 'image' && is_string($v) && $v !== '') {
                ok(is_file(SEED_DIR . '/media/' . $v), "image file $v exists");
                $n++;
            } elseif ($def['type'] === 'list' && is_array($v)) {
                $n += array_sum(array_map(fn($item) => $count((array) $item, $def['fields']), $v));
            }
        }
        return $n;
    };
    $n = 0;
    foreach ($sections as $s) {
        $f = $s['fields'] ?? [];
        if ($s['type'] === 'hero') {
            $n += ($f['drawing'] ?? 'none') !== 'none' || $count($f, $registry['hero']['fields']) > 0 ? 1 : 0;
        } else {
            $n += $count($f, $registry[$s['type']]['fields']);
        }
    }
    return $n;
}

/** A logo key the registry does not know is dropped silently on import, so the seed must only use known ones. */
function test_seed_tool_logos_are_known(): void
{
    $keys = section_registry()['tools']['fields']['tools']['fields']['brand']['options'];
    $symbols = section_registry()['tools']['fields']['tools']['fields']['symbol']['options'];
    foreach (seed_pages() as $slug => $page) {
        foreach ($page['sections'] as $s) {
            foreach ($s['type'] === 'tools' ? $s['fields']['tools'] : [] as $t) {
                ok(in_array($t['brand'] ?? '', $keys, true) || in_array($t['symbol'] ?? '', $symbols, true), "$slug: tool {$t['name']} has no known logo or symbol");
            }
        }
    }
}

function test_every_page_has_three_images(): void
{
    // course uses a code-drawn learning diagram and tool marks; solutions uses code-drawn illustrations and the hero canvas;
    // about shows its one real photo (the founder) and code-drawn diagrams, since the stock illustrations are not proof;
    // corporate training shows its one real reviewer photo beside code-drawn documents and the hero canvas, for the same reason
    // contact shows its details, routes and the office map, all code-drawn; its old stock photos were not proof either
    $textOnly = ['blog', 'privacy', 'terms', 'ai-masterclass', 'services', 'about', 'corporate-ai-training', 'contact'];
    foreach (seed_pages() as $slug => $page) {
        $n = seed_image_count($page['sections']);
        ok(in_array($slug, $textOnly, true) || $n >= 3, "$slug has $n images, needs 3");
    }
    $alts = json_decode((string) file_get_contents(SEED_DIR . '/media/alt.json'), true, 512, JSON_THROW_ON_ERROR);
    foreach (glob(SEED_DIR . '/media/*.jpg') as $file) {
        ok(trim($alts[basename($file)] ?? '') !== '', basename($file) . ' has alt text');
    }
}

function test_meta_lengths(): void
{
    $seen = [];
    foreach (seed_pages() as $slug => $page) {
        ok(mb_strlen($page['meta_title']) <= 60, "$slug meta_title is " . mb_strlen($page['meta_title']) . ' chars');
        ok(str_ends_with($page['meta_title'], ' | Artificia AI'), "$slug meta_title ends with \" | Artificia AI\"");
        $d = mb_strlen($page['meta_description']);
        ok($d >= 70 && $d <= 160, "$slug meta_description is $d chars");
        foreach (['title', 'h1', 'meta_title', 'meta_description'] as $col) {
            $key = $col . ':' . mb_strtolower(heading_plain($page[$col]));
            ok(!isset($seen[$key]), "$slug repeats the $col of " . ($seen[$key] ?? ''));
            $seen[$key] = $slug;
        }
    }
}
