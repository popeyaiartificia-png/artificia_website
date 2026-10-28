<?php
declare(strict_types=1);

const ROLE_RANK = ['seo' => 1, 'admin' => 2];
const LOGIN_MAX_FAILS = 5;
const PASSWORD_MIN = 10;

/** A response an admin handler returns; also thrown by auth_require to stop a request. */
final class HttpResponse extends Exception
{
    public function __construct(public int $status = 200, public string $body = '', public ?string $location = null, public string $type = 'text/html; charset=utf-8')
    {
        parent::__construct('');
    }
}

/** Starts the session with safe cookie flags. Under the CLI (tests, scripts) $_SESSION is just an array. */
function auth_session_start(): void
{
    if (PHP_SAPI === 'cli' || session_status() !== PHP_SESSION_NONE) {
        return;
    }
    // Our own folder: on shared hosting the common one is swept by the host's cleanup, which ignores our lifetime.
    $dir = dirname(__DIR__, 2) . '/storage/sessions';
    is_dir($dir) || @mkdir($dir, 0700, true); // a second request racing to create it is fine
    session_save_path($dir);
    ini_set('session.gc_probability', '1'); // hosts that clean by cron set this to 0; nothing else sweeps our folder
    ini_set('session.gc_maxlifetime', '43200'); // a working day of editing, not PHP's 24 idle minutes
    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params([
        'lifetime' => 43200, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax',
        'secure' => (config()['env'] ?? 'production') !== 'local',
    ]);
    session_name('artificia_admin');
    session_start();
}

/** Resumes a session only if the browser already has the admin cookie; anonymous visitors get none. */
function auth_session_resume(): void
{
    if (isset($_COOKIE['artificia_admin'])) {
        auth_session_start();
    }
}

function csrf_token(): string
{
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}

function csrf_check(mixed $token): bool
{
    return is_string($token) && $token !== '' && isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

/** The logged-in, still-active user (looked up each time, so deactivating takes effect at once). */
function auth_user(?PDO $db = null): ?array
{
    $id = $_SESSION['uid'] ?? null;
    if (!is_int($id)) {
        return null;
    }
    $st = ($db ?? db())->prepare('SELECT id, email, role FROM users WHERE id = ? AND active = 1');
    $st->execute([$id]);
    return $st->fetch() ?: null;
}

/** Returns the user, or throws HttpResponse: a redirect to the login page, or 403 for a lower role. */
function auth_require(string $minRole = 'seo', ?PDO $db = null): array
{
    $user = auth_user($db);
    if (!$user) {
        throw new HttpResponse(302, '', '/admin/login');
    }
    if (ROLE_RANK[$user['role']] < ROLE_RANK[$minRole]) {
        throw new HttpResponse(403, admin_view('forbidden', ['title' => 'Not allowed'], $user));
    }
    return $user;
}

/** @return true|'invalid'|'locked' */
function auth_login(PDO $db, string $email, string $password, string $ip): true|string
{
    $email = strtolower(trim($email));
    $db->exec("DELETE FROM login_attempts WHERE created_at < NOW() - INTERVAL 1 DAY");
    $st = $db->prepare('SELECT COUNT(*) FROM login_attempts WHERE email = ? AND ip = ? AND created_at > NOW() - INTERVAL 15 MINUTE');
    $st->execute([$email, $ip]);
    if ((int) $st->fetchColumn() >= LOGIN_MAX_FAILS) {
        return 'locked';
    }
    $st = $db->prepare('SELECT id, password_hash FROM users WHERE email = ? AND active = 1');
    $st->execute([$email]);
    $row = $st->fetch();
    // Unknown emails verify against a fixed default-cost hash, so timing doesn't reveal which emails exist.
    $hash = $row['password_hash'] ?? '$2y$12$M3rCFAOZqW24HDsoiRCe3eV9yrgxAc6fjXd0e8qjzRiJ2HA07zCO6';
    if (!password_verify($password, $hash) || !$row) {
        $db->prepare('INSERT INTO login_attempts (email, ip) VALUES (?, ?)')->execute([$email, $ip]);
        return 'invalid';
    }
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
    $_SESSION['uid'] = (int) $row['id'];
    unset($_SESSION['csrf']);
    $db->prepare('DELETE FROM login_attempts WHERE email = ? AND ip = ?')->execute([$email, $ip]);
    if (password_needs_rehash($row['password_hash'], PASSWORD_DEFAULT)) {
        $db->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([password_hash($password, PASSWORD_DEFAULT), $row['id']]);
    }
    return true;
}

function auth_logout(): void
{
    $_SESSION = [];
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

/** @throws InvalidArgumentException with a message fit to show the user */
function user_create(PDO $db, string $email, string $password, string $role): int
{
    $email = strtolower(trim($email));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
        throw new InvalidArgumentException('Enter a valid email address.');
    }
    if (!isset(ROLE_RANK[$role])) {
        throw new InvalidArgumentException('The role must be admin or seo.');
    }
    if (strlen($password) < PASSWORD_MIN) {
        throw new InvalidArgumentException('The password needs at least ' . PASSWORD_MIN . ' characters.');
    }
    $st = $db->prepare('SELECT 1 FROM users WHERE email = ?');
    $st->execute([$email]);
    if ($st->fetchColumn()) {
        throw new InvalidArgumentException('A user with that email already exists.');
    }
    $db->prepare('INSERT INTO users (email, password_hash, role) VALUES (?, ?, ?)')
       ->execute([$email, password_hash($password, PASSWORD_DEFAULT), $role]);
    return (int) $db->lastInsertId();
}

/** Mails a one-hour link if the email belongs to an active user, at most once per 5 minutes; does nothing visible otherwise. */
function password_reset_request(PDO $db, string $email): void
{
    $st = $db->prepare('SELECT id, email FROM users WHERE email = ? AND active = 1');
    $st->execute([strtolower(trim($email))]);
    if (!($u = $st->fetch())) {
        return;
    }
    // A link lives one hour, so one that still has over 55 minutes left was sent in the last 5.
    $recent = $db->prepare('SELECT 1 FROM password_resets WHERE user_id = ? AND expires_at > NOW() + INTERVAL 55 MINUTE');
    $recent->execute([$u['id']]);
    if ($recent->fetchColumn()) {
        return;
    }
    $token = bin2hex(random_bytes(32));
    $db->prepare('DELETE FROM password_resets WHERE user_id = ? OR expires_at < NOW()')->execute([$u['id']]);
    $db->prepare('INSERT INTO password_resets (token_hash, user_id, expires_at) VALUES (?, ?, NOW() + INTERVAL 1 HOUR)')
       ->execute([hash('sha256', $token), $u['id']]);
    send_mail($u['email'], 'Reset your Artificia admin password',
        "Use this link within one hour to choose a new password:\n\n" . config()['base_url'] . "/admin/reset?token=$token\n\nIf you did not ask for this, ignore this email.");
}

function password_reset_apply(PDO $db, string $token, string $newPassword): bool
{
    if (strlen($newPassword) < PASSWORD_MIN) {
        return false;
    }
    $hash = hash('sha256', $token);
    $st = $db->prepare('SELECT user_id FROM password_resets WHERE token_hash = ? AND expires_at > NOW()');
    $st->execute([$hash]);
    $uid = $st->fetchColumn();
    // The DELETE is the single-use gate: only one caller sees rowCount 1.
    $del = $db->prepare('DELETE FROM password_resets WHERE token_hash = ?');
    $del->execute([$hash]);
    if ($uid === false || $del->rowCount() !== 1) {
        return false;
    }
    $db->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([password_hash($newPassword, PASSWORD_DEFAULT), $uid]);
    $db->prepare('DELETE FROM login_attempts WHERE email = (SELECT email FROM users WHERE id = ?)')->execute([$uid]);
    return true;
}

/** First-run setup is open only while there are no users and the token matches config setup_token. */
function setup_allowed(PDO $db, string $token): bool
{
    $expected = (string) (config()['setup_token'] ?? '');
    return $expected !== '' && hash_equals($expected, $token)
        && (int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn() === 0;
}
