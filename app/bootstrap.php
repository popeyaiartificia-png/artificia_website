<?php
declare(strict_types=1);

const MIGRATIONS_DIR = __DIR__ . '/../db/migrations';

function config(): array
{
    static $config = null;
    if ($config === null) {
        $path = getenv('ARTIFICIA_CONFIG') ?: dirname(__DIR__, 3) . '/artificia-config.php';
        $config = require $path;
    }
    return $config;
}

require __DIR__ . '/lib/http.php';
require __DIR__ . '/lib/db.php';
require __DIR__ . '/lib/settings.php';
require __DIR__ . '/lib/html.php';
require __DIR__ . '/sections/registry.php';

// Migrate on boot, under a lock so two simultaneous first requests can't race.
if (count(applied_migrations(db())) !== count(glob(MIGRATIONS_DIR . '/*.sql'))) {
    $db = db();
    if ($db->query("SELECT GET_LOCK('artificia_migrate', 10)")->fetchColumn() !== 1) {
        throw new RuntimeException('Could not get the migration lock');
    }
    try {
        migrate($db, MIGRATIONS_DIR); // re-reads applied files, so the loser of the race does nothing
    } finally {
        $db->query("SELECT RELEASE_LOCK('artificia_migrate')")->fetchColumn();
    }
}
