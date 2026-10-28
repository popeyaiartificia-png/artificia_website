<?php

function design_samples(): array
{
    return json_decode((string) file_get_contents(__DIR__ . '/../content/design-samples.json'), true, flags: JSON_THROW_ON_ERROR);
}

function test_design_samples_cover_every_type(): void
{
    $types = array_column(design_samples()['sections'], 'type');
    eq(array_values(array_diff(array_keys(section_registry()), $types)), [], 'every section type has a sample');
}

function test_design_samples_render_with_one_h1(): void
{
    $data = design_samples();
    $page = $data['page'] + ['sections' => $data['sections'], 'kind' => 'page'];
    $html = '';
    foreach ($data['sections'] as $i => $section) {
        $out = render_section_in($section, $i, $page, $data['settings']);
        ok(str_contains($out, 'sec--' . $section['type']), $section['type'] . ' renders');
        $html .= $out;
    }
    eq(substr_count($html, '<h1'), 1);
    ok(!str_contains($html, 'href="javascript'), 'no unsafe links');
}

function test_design_helpers(): void
{
    ok(str_contains(icon('graduation-cap'), '<svg class="icon"'));
    eq(icon('no-such-icon'), '');
    eq(drawing('none'), '');
    eq(drawing('../registry'), '');
    ok(str_contains(drawing('documents'), 'role="img"'), 'drawings are labelled images');
    eq(image('hardik-aseri.jpg', '100vw'), '', 'a filename is not a media id');
    ok(str_contains(sec_head('Hello *there*'), '<h2 class="h2">Hello <em>there</em></h2>'));
}

function test_every_icon_option_exists(): void
{
    $card = section_registry()['card_grid']['fields']['cards']['fields']['icon'];
    foreach ($card['options'] as $name) {
        ok(icon($name) !== '', "icon $name");
    }
}

function test_every_tool_logo_option_exists(): void
{
    foreach (section_registry()['tools']['fields']['tools']['fields']['brand']['options'] as $key) {
        ok(str_starts_with(brand($key), '<svg class="brand" viewBox="'), "logo $key");
    }
    eq(brand('no-such-tool'), '');
    $html = render_section_in(['type' => 'tools', 'fields' => ['heading' => 'Tools', 'tools' => [['name' => 'n8n', 'brand' => 'n8n']]]], 0, [], []);
    ok(str_contains($html, '<svg class="brand"'), 'a tool shows its built-in logo');
}

function test_every_drawing_option_exists(): void
{
    foreach (section_registry()['hero']['fields']['drawing']['options'] as $name) {
        ok($name === 'none' || drawing($name) !== '', "drawing $name");
    }
}

function test_only_allowlisted_videos_become_players(): void
{
    $html = render_section_in(['type' => 'testimonials', 'fields' => ['heading' => 'Words', 'items' => [
        ['name' => 'A', 'role' => 'r', 'video_url' => 'javascript:alert(1)'],
        ['name' => 'B', 'role' => 'r', 'video_url' => 'data:text/html,<script>1</script>'],
        ['name' => 'C', 'role' => 'r', 'video_url' => 'https://drive.google.com/file/d/abc_123/view?usp=sharing'],
    ]]], 0, [], []);
    ok(!str_contains($html, 'javascript:') && !str_contains($html, 'data:text'), 'unsafe video URLs never reach the page');
    eq(substr_count($html, 'data-src='), 1, 'only the Drive link becomes a player');
    ok(str_contains($html, 'data-src="https://drive.google.com/file/d/abc_123/preview"'), 'Drive links play their preview');
    eq(video_embed_src('https://youtu.be/dQw4w9WgXcQ'), 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ');
    eq(video_embed_src('https://evil.example/drive.google.com/file/d/x'), '');
}

function test_hero_h1_falls_back_to_title(): void
{
    $html = render_section_in(['type' => 'hero', 'fields' => []], 0, ['h1' => '', 'title' => 'About Artificia'], []);
    ok((bool) preg_match('#<h1[^>]*>About Artificia</h1>#', $html));
}

function test_cta_without_button_renders_no_link(): void
{
    $html = render_section_in(['type' => 'cta', 'fields' => ['heading' => 'Hi']], 0, [], []);
    ok(!str_contains($html, '<a '), 'no empty button');
}

function test_whatsapp_url_falls_back_to_the_business_number(): void
{
    eq(whatsapp_url([]), 'https://wa.me/919104488859');
    eq(whatsapp_url(['whatsapp_number' => '+91 98765 43210']), 'https://wa.me/919876543210');
}
