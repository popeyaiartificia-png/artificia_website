<?php
// wp artificia import: copies the PHP site's database into WordPress. Safe to re-run (for example just before going live):
// every item is matched by its old id and updated in place.
declare(strict_types=1);

/**
 * Imports the PHP site's settings, media, pages and posts, page templates, redirects and leads. Its database is only read.
 *
 * ## OPTIONS
 *
 * --config=<file>
 * : The PHP site's config file (artificia-config.php), for its database.
 *
 * [--uploads=<dir>]
 * : The PHP site's public/uploads folder: its image files are copied to the same paths here, so their URLs stay the same.
 *
 * [--resumes=<dir>]
 * : The PHP site's storage/resumes folder (Careers PDFs), copied to this checkout's storage/resumes.
 */
WP_CLI::add_command('artificia import', function (array $args, array $assoc): void {
    $config = require $assoc['config'];
    artificia_ist(function () use ($config, $assoc): void {
        $old = db_connect($config['db']['dsn'], $config['db']['user'], $config['db']['pass']);
        $identity = 'SELECT CONCAT(@@hostname, ":", @@port, "/", DATABASE())';
        if ($old->query($identity)->fetchColumn() === db()->query($identity)->fetchColumn()) {
            WP_CLI::error('The source and WordPress databases must be different; the old database is the rollback.');
        }
        kses_remove_filters(); // the PHP site's content is already cleaned; kses would rewrite block attributes
        artificia_import_settings($old);
        $media = artificia_import_media($old, $assoc['uploads'] ?? null);
        artificia_import_pages($old, $media);
        artificia_import_templates($old, $media);
        artificia_import_redirects($old);
        artificia_import_leads($old);
        if (isset($assoc['resumes'])) {
            artificia_import_resumes($assoc['resumes']);
        }
    });
    WP_CLI::success('Import finished.');
});

/** old id => WordPress post id, for posts carrying $key. */
function artificia_by_meta(string $key): array
{
    global $wpdb;
    $rows = $wpdb->get_results($wpdb->prepare("SELECT meta_value, post_id FROM $wpdb->postmeta WHERE meta_key = %s", $key), ARRAY_N);
    return array_column(array_map(fn(array $r) => [(int) $r[0], (int) $r[1]], $rows), 1, 0);
}

function artificia_import_settings(PDO $old): void
{
    $settings = $old->query('SELECT k, v FROM settings')->fetchAll(PDO::FETCH_KEY_PAIR);
    update_option('artificia_settings', $settings);
    WP_CLI::log(count($settings) . ' settings');
}

/** Media rows as attachments that keep the PHP site's files and URLs. @return array old media id => attachment id */
function artificia_import_media(PDO $old, ?string $uploads): array
{
    $basedir = wp_upload_dir()['basedir'];
    $existing = artificia_by_meta('_artificia_media_id');
    $map = [];
    foreach ($old->query('SELECT * FROM media ORDER BY id')->fetchAll() as $m) {
        $widths = media_widths((int) $m['width']);
        $max = max($widths);
        foreach ($uploads !== null ? $widths : [] as $w) {
            $from = "$uploads/{$m['base_name']}-$w.webp";
            $to = "$basedir/{$m['base_name']}-$w.webp";
            if (!is_file($from)) {
                WP_CLI::warning("Missing $from");
                continue;
            }
            wp_mkdir_p(dirname($to));
            copy($from, $to);
        }
        $file = "{$m['base_name']}-$max.webp";
        $post = [
            'ID' => $existing[(int) $m['id']] ?? 0,
            'post_mime_type' => 'image/webp',
            'post_title' => $m['source'] ? pathinfo((string) $m['source'], PATHINFO_FILENAME) : mb_substr((string) $m['alt'], 0, 80),
            'post_status' => 'inherit',
            'post_date' => $m['created_at'],
            'guid' => wp_upload_dir()['baseurl'] . "/$file",
        ];
        $id = wp_insert_attachment(wp_slash($post), $file, 0, true);
        if (is_wp_error($id)) {
            WP_CLI::error("Media {$m['id']}: " . $id->get_error_message());
        }
        $size = fn(int $w) => ['file' => basename("{$m['base_name']}-$w.webp"), 'width' => $w, 'height' => (int) round($m['height'] * $w / $m['width']), 'mime-type' => 'image/webp'];
        $sizes = array_combine(array_slice(['medium', 'large'], 0, count($widths) - 1), array_map($size, array_slice($widths, 0, -1)));
        wp_update_attachment_metadata($id, ['width' => $max, 'height' => $size($max)['height'], 'file' => $file, 'sizes' => $sizes]);
        update_post_meta($id, '_wp_attachment_image_alt', wp_slash((string) $m['alt']));
        update_post_meta($id, '_artificia_media', ['base' => $m['base_name'], 'width' => (int) $m['width'], 'height' => (int) $m['height']]);
        update_post_meta($id, '_artificia_media_id', (int) $m['id']);
        $map[(int) $m['id']] = $id;
    }
    WP_CLI::log(count($map) . ' images');
    return $map;
}

/** Image fields (by the registry, inside lists too) pointed at their attachments. */
function artificia_remap_images(array $fields, array $defs, array $media): array
{
    foreach ($defs as $name => $def) {
        if (!isset($fields[$name])) {
            continue;
        }
        if ($def['type'] === 'image') {
            $fields[$name] = $media[(int) $fields[$name]] ?? null;
            if ($fields[$name] === null) {
                unset($fields[$name]);
            }
        } elseif ($def['type'] === 'list' && is_array($fields[$name])) {
            $fields[$name] = array_map(fn($item) => is_array($item) ? artificia_remap_images($item, $def['fields'], $media) : $item, $fields[$name]);
        }
    }
    return $fields;
}

/** A parsed-block array; $open and $close are the block's own HTML around its inner blocks. */
function artificia_block(string $name, array $attrs, array $inner = [], string $open = '', string $close = ''): array
{
    return ['blockName' => $name, 'attrs' => $attrs, 'innerBlocks' => $inner, 'innerHTML' => $open . $close,
        'innerContent' => array_merge($open !== '' ? [$open] : [], array_fill(0, count($inner), null), $close !== '' ? [$close] : [])];
}

/** One section as its block: fields as they are (images remapped), the rich-text field as paragraph/heading/list blocks inside. */
function artificia_section_block(array $section, array $media): ?array
{
    $type = (string) ($section['type'] ?? '');
    $defs = section_registry()[$type]['fields'] ?? null;
    if ($defs === null) {
        return null;
    }
    $fields = artificia_remap_images(is_array($section['fields'] ?? null) ? $section['fields'] : [], $defs, $media);
    $rich = artificia_rich_field($type);
    $inner = $rich !== '' ? artificia_html_blocks((string) ($fields[$rich] ?? '')) : [];
    unset($fields[$rich]);
    return artificia_block(artificia_block_name($type), $fields ? ['fields' => $fields] : [], $inner);
}

function artificia_sections_content(array $sections, array $media): string
{
    return serialize_blocks(array_values(array_filter(array_map(fn($s) => is_array($s) ? artificia_section_block($s, $media) : null, $sections))));
}

function artificia_inner_html(DOMNode $node): string
{
    $html = '';
    foreach ($node->childNodes as $child) {
        $html .= $node->ownerDocument->saveHTML($child);
    }
    return $html;
}

/** The PHP site's cleaned rich text (p, h2, h3, ul, ol, li, strong, em, a, br) as core blocks, saved as the editor saves them. */
function artificia_html_blocks(string $html): array
{
    if (trim($html) === '') {
        return [];
    }
    $doc = new DOMDocument();
    $prev = libxml_use_internal_errors(true);
    $doc->loadHTML('<meta charset="utf-8"><div>' . $html . '</div>', LIBXML_NONET);
    libxml_clear_errors();
    libxml_use_internal_errors($prev);
    $blocks = [];
    foreach ($doc->getElementsByTagName('div')->item(0)->childNodes as $node) {
        if ($node instanceof DOMElement) {
            $blocks[] = artificia_html_block($node);
        } elseif (trim($node->textContent) !== '') { // loose text between blocks
            $blocks[] = artificia_block('core/paragraph', [], [], '<p>' . trim($doc->saveHTML($node)) . '</p>');
        }
    }
    return $blocks;
}

function artificia_html_block(DOMElement $el): array
{
    $tag = strtolower($el->nodeName);
    if ($tag === 'h2' || $tag === 'h3') {
        return artificia_block('core/heading', $tag === 'h3' ? ['level' => 3] : [], [], "<$tag class=\"wp-block-heading\">" . artificia_inner_html($el) . "</$tag>");
    }
    if ($tag === 'ul' || $tag === 'ol') {
        $items = [];
        foreach ($el->childNodes as $li) {
            if ($li instanceof DOMElement && strtolower($li->nodeName) === 'li') {
                $text = '';
                $lists = [];
                foreach ($li->childNodes as $c) {
                    if ($c instanceof DOMElement && in_array(strtolower($c->nodeName), ['ul', 'ol'], true)) {
                        $lists[] = artificia_html_block($c);
                    } else {
                        $text .= $li->ownerDocument->saveHTML($c);
                    }
                }
                $items[] = artificia_block('core/list-item', [], $lists, "<li>$text", '</li>');
            }
        }
        return artificia_block('core/list', $tag === 'ol' ? ['ordered' => true] : [], $items, "<$tag class=\"wp-block-list\">", "</$tag>");
    }
    return artificia_block('core/paragraph', [], [], '<p>' . artificia_inner_html($el) . '</p>');
}

/** Pages and posts, parents first; the homepage becomes the front page. */
function artificia_import_pages(PDO $old, array $media): void
{
    global $wpdb;
    $existing = artificia_by_meta('_artificia_page_id');
    $ids = []; // page slug => WordPress id
    foreach ($old->query('SELECT * FROM pages ORDER BY kind, CHAR_LENGTH(slug), slug')->fetchAll() as $r) {
        $page = $r['kind'] === 'page';
        $parentSlug = $page && str_contains($r['slug'], '/') ? dirname($r['slug']) : '';
        if ($parentSlug !== '' && !isset($ids[$parentSlug])) {
            $ids[$parentSlug] = artificia_placeholder_parent($parentSlug, $ids[dirname($parentSlug)] ?? 0);
        }
        $post = [
            'ID' => $existing[(int) $r['id']] ?? 0,
            'post_type' => $page ? 'page' : 'post',
            'post_name' => basename($r['slug']),
            'post_parent' => $ids[$parentSlug] ?? 0,
            'post_title' => $r['title'],
            'post_excerpt' => (string) $r['excerpt'],
            'post_status' => $r['status'] === 'published' ? 'publish' : 'draft',
            'post_date' => $r['published_at'] ?? $r['created_at'],
            'post_content' => artificia_sections_content(json_decode((string) $r['sections'], true) ?: [], $media),
            'comment_status' => 'closed',
            'ping_status' => 'closed',
            'meta_input' => [
                '_artificia_page_id' => (int) $r['id'],
                '_artificia_h1' => $r['h1'],
                '_artificia_meta_title' => $r['meta_title'],
                '_artificia_meta_description' => $r['meta_description'],
                '_artificia_focus_keyword' => $r['focus_keyword'] ?? '',
                '_artificia_canonical' => $r['canonical'] ?? '',
                '_artificia_noindex' => $r['noindex'] ? '1' : '',
                '_artificia_schema_auto' => ($r['schema_auto'] ?? 1) ? '1' : '',
                '_artificia_schema_custom' => (string) ($r['schema_custom'] ?? ''),
                '_artificia_og_image' => $media[(int) $r['og_image_id']] ?? 0,
            ],
        ];
        $id = wp_insert_post(wp_slash($post), true);
        if (is_wp_error($id)) {
            WP_CLI::error("{$r['slug']}: " . $id->get_error_message());
        }
        if (get_post_field('post_name', $id) !== basename($r['slug'])) {
            WP_CLI::warning("{$r['slug']}: WordPress changed the slug to " . get_post_field('post_name', $id));
        }
        // The sitemap's lastmod is the PHP site's updated_at.
        $wpdb->update($wpdb->posts, ['post_modified' => $r['updated_at'], 'post_modified_gmt' => get_gmt_from_date($r['updated_at'])], ['ID' => $id]);
        clean_post_cache($id);
        if ($page) {
            $ids[$r['slug']] = $id;
        }
        if ($page && $r['slug'] === 'home') {
            update_option('show_on_front', 'page');
            update_option('page_on_front', $id);
        }
    }
    WP_CLI::log(count($ids) . ' pages');
}

/**
 * A draft page that only holds an address segment the PHP site had without a page of its own (/case-studies in
 * /case-studies/orkay-tiles). Drafts still give child pages their address, and the segment alone stays a 404 as before.
 */
function artificia_placeholder_parent(string $slug, int $parent): int
{
    $found = get_posts(['post_type' => 'page', 'post_status' => 'any', 'meta_key' => '_artificia_placeholder', 'meta_value' => $slug, 'fields' => 'ids', 'numberposts' => 1]);
    $id = wp_insert_post(wp_slash([
        'ID' => $found[0] ?? 0, 'post_type' => 'page', 'post_status' => 'draft', 'post_name' => basename($slug), 'post_parent' => $parent,
        'post_title' => "/$slug/ (address only: keep as a draft)", 'meta_input' => ['_artificia_placeholder' => $slug],
    ]), true);
    if (is_wp_error($id)) {
        WP_CLI::error("$slug: " . $id->get_error_message());
    }
    return $id;
}

function artificia_import_templates(PDO $old, array $media): void
{
    $templates = [];
    foreach ($old->query('SELECT name, sections FROM templates ORDER BY name')->fetchAll() as $r) {
        $templates[$r['name']] = artificia_sections_content(json_decode((string) $r['sections'], true) ?: [], $media);
    }
    update_option('artificia_templates', $templates, false);
    WP_CLI::log(count($templates) . ' templates');
}

/** Replace the imported snapshot atomically, including rules removed since the previous import. */
function artificia_import_redirects(PDO $old): void
{
    $rows = $old->query('SELECT id, from_path, to_path, match_type, code FROM redirects ORDER BY id')->fetchAll();
    $db = db();
    artificia_redirects_table($db);
    $db->beginTransaction();
    try {
        $db->exec('DELETE FROM redirects');
        $insert = $db->prepare('INSERT INTO redirects (id, from_path, to_path, match_type, code) VALUES (?, ?, ?, ?, ?)');
        foreach ($rows as $row) {
            $insert->execute(array_values($row));
        }
        $db->commit();
    } catch (Throwable $e) {
        $db->rollBack();
        throw $e;
    }
    update_option('artificia_redirects_imported', '1', false);
    WP_CLI::log(count($rows) . ' redirects');
}

/** Leads keep their ids, so links in old alert emails and n8n rows (/admin/leads/{id}) still open them. */
function artificia_import_leads(PDO $old): void
{
    $db = db();
    artificia_tables($db);
    $n = 0;
    foreach ($old->query('SELECT * FROM leads ORDER BY id')->fetchAll() as $row) {
        $cols = array_keys($row);
        $db->prepare('REPLACE INTO leads (' . implode(', ', $cols) . ') VALUES (' . implode(', ', array_fill(0, count($cols), '?')) . ')')
           ->execute(array_values($row));
        $n++;
    }
    WP_CLI::log("$n leads");
}

function artificia_import_resumes(string $dir): void
{
    wp_mkdir_p(RESUME_DIR);
    @chmod(RESUME_DIR, 0700);
    $n = 0;
    foreach (glob(rtrim($dir, '/\\') . '/*.pdf') ?: [] as $file) {
        copy($file, RESUME_DIR . '/' . basename($file)) && $n++;
    }
    WP_CLI::log("$n resumes");
}
