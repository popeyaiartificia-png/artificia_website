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

/** A copy of content/seed with only home.json plus $extra page files; media is a symlink, never written. */
function seed_test_copy(array $extra): string
{
    $dir = temp_dir();
    foreach (['templates.json', 'redirects.json', 'settings.json'] as $f) {
        copy(seed_test_dir() . "/$f", "$dir/$f");
    }
    symlink(realpath(seed_test_dir() . '/media'), "$dir/media");
    mkdir("$dir/pages");
    copy(seed_test_dir() . '/pages/home.json', "$dir/pages/home.json");
    foreach ($extra as $name => $json) {
        file_put_contents("$dir/pages/$name", $json);
    }
    return $dir;
}

function test_seed_bad_file_names_it_and_writes_nothing(): void
{
    $cases = [
        'broken.json' => '{"slug": "broken",',
        'orphan.json' => '{"slug": "orphan", "template": "No such template", "sections": []}',
    ];
    foreach ($cases as $name => $json) {
        $db = test_db();
        try {
            seed_import($db, seed_test_copy([$name => $json]), temp_dir());
            throw new LogicException("expected $name to be refused");
        } catch (RuntimeException $e) {
            ok(str_contains($e->getMessage(), $name), $e->getMessage());
        }
        foreach (['pages', 'templates', 'redirects', 'media', 'settings'] as $table) {
            eq((int) $db->query("SELECT COUNT(*) FROM $table")->fetchColumn(), 0, "$name: nothing written to $table");
        }
    }
}

function test_seed_page_failing_publish_rules_is_saved_as_draft(): void
{
    $db = test_db();
    $dir = seed_test_copy(['thin.json' => '{"slug": "thin", "title": "Thin", "sections": []}']);
    try {
        seed_import($db, $dir, temp_dir());
        throw new LogicException('expected the draft to be reported');
    } catch (RuntimeException $e) {
        ok(str_contains($e->getMessage(), 'thin'), $e->getMessage());
    }
    eq(page_find_published($db, 'thin'), null, 'not published');
    eq($db->query("SELECT status FROM pages WHERE slug = 'thin'")->fetchColumn(), 'draft');
    ok(page_find_published($db, 'home') !== null, 'the good page still imported');
}
