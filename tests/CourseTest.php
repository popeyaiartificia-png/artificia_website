<?php
declare(strict_types=1);

function course_seed(): array
{
    return json_decode(file_get_contents(__DIR__ . '/../content/seed/pages/ai-masterclass.json'), true, flags: JSON_THROW_ON_ERROR);
}

function test_course_cms_roundtrip_keeps_module_overview_and_fees(): void
{
    $page = course_seed();
    $normalized = sections_normalize($page['sections']);
    ok($normalized == $page['sections'], 'all course fields survive an admin save (field order may follow the registry)');
    $curriculum = array_values(array_filter($normalized, fn($section) => $section['type'] === 'curriculum'))[0];
    $modules = $curriculum['fields']['modules'];
    eq(count($modules), 12);
    foreach ($modules as $module) ok($module['topics'] !== '' && !isset($module['lessons']), 'overview only, no day-by-day lessons');
    $plans = array_values(array_filter($normalized, fn($section) => ($section['fields']['variant'] ?? '') === 'course-plans'))[0];
    eq(array_column($plans['fields']['cards'], 'price'), ['₹20,000', '₹40,000']);
}

function test_course_renders_accessible_content_and_plan_selection(): void
{
    $page = course_seed();
    $html = render_page_body($page, []);
    eq(substr_count($html, '<h1'), 1);
    eq(substr_count($html, '<details class="course-module"'), 12);
    ok(!str_contains($html, 'Day 1 — '), 'no day-by-day lesson list');
    ok(str_contains($html, 'Days 1–30 / Included in both courses'));
    ok(str_contains($html, 'Days 31–60 / Full program only'));
    ok(str_contains($html, 'data-course-track="30-day Foundation"'));
    ok(str_contains($html, 'data-course-track="60-day Full program"'));
    ok(str_contains($html, 'name="ts"'), 'standard protected enquiry retained');
    ok(str_contains($html, 'method="post" action="/lead"'));
    ok(str_contains($html, 'id="overview"') && str_contains($html, 'id="course-tools"'));
    ok(str_contains($html, 'AI <em>Masterclass.</em>') && str_contains($html, 'Live AI training in Vadodara'), 'course identity and location are server rendered');
    ok(str_contains($html, 'aria-label="Breadcrumb"') && str_contains($html, 'aria-current="page"'));
    ok(str_contains($html, 'Before the course') && str_contains($html, 'After the course'), 'the transformation remains understandable without JavaScript');
    ok(str_contains($html, 'Still doing repetitive work?') && str_contains($html, 'AI-empowered. Ready for real work.'), 'the two endpoints connect a real work problem to the AI skill taught');
    $graph = schema_graph($page, [], 'https://artificiaai.com');
    $breadcrumbs = array_values(array_filter($graph, fn($node) => $node['@type'] === 'BreadcrumbList'));
    eq($breadcrumbs[0]['itemListElement'], [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => 'https://artificiaai.com/'],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'AI Masterclass', 'item' => 'https://artificiaai.com/ai-masterclass'],
    ]);
}

function test_course_variants_escape_editor_content(): void
{
    $html = render_section(['type' => 'card_grid', 'fields' => ['variant' => 'course-plans', 'cards' => [
        ['title' => '<script>bad()</script>', 'price' => '<b>20,000</b>', 'text' => '<img src=x onerror=bad()>', 'link' => 'javascript:bad()'],
    ]]]);
    ok(!str_contains($html, '<script>'));
    ok(!str_contains($html, 'href="javascript:'));
    ok(str_contains($html, '&lt;script&gt;'));
    ok(str_contains($html, '&lt;b&gt;20,000&lt;/b&gt;'));
    $html = render_section(['type' => 'curriculum', 'fields' => ['variant' => 'course', 'modules' => [
        ['title' => '<img src=x>', 'topics' => '<script>bad()</script>'],
    ]]]);
    ok(!str_contains($html, '<script>') && !str_contains($html, '<img'));
    $html = render_section(['type' => 'process_steps', 'fields' => ['variant' => 'course-projects', 'heading' => '<script>bad()</script>', 'cta_link' => 'javascript:bad()', 'steps' => [
        ['title' => '<img src=x>', 'text' => '<script>bad()</script>', 'tools' => '<b>Tool</b>', 'visual' => '<script>'],
    ]]]);
    ok(!str_contains($html, '<script>') && !str_contains($html, '<img'));
    ok(!str_contains($html, 'href="javascript:') && str_contains($html, '&lt;b&gt;Tool&lt;/b&gt;'));
}
