<?php
declare(strict_types=1);

function test_migrate_applies_once(): void
{
    $db = test_db(false);
    eq(migrate($db, MIGRATIONS_DIR), ['001_init.sql', '002_page_noindex.sql'], 'first run');
    eq(migrate($db, MIGRATIONS_DIR), [], 'second run');
}

function test_settings_roundtrip(): void
{
    $db = test_db();
    setting_set($db, 'ga4_id', 'G-X');
    eq(settings($db)['ga4_id'], 'G-X');
}

function test_migration_reruns_after_partial_failure(): void
{
    $db = test_db();
    $db->exec('DELETE FROM schema_migrations'); // as if the run died before recording itself
    eq(migrate($db, MIGRATIONS_DIR), ['001_init.sql', '002_page_noindex.sql'], 'every migration re-runs without error');
}
