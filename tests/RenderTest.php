<?php
declare(strict_types=1);

function render_test_page(array $over = []): array
{
    return $over + [
        'kind' => 'page', 'slug' => 'about', 'title' => 'About us', 'h1' => '', 'meta_title' => '',
        'meta_description' => 'Who we are', 'og_image_id' => null, 'excerpt' => null, 'sections' => [],
        'published_at' => null, 'updated_at' => '2026-10-01 10:00:00',
    ];
}

function test_single_h1_with_hero(): void
{
    $html = render_page(render_test_page(['h1' => 'Learn *AI* properly', 'sections' => [
        ['type' => 'hero', 'fields' => ['subheading' => 'Hands-on']],
        ['type' => 'rich_text', 'fields' => ['heading' => 'Why', 'body' => '<p>Because</p>']],
    ]]), []);
    eq(substr_count($html, '<h1'), 1);
    ok(str_contains($html, '<h1>Learn <em>AI</em> properly</h1>'), 'hero renders the page h1');
    ok(str_contains($html, '<h2>Why</h2>'));
}

function test_single_h1_without_hero(): void
{
    $html = render_page(render_test_page(['sections' => [
        ['type' => 'rich_text', 'fields' => ['heading' => 'Why', 'body' => '<p>Because</p>']],
        ['type' => 'cta', 'fields' => ['heading' => 'Join', 'button_label' => 'Go', 'button_link' => '/x']],
    ]]), []);
    eq(substr_count($html, '<h1'), 1);
    ok(str_contains($html, '<h1>About us</h1>'), 'falls back to the title');
    ok(strpos($html, '<h1') < strpos($html, '<h2'), 'h1 comes first');
}

function test_every_section_type_renders_without_fields(): void
{
    foreach (array_keys(section_registry()) as $type) {
        $html = render_section(['type' => $type, 'fields' => []]);
        ok(str_contains($html, "sec--$type"), "$type renders its wrapper");
    }
    eq(render_section(['type' => 'nope', 'fields' => []]), '');
    eq(render_section(['type' => '../registry', 'fields' => []]), '');
}

function test_unpublished_page_404s(): void
{
    $db = test_db();
    $ins = $db->prepare("INSERT INTO pages (slug, title, sections, status) VALUES (?, 'T', '[]', ?)");
    $ins->execute(['draft-page', 'draft']);
    $ins->execute(['live-page', 'published']);
    eq(page_find_published($db, 'draft-page'), null);
    eq(page_find_published($db, 'missing'), null);
    $page = page_find_published($db, 'live-page');
    eq($page['title'], 'T');
    eq($page['sections'], []);
}

function test_heading_emphasis(): void
{
    eq(heading_html('Learn *AI* <b>now</b>'), 'Learn <em>AI</em> &lt;b&gt;now&lt;/b&gt;');
    eq(heading_html('a * b'), 'a * b');
    eq(heading_html("*one\ntwo*"), "*one\ntwo*");
    eq(heading_plain('Learn *AI* <b>now</b>'), 'Learn AI <b>now</b>');
}

function test_head_tags_use_plain_headings_and_settings(): void
{
    $html = render_page(render_test_page(['title' => 'Learn *AI*', 'meta_title' => 'Meta *Title*', 'slug' => 'home']), [
        'ga4_id' => 'G-TEST123', 'meta_pixel_id' => '12345', 'gsc_verification' => 'tok"en',
    ]);
    ok(str_contains($html, '<title>Meta Title</title>'));
    ok(str_contains($html, '<meta name="description" content="Who we are">'));
    ok(str_contains($html, '<link rel="canonical" href="' . config()['base_url'] . '/">'));
    ok(str_contains($html, 'googletagmanager.com/gtag/js?id=G-TEST123'));
    ok(str_contains($html, 'fbq(\'init\', \'12345\')'));
    ok(str_contains($html, '<meta name="google-site-verification" content="tok&quot;en">'));
    ok(str_contains($html, '<html lang="en">'));
    ok(!str_contains($html, 'Meta *Title*'));
    $bare = render_page(render_test_page(), []);
    ok(!str_contains($bare, 'gtag') && !str_contains($bare, 'fbq') && !str_contains($bare, 'google-site-verification'));
}

function test_jsonld_cannot_break_out_of_script(): void
{
    $html = render_page(render_test_page(['sections' => [
        ['type' => 'faqs', 'fields' => ['heading' => 'Q', 'items' => [['question' => '</script><b>x', 'answer' => 'a']]]],
    ]]), []);
    ok(!str_contains($html, '</script><b>'));
    ok(str_contains($html, 'application/ld+json'));
}

function test_rich_text_is_sanitized_at_render(): void
{
    $html = render_section(['type' => 'rich_text', 'fields' => ['body' => '<p>ok</p><script>alert(1)</script>']]);
    ok(str_contains($html, '<p>ok</p>') && !str_contains($html, 'script'));
}

function test_staging_guard_credentials(): void
{
    $auth = ['user' => 'staging', 'pass' => 's3cret'];
    ok(staging_authorized(['PHP_AUTH_USER' => 'staging', 'PHP_AUTH_PW' => 's3cret'], $auth));
    ok(staging_authorized(['HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('staging:s3cret')], $auth));
    ok(staging_authorized(['REDIRECT_HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('staging:s3cret')], $auth));
    ok(!staging_authorized(['PHP_AUTH_USER' => 'staging', 'PHP_AUTH_PW' => 'nope'], $auth));
    ok(!staging_authorized(['HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('staging:')], $auth));
    ok(!staging_authorized([], $auth));
    ok(!staging_authorized(['PHP_AUTH_USER' => '', 'PHP_AUTH_PW' => ''], ['user' => '', 'pass' => '']), 'empty config never opens the site');
}
