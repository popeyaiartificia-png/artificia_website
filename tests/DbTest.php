<?php
declare(strict_types=1);

function test_migrate_applies_once(): void
{
    $db = test_db(false);
    eq(migrate($db, MIGRATIONS_DIR), ['001_init.sql', '002_page_noindex.sql', '003_lead_spam_reason.sql'], 'first run');
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
    eq(migrate($db, MIGRATIONS_DIR), ['001_init.sql', '002_page_noindex.sql', '003_lead_spam_reason.sql'], 'every migration re-runs without error');
}

function test_connection_is_utf8mb4_whatever_the_dsn_says(): void
{
    $c = config();
    $dsn = preg_replace('/;?charset=[^;]*/i', '', $c['db']['dsn']);
    foreach ([$dsn, "$dsn;charset=latin1"] as $d) {
        eq(db_connect($d, $c['db']['user'], $c['db']['pass'])->query('SELECT @@character_set_connection')->fetchColumn(), 'utf8mb4', $d === $dsn ? 'no charset' : 'latin1');
    }
}

function test_db_password_is_kept_out_of_traces(): void
{
    $param = (new ReflectionFunction('db_connect'))->getParameters()[2];
    eq($param->getName(), 'pass');
    ok($param->getAttributes(SensitiveParameter::class) !== [], 'db_connect $pass is #[\SensitiveParameter]');
}
