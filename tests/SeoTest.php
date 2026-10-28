<?php
declare(strict_types=1);

const SEO_BASE = 'https://example.test';

function seo_node(array $graph, string $type): ?array
{
    foreach ($graph as $n) {
        if (in_array($type, (array) $n['@type'], true)) {
            return $n;
        }
    }
    return null;
}

function seo_page(array $over = []): array
{
    return $over + [
        'kind' => 'page', 'slug' => 'masterclass', 'title' => 'AI *Masterclass*', 'h1' => '', 'meta_title' => '',
        'meta_description' => 'Learn AI', 'og_image_id' => null, 'excerpt' => null, 'sections' => [],
        'published_at' => null, 'updated_at' => '2026-10-01 10:00:00',
    ];
}

function test_sitemap_lists_only_published(): void
{
    $db = test_db();
    $ins = $db->prepare('INSERT INTO pages (kind, slug, title, sections, status) VALUES (?, ?, ?, ?, ?)');
    $ins->execute(['page', 'home', 'Home', '[]', 'published']);
    $ins->execute(['page', 'masterclass', 'M', '[]', 'published']);
    $ins->execute(['post', 'hello-post', 'P', '[]', 'published']);
    $ins->execute(['post', 'secret-post', 'D', '[]', 'draft']);
    $ins->execute(['page', 'secret-page', 'D', '[]', 'draft']);
    $xml = sitemap_xml($db, SEO_BASE);
    ok(str_contains($xml, '<loc>https://example.test/</loc>'));
    ok(str_contains($xml, '<loc>https://example.test/masterclass</loc>'));
    ok(str_contains($xml, '<loc>https://example.test/blog/hello-post</loc>'));
    ok(!str_contains($xml, 'secret'), 'drafts are excluded');
    ok(!str_contains($xml, '/home<'), 'home is only listed as /');
    ok(simplexml_load_string($xml) !== false, 'well-formed XML');
}

function test_robots_staging_disallows_all(): void
{
    ok(str_contains(robots_txt('staging', SEO_BASE), "Disallow: /\n"));
    ok(!str_contains(robots_txt('staging', SEO_BASE), 'Sitemap'));
    $prod = robots_txt('production', SEO_BASE);
    ok(!preg_match('#^Disallow: /$#m', $prod), 'production does not block the site');
    ok(str_contains($prod, "Allow: /\n"));
    ok(str_contains($prod, 'Sitemap: https://example.test/sitemap.xml'));
}

function test_schema_faq_from_faqs_section(): void
{
    $graph = schema_graph(seo_page(['sections' => [
        ['type' => 'faqs', 'fields' => ['heading' => 'FAQ', 'items' => [
            ['question' => 'Who is it for?', 'answer' => 'Everyone'],
            ['question' => 'How long?', 'answer' => '30 days'],
        ]]],
    ]]), [], SEO_BASE);
    $faq = seo_node($graph, 'FAQPage');
    eq(count($faq['mainEntity']), 2);
    eq($faq['mainEntity'][0]['@type'], 'Question');
    eq($faq['mainEntity'][1]['acceptedAnswer']['text'], '30 days');
    eq(seo_node(schema_graph(seo_page(), [], SEO_BASE), 'FAQPage'), null);
}

function test_schema_course_when_curriculum(): void
{
    $graph = schema_graph(seo_page(['sections' => [
        ['type' => 'curriculum', 'fields' => ['heading' => 'Plan', 'modules' => []]],
    ]]), [], SEO_BASE);
    $course = seo_node($graph, 'Course');
    eq($course['name'], 'AI Masterclass');
    eq($course['provider']['name'], 'Artificia AI');
    ok(!str_contains(json_encode($graph), 'price'), 'no price anywhere');
    ok(!isset($course['offers']));
}

function test_schema_home_has_org_and_local_business(): void
{
    $settings = [
        'org_address' => 'Alkapuri, Vadodara', 'org_phone' => '+91 91044 88859', 'org_email' => 'hello@example.test',
        'gbp_url' => 'https://g.page/artificia',
    ];
    $graph = schema_graph(seo_page(['slug' => 'home', 'title' => 'Home']), $settings, SEO_BASE);
    $org = seo_node($graph, 'EducationalOrganization');
    ok(in_array('LocalBusiness', $org['@type'], true));
    eq($org['name'], 'Artificia AI');
    eq($org['url'], SEO_BASE . '/');
    eq($org['address'], [
        '@type' => 'PostalAddress', 'streetAddress' => 'Alkapuri, Vadodara', 'addressLocality' => 'Vadodara',
        'addressRegion' => 'Gujarat', 'addressCountry' => 'IN',
    ]);
    eq($org['telephone'], '+91 91044 88859');
    eq($org['email'], 'hello@example.test');
    ok(in_array('https://g.page/artificia', $org['sameAs'], true));
    eq(seo_node(schema_graph(seo_page(), $settings, SEO_BASE), 'EducationalOrganization'), null, 'org is home only');
    $bare = seo_node(schema_graph(seo_page(['slug' => 'home']), [], SEO_BASE), 'EducationalOrganization');
    ok(!isset($bare['telephone']) && !isset($bare['sameAs']), 'unset settings are omitted');
}

function test_schema_article_for_posts(): void
{
    $graph = schema_graph(seo_page([
        'kind' => 'post', 'slug' => 'hello', 'title' => 'Hello *world*', 'published_at' => '2026-09-01 09:30:00',
    ]), [], SEO_BASE);
    $a = seo_node($graph, 'Article');
    eq($a['headline'], 'Hello world');
    eq($a['mainEntityOfPage'], SEO_BASE . '/blog/hello');
    ok(str_starts_with($a['datePublished'], '2026-09-01T09:30:00'));
    eq($a['publisher']['name'], 'Artificia AI');
}

function test_blog_index_is_noindex_only_while_no_post_is_published(): void
{
    $db = test_db();
    $ins = $db->prepare('INSERT INTO pages (kind, slug, title, sections, status, noindex) VALUES (?, ?, ?, ?, ?, ?)');
    $ins->execute(['page', 'blog', 'Blog', '[]', 'published', 0]);
    $ins->execute(['page', 'hidden-page', 'H', '[]', 'published', 1]);
    $ins->execute(['page', 'shown-page', 'S', '[]', 'published', 0]);
    $ins->execute(['post', 'draft-post', 'D', '[]', 'draft', 0]);
    $robots = fn(string $slug) => str_contains(render_page(page_find_published($db, $slug), []), '<meta name="robots" content="noindex">');
    $prev = db_swap($db);
    try {
        ok($robots('blog'), 'no published post: noindex, though the stored flag says index');
        ok(!str_contains(sitemap_xml($db, SEO_BASE), '<loc>https://example.test/blog</loc>'), 'and left out of the sitemap');
        $db->exec("UPDATE pages SET noindex = 1 WHERE slug = 'blog'");
        $ins->execute(['post', 'first-post', 'P', '[]', 'published', 0]);
        ok(!$robots('blog'), 'a published post: indexed, though the stored flag says noindex');
        $xml = sitemap_xml($db, SEO_BASE);
        ok(str_contains($xml, '<loc>https://example.test/blog</loc>') && str_contains($xml, '/blog/first-post'), 'and in the sitemap');
        ok($robots('hidden-page') && !$robots('shown-page'), 'other pages keep their stored flag');
        ok(!str_contains($xml, 'hidden-page') && str_contains($xml, 'shown-page'));
    } finally {
        db_swap($prev);
    }
}
