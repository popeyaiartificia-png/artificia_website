<?php
// Front end: the PHP site's routes, pages rendered through its layout, and WordPress kept from adding anything visitors would see.
declare(strict_types=1);

require __DIR__ . '/redirects.php';

/** Runs $fn on the PHP site's clock: its templates, lead times and database session use Asia/Kolkata; WordPress keeps PHP on UTC. */
function artificia_ist(callable $fn): mixed
{
    $tz = date_default_timezone_get();
    date_default_timezone_set('Asia/Kolkata');
    try {
        return $fn();
    } finally {
        date_default_timezone_set($tz);
    }
}

/** The sections of a post's content: each top-level artificia/* block, its rich-text field rebuilt from the blocks inside it. */
function artificia_sections(string $content): array
{
    $sections = [];
    foreach (parse_blocks($content) as $block) {
        $type = artificia_block_type($block['blockName']);
        if ($type === '') {
            continue;
        }
        $fields = is_array($block['attrs']['fields'] ?? null) ? $block['attrs']['fields'] : [];
        $rich = artificia_rich_field($type);
        if ($rich !== '') {
            $fields[$rich] = implode('', array_map('render_block', $block['innerBlocks']));
        }
        $sections[] = ['type' => $type, 'fields' => $fields];
    }
    return sections_normalize($sections); // the PHP site's trust boundary for editor input: known fields, clean HTML, valid options
}

/** A page or post as the PHP site's page array, which its templates, schema and SEO code read. */
function artificia_page(WP_Post $post): array
{
    $meta = fn(string $key) => get_post_meta($post->ID, "_artificia_$key", true);
    $page = $post->post_type === 'page';
    return [
        'id' => $post->ID,
        'kind' => $page ? 'page' : 'post',
        'slug' => $page ? ((int) get_option('page_on_front') === $post->ID ? 'home' : get_page_uri($post)) : $post->post_name,
        'title' => $post->post_title,
        'h1' => (string) $meta('h1'),
        'meta_title' => (string) $meta('meta_title'),
        'meta_description' => (string) $meta('meta_description'),
        'focus_keyword' => (string) $meta('focus_keyword'),
        'canonical' => (string) $meta('canonical'),
        'og_image_id' => (int) $meta('og_image') ?: (int) get_post_thumbnail_id($post) ?: null,
        'excerpt' => $post->post_excerpt,
        'noindex' => (bool) $meta('noindex'),
        'schema_auto' => (bool) $meta('schema_auto'),
        'schema_custom' => (string) $meta('schema_custom'),
        'status' => $post->post_status === 'publish' ? 'published' : 'draft',
        'published_at' => $post->post_date,
        'updated_at' => $post->post_modified,
        'sections' => artificia_sections($post->post_content),
    ];
}

/** pages.php page_noindex(): the stored flag, except the blog index, which is hidden only while no post is published. */
function artificia_noindex(array $page): bool
{
    return $page['kind'] === 'page' && $page['slug'] === 'blog' ? !posts_recent(null, 1) : $page['noindex'];
}

/** render.php render_page(), reading WordPress. */
function artificia_render_page(array $page, array $settings): string
{
    $base = config()['base_url'];
    $og = $page['og_image_id'] ? artificia_media((int) $page['og_image_id']) : null;
    $ogUrl = $og ? (array_filter($og['urls'], fn(int $w) => $w <= 1600, ARRAY_FILTER_USE_KEY) ?: $og['urls']) : [];
    return render_layout([
        'settings' => $settings,
        'body' => render_page_body($page, $settings),
        'title' => heading_plain($page['meta_title'] ?: $page['title']),
        'description' => heading_plain($page['meta_description'] ?: strip_tags((string) $page['excerpt'])),
        'canonical' => page_canonical($page, $base),
        'robots' => artificia_noindex($page) ? 'noindex' : '',
        'ogImage' => $base . ($ogUrl ? end($ogUrl) : '/assets/img/og-default.jpg'),
        'ogType' => $page['kind'] === 'post' ? 'article' : 'website',
        'jsonld' => schema_for_page($page, $settings, $base),
        'hasForm' => page_has_form($page),
    ]);
}

/** Sends a page or post with the PHP site's headers, or its 404 page when there is none. */
function artificia_send(?WP_Post $post): void
{
    header('Content-Type: text/html; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-Frame-Options: SAMEORIGIN');
    if (!get_option('blog_public')) { // staging: Settings → Reading → "Discourage search engines"
        header('X-Robots-Tag: noindex, nofollow');
    }
    $settings = settings();
    if (!$post) {
        status_header(404);
        header_remove('Cache-Control'); // WordPress's no-cache headers: the PHP site sent none with its 404
        header_remove('Expires');
        echo artificia_ist(fn() => render_404($settings));
        return;
    }
    $page = artificia_page($post);
    if (page_has_form($page)) {
        header('Cache-Control: no-store'); // a fresh form token on every view
    }
    echo artificia_ist(fn() => artificia_render_page($page, $settings));
}

/** The published pages and posts search engines may index, in the PHP site's sitemap order (kind, then slug). */
function artificia_indexable(): array
{
    $rows = [];
    foreach (get_posts(['post_type' => ['page', 'post'], 'post_status' => 'publish', 'numberposts' => -1]) as $post) {
        $page = artificia_page($post);
        if (!artificia_noindex($page)) {
            $rows[] = $page;
        }
    }
    usort($rows, fn(array $a, array $b) => [$a['kind'], $a['slug']] <=> [$b['kind'], $b['slug']]);
    return $rows;
}

/** The PHP site's leads and lead_rate tables (db/migrations 001 + 003), unprefixed so its lead code runs on them unchanged. */
function artificia_tables(PDO $db): void
{
    $db->exec("CREATE TABLE IF NOT EXISTS leads (
      id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, event_id CHAR(36) NOT NULL UNIQUE, form_type VARCHAR(50) NOT NULL,
      name VARCHAR(255) NOT NULL DEFAULT '', email VARCHAR(255) NOT NULL DEFAULT '', phone VARCHAR(50) NOT NULL DEFAULT '',
      company VARCHAR(255) NOT NULL DEFAULT '', fields JSON NULL, page_url VARCHAR(1000) NOT NULL DEFAULT '', referrer VARCHAR(1000) NOT NULL DEFAULT '',
      utm_source VARCHAR(255) NOT NULL DEFAULT '', utm_medium VARCHAR(255) NOT NULL DEFAULT '', utm_campaign VARCHAR(255) NOT NULL DEFAULT '',
      utm_term VARCHAR(255) NOT NULL DEFAULT '', utm_content VARCHAR(255) NOT NULL DEFAULT '', gclid VARCHAR(255) NOT NULL DEFAULT '',
      fbclid VARCHAR(255) NOT NULL DEFAULT '', fbp VARCHAR(255) NOT NULL DEFAULT '', fbc VARCHAR(255) NOT NULL DEFAULT '',
      ip VARCHAR(45) NOT NULL DEFAULT '', user_agent VARCHAR(500) NOT NULL DEFAULT '', consent_at DATETIME NULL,
      spam TINYINT NOT NULL DEFAULT 0, spam_reason VARCHAR(100) NULL, forwarded_at DATETIME NULL, forward_error TEXT NULL,
      emailed_at DATETIME NULL, capi_sent_at DATETIME NULL, capi_error TEXT NULL,
      created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, INDEX (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $db->exec("CREATE TABLE IF NOT EXISTS lead_rate (
      id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, ip VARCHAR(45) NOT NULL,
      created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, INDEX (ip, created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

// The PHP site's own routes, answered before WordPress looks for a page.
add_action('init', function (): void {
    if (PHP_SAPI !== 'cli') {
        $canonical = canonical_redirect(config(), $_SERVER);
        if ($canonical !== null) {
            redirect($canonical, 301);
        }
    }
    $path = request_path();
    $text = function (string $type, string $body): never {
        header("Content-Type: $type; charset=utf-8");
        echo $body;
        exit;
    };
    if ($path === '/lead' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        artificia_ist(function (): void {
            artificia_tables(db()); // a lead must never fail for a missing table
            lead_endpoint(db());    // the PHP site's handler, unchanged: validation, spam checks, CAPI, email, n8n
        });
        exit;
    }
    match ($path) {
        '/robots.txt' => $text('text/plain', robots_txt(config()['env'], config()['base_url'], settings())),
        '/sitemap.xml' => $text('application/xml', artificia_ist(fn() => sitemap_xml_for(artificia_indexable(), config()['base_url']))),
        '/llms.txt' => $text('text/plain', llms_txt_for(artificia_llms_order(artificia_indexable()), config()['base_url'], settings())),
        default => null,
    };
    // Links the PHP site put in alert emails, n8n rows and saved leads open the same record in WordPress.
    if (preg_match('#^/admin/leads/(\d+)$#', $path, $m)) {
        wp_safe_redirect(admin_url('admin.php?page=artificia-leads&lead=' . $m[1]));
        exit;
    }
    if ($path === '/admin/resume') {
        artificia_resume((string) ($_GET['f'] ?? ''));
        exit;
    }
    if ($path === '/admin' || str_starts_with($path, '/admin/')) {
        wp_safe_redirect(admin_url());
        exit;
    }
}, 1);

/** llms.txt lists the homepage first, then the rest by slug (seo.php's ORDER BY kind, slug = 'home' DESC, slug). */
function artificia_llms_order(array $rows): array
{
    usort($rows, fn(array $a, array $b) => [$a['kind'], $a['slug'] !== 'home', $a['slug']] <=> [$b['kind'], $b['slug'] !== 'home', $b['slug']]);
    return $rows;
}

/** A Careers résumé PDF for a logged-in administrator; everyone else gets the login screen or a 404. */
function artificia_resume(string $file): void
{
    if (!is_user_logged_in()) {
        auth_redirect();
    }
    $path = RESUME_DIR . '/' . basename($file);
    if (!current_user_can('manage_options') || !preg_match('/^[0-9a-f]{32}\.pdf$/', $file) || !is_file($path)) {
        artificia_send(null);
        return;
    }
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="resume.pdf"');
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: private, no-store');
    readfile($path);
}

// Only the URLs the PHP site had: no slash-only redirects (it served /about and /about/ alike) and no guessing at
// mistyped addresses. It ignored query strings, so ?s=, ?p= and ?cat= show the page as before; WordPress-only
// addresses (feeds, archives, author pages, /about/2) are its 404.
add_filter('redirect_canonical', '__return_false');
add_filter('do_redirect_guess_404_permalink', '__return_false');
add_filter('request', function (array $vars): array {
    if (is_admin()) {
        return $vars;
    }
    $keep = array_flip(['pagename', 'name', 'post_type', 'rest_route', 'error', 'favicon', 'preview']);
    if (isset($_GET['preview'])) {
        $keep += ['p' => 1, 'page_id' => 1];
    }
    $vars = array_filter($vars, fn($v) => $v !== '' && $v !== null); // pages always carry an empty "page"
    $vars = array_diff_key($vars, array_diff_key(array_intersect_key($vars, $_GET), $keep)); // query-string extras: ignored
    return array_diff_key($vars, $keep) ? ['error' => '404'] : $vars;
});
add_action('do_faviconico', function (): void {
    artificia_send(null);
    exit;
});
add_action('template_redirect', function (): void {
    $path = request_path();
    if (trim($path, '/') === 'home') {
        redirect(redirect_location('/', (string) ($_SERVER['QUERY_STRING'] ?? '')), 301);
    }
    $hit = artificia_legacy_redirect($path, is_singular(['page', 'post']));
    if ($hit) {
        redirect(redirect_location($hit['to'], (string) ($_SERVER['QUERY_STRING'] ?? '')), $hit['code']);
    }
    if (is_feed() || is_embed() || is_trackback()) {
        artificia_send(null);
        exit;
    }
}, 0);
add_filter('wp_sitemaps_enabled', '__return_false'); // /sitemap.xml is the PHP site's
add_filter('xmlrpc_enabled', '__return_false');
add_filter('wp_headers', function (array $headers): array {
    unset($headers['X-Pingback']);
    return $headers;
});
remove_action('template_redirect', 'wp_shortlink_header', 11);
remove_action('template_redirect', 'rest_output_link_header', 11);
add_filter('comments_open', '__return_false');
add_filter('pings_open', '__return_false');

// New uploads get the PHP site's widths, as WebP.
add_action('after_setup_theme', function (): void {
    add_theme_support('post-thumbnails');
    foreach (MEDIA_WIDTHS as $w) {
        add_image_size("artificia-$w", $w, 0);
    }
});
add_filter('image_editor_output_format', fn(array $formats) => ['image/jpeg' => 'image/webp', 'image/png' => 'image/webp'] + $formats);
add_filter('wp_editor_set_quality', fn($q, $mime) => $mime === 'image/webp' ? 80 : $q, 10, 2);
