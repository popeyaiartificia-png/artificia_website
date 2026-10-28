<?php
declare(strict_types=1);

/** A published page with passing title, description, h1 and 300+ words unless overridden. */
function chk_page(PDO $db, string $slug, array $over = []): int
{
    $words = implode(' ', array_map(fn($i) => "$slug-word$i", range(1, 320)));
    return page_save($db, $over + [
        'slug' => $slug, 'title' => "Page $slug", 'h1' => "Heading $slug", 'status' => 'published',
        'meta_title' => "A perfectly sized title for $slug page",
        'meta_description' => "A description of the $slug page that is comfortably long enough to pass the length check.",
        'sections' => [['type' => 'rich_text', 'fields' => ['heading' => 'Intro', 'body' => "<p>$words</p>"]]],
    ], 1);
}

/** check keys by page slug */
function chk_keys(PDO $db): array
{
    $out = [];
    foreach (seo_checks($db, settings($db)) as $r) {
        $out[$r['page']['slug']] = array_column($r['issues'], 'check');
    }
    return $out;
}

function chk_issue(PDO $db, string $slug, string $check): ?string
{
    foreach (seo_checks($db, settings($db)) as $r) {
        foreach ($r['issues'] as $i) {
            if ($r['page']['slug'] === $slug && $i['check'] === $check) {
                return $i['message'];
            }
        }
    }
    return null;
}

function chk_links(array $paths): array
{
    return [['type' => 'card_grid', 'fields' => ['heading' => 'More', 'cards' =>
        array_map(fn($p) => ['title' => "Go $p", 'text' => 'Read on.', 'link' => $p], $paths)]]];
}

function test_title_and_description_lengths(): void
{
    $db = test_db();
    chk_page($db, 'short', ['title' => 'Twenty chars title!!', 'meta_title' => '', 'meta_description' => '']);
    chk_page($db, 'fine');
    $k = chk_keys($db);
    ok(in_array('title', $k['short'], true) && in_array('description', $k['short'], true), 'short page flagged');
    ok(!in_array('title', $k['fine'], true) && !in_array('description', $k['fine'], true), 'fine page clean');
    ok(str_contains(chk_issue($db, 'short', 'title'), '20'), 'message names the length');
}

function test_duplicate_titles_flagged(): void
{
    $db = test_db();
    $same = ['meta_title' => 'The very same title for several pages', 'meta_description' => ''];
    chk_page($db, 'one', $same);
    chk_page($db, 'two', $same);
    chk_page($db, 'three', $same + ['status' => 'draft']);
    chk_page($db, 'solo', ['meta_title' => 'A separate title that only a draft shares']);
    chk_page($db, 'solo-draft', ['status' => 'draft', 'meta_title' => 'A separate title that only a draft shares']);
    $k = chk_keys($db);
    ok(in_array('title_duplicate', $k['one'], true) && in_array('title_duplicate', $k['two'], true), 'both published flagged');
    ok(str_contains(chk_issue($db, 'one', 'title_duplicate'), '/two'), 'names the other page');
    ok(in_array('title_duplicate', $k['three'], true), 'a draft is checked against published pages');
    ok(!in_array('title_duplicate', $k['solo'], true), 'a draft does not make a published page fail');
}

function test_h1_count(): void
{
    $db = test_db();
    chk_page($db, 'plain');
    $hero = ['type' => 'hero', 'fields' => ['heading' => 'x']];
    $text = ['type' => 'rich_text', 'fields' => ['body' => '<p>hi</p>']];
    chk_page($db, 'second-hero', ['sections' => [$text, $hero]]);
    $k = chk_keys($db);
    ok(!in_array('h1', $k['plain'], true), 'page header supplies the h1');
    ok(!in_array('h1', $k['second-hero'], true), 'a second-section hero is an h2');
}

function test_image_without_alt(): void
{
    $db = test_db();
    $db->exec("INSERT INTO media (base_name, alt, width, height) VALUES ('no-alt', '', 1200, 800), ('with-alt', 'A desk', 1200, 800)");
    $img = fn(string $name) => ['type' => 'text_image', 'fields' => ['heading' => 'Pic', 'body' => '<p>x</p>',
        'image' => (int) $db->query("SELECT id FROM media WHERE base_name = '$name'")->fetchColumn()]];
    chk_page($db, 'bad', ['sections' => [$img('no-alt')]]);
    chk_page($db, 'good', ['sections' => [$img('with-alt')]]);
    $k = chk_keys($db);
    ok(in_array('alt', $k['bad'], true), 'empty alt flagged');
    ok(!in_array('alt', $k['good'], true), 'alt present');
}

function test_word_count(): void
{
    $db = test_db();
    chk_page($db, 'thin', ['sections' => [['type' => 'rich_text', 'fields' => ['body' => '<p>' . implode(' ', array_fill(0, 50, 'word')) . '</p>']]]]);
    chk_page($db, 'full');
    $msg = chk_issue($db, 'thin', 'words');
    ok($msg !== null && str_contains($msg, '52 words'), 'count in the message (50 + the 2-word page heading): ' . $msg);
    ok(chk_issue($db, 'full', 'words') === null);
}

function test_internal_links_in_and_out(): void
{
    $db = test_db();
    chk_page($db, 'a', ['sections' => chk_links(['/b', '/c', '#top', '/assets/x.css', 'https://elsewhere.test/'])]);
    chk_page($db, 'b', ['sections' => chk_links(['/a?x=1#y'])]);
    chk_page($db, 'c');
    chk_page($db, 'lonely');
    $k = chk_keys($db);
    ok(!in_array('links_out', $k['a'], true), 'A links to B and C');
    ok(in_array('links_out', $k['b'], true), 'B links to one page; header and footer links do not count');
    ok(!in_array('links_in', $k['a'], true) && !in_array('links_in', $k['c'], true), 'A and C are linked to');
    ok(in_array('links_in', $k['lonely'], true), 'nothing links to the lonely page');
    // Only published pages count as a link in.
    chk_page($db, 'draft-linker', ['status' => 'draft', 'sections' => chk_links(['/lonely'])]);
    ok(in_array('links_in', chk_keys($db)['lonely'], true), 'a draft link does not count');
}

function test_home_and_slug(): void
{
    $db = test_db();
    chk_page($db, 'home');
    chk_page($db, 'blog-2026/2024');
    chk_page($db, str_repeat('long-', 13) . 'slug');
    $k = chk_keys($db);
    ok(!in_array('links_in', $k['home'], true) && !in_array('slug', $k['home'], true), 'home is exempt');
    ok(in_array('slug', $k['blog-2026/2024'], true), 'a digits-only segment is unreadable');
    ok(in_array('slug', $k[str_repeat('long-', 13) . 'slug'], true), 'over 60 characters');
}

function test_near_duplicate(): void
{
    $db = test_db();
    $body = [['type' => 'rich_text', 'fields' => ['body' => '<p>' . implode(' ', array_map(fn($i) => "w$i", range(1, 400))) . '</p>']]];
    chk_page($db, 'twin-a', ['sections' => $body]);
    chk_page($db, 'twin-b', ['sections' => $body]);
    chk_page($db, 'other');
    $msg = chk_issue($db, 'twin-a', 'duplicate');
    ok($msg !== null && str_contains($msg, '/twin-b') && preg_match('/\\b(99|100)%/', $msg), 'names the twin: ' . $msg);
    ok(str_contains((string) chk_issue($db, 'twin-b', 'duplicate'), '/twin-a'));
    ok(!in_array('duplicate', chk_keys($db)['other'], true));
}

function test_page_issues_and_order(): void
{
    $db = test_db();
    chk_page($db, 'good');
    $bad = chk_page($db, 'bad', ['meta_title' => '', 'title' => 'Tiny', 'meta_description' => '']);
    $rows = seo_checks($db, settings($db));
    eq($rows[0]['page']['slug'], 'bad', 'most issues first');
    eq($rows[0]['page']['path'], '/bad');
    eq(seo_page_issues($db, settings($db), $bad), $rows[0]['issues']);
}

function test_seed_home_passes_core_checks(): void
{
    $db = test_db();
    seed_import($db, __DIR__ . '/../content/seed', temp_dir());
    $k = chk_keys($db);
    eq(array_intersect($k['home'], ['title', 'description', 'h1', 'alt', 'schema']), [], 'home core checks');
    eq(count($k), (int) $db->query('SELECT COUNT(*) FROM pages')->fetchColumn(), 'every page is checked');
}
