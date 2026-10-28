<?php
declare(strict_types=1);

const AUTH_TEST_PW = 'correct horse 1';

function auth_test_user(PDO $db, string $role = 'admin', string $email = 'a@example.com'): int
{
    $_SESSION = [];
    return user_create($db, $email, AUTH_TEST_PW, $role);
}

function test_login_ok_and_invalid(): void
{
    $db = test_db();
    $id = auth_test_user($db);
    eq(auth_login($db, 'a@example.com', AUTH_TEST_PW, '1.1.1.1'), true);
    eq(auth_user($db)['id'], $id);
    $_SESSION = [];
    eq(auth_login($db, 'a@example.com', 'wrong password', '1.1.1.1'), 'invalid');
    eq(auth_login($db, 'nobody@example.com', AUTH_TEST_PW, '1.1.1.1'), 'invalid');
    eq(auth_user($db), null);
    $db->exec('UPDATE users SET active = 0');
    eq(auth_login($db, 'a@example.com', AUTH_TEST_PW, '1.1.1.1'), 'invalid');
}

function test_lockout_after_5_failures(): void
{
    $db = test_db();
    auth_test_user($db);
    for ($i = 0; $i < 5; $i++) {
        eq(auth_login($db, 'a@example.com', 'nope nope nope', '1.1.1.1'), 'invalid');
    }
    eq(auth_login($db, 'a@example.com', AUTH_TEST_PW, '1.1.1.1'), 'locked', 'even the right password');
    eq(auth_login($db, 'a@example.com', AUTH_TEST_PW, '2.2.2.2'), true, 'another IP is not locked');
    $_SESSION = [];
    $db->exec('UPDATE login_attempts SET created_at = NOW() - INTERVAL 16 MINUTE');
    eq(auth_login($db, 'a@example.com', AUTH_TEST_PW, '1.1.1.1'), true, 'attempts expire after 15 minutes');
}

function test_csrf_rejects_missing_and_wrong(): void
{
    $_SESSION = [];
    $t = csrf_token();
    eq(strlen($t), 64);
    eq(csrf_token(), $t, 'stable within a session');
    eq(csrf_check($t), true);
    eq(csrf_check(null), false);
    eq(csrf_check(''), false);
    eq(csrf_check('x' . $t), false);
    $_SESSION = [];
    eq(csrf_check($t), false, 'no token issued yet');
    // An admin POST without a token is refused before any handler runs.
    $db = test_db();
    auth_test_user($db);
    auth_login($db, 'a@example.com', AUTH_TEST_PW, '1.1.1.1');
    $_POST = ['ga4_id' => 'G-X'];
    eq(admin_handle($db, 'POST', '/admin/settings')->status, 403);
    $_POST = [];
}

function auth_test_reset_token(): string
{
    preg_match_all('/token=([a-f0-9]{64})/', (string) file_get_contents(dirname(__DIR__) . '/storage/mail.log'), $m);
    return end($m[1]);
}

function test_reset_token_single_use_and_expiry(): void
{
    $db = test_db();
    $id = auth_test_user($db);
    password_reset_request($db, 'nobody@example.com'); // same silent result
    eq((int) $db->query('SELECT COUNT(*) FROM password_resets')->fetchColumn(), 0);
    password_reset_request($db, 'a@example.com');
    $token = auth_test_reset_token();
    $stored = $db->query('SELECT token_hash FROM password_resets')->fetchColumn();
    eq($stored, hash('sha256', $token), 'only the hash is stored');
    eq(password_reset_apply($db, $token, 'short'), false, 'minimum length');
    eq(password_reset_apply($db, $token, 'brand new passphrase'), true);
    eq(password_reset_apply($db, $token, 'another passphrase 2'), false, 'single use');
    eq(auth_login($db, 'a@example.com', 'brand new passphrase', '1.1.1.1'), true);
    $_SESSION = [];

    password_reset_request($db, 'a@example.com');
    $token = auth_test_reset_token();
    $db->exec('UPDATE password_resets SET expires_at = NOW() - INTERVAL 1 MINUTE');
    eq(password_reset_apply($db, $token, 'yet another passphrase'), false, 'expired');
    eq(password_reset_apply($db, bin2hex(random_bytes(32)), 'yet another passphrase'), false, 'unknown token');
}

function test_setup_only_when_no_users(): void
{
    $db = test_db();
    $token = config()['setup_token'] ?? '';
    ok($token !== '', 'the local config needs a setup_token');
    eq(setup_allowed($db, $token), true);
    eq(setup_allowed($db, $token . 'x'), false);
    eq(setup_allowed($db, ''), false);
    user_create($db, 'first@example.com', AUTH_TEST_PW, 'admin');
    eq(setup_allowed($db, $token), false, 'a user exists');
}

function test_seo_role_cannot_reach_settings(): void
{
    $db = test_db();
    $seo = user_create($db, 'seo@example.com', AUTH_TEST_PW, 'seo');
    $admin = user_create($db, 'admin@example.com', AUTH_TEST_PW, 'admin');
    $_SESSION = [];
    $r = admin_handle($db, 'GET', '/admin/settings');
    eq([$r->status, $r->location], [302, '/admin/login'], 'logged out');
    $_SESSION = ['uid' => $seo];
    eq(admin_handle($db, 'GET', '/admin/settings')->status, 403);
    eq(admin_handle($db, 'GET', '/admin/users')->status, 403);
    eq(admin_handle($db, 'GET', '/admin')->status, 200, 'the dashboard is open to SEO');
    $_SESSION = ['uid' => $admin];
    eq(admin_handle($db, 'GET', '/admin/settings')->status, 200);
    eq(admin_handle($db, 'GET', '/admin/users')->status, 200);
}

function test_password_and_email_rules(): void
{
    $db = test_db();
    foreach ([['bad', AUTH_TEST_PW, 'admin'], ['a@example.com', 'short', 'admin'], ['a@example.com', AUTH_TEST_PW, 'boss']] as [$e, $p, $r]) {
        try {
            user_create($db, $e, $p, $r);
            ok(false, "accepted $e / $p / $r");
        } catch (InvalidArgumentException) {
        }
    }
    user_create($db, 'a@example.com', AUTH_TEST_PW, 'admin');
    try {
        user_create($db, 'A@example.com', AUTH_TEST_PW, 'admin');
        ok(false, 'accepted a duplicate email');
    } catch (InvalidArgumentException) {
    }
    ok(str_starts_with((string) $db->query('SELECT password_hash FROM users')->fetchColumn(), '$'));
}

function test_settings_save_trims_and_validates(): void
{
    $db = test_db();
    $errors = settings_save($db, ['ga4_id' => " G-ABC123\n", 'meta_pixel_id' => ' 12345 ', 'whatsapp_number' => '+91 91044-88859', 'org_email' => ' info@artificiaai.com ']);
    eq($errors, []);
    $s = settings($db);
    eq([$s['ga4_id'], $s['meta_pixel_id'], $s['whatsapp_number'], $s['org_email']], ['G-ABC123', '12345', '919104488859', 'info@artificiaai.com']);
    $errors = settings_save($db, ['ga4_id' => 'G-1"><script>', 'n8n_webhook_url' => 'ftp://x', 'lead_alert_email' => 'nope']);
    eq(array_keys($errors), ['ga4_id', 'n8n_webhook_url', 'lead_alert_email']);
    eq(settings($db)['ga4_id'], 'G-ABC123', 'nothing saved when a field is invalid');
    // A blank secret keeps the stored one; the clear box empties it.
    settings_save($db, ['n8n_webhook_token' => 'sekret']);
    settings_save($db, ['n8n_webhook_token' => '']);
    eq(settings($db)['n8n_webhook_token'], 'sekret');
    settings_save($db, ['n8n_webhook_token' => '', 'clear_n8n_webhook_token' => '1']);
    eq(settings($db)['n8n_webhook_token'], '');
}

function test_tracking_id_rejects_trailing_newline(): void
{
    eq(tracking_id(['ga4_id' => "G-ABC\n"], 'ga4_id'), '');
    eq(tracking_id(['ga4_id' => 'G-ABC'], 'ga4_id'), 'G-ABC');
}

function test_seed_import_button_refuses_over_existing_pages(): void
{
    $db = test_db();
    $admin = user_create($db, 'admin@example.com', AUTH_TEST_PW, 'admin');
    $_SESSION = ['uid' => $admin];
    $_POST = ['_csrf' => csrf_token()];
    $db->exec("INSERT INTO pages (slug, title, sections) VALUES ('x', 'X', '[]')");
    $r = admin_handle($db, 'POST', '/admin/settings/import');
    ok(str_contains($r->body, 'already has pages'), 'shows the refusal');
    eq((int) $db->query('SELECT COUNT(*) FROM pages')->fetchColumn(), 1);
    $_POST = [];
}

function test_array_csrf_field_gets_the_normal_refusal(): void
{
    $db = test_db();
    $admin = user_create($db, 'admin@example.com', AUTH_TEST_PW, 'admin');
    $_SESSION = ['uid' => $admin];
    csrf_token();
    eq(csrf_check(['x']), false);
    $_POST = ['_csrf' => ['x']];
    eq(admin_handle($db, 'POST', '/admin/settings')->status, 403);
    $_POST = [];
}

function test_settings_errors_keep_typed_values_and_saved_secret_state(): void
{
    $db = test_db();
    $admin = user_create($db, 'admin@example.com', AUTH_TEST_PW, 'admin');
    setting_set($db, 'n8n_webhook_token', 'sekret');
    $_SESSION = ['uid' => $admin];
    $_POST = ['_csrf' => csrf_token(), 'meta_pixel_id' => 'abc', 'org_phone' => '+91 99', 'ga4_id' => 'G-NEW1', 'n8n_webhook_token' => ''];
    $r = admin_handle($db, 'POST', '/admin/settings');
    eq($r->status, 422);
    ok(str_contains($r->body, 'value="+91 99"') && str_contains($r->body, 'value="G-NEW1"'), 'typed values come back');
    ok(str_contains($r->body, 'Saved. Leave blank') && str_contains($r->body, 'clear_n8n_webhook_token'), 'saved secret still shown as set');
    ok(str_contains($r->body, 'id="e-meta_pixel_id"'), 'pixel id must be digits');
    ok(!str_contains($r->body, 'sekret'));
    $_POST = [];
}

function test_import_refusal_uses_web_wording(): void
{
    $db = test_db();
    $admin = user_create($db, 'admin@example.com', AUTH_TEST_PW, 'admin');
    $_SESSION = ['uid' => $admin];
    $_POST = ['_csrf' => csrf_token()];
    $db->exec("INSERT INTO pages (slug, title, sections) VALUES ('x', 'X', '[]')");
    $body = admin_handle($db, 'POST', '/admin/settings/import')->body;
    ok(str_contains($body, 'Replace existing pages') && !str_contains($body, '--force'));
    $_POST = [];
}

function test_create_admin_refuses_mismatched_passwords(): void
{
    $p = proc_open([PHP_BINARY, dirname(__DIR__) . '/bin/create-admin.php', 'mismatch@example.com'],
        [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, null, ['ARTIFICIA_CONFIG' => getenv('ARTIFICIA_CONFIG')] + getenv());
    fwrite($pipes[0], "first password 1\nsecond password 2\n");
    fclose($pipes[0]);
    $out = stream_get_contents($pipes[1]);
    $err = stream_get_contents($pipes[2]);
    eq(proc_close($p), 1);
    ok(str_contains($err, 'do not match'), $err);
    ok(!str_contains($out . $err, 'first password'), 'never prints the password');
}
