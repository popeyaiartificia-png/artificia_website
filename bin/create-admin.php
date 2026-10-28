<?php
declare(strict_types=1);

// Usage: php bin/create-admin.php <email> [--role=admin|seo]
// The password is read from STDIN, twice on a terminal (hidden); a piped line works for scripts. It is never printed.
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
// Echo is switched off by a small shell that restores it on exit or Ctrl-C; the shutdown function is a second net.
function ask_hidden(string $prompt): string
{
    fwrite(STDERR, $prompt);
    $line = (string) shell_exec('stty -echo; trap "stty echo" EXIT INT TERM HUP; IFS= read -r p; printf "%s" "$p"');
    fwrite(STDERR, "\n");
    return rtrim($line, "\r\n");
}

if (stream_isatty(STDIN)) {
    register_shutdown_function(fn() => system('stty echo'));
    $password = ask_hidden('Password (min ' . PASSWORD_MIN . ' characters): ');
    if (!hash_equals($password, ask_hidden('Password again: '))) {
        fwrite(STDERR, "The two passwords do not match. Nothing was created.\n");
        exit(1);
    }
} else {
    // Piped: line 1 is the password; an optional line 2 must repeat it.
    $password = rtrim((string) fgets(STDIN), "\r\n");
    $again = fgets(STDIN);
    if ($again !== false && !hash_equals($password, rtrim($again, "\r\n"))) {
        fwrite(STDERR, "The two passwords do not match. Nothing was created.\n");
        exit(1);
    }
}
try {
    user_create(db(), $email, $password, $role);
} catch (InvalidArgumentException $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}
echo "Created $role user $email\n";
