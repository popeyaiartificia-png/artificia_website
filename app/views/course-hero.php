<?php
/** The AI Masterclass hero: the shared journey layout with the course's own wording as defaults (fields override them).
 *  Course content stays server rendered below. */
$j = [
    'format' => 'In person, Vadodara · 1 hour a day',
    'primary_cta_label' => 'Explore the masterclass', 'primary_cta_link' => '#curriculum',
    'secondary_cta_label' => 'Help me choose', 'secondary_cta_link' => '#enquire',
    'before_label' => 'Before the course', 'before_text' => 'Still doing repetitive work?',
    'after_label' => 'After the course', 'after_text' => 'AI-empowered. Ready for real work.',
    'stat_value' => '60', 'stat_unit' => 'days', 'stat_label' => 'AI Masterclass',
];
include VIEWS_DIR . '/journey-hero.php';
