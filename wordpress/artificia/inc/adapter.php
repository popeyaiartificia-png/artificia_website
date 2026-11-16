<?php
// What the PHP site's code reads from its own config, database and media table, answered from WordPress.
declare(strict_types=1);

/** The PHP site's config() array: environment, address, database, form-token secret and mail sender. */
function config(): array
{
    static $c;
    if ($c === null) {
        [$host, $port] = explode(':', DB_HOST, 2) + [1 => '']; // "host", "host:port" or "host:/socket"
        $c = [
            'env' => ['production' => 'production', 'staging' => 'staging'][wp_get_environment_type()] ?? 'local',
            'base_url' => untrailingslashit(home_url()),
            'db' => [
                'dsn' => 'mysql:' . (str_starts_with($port, '/') ? "unix_socket=$port" : "host=$host" . ($port !== '' ? ";port=$port" : ''))
                    . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                'user' => DB_USER,
                'pass' => DB_PASSWORD,
            ],
            // wp-config.php sets both from the PHP site's config: the same secret keeps forms open at the switchover valid.
            'app_secret' => defined('ARTIFICIA_APP_SECRET') ? ARTIFICIA_APP_SECRET : wp_salt('secure_auth'),
            'mail_from' => defined('ARTIFICIA_MAIL_FROM') ? ARTIFICIA_MAIL_FROM : 'no-reply@artificiaai.com',
        ];
    }
    return $c;
}

/** The PHP site's settings table, key for key, kept in one option (Settings → Artificia). */
function settings(mixed $db = null): array
{
    $s = get_option('artificia_settings');
    return is_array($s) ? array_map('strval', $s) : [];
}

/**
 * An attachment as the PHP site's media row: width, height, alt and its renditions (width => URL, ascending); null if unknown.
 * Imported images keep the PHP site's own files and URLs (/uploads/{base}-{480|960|1600}.webp); new uploads use the
 * artificia-480/960/1600 sizes WordPress makes.
 */
function artificia_media(int $id): ?array
{
    static $cache = [];
    if (array_key_exists($id, $cache)) {
        return $cache[$id];
    }
    $post = $id > 0 ? get_post($id) : null;
    if (!$post || $post->post_type !== 'attachment') {
        return $cache[$id] = null;
    }
    $alt = (string) get_post_meta($id, '_wp_attachment_image_alt', true);
    $old = get_post_meta($id, '_artificia_media', true);
    if (is_array($old)) {
        $urls = [];
        foreach (media_widths((int) $old['width']) as $w) {
            $urls[$w] = "/uploads/{$old['base']}-$w.webp";
        }
        return $cache[$id] = ['width' => (int) $old['width'], 'height' => (int) $old['height'], 'alt' => $alt, 'urls' => $urls];
    }
    $meta = wp_get_attachment_metadata($id);
    $url = wp_get_attachment_url($id);
    if (empty($meta['width']) || !$url) {
        return $cache[$id] = null;
    }
    $dir = dirname(wp_make_link_relative($url));
    $urls = [];
    foreach (MEDIA_WIDTHS as $w) {
        if (isset($meta['sizes']["artificia-$w"]['file'])) {
            $urls[$w] = "$dir/" . $meta['sizes']["artificia-$w"]['file'];
        }
    }
    $urls = $urls ?: [(int) $meta['width'] => wp_make_link_relative($url)]; // smaller than 480px: the file itself
    return $cache[$id] = ['width' => (int) $meta['width'], 'height' => (int) $meta['height'], 'alt' => $alt, 'urls' => $urls];
}

/** An image field (an attachment id) as the PHP site's responsive <img> (media.php img_tag); '' when empty or unknown. */
function image(mixed $id, string $sizes, bool $eager = false, string $class = ''): string
{
    $m = is_numeric($id) && (int) $id > 0 ? artificia_media((int) $id) : null;
    if (!$m) {
        return '';
    }
    $srcset = implode(', ', array_map(fn(int $w, string $u) => "$u {$w}w", array_keys($m['urls']), $m['urls']));
    $src = $m['urls'][960] ?? end($m['urls']);
    $loading = $eager ? 'loading="eager" fetchpriority="high"' : 'loading="lazy" decoding="async"';
    return '<img' . ($class !== '' ? ' class="' . e($class) . '"' : '') . ' src="' . e($src) . '" srcset="' . e($srcset) . '" sizes="' . e($sizes)
        . '" width="' . $m['width'] . '" height="' . $m['height'] . '" alt="' . e($m['alt']) . '" ' . $loading . '>';
}

/** Newest published posts first, as the blog_list section reads them. */
function posts_recent(mixed $db, int $limit): array
{
    $posts = get_posts(['numberposts' => $limit, 'post_status' => 'publish', 'orderby' => ['date' => 'DESC', 'ID' => 'DESC']]);
    return array_map(fn(WP_Post $p) => ['slug' => $p->post_name, 'title' => $p->post_title, 'excerpt' => $p->post_excerpt, 'published_at' => $p->post_date], $posts);
}
