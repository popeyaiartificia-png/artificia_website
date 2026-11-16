<?php
declare(strict_types=1);

function work_gallery_test_items(): array
{
    return [
        ['visual' => 'ghl', 'label' => 'CRM setup', 'title' => 'Connected CRM'],
        ['visual' => 'support', 'label' => 'IT support', 'title' => 'Connected support'],
        ['visual' => 'website', 'label' => 'Website', 'title' => 'A brand website'],
        ['visual' => 'conversations', 'label' => 'AI automation', 'title' => 'Automated work'],
        ['visual' => 'voice', 'label' => 'Voice agents', 'title' => 'Business conversations'],
    ];
}

function work_gallery_test_removed_item(): array
{
    return [
        'visual' => 'knowledge',
        'label' => 'Our services / Digital solutions',
        'title' => 'Legacy digital solutions',
    ];
}

function work_gallery_test_render(array $items, string $slug = 'home'): string
{
    return render_section_in([
        'type' => 'work_gallery',
        'fields' => ['heading' => 'Our work', 'items' => $items],
    ], 0, ['slug' => $slug], []);
}

function work_gallery_test_visuals(string $html): array
{
    preg_match_all('/<li class="workscape__item workscape__item--([^\s"]+)(?:\s+[^"]+)?">/', $html, $matches);
    return $matches[1];
}

function test_home_legacy_gallery_omits_removed_digital_solutions_card(): void
{
    $items = [...work_gallery_test_items(), work_gallery_test_removed_item()];
    $original = $items;
    $html = work_gallery_test_render($items);

    eq(work_gallery_test_visuals($html), ['ghl', 'support', 'website', 'conversations', 'voice']);
    ok(!str_contains($html, 'Legacy digital solutions'), 'removed card content is not rendered');
    eq($items, $original, 'rendering leaves the saved CMS items intact');
    eq(substr_count($html, '<li><img src="/assets/img/brands/'), 12, 'all brand logos remain');
}

function test_current_five_card_home_gallery_is_unchanged(): void
{
    $items = work_gallery_test_items();
    $html = work_gallery_test_render($items);

    eq(work_gallery_test_visuals($html), ['ghl', 'support', 'website', 'conversations', 'voice']);
    foreach ($items as $item) {
        ok(str_contains($html, $item['label']), 'retained card label: ' . $item['label']);
        ok(str_contains($html, $item['title']), 'retained card title: ' . $item['title']);
    }
}

function test_other_page_retains_digital_solutions_gallery_card(): void
{
    $html = work_gallery_test_render([work_gallery_test_removed_item()], 'services');

    eq(work_gallery_test_visuals($html), ['knowledge']);
    ok(str_contains($html, 'Legacy digital solutions'), 'the removal applies only to the homepage');
}

function test_home_retains_unrelated_knowledge_and_same_label_cards(): void
{
    $knowledge = work_gallery_test_removed_item();
    $knowledge['label'] = 'Knowledge management';
    $knowledge['title'] = 'A searchable knowledge base';
    $sameLabel = work_gallery_test_removed_item();
    $sameLabel['visual'] = 'voice';
    $sameLabel['title'] = 'A different digital service';
    $html = work_gallery_test_render([$knowledge, $sameLabel]);

    eq(work_gallery_test_visuals($html), ['knowledge', 'voice']);
    ok(str_contains($html, $knowledge['title']), 'other knowledge cards remain');
    ok(str_contains($html, $sameLabel['title']), 'the label alone does not remove a card');
}
