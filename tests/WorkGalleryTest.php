<?php
declare(strict_types=1);

function work_gallery_test_items(): array
{
    return [
        ['visual' => 'ghl', 'label' => 'CRM setup', 'title' => 'Connected CRM'],
        ['visual' => 'support', 'label' => 'IT support', 'title' => 'Connected support'],
        ['visual' => 'website', 'label' => 'Website', 'title' => 'A brand website'],
        ['visual' => 'conversations', 'label' => 'AI automation', 'title' => 'Automated work'],
        ['visual' => 'voice', 'label' => 'Our services / Voice agents', 'title' => 'Business conversations'],
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

function work_gallery_test_rag_item(): array
{
    return [
        'visual' => 'knowledge',
        'label' => 'Our services / RAG chatbot',
        'title' => 'Answers from company documents',
        'link' => '/services/rag-custom-automation',
    ];
}

function work_gallery_test_combined_item(): array
{
    return [
        'visual' => 'assistants',
        'label' => 'Our services / Voice AI & RAG chatbots',
        'title' => 'Voice agents and document-powered chatbots for your business.',
        'link' => '/services',
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

function test_home_legacy_digital_gallery_combines_voice_and_chat(): void
{
    $items = [...work_gallery_test_items(), work_gallery_test_removed_item()];
    $original = $items;
    $html = work_gallery_test_render($items);

    eq(work_gallery_test_visuals($html), ['ghl', 'support', 'website', 'conversations', 'assistants']);
    ok(!str_contains($html, 'Legacy digital solutions'), 'removed card content is not rendered');
    ok(str_contains($html, 'Our services / Voice AI &amp; RAG chatbots'), 'one card names both services');
    ok(str_contains($html, 'href="/services"'), 'combined card links to the services page');
    ok(str_contains($html, 'Voice agents for calls. RAG chatbots for answers from your documents.'), 'both capabilities remain visible');
    eq($items, $original, 'rendering leaves the saved CMS items intact');
    eq(substr_count($html, '<li><img src="/assets/img/brands/'), 12, 'all brand logos remain');
}

function test_home_legacy_five_card_gallery_gets_combined_service(): void
{
    $items = work_gallery_test_items();
    $html = work_gallery_test_render($items);

    eq(work_gallery_test_visuals($html), ['ghl', 'support', 'website', 'conversations', 'assistants']);
    ok(str_contains($html, 'Voice agents and document-powered chatbots for your business.'));
    foreach (array_slice($items, 0, 4) as $item) {
        ok(str_contains($html, $item['label']), 'retained card label: ' . $item['label']);
        ok(str_contains($html, $item['title']), 'retained card title: ' . $item['title']);
    }
}

function test_home_current_voice_and_rag_gallery_renders_one_combined_card(): void
{
    $items = [...work_gallery_test_items(), work_gallery_test_rag_item()];
    $html = work_gallery_test_render($items);

    eq(work_gallery_test_visuals($html), ['ghl', 'support', 'website', 'conversations', 'assistants']);
    eq(substr_count($html, 'Our services / Voice AI &amp; RAG chatbots'), 1, 'only one combined card');
    ok(!str_contains($html, 'Our services / Voice agents'), 'the standalone voice card is gone');
    ok(!str_contains($html, 'Our services / RAG chatbot'), 'the standalone RAG card is gone');
}

function test_home_legacy_and_current_duplicates_still_render_one_combined_card(): void
{
    $items = [...work_gallery_test_items(), work_gallery_test_removed_item(), work_gallery_test_rag_item()];
    $html = work_gallery_test_render($items);

    eq(work_gallery_test_visuals($html), ['ghl', 'support', 'website', 'conversations', 'assistants']);
    eq(substr_count($html, 'Our services / Voice AI &amp; RAG chatbots'), 1);
    ok(!str_contains($html, 'Legacy digital solutions'), 'retired card stays removed');
}

function test_home_existing_combined_card_preserves_custom_metadata_and_is_not_duplicated(): void
{
    $combined = work_gallery_test_combined_item();
    $combined['label'] = 'Custom voice and knowledge assistants';
    $combined['title'] = 'Approved combined service description';
    $combined['link'] = '/services#enquire';
    $items = [...array_slice(work_gallery_test_items(), 0, 4), $combined];
    $html = work_gallery_test_render($items);

    eq(work_gallery_test_visuals($html), ['ghl', 'support', 'website', 'conversations', 'assistants']);
    ok(str_contains($html, $combined['label']), 'custom combined label remains');
    ok(str_contains($html, $combined['title']), 'custom combined description remains');
    ok(str_contains($html, 'href="/services#enquire"'), 'custom combined link remains');
    eq(work_gallery_items($items, 'home'), $items, 'already combined CMS fields are unchanged');

    $withLegacy = [...work_gallery_test_items(), work_gallery_test_rag_item(), $combined];
    $legacyHtml = work_gallery_test_render($withLegacy);
    eq(work_gallery_test_visuals($legacyHtml), ['ghl', 'support', 'website', 'conversations', 'assistants']);
    ok(str_contains($legacyHtml, $combined['title']), 'existing combined metadata takes precedence over legacy copies');
    ok(str_contains($legacyHtml, 'href="/services#enquire"'));
    $normalized = work_gallery_items($withLegacy, 'home');
    eq(work_gallery_items($normalized, 'home'), $normalized, 'normalizing again adds or removes nothing');
}

function test_home_combined_card_uses_first_matching_service_position(): void
{
    $projects = array_slice(work_gallery_test_items(), 0, 3);
    $automation = work_gallery_test_items()[3];
    $voice = work_gallery_test_items()[4];
    $html = work_gallery_test_render([...$projects, work_gallery_test_rag_item(), $automation, $voice]);

    eq(work_gallery_test_visuals($html), ['ghl', 'support', 'website', 'assistants', 'conversations']);
}

function test_other_pages_retain_separate_voice_rag_and_digital_cards(): void
{
    $items = [...work_gallery_test_items(), work_gallery_test_removed_item(), work_gallery_test_rag_item()];
    $html = work_gallery_test_render($items, 'services');

    eq(work_gallery_test_visuals($html), ['ghl', 'support', 'website', 'conversations', 'voice', 'knowledge', 'knowledge']);
    ok(str_contains($html, 'Our services / Voice agents'));
    ok(str_contains($html, 'Our services / RAG chatbot'));
    ok(str_contains($html, 'Legacy digital solutions'), 'combining applies only to the homepage');
}

function test_home_retains_unrelated_voice_knowledge_and_same_label_cards(): void
{
    $knowledge = work_gallery_test_removed_item();
    $knowledge['label'] = 'Knowledge management';
    $knowledge['title'] = 'A searchable knowledge base';
    $sameLabel = work_gallery_test_removed_item();
    $sameLabel['visual'] = 'voice';
    $sameLabel['title'] = 'A different digital service';
    $voice = ['visual' => 'voice', 'label' => 'Specialist phone agents', 'title' => 'Custom voice example'];
    $html = work_gallery_test_render([$knowledge, $sameLabel, $voice]);

    eq(work_gallery_test_visuals($html), ['knowledge', 'voice', 'voice']);
    ok(str_contains($html, $knowledge['title']), 'other knowledge cards remain');
    ok(str_contains($html, $sameLabel['title']), 'the label alone does not remove a card');
    ok(str_contains($html, $voice['title']), 'unrelated voice cards remain');
}
