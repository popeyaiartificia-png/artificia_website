<?php
declare(strict_types=1);

// Usage: php bin/seed.php [--force]
// Imports content/seed (pages, templates, redirects, media, empty settings) into the configured database.
// Locally the config is ~/.config/artificia/config.php (as bin/serve uses); on the server, $ARTIFICIA_CONFIG or the default path.
$home = getenv('HOME') . '/.config/artificia/config.php';
if (!getenv('ARTIFICIA_CONFIG') && is_file($home)) {
    putenv("ARTIFICIA_CONFIG=$home");
}
require __DIR__ . '/../app/bootstrap.php';

$root = dirname(__DIR__);
try {
    $counts = seed_import(db(), "$root/content/seed", "$root/public/uploads", in_array('--force', $argv, true));
} catch (RuntimeException | InvalidArgumentException $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}
foreach ($counts as $what => $n) {
    echo str_pad($what, 10), $n, "\n";
}
