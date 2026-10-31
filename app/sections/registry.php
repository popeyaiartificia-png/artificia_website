<?php
declare(strict_types=1);

/** One field definition. $more carries options / fields / default. */
function section_field(string $type, string $label, bool $required = false, array $more = []): array
{
    return ['type' => $type, 'label' => $label] + ($required ? ['required' => true] : []) + $more;
}

/**
 * Every section type an editor can add to a page, with its fields.
 * @return array<string, array{label:string, fields:array<string, array>}>
 */
function section_registry(): array
{
    $text = fn(string $label, bool $req = false) => section_field('text', $label, $req);
    $area = fn(string $label, bool $req = false) => section_field('textarea', $label, $req);
    $rich = fn(string $label, bool $req = false) => section_field('richtext', $label, $req);
    $image = fn(string $label, bool $req = false) => section_field('image', $label, $req);
    $select = fn(string $label, array $options, bool $req = false) => section_field('select', $label, $req, ['options' => $options]);
    $list = fn(string $label, array $fields, bool $req = false) => section_field('list', $label, $req, ['fields' => $fields]);

    $icons = [
        'graduation-cap', 'chalkboard-teacher', 'student', 'users-three', 'briefcase', 'buildings', 'factory',
        'first-aid-kit', 'flow-arrow', 'tree-structure', 'plugs-connected', 'chat-circle-text', 'microphone',
        'file-text', 'magnifying-glass', 'database', 'globe', 'pen-nib', 'megaphone', 'chart-line-up', 'clock',
        'gear-six', 'lock-key', 'handshake',
    ];

    return [
        // The heading is the page's h1, so the hero has no heading field.
        'hero' => ['label' => 'Hero', 'fields' => [
            'eyebrow' => $text('Eyebrow'),
            'subheading' => $area('Subheading'),
            'primary_cta_label' => $text('Primary button label'),
            'primary_cta_link' => $text('Primary button link'),
            'secondary_cta_label' => $text('Secondary button label'),
            'secondary_cta_link' => $text('Secondary button link'),
            'image' => $image('Image'),
            'variant' => $select('Layout', ['split', 'centered', 'cover', 'stage']),
            'doors' => $list('Doors (chooser rows)', [
                'label' => $text('Label', true),
                'note' => $text('Note'),
                'link' => $text('Link', true),
            ]),
            'drawing' => $select('Drawing', ['none', 'predict', 'documents', 'masterclass', 'corporate', 'services']),
        ]],
        // One sentence set very large; `*words*` turn serif and neon.
        'statement' => ['label' => 'Statement (large type)', 'fields' => [
            'text' => $area('Statement', true),
        ]],
        'work_gallery' => ['label' => 'Work gallery (floating visuals)', 'fields' => [
            'heading' => $text('Heading', true),
            'thought' => $area('Short gallery statement'),
            'review_text' => $area('Client review text (replaces the gallery statement)'),
            'review_status' => section_field('select', 'Review status', false, ['options' => ['draft', 'approved'], 'default' => 'draft']),
            'review_name' => $text('Reviewer name'),
            'review_company' => $text('Reviewer company'),
            'items' => $list('Visuals', [
                'label' => $text('Caption', true),
                'title' => $text('Short description', true),
                'visual' => $select('Visual', ['website', 'mobile', 'ghl', 'conversations', 'workflow', 'support', 'knowledge', 'voice'], true),
                'image' => $image('Image upload (replaces the built-in visual)'),
                'link' => $text('Link'),
            ], true),
        ]],
        // Two sides set against each other (landonorris.com's "On track / Off track"), a cut-out figure at each edge.
        // In a title the `*emphasised*` word becomes the serif top line.
        'versus' => ['label' => 'Two sides (before and after)', 'fields' => [
            'sides' => $list('Sides (the first two are shown)', [
                'title' => $text('Title', true),
                'text' => $area('Text'),
                'status_label' => $text('Static status label (replaces the link)'),
                'link' => $text('Link'),
                'link_label' => $text('Link label (read out by screen readers)'),
                'figure' => $select('Figure (built in)', ['person', 'robot', 'none']),
                'image' => $image('Figure upload, a cut-out (replaces the built-in one)'),
            ], true),
        ]],
        'rich_text' => ['label' => 'Text block', 'fields' => [
            'heading' => $text('Heading'),
            'body' => $rich('Body', true),
        ]],
        'text_image' => ['label' => 'Text and image', 'fields' => [
            'heading' => $text('Heading', true),
            'body' => $rich('Body', true),
            'image' => $image('Image', true),
            'image_side' => $select('Image side', ['left', 'right']),
            'cta_label' => $text('Button label'),
            'cta_link' => $text('Button link'),
        ]],
        'card_grid' => ['label' => 'Card grid', 'fields' => [
            'heading' => $text('Heading', true),
            'intro' => $area('Intro'),
            'columns' => $select('Columns', ['2', '3', '4']),
            'cards' => $list('Cards', [
                'icon' => $select('Icon', $icons),
                'drawing' => $select('Drawing (replaces the icon)', ['none', 'documents', 'masterclass', 'corporate', 'services']),
                'image' => $image('Photo (replaces the icon and drawing)'),
                'title' => $text('Title', true),
                'text' => $area('Text', true),
                'link' => $text('Link'),
            ], true),
        ]],
        'solutions' => ['label' => 'Solutions gallery', 'fields' => [
            'heading' => $text('Heading', true),
            'intro' => $area('Intro'),
            'link_label' => $text('All solutions link label'),
            'link' => $text('All solutions link'),
            'items' => $list('Solutions', [
                'title' => $text('Title', true),
                'text' => $area('Description', true),
                'link' => $text('Service page link', true),
                'visual' => $select('Illustration', ['workflow', 'agentic', 'software', 'marketing', 'knowledge', 'search', 'cloud']),
            ], true),
        ]],
        'use_cases' => ['label' => 'Use cases (before and after)', 'fields' => [
            'heading' => $text('Heading', true),
            'intro' => $area('Intro'),
            'items' => $list('Use cases', [
                'title' => $text('Title', true),
                'before' => $area('Before'),
                'after' => $area('After'),
            ], true),
        ]],
        // Problems struck through with the neon marker, each with its outcome. The optional open line is left uncrossed for the visitor.
        'crossoff' => ['label' => 'Crossed-off list (problem to outcome)', 'fields' => [
            'heading' => $text('Heading', true),
            'intro' => $area('Intro'),
            'items' => $list('Problems', [
                'title' => $text('Problem (crossed off)', true),
                'outcome' => $text('Outcome', true),
                'text' => $area('How it works'),
            ], true),
            'open_title' => $text('Open line (left uncrossed)'),
            'open_outcome' => $text('Open line: short answer'),
            'open_text' => $area('Open line: text'),
            'cta_label' => $text('Button label'),
            'cta_link' => $text('Button link'),
        ]],
        'process_steps' => ['label' => 'Process steps', 'fields' => [
            'heading' => $text('Heading', true),
            'intro' => $area('Intro'),
            'steps' => $list('Steps', [
                'title' => $text('Title', true),
                'text' => $area('Text', true),
            ], true),
        ]],
        'tools' => ['label' => 'Tools', 'fields' => [
            'heading' => $text('Heading', true),
            'intro' => $area('Intro'),
            'flow' => $list('Flow (one piece of work, step by step)', [
                'title' => $text('Step', true),
                'note' => $text('Note'),
            ]),
            'tools' => $list('Tools', [
                'name' => $text('Name', true),
                'brand' => $select('Logo (built in)', array_keys(require __DIR__ . '/../views/brands.php')),
                'logo' => $image('Logo upload (replaces the built-in one)'),
                'note' => $text('Note'),
            ], true),
        ]],
        'curriculum' => ['label' => 'Curriculum', 'fields' => [
            'heading' => $text('Heading', true),
            'intro' => $area('Intro'),
            'modules' => $list('Modules', [
                'track' => $select('Track', ['foundation', 'full'], true),
                'day_range' => $text('Day range', true),
                'title' => $text('Title', true),
                'topics' => $area('Topics'),
                'tools' => $text('Tools'),
            ], true),
        ]],
        'stats' => ['label' => 'Stats', 'fields' => [
            'heading' => $text('Heading'),
            'note' => section_field('text', 'Note under the numbers', false, ['default' => 'Typical outcomes for AI-enabled teams.']),
            'items' => $list('Stats', [
                'value' => $text('Value', true),
                'label' => $text('Label', true),
            ], true),
        ]],
        'testimonials' => ['label' => 'Testimonials', 'fields' => [
            'heading' => $text('Heading', true),
            'items' => $list('Testimonials', [
                'name' => $text('Name', true),
                'role' => $text('Role', true),
                'quote' => $area('Quote'),
                'photo' => $image('Photo'),
                'video_url' => $text('Video link'),
            ], true),
        ]],
        'case_study' => ['label' => 'Case study', 'fields' => [
            'heading' => $text('Heading', true),
            'client' => $text('Client', true),
            'summary' => $area('Summary', true),
            'image' => $image('Screenshot or photo of the work'),
            'challenge' => $rich('Challenge'),
            'built' => $list('What we built', [
                'title' => $text('Title', true),
                'text' => $area('Text', true),
            ], true),
            'results' => $list('Results', [
                'value' => $text('Value'),
                'label' => $text('Label'),
            ]),
            'logo' => $image('Client logo'),
            'link_label' => $text('Link label'),
            'link' => $text('Link'),
        ]],
        'faqs' => ['label' => 'FAQs', 'fields' => [
            'heading' => $text('Heading', true),
            'items' => $list('Questions', [
                'question' => $text('Question', true),
                'answer' => $area('Answer', true),
            ], true),
        ]],
        'lead_form' => ['label' => 'Enquiry form', 'fields' => [
            'heading' => $text('Heading', true),
            'intro' => $area('Intro'),
            'form_type' => $select('Form type', ['masterclass', 'corporate', 'services', 'contact'], true),
            'button_label' => $text('Button label'),
            'side_points' => $list('Side points', [
                'text' => $text('Text', true),
            ]),
        ]],
        'cta' => ['label' => 'Call to action', 'fields' => [
            'heading' => $text('Heading', true),
            'text' => $area('Text'),
            'button_label' => $text('Button label', true),
            'button_link' => $text('Button link', true),
        ]],
        'people' => ['label' => 'People', 'fields' => [
            'heading' => $text('Heading', true),
            'items' => $list('People', [
                'name' => $text('Name', true),
                'role' => $text('Role', true),
                'bio' => $area('Bio'),
                'photo' => $image('Photo'),
                'linkedin' => $text('LinkedIn link'),
            ], true),
        ]],
        // Address, phone and email come from settings.
        'map_contact' => ['label' => 'Map and contact details', 'fields' => [
            'heading' => $text('Heading'),
            'show_map' => $select('Show map', ['yes', 'no']),
        ]],
        'blog_list' => ['label' => 'Latest blog posts', 'fields' => [
            'heading' => $text('Heading'),
            'count' => $select('Number of posts', ['3', '6', '12']),
        ]],
    ];
}
