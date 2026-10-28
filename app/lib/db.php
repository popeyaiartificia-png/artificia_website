<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $c = config()['db'];
        $pdo = db_connect($c['dsn'], $c['user'], $c['pass']);
    }
    return $pdo;
}

function db_connect(string $dsn, string $user, string $pass): PDO
{
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    // The DB clock (NOW(), TIMESTAMP columns) reads in PHP's offset, so a stored time and date() agree.
    $pdo->exec("SET time_zone = '" . date('P') . "'");
    return $pdo;
}

/** @return string[] filenames already applied; empty before 001_init.sql has created the table */
function applied_migrations(PDO $db): array
{
    try {
        return $db->query('SELECT filename FROM schema_migrations ORDER BY filename')->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        if (($e->errorInfo[1] ?? 0) === 1146) { // table doesn't exist yet
            return [];
        }
        throw $e;
    }
}

/** @return string[] the filenames applied by this call */
function migrate(PDO $db, string $dir): array
{
    $done = applied_migrations($db);
    $applied = [];
    $files = glob($dir . '/*.sql');
    sort($files);
    foreach ($files as $file) {
        $name = basename($file);
        if (in_array($name, $done, true)) {
            continue;
        }
        // shortcut: splits on ";" at end of line, so migrations must not contain one inside a string.
        foreach (preg_split('/;[ \t]*\R/', (string)file_get_contents($file)) as $sql) {
            if (trim($sql) !== '') {
                $db->exec($sql);
            }
        }
        $db->prepare('INSERT INTO schema_migrations (filename) VALUES (?)')->execute([$name]);
        $applied[] = $name;
    }
    return $applied;
}
