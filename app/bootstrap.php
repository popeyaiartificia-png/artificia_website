<?php
declare(strict_types=1);

const MIGRATIONS_DIR = __DIR__ . '/../db/migrations';

/** The config file's array. A missing or malformed file throws; only the error log gets its path, never the visitor. */
function config(): array
{
    static $config = null;
    if ($config === null) {
        $path = getenv('ARTIFICIA_CONFIG') ?: dirname(__DIR__, 3) . '/artificia-config.php';
        try {
            $loaded = is_file($path) ? require $path : null;
        } catch (Throwable $e) { // a syntax error in the file
            $loaded = null;
        }
        if (!is_array($loaded) || !in_array($loaded['env'] ?? null, ['local', 'staging', 'production'], true)
            || !is_array($loaded['db'] ?? null) || !is_string($loaded['base_url'] ?? null)) {
            // A parse error's message can quote the file (a password), so only its line is logged.
            error_log("Artificia config missing or malformed: $path" . (isset($e) ? ' (' . $e::class . ' on line ' . $e->getLine() . ')' : ''));
            throw new RuntimeException('The site configuration is missing or malformed; the PHP error log names the file.');
        }
        $config = $loaded;
    }
    return $config;
}

require __DIR__ . '/lib/http.php';
require __DIR__ . '/lib/db.php';
require __DIR__ . '/lib/settings.php';
require __DIR__ . '/lib/html.php';
require __DIR__ . '/lib/media.php';
require __DIR__ . '/lib/pages.php';
require __DIR__ . '/lib/render.php';
require __DIR__ . '/lib/seo.php';
require __DIR__ . '/lib/seo_checks.php';
require __DIR__ . '/lib/redirects.php';
require __DIR__ . '/lib/seed.php';
require __DIR__ . '/lib/mail.php';
require __DIR__ . '/lib/auth.php';
require __DIR__ . '/lib/leads.php';
require __DIR__ . '/admin/routes.php';
require __DIR__ . '/lib/router.php';
require __DIR__ . '/sections/registry.php';

// Quiet until the config is known to say env=local, so not even a broken config shows a visitor PHP's error output:
// errors go to storage/php-error.log and error_page() answers. The CLI has no visitor and keeps PHP's settings.
if (PHP_SAPI !== 'cli') {
    $storage = dirname(__DIR__) . '/storage';
    is_dir($storage) || @mkdir($storage, 0755, true);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', "$storage/php-error.log");
    set_exception_handler('error_page');
    if (config()['env'] === 'local') {
        ini_restore('display_errors');
        ini_restore('log_errors');
        ini_restore('error_log');
        restore_exception_handler();
    }
}

// The app's clock comes from config, not the host's php.ini; set before any DB connection (db_connect copies the offset).
date_default_timezone_set(config()['timezone'] ?? 'Asia/Kolkata');

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
