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

function test_every_drawing_option_exists(): void
{
    foreach (section_registry()['hero']['fields']['drawing']['options'] as $name) {
        ok($name === 'none' || drawing($name) !== '', "drawing $name");
    }
}
