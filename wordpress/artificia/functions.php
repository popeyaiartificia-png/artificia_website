<?php
/**
 * Artificia theme.
 *
 * WordPress stores the content (one block per section), media, settings and leads. Rendering reuses the PHP site's own
 * code from the same Git checkout, unchanged, so visitors get exactly the markup site.css and site.js were built for:
 *   app/sections, app/views  section and page templates
 *   app/lib                  helpers, SEO (schema, robots.txt, llms.txt) and the whole lead pipeline (spam checks, Meta CAPI, email, n8n)
 *   public/assets            CSS, JS, fonts and images, still served at /assets/...
 * The theme adds only what WordPress replaces: config(), settings(), images, blog posts, routes and the admin screens.
 */
declare(strict_types=1);

defined('ABSPATH') || exit;
defined('ARTIFICIA_ROOT') || define('ARTIFICIA_ROOT', dirname(__DIR__, 2)); // the checkout this theme lives in

require __DIR__ . '/inc/adapter.php'; // first: the app code checks for image() and posts_recent() before defining its own
foreach (['http', 'db', 'html', 'media', 'pages', 'render', 'seo', 'mail', 'leads'] as $lib) {
    require ARTIFICIA_ROOT . "/app/lib/$lib.php";
}
require ARTIFICIA_ROOT . '/app/sections/registry.php';
require __DIR__ . '/inc/site.php';
require __DIR__ . '/inc/blocks.php';
if (is_admin()) {
    require __DIR__ . '/inc/admin.php';
}
if (defined('WP_CLI') && WP_CLI) {
    require __DIR__ . '/inc/cli.php';
}
