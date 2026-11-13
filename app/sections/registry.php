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
            'variant' => $select('Layout', ['split', 'centered', 'cover', 'stage', 'course', 'solutions', 'about', 'corporate', 'contact', 'journey']),
            // The masterclass layout, used by every inner page: a short statement, the before and after captions on the
            // ribbon, and one verified figure on the right. Empty fields fall back to the subheading or are left out.
            'statement' => $text('Short statement (large, left of the visual)'),
            'format' => $text('Small line under the statement'),
            'before_label' => $text('Before caption: label'),
            'before_text' => $text('Before caption'),
            'after_label' => $text('After caption: label'),
            'after_text' => $text('After caption'),
            'stat_value' => $text('Figure on the right (verified only)'),
            'stat_unit' => $text('Figure unit'),
            'stat_label' => $text('Figure label'),
            'visual' => $select('Centre visual (journey layout)', ['ribbon', 'solutions', 'map', 'pipeline', 'website', 'content', 'answers', 'care', 'factory', 'projects', 'desk', 'business', 'orkay']),
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
            // pillars: big ruled rows, `*words*` in a title turn serif. notes: tall columns, each drawn from its icon.
            // kit: documents dealt into a spread, each page drawn from its icon.
            'variant' => $select('Layout', ['standard', 'course-plans', 'pillars', 'notes', 'kit']),
            'eyebrow' => $text('Eyebrow (pillars only)'),
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
                'price' => $text('Course fee (course plans only)'),
                'duration' => $text('Duration (course plans only)'),
            ], true),
        ]],
        'solutions' => ['label' => 'Solutions gallery', 'fields' => [
            'variant' => $select('Layout', ['grid', 'chapters', 'programmes']),
            'heading' => $text('Heading', true),
            'intro' => $area('Intro'),
            'link_label' => $text('All solutions link label'),
            'link' => $text('All solutions link'),
            'items' => $list('Solutions', [
                'title' => $text('Title', true),
                'text' => $area('Description', true),
                'link' => $text('Service page link', true),
                'visual' => $select('Illustration', ['workflow', 'agentic', 'software', 'marketing', 'knowledge', 'search', 'cloud']),
                'link_label' => $text('Link label (chapters layout)'),
                'includes' => $area('What it includes, one per line (chapters layout)'),
            ], true),
        ]],
        'use_cases' => ['label' => 'Use cases (before and after)', 'fields' => [
            'variant' => $select('Layout', ['standard', 'lines', 'departments']),
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
            'variant' => $select('Layout', ['standard', 'course-projects']),
            'heading' => $text('Heading', true),
            'intro' => $area('Intro'),
            'cta_label' => $text('Button label'),
            'cta_link' => $text('Button link'),
            'steps' => $list('Steps', [
                'title' => $text('Title', true),
                'text' => $area('Text', true),
                'visual' => $select('Course project preview', ['website', 'content', 'assistant', 'automation']),
                'tools' => $text('Project tools'),
                'day_range' => $text('Syllabus days'),
            ], true),
        ]],
        'tools' => ['label' => 'Tools', 'fields' => [
            'variant' => $select('Layout', ['standard', 'course-toolkit']),
            'heading' => $text('Heading', true),
            'intro' => $area('Intro'),
            'flow' => $list('Flow (one piece of work, step by step)', [
                'title' => $text('Step', true),
                'note' => $text('Note'),
            ]),
            'tools_label' => $text('Label above the tools (flow layout; default "Built on")'),
            'tools_more' => $text('Closing line under the tools, e.g. "And any tool your team already uses" (shows the "many more" panel)'),
            'tools_more_note' => $text('Small note under the closing line'),
            'tools' => $list('Tools', [
                'name' => $text('Name', true),
                'brand' => $select('Logo (built in)', array_keys(require __DIR__ . '/../views/brands.php')),
                'symbol' => $select('Symbol when no brand mark is available', $icons),
                'logo' => $image('Logo upload (replaces the built-in one)'),
                'note' => $text('Note'),
            ], true),
        ]],
        'curriculum' => ['label' => 'Curriculum', 'fields' => [
            'variant' => $select('Layout', ['standard', 'course']),
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
            'layout' => $select('Layout', ['standard', 'fan', 'proof']),
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
            // pass: a ticket with the details on its stub (the corporate free pilot).
            'variant' => $select('Layout', ['standard', 'pass']),
            'heading' => $text('Heading', true),
            'text' => $area('Text'),
            'button_label' => $text('Button label', true),
            'button_link' => $text('Button link', true),
            'details' => $list('Details (pass layout)', [
                'label' => $text('Label', true),
                'value' => $text('Value', true),
            ]),
        ]],
        'people' => ['label' => 'People', 'fields' => [
            'heading' => $text('Heading', true),
            'items' => $list('People', [
                'name' => $text('Name', true),
                'role' => $text('Role', true),
                'bio' => $area('Bio'),
                // One per line, "Label: text". Credentials show under the bio; "more" sits behind a Know more toggle,
                // where a text written as "a · b · c" shows as tags.
                'credentials' => $area('Credentials (one per line, Label: text)'),
                'more' => $area('Know more points (one per line, Title: text)'),
                'quote' => $text('Guiding philosophy (optional)'),
                'photo' => $image('Photo'),
                'photo_2' => $image('Second photo (optional)'),
                'linkedin' => $text('LinkedIn link'),
            ], true),
        ]],
        // Address, phone and email come from settings.
        'map_contact' => ['label' => 'Map and contact details', 'fields' => [
            'heading' => $text('Heading'),
            'show_map' => $select('Show map', ['yes', 'no']),
            // address: the address and map only, for a page that lists the phone, WhatsApp and email elsewhere (Contact's hero).
            'details' => $select('Details shown', ['all', 'address']),
        ]],
        'blog_list' => ['label' => 'Latest blog posts', 'fields' => [
            'heading' => $text('Heading'),
            'count' => $select('Number of posts', ['3', '6', '12']),
        ]],
    ];
}
