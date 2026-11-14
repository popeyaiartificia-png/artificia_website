<?php
declare(strict_types=1);

// Package A, the SEO desk: robots.txt / llms.txt settings, custom schema, canonical, focus keyword, Meta manager saves.
function_exists('chk_page') || require __DIR__ . '/SeoChecksTest.php'; // chk_page(), chk_issue()

function test_robots_automatic_and_ai_switch(): void
{
    $auto = robots_txt('production', 'https://example.test');
    ok(str_contains($auto, "User-agent: GPTBot\nAllow: /"), 'AI crawlers allowed by default');
    ok(str_contains($auto, "Disallow: /admin\n"));
    ok(!robots_blocks_site($auto));
    $blocked = robots_txt('production', 'https://example.test', ['robots_block_ai' => '1']);
    ok(str_contains($blocked, "User-agent: ClaudeBot\nDisallow: /\n"));
    ok(preg_match('#^User-agent: \*\nAllow: /#', $blocked) === 1, 'everyone else still allowed');
    eq(robots_txt('staging', 'https://example.test', ['robots_mode' => 'custom', 'robots_custom' => 'User-agent: *\nAllow: /']), "User-agent: *\nDisallow: /\n", 'staging ignores settings');
}

function test_robots_custom_keeps_admin_out_and_sitemap_in(): void
{
    $txt = robots_txt('production', 'https://example.test', ['robots_mode' => 'custom', 'robots_custom' => "User-agent: *\r\nAllow: /"]);
    ok(str_contains($txt, 'Disallow: /admin'));
    ok(str_contains($txt, 'Sitemap: https://example.test/sitemap.xml'));
    [, $errors] = seo_files_check(['robots_mode' => 'custom', 'robots_custom' => "User-agent: *\nDisallow: /"]);
    ok(isset($errors['robots_custom']), 'Disallow: / refused without the tick');
    [$v, $errors] = seo_files_check(['robots_mode' => 'custom', 'robots_custom' => "User-agent: *\nDisallow: /", 'robots_confirm' => '1']);
    eq($errors, []);
    eq($v['robots_mode'], 'custom');
    ok(!robots_blocks_site("User-agent: *\nDisallow: /admin"), '/admin is not the whole site');
}

function test_llms_lists_live_indexable_pages(): void
{
    $db = test_db();
    $ins = $db->prepare('INSERT INTO pages (kind, slug, title, meta_title, meta_description, sections, status, noindex) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $ins->execute(['page', 'home', 'Home', 'Artificia AI', 'AI training in Vadodara', '[]', 'published', 0]);
    $ins->execute(['page', 'services', 'Services', '', 'What we build', '[]', 'published', 0]);
    $ins->execute(['page', 'hidden', 'Hidden', '', '', '[]', 'published', 1]);
    $ins->execute(['page', 'draft', 'Draft', '', '', '[]', 'draft', 0]);
    $ins->execute(['post', 'first', 'First *post*', '', 'Notes', '[]', 'published', 0]);
    $txt = llms_txt($db, 'https://example.test', ['llms_intro' => 'We teach AI.']);
    ok(str_starts_with($txt, "# Artificia AI\n\n> We teach AI.\n\n## Pages\n\n- [Artificia AI](https://example.test/): AI training in Vadodara\n"), $txt);
    ok(str_contains($txt, '- [Services](https://example.test/services): What we build'));
    ok(str_contains($txt, "## Blog\n\n- [First post](https://example.test/blog/first): Notes"));
    ok(!str_contains($txt, 'hidden') && !str_contains($txt, 'draft'));
    eq(llms_txt($db, 'https://example.test', ['llms_mode' => 'custom', 'llms_custom' => "# Mine\r\n"]), "# Mine\n");
}

function test_custom_schema_checked_and_merged(): void
{
    foreach (['{bad', '"text"', '{"name": "x"}', '[{"@type": "Service", "name": "Replace with the service name"}]'] as $bad) {
        try {
            schema_custom_clean($bad);
            ok(false, "accepted $bad");
        } catch (InvalidArgumentException) {
        }
    }
    eq(schema_custom_clean('  '), null);
    $json = schema_custom_clean('{"@context": "https://schema.org", "@type": "Service", "name": "AI automation"}');
    $page = ['kind' => 'page', 'slug' => 'services', 'title' => 'Services', 'meta_description' => '', 'sections' => [
        ['type' => 'faqs', 'fields' => ['items' => [['question' => 'Q?', 'answer' => 'A']]]],
    ], 'schema_custom' => $json];
    $types = fn(array $g) => array_map(fn($n) => $n['@type'], $g);
    eq($types(schema_for_page($page, [], 'https://example.test')), ['FAQPage', 'Service']);
    eq($types(schema_for_page(['schema_auto' => 0] + $page, [], 'https://example.test')), ['Service'], 'automatic off');
    ok(!isset(schema_for_page($page, [], 'https://example.test')[1]['@context']), '@context only once, at the top');
    foreach (SCHEMA_PRESETS as $name => $node) {
        ok(!empty($node['@type']), "$name preset has a type");
    }
}

function test_canonical_and_focus_keyword_saved_and_checked(): void
{
    $db = test_db();
    $base = ['slug' => 'masterclass', 'title' => 'AI Masterclass', 'status' => 'draft'];
    try {
        page_save($db, $base + ['canonical' => 'javascript:alert(1)'], 1);
        ok(false, 'bad canonical accepted');
    } catch (InvalidArgumentException $e) {
        ok(str_starts_with($e->getMessage(), 'The canonical'));
    }
    $id = page_save($db, $base + ['canonical' => '/ai-masterclass', 'focus_keyword' => 'AI course', 'meta_title' => 'AI Masterclass', 'h1' => 'Learn AI'], 1);
    $page = page_find($db, $id);
    eq(page_canonical($page, 'https://example.test'), 'https://example.test/ai-masterclass');
    eq(page_canonical(['canonical' => ''] + $page, 'https://example.test'), 'https://example.test/masterclass');
    eq((int) $page['schema_auto'], 1, 'automatic schema on for new pages');
    $msg = chk_issue($db, 'masterclass', 'keyword');
    ok($msg !== null && str_contains($msg, 'title or H1 or description'), (string) $msg);
}

function test_meta_manager_saves_changed_rows_and_keeps_publish_rules(): void
{
    $db = test_db();
    $live = chk_page($db, 'live-page');
    $draft = page_save($db, ['slug' => 'draft-page', 'title' => 'Draft', 'status' => 'draft'], 1);
    $revs = fn() => (int) $db->query('SELECT COUNT(*) FROM page_revisions')->fetchColumn();
    $before = $revs();
    [$saved, $errors] = meta_save($db, [
        $live => ['focus_keyword' => 'live page', 'meta_title' => page_find($db, $live)['meta_title'], 'h1' => 'Heading live-page', 'meta_description' => page_find($db, $live)['meta_description']],
        $draft => ['focus_keyword' => '', 'meta_title' => '', 'h1' => '', 'meta_description' => '', 'noindex' => '1'],
    ], 1);
    eq([$saved, $errors], [2, []]);
    eq(page_find($db, $live)['focus_keyword'], 'live page');
    eq((int) page_find($db, $draft)['noindex'], 1);
    eq($revs(), $before + 2, 'one revision per saved page');
    [$saved, $errors] = meta_save($db, [$live => ['meta_description' => '', 'focus_keyword' => 'live page'], $draft => ['noindex' => '1']], 1);
    eq($saved, 0, 'unchanged draft is skipped; emptied live row refused');
    ok(str_contains($errors[$live] ?? '', 'meta description'));
    ok(page_find($db, $live)['meta_description'] !== '', 'live page kept its description');
}
