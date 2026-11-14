<?php
declare(strict_types=1);

/** AI crawlers robots.txt names, so the SEO desk can let them in (the default, for AEO) or shut them out. */
const SEO_AI_BOTS = ['GPTBot', 'OAI-SearchBot', 'ChatGPT-User', 'ClaudeBot', 'Claude-SearchBot', 'anthropic-ai', 'PerplexityBot', 'Google-Extended', 'Applebot-Extended', 'CCBot', 'Bytespider'];

/**
 * Staging and local always block everything. Production: Automatic allows all but /admin and names the AI crawlers as
 * allowed or blocked (setting robots_block_ai); Custom (robots_mode = custom) serves robots_custom, still keeping /admin out and the Sitemap line in.
 */
function robots_txt(string $env, string $baseUrl, array $settings = []): string
{
    if ($env !== 'production') {
        return "User-agent: *\nDisallow: /\n";
    }
    $sitemap = "Sitemap: $baseUrl/sitemap.xml\n";
    if (($settings['robots_mode'] ?? '') === 'custom' && trim($settings['robots_custom'] ?? '') !== '') {
        $txt = trim(str_replace("\r", '', $settings['robots_custom'])) . "\n";
        if (!preg_match('#^\s*Disallow:\s*/admin\b#mi', $txt)) {
            $txt = "User-agent: *\nDisallow: /admin\n\n" . $txt;
        }
        return preg_match('#^\s*Sitemap:#mi', $txt) ? $txt : "$txt\n$sitemap";
    }
    $block = !empty($settings['robots_block_ai']);
    $txt = "User-agent: *\nAllow: /\nDisallow: /admin\n\n";
    foreach (SEO_AI_BOTS as $bot) {
        $txt .= "User-agent: $bot\n" . ($block ? "Disallow: /\n" : "Allow: /\nDisallow: /admin\n") . "\n";
    }
    return $txt . $sitemap;
}

/** Whether a robots.txt text blocks the whole site for some crawler group (a bare "Disallow: /"). */
function robots_blocks_site(string $txt): bool
{
    return (bool) preg_match('#^\s*Disallow:\s*/\s*$#mi', $txt);
}

/** llms.txt: Custom (llms_mode = custom, llms_custom set) as typed; else every live indexable page and post with its description. */
function llms_txt(PDO $db, string $baseUrl, array $settings): string
{
    if (($settings['llms_mode'] ?? '') === 'custom' && trim($settings['llms_custom'] ?? '') !== '') {
        return trim(str_replace("\r", '', $settings['llms_custom'])) . "\n";
    }
    $out = "# Artificia AI\n\n";
    $intro = trim(str_replace("\r", '', $settings['llms_intro'] ?? ''));
    if ($intro !== '') {
        $out .= '> ' . str_replace("\n", "\n> ", $intro) . "\n\n";
    }
    $rows = $db->query("SELECT slug, kind, title, meta_title, meta_description, excerpt, noindex FROM pages WHERE status = 'published' ORDER BY kind, slug = 'home' DESC, slug")->fetchAll();
    foreach (['page' => 'Pages', 'post' => 'Blog'] as $kind => $heading) {
        $lines = '';
        foreach ($rows as $r) {
            if ($r['kind'] !== $kind || page_noindex($db, $r)) {
                continue;
            }
            $desc = trim(heading_plain($r['meta_description'] ?: strip_tags((string) $r['excerpt'])));
            $lines .= '- [' . trim(heading_plain($r['meta_title'] ?: $r['title'])) . '](' . $baseUrl . page_path($r) . ')' . ($desc !== '' ? ": $desc" : '') . "\n";
        }
        $out .= $lines !== '' ? "## $heading\n\n$lines\n" : '';
    }
    $extra = trim(str_replace("\r", '', $settings['llms_extra'] ?? ''));
    return rtrim($out . ($extra !== '' ? "$extra\n" : '')) . "\n";
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

/** Starting points for a page's custom JSON-LD box. Every "Replace ..." value must be rewritten before it saves. */
const SCHEMA_PRESETS = [
    'Service' => ['@type' => 'Service', 'name' => 'Replace with the service name', 'description' => 'Replace with one sentence', 'serviceType' => 'Replace, e.g. AI automation', 'areaServed' => 'IN', 'provider' => ['@type' => 'Organization', 'name' => 'Artificia AI']],
    'Course' => ['@type' => 'Course', 'name' => 'Replace with the course name', 'description' => 'Replace with one sentence', 'provider' => ['@type' => 'Organization', 'name' => 'Artificia AI'], 'hasCourseInstance' => ['@type' => 'CourseInstance', 'courseMode' => 'Online', 'courseWorkload' => 'Replace, e.g. PT30H']],
    'Event' => ['@type' => 'Event', 'name' => 'Replace with the event name', 'startDate' => 'Replace, e.g. 2026-12-01T10:00+05:30', 'eventAttendanceMode' => 'https://schema.org/OnlineEventAttendanceMode', 'location' => ['@type' => 'VirtualLocation', 'url' => 'Replace with the joining page'], 'organizer' => ['@type' => 'Organization', 'name' => 'Artificia AI']],
    'HowTo' => ['@type' => 'HowTo', 'name' => 'Replace with the task', 'step' => [['@type' => 'HowToStep', 'name' => 'Replace with step 1', 'text' => 'Replace with what to do']]],
    'VideoObject' => ['@type' => 'VideoObject', 'name' => 'Replace with the video title', 'description' => 'Replace with one sentence', 'thumbnailUrl' => 'Replace with an image address', 'uploadDate' => 'Replace, e.g. 2026-11-01', 'contentUrl' => 'Replace with the video address'],
    'FAQPage' => ['@type' => 'FAQPage', 'mainEntity' => [['@type' => 'Question', 'name' => 'Replace with the question', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Replace with the answer']]]],
    'LocalBusiness' => ['@type' => 'LocalBusiness', 'name' => 'Artificia AI', 'address' => ['@type' => 'PostalAddress', 'addressLocality' => 'Vadodara', 'addressRegion' => 'Gujarat', 'addressCountry' => 'IN']],
];

/**
 * A page's custom JSON-LD box, checked and pretty-printed; null when empty. Accepts one node, a list of nodes or {"@graph": [...]}.
 * @throws InvalidArgumentException with an editor-readable message
 */
function schema_custom_clean(string $json): ?string
{
    if (trim($json) === '') {
        return null;
    }
    try {
        $data = json_decode($json, true, 32, JSON_THROW_ON_ERROR);
    } catch (JsonException $e) {
        throw new InvalidArgumentException('The custom schema is not valid JSON (' . $e->getMessage() . '). Check for a missing comma or quote.');
    }
    $nodes = is_array($data) ? schema_custom_nodes($data) : [];
    if (!$nodes) {
        throw new InvalidArgumentException('The custom schema must be a JSON object, or a list of them, each with an "@type".');
    }
    foreach ($nodes as $node) {
        if (!is_array($node) || empty($node['@type'])) {
            throw new InvalidArgumentException('Every block in the custom schema needs an "@type".');
        }
        if (str_contains(json_encode($node), '"Replace')) {
            throw new InvalidArgumentException('The custom schema still has a "Replace ..." placeholder. Write the real value or remove that line.');
        }
    }
    return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

/** The nodes of decoded custom JSON-LD, without @context. */
function schema_custom_nodes(array $data): array
{
    $nodes = isset($data['@graph']) && is_array($data['@graph']) ? $data['@graph'] : (array_is_list($data) ? $data : [$data]);
    return array_map(fn($n) => is_array($n) ? array_diff_key($n, ['@context' => 1]) : $n, $nodes);
}

/** Everything a page serves as JSON-LD: the automatic nodes (unless switched off) plus its custom ones. */
function schema_for_page(array $page, array $settings, string $baseUrl): array
{
    $graph = ($page['schema_auto'] ?? 1) ? schema_graph($page, $settings, $baseUrl) : [];
    $custom = trim((string) ($page['schema_custom'] ?? ''));
    $data = $custom !== '' ? json_decode($custom, true) : null;
    return is_array($data) ? array_merge($graph, schema_custom_nodes($data)) : $graph;
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
    if (preg_match('#^/(services/|corporate-ai-training)#', page_path($page))) {
        $graph[] = ['@type' => 'Service', 'name' => $title, 'description' => $page['meta_description'] ?? '', 'url' => $url, 'provider' => $provider];
    }
    return $graph;
}

function schema_organization(array $settings, string $baseUrl): array
{
    $address = preg_replace('/\s*\n\s*/', ', ', trim($settings['org_address'] ?? ''));
    preg_match('/\b\d{6}\b/', $address, $pin);
    $latLng = preg_match('/^(-?[\d.]+),\s*(-?[\d.]+)$/', $settings['org_map_query'] ?? '', $m) ? $m : null;
    return array_filter([
        '@type' => ['EducationalOrganization', 'LocalBusiness'],
        'name' => 'Artificia AI',
        'url' => "$baseUrl/",
        'logo' => "$baseUrl/assets/img/artificia-logo.png",
        'image' => "$baseUrl/assets/img/og-default.jpg",
        'address' => array_filter([
            '@type' => 'PostalAddress', 'streetAddress' => $address, 'addressLocality' => 'Vadodara',
            'addressRegion' => 'Gujarat', 'postalCode' => $pin[0] ?? '', 'addressCountry' => 'IN',
        ]),
        'geo' => $latLng ? ['@type' => 'GeoCoordinates', 'latitude' => (float) $latLng[1], 'longitude' => (float) $latLng[2]] : null,
        'telephone' => $settings['org_phone'] ?? '',
        'email' => $settings['org_email'] ?? '',
        'sameAs' => array_values(array_filter([$settings['gbp_url'] ?? ''])),
    ]);
}
