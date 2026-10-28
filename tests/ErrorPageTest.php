<?php
declare(strict_types=1);

const ERROR_TEST_DB_PASS = 'Pw-9x7'; // short enough that a trace would print it in full

/**
 * Runs public/index.php under php-cgi (the SAPI a host uses, unlike the CLI) with $configBody as the config file,
 * or with no config file at all when it is null. display_errors=1 stands in for a host that shows errors.
 * @return array{head:string, body:string, log:string} the response and what it added to storage/php-error.log
 */
function error_test_request(?string $configBody, array $env = []): array
{
    $cgi = dirname(realpath(PHP_BINARY)) . '/php-cgi';
    ok(is_executable($cgi), "this test needs php-cgi next to php ($cgi)");
    $dir = temp_dir();
    $config = "$dir/artificia-config.php";
    if ($configBody !== null) {
        file_put_contents($config, $configBody);
    }
    $log = dirname(__DIR__) . '/storage/php-error.log';
    $before = is_file($log) ? filesize($log) : 0;
    $env += ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/', 'HTTP_HOST' => 'staging.example.test'];
    $env += ['ARTIFICIA_CONFIG' => $config, 'REDIRECT_STATUS' => '200', 'SCRIPT_FILENAME' => dirname(__DIR__) . '/public/index.php',
        'CONTENT_LENGTH' => '0', 'PATH' => getenv('PATH')];
    $p = proc_open([$cgi, '-d', 'display_errors=1', '-d', 'zend.exception_ignore_args=0'], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, null, $env);
    fclose($pipes[0]);
    $out = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
    proc_close($p);
    clearstatcache();
    [$head, $body] = explode("\r\n\r\n", $out, 2) + [1 => ''];
    return ['head' => $head, 'body' => $body, 'log' => is_file($log) ? (string) file_get_contents($log, false, null, $before) : ''];
}

/** A staging config whose database refuses connections (port 9). */
function error_test_config(): string
{
    return "<?php return ['env' => 'staging', 'base_url' => 'https://staging.example.test', 'timezone' => 'Asia/Kolkata',
        'db' => ['dsn' => 'mysql:host=127.0.0.1;port=9;dbname=nope', 'user' => 'nobody', 'pass' => '" . ERROR_TEST_DB_PASS . "'],
        'app_secret' => str_repeat('a', 64), 'staging_auth' => ['user' => 'u', 'pass' => 'p'], 'setup_token' => '', 'mail_from' => 'x@example.test'];";
}

function test_a_live_site_answers_a_crash_with_the_static_page(): void
{
    $r = error_test_request(error_test_config());
    ok(str_starts_with($r['head'], 'Status: 500'), $r['head']);
    ok(str_contains($r['body'], 'href="https://wa.me/919104488859"'), 'the WhatsApp link');
    ok(str_contains($r['body'], 'artificia-logo.png'), 'the logo');
    ok(!str_contains($r['head'], 'X-Powered-By'), 'no PHP version header');
    foreach (['PDOException', 'SQLSTATE', 'Stack trace', '.php', ERROR_TEST_DB_PASS] as $leak) {
        ok(!str_contains($r['body'], $leak), "the visitor never sees $leak");
    }
    ok(str_contains($r['log'], 'PDOException') && str_contains($r['log'], 'db_connect('), 'the error and its trace are logged');
    ok(!str_contains($r['log'], ERROR_TEST_DB_PASS), 'the database password never reaches the log');
}

function test_a_crash_answers_the_lead_fetch_with_json(): void
{
    $r = error_test_request(error_test_config(), ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/lead', 'HTTP_ACCEPT' => 'application/json']);
    ok(str_starts_with($r['head'], 'Status: 500'), $r['head']);
    ok(str_contains($r['head'], 'application/json'), $r['head']);
    eq(json_decode($r['body'], true), ['ok' => false, 'whatsapp' => 'https://wa.me/919104488859']);
    $plain = error_test_request(error_test_config(), ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/lead']);
    ok(str_contains($plain['body'], '<!doctype html>'), 'a form post without JavaScript gets the page');
}

function test_a_missing_or_broken_config_never_shows_its_path(): void
{
    foreach (['missing' => null, 'syntax error' => '<?php return [', 'not an array' => '<?php return 1;', 'no env' => "<?php return ['db' => []];"] as $case => $body) {
        $r = error_test_request($body);
        ok(str_starts_with($r['head'], 'Status: 500'), "$case: {$r['head']}");
        ok(str_contains($r['body'], 'wa.me/919104488859'), "$case: the static page");
        ok(!str_contains($r['body'], 'artificia-config.php') && !str_contains($r['body'], sys_get_temp_dir()), "$case: the path stays out of the page");
        ok(str_contains($r['log'], 'artificia-config.php'), "$case: the log names the file");
    }
}
