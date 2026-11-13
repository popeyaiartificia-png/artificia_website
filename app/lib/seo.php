<?php
declare(strict_types=1);

function robots_txt(string $env, string $baseUrl): string
{
    if ($env !== 'production') {
        return "User-agent: *\nDisallow: /\n";
    }
    return "User-agent: *\nAllow: /\n\nSitemap: $baseUrl/sitemap.xml\n";
}

function sitemap_xml(PDO $db, string $baseUrl): string
{
    $rows = $db->query("SELECT slug, kind, noindex, updated_at FROM pages WHERE status = 'published' ORDER BY kind, slug")->fetchAll();
    $rows = array_filter($rows, fn($r) => !page_noindex($db, $r));
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($rows as $r) {
        $xml .= '<url><loc>' . e($baseUrl . page_path($r)) . '</loc><lastmod>' . date('Y-m-d', strtotime($r['updated_at'])) . "</lastmod></url>\n";
    }
    return $xml . "</urlset>\n";
}

/** JSON-LD `@graph` nodes for a page. */
function schema_graph(array $page, array $settings, string $baseUrl): array
{
    $url = $baseUrl . page_path($page);
    $title = heading_plain($page['title']);
    $provider = ['@type' => 'Organization', 'name' => 'Artificia AI', 'url' => "$baseUrl/"];
    $graph = [];
    if (in_array($page['sections'][0]['fields']['variant'] ?? '', ['course', 'solutions'], true)) {
        $graph[] = ['@type' => 'BreadcrumbList', 'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => "$baseUrl/"],
            ['@type' => 'ListItem', 'position' => 2, 'name' => $title, 'item' => $url],
        ]];
    }

    if ($page['slug'] === 'home') {
        $graph[] = schema_organization($settings, $baseUrl);
        $graph[] = ['@type' => 'WebSite', 'name' => 'Artificia AI', 'url' => "$baseUrl/"];
    }
    if ($page['kind'] === 'post') {
        $published = $page['published_at'] ?? $page['updated_at'] ?? null;
        $graph[] = array_filter([
            '@type' => 'Article',
            'headline' => $title,
            'mainEntityOfPage' => $url,
            'datePublished' => $published ? date('c', strtotime($published)) : null,
            'dateModified' => !empty($page['updated_at']) ? date('c', strtotime($page['updated_at'])) : null,
            'author' => $provider,
            'publisher' => $provider,
        ]);
    }

    $faqs = $course = false;
    $questions = [];
    foreach ($page['sections'] as $section) {
        if ($section['type'] === 'faqs') {
            foreach ($section['fields']['items'] ?? [] as $item) {
                $questions[] = ['@type' => 'Question', 'name' => $item['question'] ?? '',
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['answer'] ?? '']];
            }
        }
        $course = $course || $section['type'] === 'curriculum';
    }
    if ($questions) {
        $graph[] = ['@type' => 'FAQPage', 'mainEntity' => $questions];
    }
    if ($course) {
        $graph[] = ['@type' => 'Course', 'name' => $title, 'description' => $page['meta_description'] ?? '', 'url' => $url, 'provider' => $provider];
    }
    return $graph;
}

function schema_organization(array $settings, string $baseUrl): array
{
    $address = $settings['org_address'] ?? '';
    return array_filter([
        '@type' => ['EducationalOrganization', 'LocalBusiness'],
        'name' => 'Artificia AI',
        'url' => "$baseUrl/",
        'address' => array_filter([
            '@type' => 'PostalAddress', 'streetAddress' => $address, 'addressLocality' => 'Vadodara',
            'addressRegion' => 'Gujarat', 'addressCountry' => 'IN',
        ]),
        'telephone' => $settings['org_phone'] ?? '',
        'email' => $settings['org_email'] ?? '',
        'sameAs' => array_values(array_filter([$settings['gbp_url'] ?? ''])),
    ]);
}
