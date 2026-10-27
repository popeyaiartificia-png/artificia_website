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
        'robots' => '', 'ogImage' => null, 'ogType' => 'website', 'jsonld' => [],
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
    ]);
}

function render_404(array $settings): string
{
    return render_layout([
        'settings' => $settings, 'body' => render_view('404', []), 'title' => 'Page not found | Artificia AI',
        'description' => '', 'canonical' => '', 'robots' => 'noindex',
    ]);
}
