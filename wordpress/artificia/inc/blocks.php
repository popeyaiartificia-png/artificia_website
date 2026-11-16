<?php
// One editor block per section type in app/sections/registry.php. A block stores the section's fields as they are (one
// "fields" attribute, the same shape the PHP site saved), so the registry stays the only list of fields.
declare(strict_types=1);

/** A section type's block name: block names allow dashes, not underscores (rich_text → artificia/rich-text). */
function artificia_block_name(string $type): string
{
    return 'artificia/' . str_replace('_', '-', $type);
}

/** The section type of an artificia/* block name; '' for any other block. */
function artificia_block_type(?string $name): string
{
    return str_starts_with((string) $name, 'artificia/') ? str_replace('-', '_', substr((string) $name, 10)) : '';
}

/** The section type's rich-text field, edited as paragraph, heading and list blocks inside the section; '' if none. */
function artificia_rich_field(string $type): string
{
    foreach (section_registry()[$type]['fields'] ?? [] as $name => $def) {
        if ($def['type'] === 'richtext') {
            return $name;
        }
    }
    return '';
}

/** Page and post SEO fields (the PHP site's pages columns) => meta type. */
const ARTIFICIA_META = [
    'h1' => 'string', 'meta_title' => 'string', 'meta_description' => 'string', 'focus_keyword' => 'string', 'canonical' => 'string',
    'noindex' => 'boolean', 'schema_auto' => 'boolean', 'schema_custom' => 'string', 'og_image' => 'integer',
];

add_action('init', function (): void {
    $dir = get_template_directory();
    wp_register_script('artificia-editor', get_template_directory_uri() . '/editor.js',
        ['wp-blocks', 'wp-block-editor', 'wp-components', 'wp-element', 'wp-data', 'wp-core-data', 'wp-hooks', 'wp-plugins', 'wp-editor'],
        (string) filemtime("$dir/editor.js"), true);
    wp_add_inline_script('artificia-editor', 'window.artificiaSections = ' . wp_json_encode(section_registry()) . ';', 'before');
    wp_register_style('artificia-editor', get_template_directory_uri() . '/editor.css', [], (string) filemtime("$dir/editor.css"));

    foreach (section_registry() as $type => $def) {
        register_block_type(artificia_block_name($type), [
            'api_version' => 3,
            'title' => $def['label'],
            'category' => 'artificia',
            'attributes' => ['fields' => ['type' => 'object', 'default' => []]],
            'supports' => ['html' => false, 'className' => false, 'customClassName' => false],
            'editor_script_handles' => ['artificia-editor'],
            'editor_style_handles' => ['artificia-editor'],
        ]);
    }
    foreach (['page', 'post'] as $postType) {
        foreach (ARTIFICIA_META as $key => $type) {
            register_post_meta($postType, "_artificia_$key", [
                'type' => $type, 'single' => true, 'show_in_rest' => true, 'default' => $key === 'schema_auto' ? true : ($type === 'integer' ? 0 : ($type === 'boolean' ? false : '')),
                'auth_callback' => fn() => current_user_can('edit_posts'),
            ]);
        }
    }
    // The PHP site's page templates (Service page, Course audience page, ...) as starting points in the inserter.
    register_block_pattern_category('artificia', ['label' => 'Artificia templates']);
    foreach ((array) get_option('artificia_templates', []) as $name => $content) {
        register_block_pattern('artificia/' . sanitize_title((string) $name), ['title' => (string) $name, 'content' => (string) $content,
            'categories' => ['artificia'], 'postTypes' => ['page', 'post']]);
    }
});

add_action('enqueue_block_editor_assets', fn() => wp_enqueue_script('artificia-editor')); // the SEO panel, even before a block is added

add_filter('block_categories_all', fn(array $cats) => array_merge([['slug' => 'artificia', 'title' => 'Artificia sections', 'icon' => null]], $cats));

// Pages and posts are built only from sections; paragraphs, headings and lists exist only inside a section's rich-text field
// (editor.js limits where they go), so an editor can add, reorder and edit sections but never break the page markup.
add_filter('allowed_block_types_all', function ($allowed, WP_Block_Editor_Context $context) {
    if (!in_array($context->post->post_type ?? '', ['page', 'post'], true)) {
        return $allowed;
    }
    return array_merge(array_map('artificia_block_name', array_keys(section_registry())),
        ['core/paragraph', 'core/heading', 'core/list', 'core/list-item']);
}, 10, 2);

add_action('after_setup_theme', function (): void {
    remove_theme_support('core-block-patterns');
});
add_filter('should_load_remote_block_patterns', '__return_false');
