<?php
declare(strict_types=1);

function pages_test_page(array $over = []): array
{
    return $over + [
        'slug' => 'services/demo', 'title' => 'Demo', 'h1' => 'Demo heading',
        'meta_title' => 'Demo | Artificia', 'meta_description' => 'A demo page.',
        'sections' => [['type' => 'rich_text', 'fields' => ['heading' => 'Hi', 'body' => '<p>Body</p>']]],
    ];
}

function pages_expect_invalid(callable $fn, string $contains): void
{
    try {
        $fn();
    } catch (InvalidArgumentException $e) {
        ok(str_contains($e->getMessage(), $contains), $e->getMessage());
        return;
    }
    throw new RuntimeException("expected InvalidArgumentException containing '$contains'");
}

function test_slug_rules(): void
{
    foreach (['home', 'about', 'services/gohighlevel', 'ai-masterclass/students', 'a/b/c', 'a1-b2'] as $slug) {
        ok(slug_valid($slug), $slug);
    }
    foreach (['admin/x', 'admin', 'Blog', 'blog', 'a--b', 'a/b/c/d', '', '/a', 'a/', '-a', 'a b', 'A', 'sitemap.xml', 'robots.txt',
        '_design', 'assets/x', 'uploads', 'lead', 'live/a', 'legacy', 'a//b', "a\n"] as $slug) {
        ok(!slug_valid($slug), "'$slug' should be invalid");
    }
    $db = test_db();
    page_save($db, pages_test_page(), 1);
    pages_expect_invalid(fn() => page_save($db, pages_test_page(), 1), 'already used');
    pages_expect_invalid(fn() => page_save($db, pages_test_page(['slug' => 'admin/x']), 1), 'slug');
}

function test_save_inserts_then_updates_in_place(): void
{
    $db = test_db();
    $id = page_save($db, pages_test_page(), 7);
    $id2 = page_save($db, pages_test_page(['id' => $id, 'title' => 'Renamed']), 7);
    eq($id2, $id);
    eq((int) $db->query('SELECT COUNT(*) FROM pages')->fetchColumn(), 1);
    eq(page_find($db, $id)['title'], 'Renamed');
    eq(page_find($db, $id)['status'], 'draft');
    eq((int) page_find($db, $id)['updated_by'], 7);
}

function test_save_sanitizes_richtext_and_drops_unknown_fields(): void
{
    $db = test_db();
    $id = page_save($db, pages_test_page(['sections' => [
        ['type' => 'rich_text', 'fields' => ['heading' => "  Hi  ", 'body' => '<p onclick="x">a<script>b</script></p>', 'bogus' => 'x']],
        ['type' => 'nope', 'fields' => ['heading' => 'x']],
        ['type' => 'text_image', 'fields' => ['heading' => 'T', 'image' => '12', 'image_side' => 'middle', 'body' => '']],
        ['type' => 'text_image', 'fields' => ['image' => '-3', 'image_side' => 'left']],
        ['type' => 'faqs', 'fields' => ['items' => [
            ['question' => ' Q1 ', 'answer' => 'A1', 'x' => 'y'],
            ['question' => '', 'answer' => ' '],
            'junk',
            5 => ['question' => 'Q2'],
        ]]],
    ]]), 1);
    $s = page_find($db, $id)['sections'];
    eq(count($s), 4);
    eq($s[0], ['type' => 'rich_text', 'fields' => ['heading' => 'Hi', 'body' => '<p>a</p>']]);
    eq($s[1]['fields'], ['heading' => 'T', 'body' => '', 'image' => 12]);
    eq($s[2]['fields'], ['image_side' => 'left']);
    eq($s[3]['fields']['items'], [['question' => 'Q1', 'answer' => 'A1'], ['question' => 'Q2']]);
}

function test_save_writes_revision(): void
{
    $db = test_db();
    $id = page_save($db, pages_test_page(), 5);
    page_save($db, pages_test_page(['id' => $id, 'title' => 'Second']), 6);
    $rows = $db->query("SELECT * FROM page_revisions WHERE page_id = $id ORDER BY id")->fetchAll();
    eq(count($rows), 2);
    $snap = json_decode($rows[1]['snapshot'], true);
    eq($snap['title'], 'Second');
    eq($snap['sections'][0]['fields']['heading'], 'Hi');
    eq((int) $rows[0]['user_id'], 5);
    eq((int) $rows[1]['user_id'], 6);
}

function test_save_sets_published_at_once(): void
{
    $db = test_db();
    $id = page_save($db, pages_test_page(), 1);
    eq(page_find($db, $id)['published_at'], null);
    page_save($db, pages_test_page(['id' => $id, 'status' => 'published']), 1);
    $first = page_find($db, $id)['published_at'];
    ok($first !== null);
    $db->exec("UPDATE pages SET published_at = '2020-01-02 03:04:05' WHERE id = $id");
    page_save($db, pages_test_page(['id' => $id, 'status' => 'published', 'title' => 'x']), 1);
    eq(page_find($db, $id)['published_at'], '2020-01-02 03:04:05');
    eq(page_find_published($db, 'services/demo')['title'], 'x');
}

function test_publish_blocked_without_meta(): void
{
    $db = test_db();
    eq(page_publish_errors($db, pages_test_page()), []);
    $errors = page_publish_errors($db, pages_test_page(['h1' => '', 'meta_title' => ' ', 'meta_description' => '']));
    eq($errors, ['The page heading (h1) is required.', 'The meta title is required.', 'The meta description is required.']);
}

function test_publish_errors_name_the_section_and_item(): void
{
    $db = test_db();
    $page = pages_test_page(['sections' => [
        ['type' => 'rich_text', 'fields' => ['body' => '<p></p>']],
        ['type' => 'faqs', 'fields' => ['heading' => 'FAQ', 'items' => [
            ['question' => 'Q', 'answer' => 'A'],
            ['question' => '', 'answer' => 'A2'],
        ]]],
        ['type' => 'card_grid', 'fields' => ['heading' => 'Cards', 'cards' => []]],
        ['type' => 'text_image', 'fields' => ['heading' => 'T', 'body' => '<p>x</p>']],
    ]]);
    eq(page_publish_errors($db, $page), [
        'Section 1 (Text block): Body is required.',
        'Section 2 (FAQs): Question is required in item 2.',
        'Section 3 (Card grid): Cards needs at least one item.',
        'Section 4 (Text and image): Image is required.',
    ]);
}

function test_publish_blocked_when_unique_field_unchanged(): void
{
    $db = test_db();
    $tpl = page_save($db, pages_test_page(['slug' => 'tpl-source', 'sections' => [
        ['type' => 'rich_text', 'fields' => ['heading' => 'Template heading', 'body' => '<p>Shared</p>']],
    ]]), 1);
    $tid = template_from_page($db, $tpl, 'Service', ['0.heading']);
    $pid = page_from_template($db, $tid, 'services/new', 'New', 1);
    $page = array_merge(pages_test_page(), ['template_id' => $tid, 'sections' => page_find($db, $pid)['sections']]);
    eq(page_publish_errors($db, $page), ['Section 1 (Text block): Heading is still the same as in the "Service" template; write something specific to this page.']);

    $page['sections'][0]['fields']['heading'] = '';
    eq(page_publish_errors($db, $page), ['Section 1 (Text block): Heading must be written for this page.']);

    $page['sections'][0]['fields']['heading'] = 'GoHighLevel setup';
    eq(page_publish_errors($db, $page), []);
}

function test_duplicate_copies_sections_new_slug_draft(): void
{
    $db = test_db();
    $id = page_save($db, pages_test_page(['status' => 'published', 'template_id' => 3, 'excerpt' => 'Ex']), 1);
    $copy = page_duplicate($db, $id, 'services/demo-2', 2);
    ok($copy !== $id);
    $a = page_find($db, $id);
    $b = page_find($db, $copy);
    eq($b['slug'], 'services/demo-2');
    eq($b['status'], 'draft');
    eq($b['published_at'], null);
    eq($b['sections'], $a['sections']);
    foreach (['title', 'h1', 'meta_title', 'meta_description', 'excerpt', 'template_id', 'kind'] as $k) {
        eq($b[$k], $a[$k], $k);
    }
    pages_expect_invalid(fn() => page_duplicate($db, $id, 'services/demo', 2), 'already used');
    pages_expect_invalid(fn() => page_duplicate($db, 9999, 'x', 2), 'not found');
}

function test_template_roundtrip(): void
{
    $db = test_db();
    $src = page_save($db, pages_test_page(), 1);
    $tid = template_from_page($db, $src, 'Service', ['0.heading', '1.title']);
    $row = $db->query("SELECT * FROM templates WHERE id = $tid")->fetch();
    eq(json_decode($row['unique_fields'], true), ['0.heading', '1.title']);
    eq(json_decode($row['sections'], true), page_find($db, $src)['sections']);
    pages_expect_invalid(fn() => template_from_page($db, $src, 'Service', []), 'already');

    $pid = page_from_template($db, $tid, 'services/other', 'Other', 4);
    $p = page_find($db, $pid);
    eq([$p['slug'], $p['title'], $p['status'], (int) $p['template_id'], $p['kind']], ['services/other', 'Other', 'draft', $tid, 'page']);
    eq($p['sections'], page_find($db, $src)['sections']);
    pages_expect_invalid(fn() => page_from_template($db, 9999, 'x', 'X', 1), 'not found');
}

function test_blog_index_slug_and_post_slugs(): void
{
    $db = test_db();
    $id = page_save($db, pages_test_page(['kind' => 'page', 'slug' => 'blog']), 1);
    eq(page_find($db, $id)['slug'], 'blog');
    page_save($db, pages_test_page(['id' => $id, 'slug' => 'blog', 'title' => 'Blog']), 1); // re-save works
    pages_expect_invalid(fn() => page_save($db, pages_test_page(['kind' => 'page', 'slug' => 'blog/x']), 1), 'not allowed');
    pages_expect_invalid(fn() => page_save($db, pages_test_page(['kind' => 'post', 'slug' => 'blog']), 1), 'not allowed');
    pages_expect_invalid(fn() => page_save($db, pages_test_page(['kind' => 'post', 'slug' => 'news/launch']), 1), "can't contain a slash");
    ok(page_save($db, pages_test_page(['kind' => 'post', 'slug' => 'launch']), 1) > 0);
}
