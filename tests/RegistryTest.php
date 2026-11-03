<?php
declare(strict_types=1);

const FIELD_TYPES = ['text', 'textarea', 'richtext', 'image', 'select', 'list'];

function test_registry_has_all_types(): void
{
    eq(array_keys(section_registry()), [
        'hero', 'statement', 'work_gallery', 'versus', 'rich_text', 'text_image', 'card_grid', 'solutions', 'use_cases', 'crossoff', 'process_steps', 'tools', 'curriculum',
        'stats', 'testimonials', 'case_study', 'faqs', 'lead_form', 'cta', 'people', 'map_contact', 'blog_list',
    ]);
}

function test_every_field_is_well_formed(): void
{
    foreach (section_registry() as $type => $section) {
        ok($section['label'] !== '', "$type label");
        ok($section['fields'] !== [], "$type fields");
        $check = function (array $fields, string $where) use (&$check): void {
            foreach ($fields as $name => $f) {
                ok(in_array($f['type'], FIELD_TYPES, true), "$where.$name type");
                ok($f['label'] !== '', "$where.$name label");
                eq(isset($f['options']), $f['type'] === 'select', "$where.$name options only on select");
                eq(isset($f['fields']), $f['type'] === 'list', "$where.$name fields only on list");
                if ($f['type'] === 'select') {
                    ok($f['options'] !== [], "$where.$name has options");
                }
                if ($f['type'] === 'list') {
                    ok($f['fields'] !== [], "$where.$name has subfields");
                    foreach ($f['fields'] as $sub) {
                        ok($sub['type'] !== 'list', "$where.$name no nested lists");
                    }
                    $check($f['fields'], "$where.$name");
                }
            }
        };
        $check($section['fields'], $type);
    }
}

function test_every_list_has_subfields(): void
{
    $lists = [];
    foreach (section_registry() as $type => $section) {
        foreach ($section['fields'] as $name => $f) {
            if ($f['type'] === 'list') {
                $lists["$type.$name"] = array_keys($f['fields']);
            }
        }
    }
    eq($lists, [
        'hero.doors' => ['label', 'note', 'link'],
        'work_gallery.items' => ['label', 'title', 'visual', 'image', 'link'],
        'versus.sides' => ['title', 'text', 'status_label', 'link', 'link_label', 'figure', 'image'],
        'card_grid.cards' => ['icon', 'drawing', 'image', 'title', 'text', 'link'],
        'solutions.items' => ['title', 'text', 'link', 'visual'],
        'use_cases.items' => ['title', 'before', 'after'],
        'crossoff.items' => ['title', 'outcome', 'text'],
        'process_steps.steps' => ['title', 'text'],
        'tools.flow' => ['title', 'note'],
        'tools.tools' => ['name', 'brand', 'logo', 'note'],
        'curriculum.modules' => ['track', 'day_range', 'title', 'topics', 'tools'],
        'stats.items' => ['value', 'label'],
        'testimonials.items' => ['name', 'role', 'quote', 'photo', 'video_url'],
        'case_study.built' => ['title', 'text'],
        'case_study.results' => ['value', 'label'],
        'faqs.items' => ['question', 'answer'],
        'lead_form.side_points' => ['text'],
        'people.items' => ['name', 'role', 'bio', 'photo', 'linkedin'],
    ]);
}

function test_required_flags(): void
{
    $required = [];
    $walk = function (array $fields, string $path) use (&$walk, &$required): void {
        foreach ($fields as $name => $f) {
            if (!empty($f['required'])) {
                $required[] = "$path.$name";
            }
            if ($f['type'] === 'list') {
                $walk($f['fields'], "$path.$name");
            }
        }
    };
    foreach (section_registry() as $type => $section) {
        $walk($section['fields'], $type);
    }
    eq($required, [
        'hero.doors.label', 'hero.doors.link',
        'statement.text',
        'work_gallery.heading', 'work_gallery.items', 'work_gallery.items.label', 'work_gallery.items.title', 'work_gallery.items.visual',
        'versus.sides', 'versus.sides.title',
        'rich_text.body',
        'text_image.heading', 'text_image.body', 'text_image.image',
        'card_grid.heading', 'card_grid.cards', 'card_grid.cards.title', 'card_grid.cards.text',
        'solutions.heading', 'solutions.items', 'solutions.items.title', 'solutions.items.text', 'solutions.items.link',
        'use_cases.heading', 'use_cases.items', 'use_cases.items.title',
        'crossoff.heading', 'crossoff.items', 'crossoff.items.title', 'crossoff.items.outcome',
        'process_steps.heading', 'process_steps.steps', 'process_steps.steps.title', 'process_steps.steps.text',
        'tools.heading', 'tools.flow.title', 'tools.tools', 'tools.tools.name',
        'curriculum.heading', 'curriculum.modules', 'curriculum.modules.track', 'curriculum.modules.day_range', 'curriculum.modules.title',
        'stats.items', 'stats.items.value', 'stats.items.label',
        'testimonials.heading', 'testimonials.items', 'testimonials.items.name', 'testimonials.items.role',
        'case_study.heading', 'case_study.client', 'case_study.summary', 'case_study.built',
        'case_study.built.title', 'case_study.built.text',
        'faqs.heading', 'faqs.items', 'faqs.items.question', 'faqs.items.answer',
        'lead_form.heading', 'lead_form.form_type', 'lead_form.side_points.text',
        'cta.heading', 'cta.button_label', 'cta.button_link',
        'people.heading', 'people.items', 'people.items.name', 'people.items.role',
    ]);
}

function test_select_options(): void
{
    $r = section_registry();
    eq($r['hero']['fields']['variant']['options'], ['split', 'centered', 'cover', 'stage']);
    eq($r['text_image']['fields']['image_side']['options'], ['left', 'right']);
    eq($r['card_grid']['fields']['columns']['options'], ['2', '3', '4']);
    eq($r['curriculum']['fields']['modules']['fields']['track']['options'], ['foundation', 'full']);
    eq($r['lead_form']['fields']['form_type']['options'], ['masterclass', 'corporate', 'services', 'contact']);
    eq($r['map_contact']['fields']['show_map']['options'], ['yes', 'no']);
    eq($r['blog_list']['fields']['count']['options'], ['3', '6', '12']);
}

function test_card_icon_options(): void
{
    eq(section_registry()['card_grid']['fields']['cards']['fields']['icon']['options'], [
        'graduation-cap', 'chalkboard-teacher', 'student', 'users-three', 'briefcase', 'buildings', 'factory',
        'first-aid-kit', 'flow-arrow', 'tree-structure', 'plugs-connected', 'chat-circle-text', 'microphone',
        'file-text', 'magnifying-glass', 'database', 'globe', 'pen-nib', 'megaphone', 'chart-line-up', 'clock',
        'gear-six', 'lock-key', 'handshake',
    ]);
}

function test_hero_design_fields(): void
{
    $hero = section_registry()['hero']['fields'];
    $doors = $hero['doors'];
    eq($doors['type'], 'list');
    eq($doors['label'], 'Doors (chooser rows)');
    eq(array_keys($doors['fields']), ['label', 'note', 'link']);
    eq(array_map(fn($f) => $f['type'], $doors['fields']), ['label' => 'text', 'note' => 'text', 'link' => 'text']);
    ok(!empty($doors['fields']['label']['required']) && !empty($doors['fields']['link']['required']));
    ok(empty($doors['fields']['note']['required']));
    eq($hero['drawing']['type'], 'select');
    eq($hero['drawing']['label'], 'Drawing');
    eq($hero['drawing']['options'], ['none', 'predict', 'documents', 'masterclass', 'corporate', 'services']);
}

function test_stats_note_default(): void
{
    eq(section_registry()['stats']['fields']['note']['default'], 'Typical outcomes for AI-enabled teams.');
}
