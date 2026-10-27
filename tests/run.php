<?php
declare(strict_types=1);

// Usage: php tests/run.php [tests/FooTest.php ...]   (default: every tests/*Test.php)
putenv('ARTIFICIA_CONFIG=' . (getenv('ARTIFICIA_CONFIG') ?: getenv('HOME') . '/.config/artificia/config.php'));
require __DIR__ . '/../app/bootstrap.php';

set_error_handler(function (int $no, string $msg, string $file, int $line): never {
    throw new ErrorException($msg, 0, $no, $file, $line);
});

function eq(mixed $a, mixed $b, string $msg = ''): void
{
    if ($a !== $b) {
        throw new RuntimeException(($msg ? "$msg: " : '') . 'expected ' . var_export($b, true) . ', got ' . var_export($a, true));
    }
}

function ok(bool $c, string $msg = ''): void
{
    if (!$c) {
        throw new RuntimeException($msg ?: 'assertion failed');
    }
}

/** Fresh artificia_test database; $migrate = false leaves it empty. */
function test_db(bool $migrate = true): PDO
{
    $c = config();
    ok($c['env'] === 'local', 'refusing to drop a database outside env=local');
    $root = db_connect($c['db']['dsn'], $c['db']['user'], $c['db']['pass']);
    $root->exec('DROP DATABASE IF EXISTS artificia_test');
    $root->exec('CREATE DATABASE artificia_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $pdo = db_connect(preg_replace('/dbname=[^;]+/', 'dbname=artificia_test', $c['db']['dsn']), $c['db']['user'], $c['db']['pass']);
    if ($migrate) {
        migrate($pdo, MIGRATIONS_DIR);
    }
    return $pdo;
}

/** Fresh temp folder, removed when the run ends. */
function temp_dir(): string
{
    $dir = sys_get_temp_dir() . '/artificia-' . bin2hex(random_bytes(4));
    mkdir($dir);
    register_shutdown_function(fn() => exec('rm -rf ' . escapeshellarg($dir)));
    return $dir;
}

$files = array_slice($argv, 1) ?: glob(__DIR__ . '/*Test.php');
$passed = $failed = 0;
foreach ($files as $file) {
    $before = get_defined_functions()['user'];
    require $file;
    foreach (array_diff(get_defined_functions()['user'], $before) as $fn) {
        if (!str_starts_with($fn, 'test_') || $fn === 'test_db') {
            continue;
        }
        try {
            $fn();
            $passed++;
            echo "  ok   $fn\n";
        } catch (Throwable $t) {
            $failed++;
            echo "  FAIL $fn: " . $t->getMessage() . "\n";
        }
    }
}
echo "$passed passed, $failed failed\n";
exit($failed ? 1 : 0);
