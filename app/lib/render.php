<?php
declare(strict_types=1);

const SECTIONS_DIR = __DIR__ . '/../sections';
const VIEWS_DIR = __DIR__ . '/../views';

/** Escaped heading with `*word*` turned into emphasis. */
function heading_html(string $s): string
{
    return preg_replace('/\*([^*\n]+)\*/', '<em>$1</em>', e($s));
}

/** Heading as plain text, for <title>, meta tags and JSON-LD. */
function heading_plain(string $s): string
{
    return preg_replace('/\*([^*\n]+)\*/', '$1', $s);
}

function rich(string $html): string
{
    return sanitize_html($html);
}

/** href for an editor-entered link: site paths, anchors, http(s), mailto and tel only. */
function link_href(string $url): string
{
    return preg_match('#^(/|\#|https?://|mailto:|tel:)#i', $url) ? e($url) : '#';
}

/** Inline Phosphor icon by name; '' for an unknown name. */
function icon(string $name, string $class = 'icon'): string
{
    static $set;
    $set ??= require VIEWS_DIR . '/icons.php';
    if (!isset($set[$name])) {
        return '';
    }
    return '<svg class="' . e($class) . '" viewBox="0 0 256 256" aria-hidden="true" focusable="false">' . $set[$name] . '</svg>';
}

/** A tool's logo mark from app/views/brands.php by key; '' for an unknown key. */
function brand(string $key, string $class = 'brand'): string
{
    static $set;
    $set ??= require VIEWS_DIR . '/brands.php';
    if (!isset($set[$key])) {
        return '';
    }
    [$box, $body] = $set[$key];
    return '<svg class="' . e($class) . '" viewBox="' . $box . '" aria-hidden="true" focusable="false">' . $body . '</svg>';
}

/** The completion tick that sits on the margin rule beside a section heading. */
function tick(): string
{
    return '<svg class="tick" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path pathLength="1" d="M5 12.5l4.5 4.5L19 7.5"/></svg>';
}

/** One of the lime sketches in app/views/drawings; '' for none or an unknown name. */
function drawing(string $name): string
{
    $file = VIEWS_DIR . '/drawings/' . basename($name) . '.php';
    if ($name === '' || $name === 'none' || !is_file($file)) {
        return '';
    }
    ob_start();
    include $file;
    return (string) ob_get_clean();
}

/** Section heading: an h2 with its margin tick. */
function sec_head(string $heading, string $class = 'h2'): string
{
    if (trim($heading) === '') {
        return '';
    }
    return '<div class="sec__head">' . tick() . '<h2 class="' . e($class) . '">' . heading_html($heading) . '</h2></div>';
}

/** A testimonial video URL as an embeddable https src (Google Drive or YouTube); '' for anything else. */
function video_embed_src(string $url): string
{
    if (preg_match('#^https://drive\.google\.com/file/d/([\w-]+)#', $url, $m)) {
        return 'https://drive.google.com/file/d/' . $m[1] . '/preview';
    }
    if (preg_match('#^https://(?:www\.)?(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([\w-]{6,})#', $url, $m)) {
        return 'https://www.youtube-nocookie.com/embed/' . $m[1];
    }
    return '';
}

/** wa.me link for the site's WhatsApp number (settings, else the business default). */
function whatsapp_url(array $settings): string
{
    $digits = preg_replace('/\D/', '', (string) ($settings['whatsapp_number'] ?? ''));
    return 'https://wa.me/' . ($digits !== '' ? $digits : '919104488859');
}

/** An image field (a media id) as a responsive <img>; '' when empty or unknown. */
function image(mixed $id, string $sizes, bool $eager = false, string $class = ''): string
{
    if (!is_numeric($id) || (int) $id <= 0) {
        return '';
    }
    $tag = img_tag(db(), (int) $id, $sizes, $eager);
    return $class !== '' && $tag !== '' ? '<img class="' . e($class) . '" ' . substr($tag, 5) : $tag;
}

function render_view(string $name, array $vars): string
{
    extract($vars, EXTR_SKIP);
    ob_start();
    include VIEWS_DIR . "/$name.php";
    return (string) ob_get_clean();
}

/** Runs a section partial. It sees exactly $f, $s, $page and $settings; the file arrives as an extra argument to keep it out of scope. */
function section_include(array $f, int $s, array $page, array $settings): string
{
    ob_start();
    include func_get_arg(4);
    return (string) ob_get_clean();
}

function render_section_in(array $section, int $s, array $page, array $settings): string
{
    $type = (string) ($section['type'] ?? '');
    if (!isset(section_registry()[$type])) {
        return '';
    }
    return section_include($section['fields'] ?? [], $s, $page, $settings, SECTIONS_DIR . "/$type.php");
}

/** One section without page context ($page and $settings are empty). */
function render_section(array $section): string
{
    return render_section_in($section, 0, [], []);
}

/** Only a non-empty setting that looks like a tracking id is ever printed. */
function tracking_id(array $settings, string $key): string
{
    return preg_match('/^[A-Za-z0-9_-]+$/', $settings[$key] ?? '') ? $settings[$key] : '';
}

function render_layout(array $vars): string
{
    $settings = $vars['settings'];
    return render_view('layout', $vars + [
        'robots' => '', 'ogImage' => null, 'ogType' => 'website', 'jsonld' => [], 'hasForm' => false,
        'current' => request_path(),
        'ga4' => tracking_id($settings, 'ga4_id'), 'pixel' => tracking_id($settings, 'meta_pixel_id'),
        'gsc' => $settings['gsc_verification'] ?? '',
    ]);
}

function render_page(array $page, array $settings): string
{
    $base = config()['base_url'];
    $sections = $page['sections'];
    $body = '';
    if (($sections[0]['type'] ?? '') !== 'hero') {
        $body .= render_view('page-header', ['page' => $page]);
    }
    foreach ($sections as $i => $section) {
        $body .= render_section_in($section, $i, $page, $settings);
    }
    $ogImage = $page['og_image_id'] ? media_url(db(), (int) $page['og_image_id'], 1600) : null;
    return render_layout([
        'settings' => $settings,
        'body' => $body,
        'title' => heading_plain($page['meta_title'] ?: $page['title']),
        'description' => heading_plain($page['meta_description'] ?: strip_tags((string) $page['excerpt'])),
        'canonical' => $base . page_path($page),
        'ogImage' => $ogImage ? $base . $ogImage : null,
        'ogType' => $page['kind'] === 'post' ? 'article' : 'website',
        'jsonld' => schema_graph($page, $settings, $base),
        'hasForm' => in_array('lead_form', array_column($sections, 'type'), true),
    ]);
}

/** /_design: every section type rendered from content/design-samples.json, in order. */
function render_design_gallery(PDO $db, array $settings): string
{
    $root = dirname(__DIR__, 2);
    $data = json_decode((string) file_get_contents($root . '/content/design-samples.json'), true, flags: JSON_THROW_ON_ERROR);
    $settings += $data['settings'] ?? [];
    $media = $root . '/content/seed/media';
    $sections = seed_resolve_images($db, $data['sections'], $media, $root . '/public/uploads', seed_json("$media/alt.json"));
    $page = $data['page'] + ['sections' => $sections, 'kind' => 'page'];
    $body = render_view('design-gallery', ['sections' => $sections, 'page' => $page, 'settings' => $settings]);
    return render_layout([
        'settings' => $settings, 'body' => $body, 'title' => $page['meta_title'] ?? 'Section library',
        'description' => '', 'canonical' => '', 'robots' => 'noindex', 'hasForm' => true,
    ]);
}

function render_404(array $settings): string
{
    return render_layout([
        'settings' => $settings, 'body' => render_view('404', []), 'title' => 'Page not found | Artificia AI',
        'description' => '', 'canonical' => '', 'robots' => 'noindex',
    ]);
}
