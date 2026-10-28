<?php
declare(strict_types=1);

// Usage: php bin/create-admin.php <email> [--role=admin|seo]
// The password is read from STDIN (hidden when it is a terminal; a piped line works for scripts) and never printed.
$home = getenv('HOME') . '/.config/artificia/config.php';
if (!getenv('ARTIFICIA_CONFIG') && is_file($home)) {
    putenv("ARTIFICIA_CONFIG=$home");
}
require __DIR__ . '/../app/bootstrap.php';

$email = '';
$role = 'admin';
foreach (array_slice($argv, 1) as $a) {
    if (str_starts_with($a, '--role=')) {
        $role = substr($a, 7);
    } else {
        $email = $a;
    }
}
if ($email === '') {
    fwrite(STDERR, "Usage: php bin/create-admin.php <email> [--role=admin|seo]\n");
    exit(2);
}
$tty = function_exists('posix_isatty') && posix_isatty(STDIN);
if ($tty) {
    fwrite(STDERR, 'Password (min ' . PASSWORD_MIN . ' characters): ');
    system('stty -echo');
}
$password = rtrim((string) fgets(STDIN), "\r\n");
if ($tty) {
    system('stty echo');
    fwrite(STDERR, "\n");
}
try {
    user_create(db(), $email, $password, $role);
} catch (InvalidArgumentException $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}
echo "Created $role user $email\n";
