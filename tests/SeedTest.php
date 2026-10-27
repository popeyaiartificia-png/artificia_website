<?php
declare(strict_types=1);

function seed_test_dir(): string
{
    return __DIR__ . '/../content/seed';
}

function test_seed_import_idempotent(): void
{
    $db = test_db();
    $uploads = temp_dir();
    $first = seed_import($db, seed_test_dir(), $uploads);
    foreach (['pages', 'templates', 'redirects', 'media'] as $table) {
        ok($first[$table] > 0, "$table imported");
        eq((int) $db->query("SELECT COUNT(*) FROM $table")->fetchColumn(), $first[$table], "$table rows");
    }
    eq(settings($db)['org_email'] ?? null, 'info@artificiaai.com');
    setting_set($db, 'org_phone', '+91 00000 00000'); // an admin's edit survives a re-run

    eq(seed_import($db, seed_test_dir(), $uploads, true), $first);
    foreach (['pages', 'templates', 'redirects', 'media'] as $table) {
        eq((int) $db->query("SELECT COUNT(*) FROM $table")->fetchColumn(), $first[$table], "$table rows after re-run");
    }
    eq(settings($db)['org_phone'], '+91 00000 00000');

    $students = page_find_published($db, 'ai-masterclass/students');
    ok(positive_int($students['template_id']) !== null, 'a template page is linked to its template');
    ok(is_int($students['sections'][0]['fields']['image'] ?? null), 'image filenames became media ids');
    eq(redirect_match($db, '/about.html'), ['to' => '/about', 'code' => 301]);
}

function test_seed_refuses_nonempty_without_force(): void
{
    $db = test_db();
    page_save($db, ['slug' => 'existing', 'title' => 'Existing', 'sections' => []], 1);
    try {
        seed_import($db, seed_test_dir(), temp_dir());
        throw new LogicException('expected a refusal');
    } catch (RuntimeException $e) {
        ok(str_contains($e->getMessage(), 'already has pages'), $e->getMessage());
    }
    eq((int) $db->query('SELECT COUNT(*) FROM templates')->fetchColumn(), 0, 'nothing imported');
}
