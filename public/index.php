<?php
declare(strict_types=1);

// Built-in dev server: let it serve real files (assets) itself.
if (PHP_SAPI === 'cli-server') {
    $file = __DIR__ . rawurldecode((string)parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    if (is_file($file) && $file !== __FILE__) {
        return false;
    }
}

header_remove('X-Powered-By'); // PHP adds it at startup; it only tells scanners the version
require __DIR__ . '/../app/bootstrap.php';

dispatch($_SERVER['REQUEST_METHOD'], request_path());
