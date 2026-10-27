<?php
declare(strict_types=1);

// Built-in dev server: let it serve real files (assets) itself.
if (PHP_SAPI === 'cli-server') {
    $file = __DIR__ . rawurldecode((string)parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    if (is_file($file) && $file !== __FILE__) {
        return false;
    }
}

require __DIR__ . '/../app/bootstrap.php';

// Placeholder until the router lands.
header('Content-Type: text/plain; charset=utf-8');
echo "artificiaai.com: foundation only\n";
