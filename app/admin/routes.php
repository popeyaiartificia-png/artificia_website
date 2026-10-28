<?php
declare(strict_types=1);

const ADMIN_VIEWS_DIR = __DIR__ . '/views';

require __DIR__ . '/shell.php';
require __DIR__ . '/media.php';
require __DIR__ . '/pages.php';
require __DIR__ . '/redirects.php';
require __DIR__ . '/leads.php';

/**
 * "METHOD /path" => handler(PDO, method): HttpResponse. Later tasks add their pages here and a line in admin_nav() (shell.php).
 * A "{id}" segment matches a positive integer, which the handler gets as a third argument (int $id).
 */
function admin_routes(): array
{
    return [
        'GET /admin' => 'admin_dashboard',
        'GET /admin/login' => 'admin_login',
        'POST /admin/login' => 'admin_login',
        'POST /admin/logout' => 'admin_logout',
        'GET /admin/forgot' => 'admin_forgot',
        'POST /admin/forgot' => 'admin_forgot',
        'GET /admin/reset' => 'admin_reset',
        'POST /admin/reset' => 'admin_reset',
        'GET /admin/setup' => 'admin_setup',
        'POST /admin/setup' => 'admin_setup',
        'GET /admin/settings' => 'admin_settings',
        'POST /admin/settings' => 'admin_settings',
        'POST /admin/settings/import' => 'admin_settings_import',
        'GET /admin/users' => 'admin_users',
        'POST /admin/users' => 'admin_users',
        'GET /admin/media' => 'admin_media',
        'POST /admin/media' => 'admin_media',
        'GET /admin/media.json' => 'admin_media_json',
        'GET /admin/pages' => 'admin_pages',
        'GET /admin/posts' => 'admin_posts',
        'GET /admin/pages/new' => 'admin_page_new',
        'POST /admin/pages/new' => 'admin_page_new',
        'GET /admin/posts/new' => 'admin_post_new',
        'POST /admin/posts/new' => 'admin_post_new',
        'GET /admin/pages/{id}' => 'admin_page_edit',
        'POST /admin/pages/{id}' => 'admin_page_edit',
        'GET /admin/posts/{id}' => 'admin_post_edit',
        'POST /admin/posts/{id}' => 'admin_post_edit',
        'POST /admin/pages/{id}/duplicate' => 'admin_page_duplicate',
        'POST /admin/pages/{id}/template' => 'admin_page_template',
        'GET /admin/preview/{id}' => 'admin_preview',
        'GET /admin/section-form' => 'admin_section_form',
        'GET /admin/templates' => 'admin_templates',
        'GET /admin/redirects' => 'admin_redirects',
        'POST /admin/redirects' => 'admin_redirects',
        'GET /admin/leads' => 'admin_leads',
        'GET /admin/leads/export' => 'admin_leads_export',
        'GET /admin/leads/{id}' => 'admin_lead',
        'POST /admin/leads/{id}' => 'admin_lead',
    ];
}

/** [handler, extra args] for a request, or null. Exact routes win; then "{id}" routes. */
function admin_route(string $method, string $path): ?array
{
    $routes = admin_routes();
    if (isset($routes["$method $path"])) {
        return [$routes["$method $path"], []];
    }
    foreach ($routes as $key => $handler) {
        if (!str_contains($key, '{id}')) {
            continue;
        }
        $re = '#^' . str_replace('\{id\}', '([1-9][0-9]{0,9})', preg_quote($key, '#')) . '$#D';
        if (preg_match($re, "$method $path", $m)) {
            return [$handler, [(int) $m[1]]];
        }
    }
    return null;
}

/** Runs the matching handler. Every POST is CSRF-checked here, before any handler sees it. */
function admin_handle(PDO $db, string $method, string $path): HttpResponse
{
    $path = rtrim($path, '/') ?: '/';
    try {
        [$handler, $args] = admin_route($method, $path) ?? [null, []];
        if (!$handler) {
            return new HttpResponse(404, admin_view('notfound', ['title' => 'Not found'], auth_user($db)));
        }
        if ($method === 'POST' && !$_POST && !$_FILES && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) { // PHP drops the whole body past post_max_size
            return new HttpResponse(413, admin_view('notfound', ['title' => 'Upload too big', 'message' => 'That upload is too big. Go back and choose a file under 15 MB.'], auth_user($db)));
        }
        if ($method === 'POST' && !csrf_check($_POST['_csrf'] ?? null)) {
            return new HttpResponse(403, admin_view('notfound', ['title' => 'Session expired', 'message' => 'Your session expired. Go back, reload the page and try again.'], auth_user($db)));
        }
        return $handler($db, $method, ...$args);
    } catch (HttpResponse $r) {
        return $r;
    }
}

function admin_dispatch(PDO $db, string $method, string $path): void
{
    auth_session_start();
    $r = admin_handle($db, $method, $path);
    header('X-Robots-Tag: noindex, nofollow');
    header('Cache-Control: no-store');
    header('X-Frame-Options: DENY');
    if ($r->location !== null) {
        redirect($r->location);
    }
    header('Content-Type: ' . $r->type);
    http_response_code($r->status);
    echo $r->body;
}

/** A view inside the admin layout. */
function admin_view(string $name, array $vars, ?array $user = null): string
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    $render = function (string $file, array $vars): string {
        extract($vars, EXTR_SKIP);
        ob_start();
        include ADMIN_VIEWS_DIR . "/$file.php";
        return (string) ob_get_clean();
    };
    $vars += ['title' => '', 'error' => null, 'user' => $user];
    $content = $render($name, $vars);
    return $render('layout', ['content' => $content, 'title' => $vars['title'], 'user' => $user, 'flash' => $flash]);
}

/** One labelled input with its error beside it. */
function admin_field(string $name, string $label, string $value = '', ?string $error = null, string $type = 'text', string $hint = '', string $attrs = ''): string
{
    $id = 'f-' . $name;
    $describe = trim(($hint !== '' ? "h-$name " : '') . ($error ? "e-$name" : ''));
    $input = $type === 'textarea'
        ? '<textarea id="' . $id . '" name="' . e($name) . '" rows="3"'
        : '<input id="' . $id . '" name="' . e($name) . '" type="' . e($type) . '" value="' . e($type === 'password' ? '' : $value) . '"';
    $input .= ($describe ? ' aria-describedby="' . $describe . '"' : '') . ($error ? ' aria-invalid="true"' : '') . " $attrs";
    $input .= $type === 'textarea' ? '>' . e($value) . '</textarea>' : '>';
    return '<div class="field' . ($error ? ' has-error' : '') . '"><label for="' . $id . '">' . e($label) . '</label>'
        . ($hint !== '' ? '<p class="hint" id="h-' . e($name) . '">' . e($hint) . '</p>' : '')
        . $input
        . ($error ? '<p class="err" id="e-' . e($name) . '" role="alert">' . e($error) . '</p>' : '') . '</div>';
}

/** $flash shows once, as a toast, on the next admin page. $kind is 'ok' (3 s) or 'error' (6 s). */
function admin_redirect(string $to, ?string $flash = null, string $kind = 'ok'): HttpResponse
{
    if ($flash !== null) {
        $_SESSION['flash'] = ['msg' => $flash, 'kind' => $kind === 'error' ? 'error' : 'ok'];
    }
    return new HttpResponse(302, '', $to);
}

// ---------- Pages ----------

function admin_dashboard(PDO $db, string $method): HttpResponse
{
    $user = auth_require('seo', $db);
    $count = fn(string $sql): int => (int) $db->query($sql)->fetchColumn();
    $stats = [
        'pages' => $count("SELECT COUNT(*) FROM pages WHERE kind = 'page' AND status = 'published'"),
        'page_drafts' => $count("SELECT COUNT(*) FROM pages WHERE kind = 'page' AND status = 'draft'"),
        'posts' => $count("SELECT COUNT(*) FROM pages WHERE kind = 'post' AND status = 'published'"),
        'post_drafts' => $count("SELECT COUNT(*) FROM pages WHERE kind = 'post' AND status = 'draft'"),
        'leads_week' => $count('SELECT COUNT(*) FROM leads WHERE spam = 0 AND created_at >= NOW() - INTERVAL 7 DAY'),
        'leads' => $count('SELECT COUNT(*) FROM leads WHERE spam = 0'),
        'redirects' => $count('SELECT COUNT(*) FROM redirects'),
        'media' => $count('SELECT COUNT(*) FROM media'),
    ];
    $seo = seo_checks($db, settings($db));
    $noindex = array_map('intval', $db->query('SELECT id FROM pages WHERE noindex = 1')->fetchAll(PDO::FETCH_COLUMN));
    $filter = is_string($_GET['check'] ?? null) && isset(SEO_CHECK_LABELS[$_GET['check']]) ? $_GET['check'] : '';
    return new HttpResponse(200, admin_view('dashboard', ['title' => 'Dashboard', 'stats' => $stats, 'seo' => $seo, 'noindex' => $noindex, 'filter' => $filter], $user));
}

function admin_login(PDO $db, string $method): HttpResponse
{
    if (auth_user($db)) {
        return admin_redirect('/admin');
    }
    $error = null;
    $email = '';
    if ($method === 'POST') {
        $email = trim((string) ($_POST['email'] ?? ''));
        $result = auth_login($db, $email, (string) ($_POST['password'] ?? ''), (string) ($_SERVER['REMOTE_ADDR'] ?? ''));
        if ($result === true) {
            return admin_redirect('/admin');
        }
        $error = $result === 'locked' ? 'Too many failed attempts. Wait 15 minutes and try again.' : 'The email or password is not right.';
    }
    return new HttpResponse($error ? 422 : 200, admin_view('login', ['title' => 'Log in', 'error' => $error, 'email' => $email]));
}

function admin_logout(PDO $db, string $method): HttpResponse
{
    auth_logout();
    return admin_redirect('/admin/login');
}

function admin_forgot(PDO $db, string $method): HttpResponse
{
    $sent = $method === 'POST';
    if ($sent) {
        password_reset_request($db, (string) ($_POST['email'] ?? '')); // same page whether or not the email exists
    }
    return new HttpResponse(200, admin_view('forgot', ['title' => 'Forgot password', 'sent' => $sent]));
}

function admin_reset(PDO $db, string $method): HttpResponse
{
    $token = (string) ($_POST['token'] ?? $_GET['token'] ?? '');
    $error = null;
    if ($method === 'POST') {
        if (!hash_equals((string) ($_POST['password'] ?? ''), (string) ($_POST['password2'] ?? ''))) {
            $error = 'The two passwords are not the same.';
        } elseif (strlen((string) ($_POST['password'] ?? '')) < PASSWORD_MIN) {
            $error = 'The password needs at least ' . PASSWORD_MIN . ' characters.';
        } elseif (!password_reset_apply($db, $token, (string) $_POST['password'])) {
            $error = 'This link has expired or was already used. Ask for a new one.';
        } else {
            return admin_redirect('/admin/login', 'Password changed. Log in with the new one.');
        }
    }
    return new HttpResponse($error ? 422 : 200, admin_view('reset', ['title' => 'New password', 'token' => $token, 'error' => $error]));
}

function admin_setup(PDO $db, string $method): HttpResponse
{
    $token = (string) ($_POST['token'] ?? $_GET['token'] ?? '');
    if (!setup_allowed($db, $token)) {
        return new HttpResponse(404, admin_view('notfound', ['title' => 'Not found']));
    }
    $error = null;
    $email = '';
    if ($method === 'POST') {
        $email = (string) ($_POST['email'] ?? '');
        try {
            user_create($db, $email, (string) ($_POST['password'] ?? ''), 'admin');
            return admin_redirect('/admin/login', 'Admin created. Log in.');
        } catch (InvalidArgumentException $e) {
            $error = $e->getMessage();
        }
    }
    return new HttpResponse($error ? 422 : 200, admin_view('setup', ['title' => 'First-run setup', 'token' => $token, 'error' => $error, 'email' => $email]));
}

// ---------- Settings ----------

/** group => key => [label, type, hint]. Types: text, textarea, secret, track, url, email, digits. */
function settings_fields(): array
{
    return [
        'Tracking codes' => [
            'ga4_id' => ['Google Analytics 4 measurement ID', 'track', 'Looks like G-XXXXXXXXXX.'],
            'gsc_verification' => ['Search Console verification code', 'track', 'Only the code, not the whole meta tag.'],
            'meta_pixel_id' => ['Meta Pixel ID', 'numeric', 'Digits only.'],
            'meta_capi_token' => ['Meta Conversions API token', 'secret', ''],
            'meta_test_event_code' => ['Meta test event code', 'track', 'Only while testing in Events Manager; clear it afterwards.'],
        ],
        'Lead routing' => [
            'n8n_webhook_url' => ['n8n webhook URL', 'url', 'Every new lead is sent here.'],
            'n8n_webhook_token' => ['n8n webhook token', 'secret', ''],
            'lead_alert_email' => ['Lead alert email', 'email', 'New leads are mailed here.'],
        ],
        'Business details' => [
            'gbp_url' => ['Google Business Profile URL', 'url', ''],
            'whatsapp_number' => ['WhatsApp number', 'digits', 'With country code. Blank uses 919104488859.'],
            'org_phone' => ['Phone', 'text', ''],
            'org_email' => ['Public email', 'email', ''],
            'org_address' => ['Address', 'textarea', 'Shown in the site footer.'],
        ],
    ];
}

/**
 * Saves the submitted settings, trimmed. Keys missing from $in are left alone; a blank secret keeps its stored
 * value unless "clear_<key>" is set. Nothing is saved if any field is invalid.
 * @return array<string,string> error message per field
 */
function settings_save(PDO $db, array $in): array
{
    $errors = [];
    $save = [];
    foreach (settings_fields() as $group) {
        foreach ($group as $key => [$label, $type]) {
            if (!array_key_exists($key, $in)) {
                continue;
            }
            $v = trim((string) $in[$key]);
            if ($type === 'secret' && $v === '') {
                if (empty($in["clear_$key"])) {
                    continue;
                }
            } elseif ($type === 'digits') {
                $digits = preg_replace('/\D/', '', $v);
                if ($v !== '' && $digits === '') {
                    $errors[$key] = 'Use digits, with the country code.';
                }
                $v = $digits;
            } elseif ($v !== '') {
                if ($type === 'numeric' && !preg_match('/^\d+\z/', $v)) {
                    $errors[$key] = 'Digits only.';
                } elseif ($type === 'track' && !preg_match('/^[A-Za-z0-9_-]+\z/', $v)) {
                    $errors[$key] = 'Letters, digits, dash and underscore only.';
                } elseif ($type === 'url' && !preg_match('#^https?://[^\s]+\z#i', $v)) {
                    $errors[$key] = 'Enter a full address starting with https://.';
                } elseif ($type === 'email' && !filter_var($v, FILTER_VALIDATE_EMAIL)) {
                    $errors[$key] = 'Enter a valid email address.';
                }
            }
            $save[$key] = $v;
        }
    }
    if (!$errors) {
        foreach ($save as $k => $v) {
            setting_set($db, $k, $v);
        }
    }
    return $errors;
}

function admin_settings_render(PDO $db, array $user, int $status, array $values, array $errors, array $extra = []): HttpResponse
{
    $canImport = (int) $db->query('SELECT COUNT(*) FROM pages')->fetchColumn() === 0; // seeding is for an empty site only
    return new HttpResponse($status, admin_view('settings', ['title' => 'Settings', 'values' => $values, 'errors' => $errors, 'groups' => settings_fields(),
        'canImport' => $canImport] + $extra, $user));
}

function admin_settings(PDO $db, string $method): HttpResponse
{
    $user = auth_require('admin', $db);
    $values = settings($db);
    if ($method !== 'POST') {
        return admin_settings_render($db, $user, 200, $values, [], ['stored' => $values]);
    }
    $errors = settings_save($db, $_POST);
    if (!$errors) {
        return admin_redirect('/admin/settings', 'Settings saved.');
    }
    $stored = $values;
    foreach (settings_fields() as $group) {
        foreach ($group as $k => [, $type]) {
            if ($type !== 'secret' && isset($_POST[$k]) && is_string($_POST[$k])) {
                $values[$k] = trim($_POST[$k]);
            }
        }
    }
    return admin_settings_render($db, $user, 422, $values, $errors, ['stored' => $stored]);
}

function admin_settings_import(PDO $db, string $method): HttpResponse
{
    $user = auth_require('admin', $db);
    $root = dirname(__DIR__, 2);
    try {
        $n = seed_import($db, "$root/content/seed", "$root/public/uploads"); // never forced from the web: bin/seed.php --force is for the command line
        return admin_redirect('/admin/settings', "Imported {$n['pages']} pages, {$n['templates']} templates, {$n['redirects']} redirects and {$n['media']} images.");
    } catch (RuntimeException | InvalidArgumentException $e) {
        $stored = settings($db);
        $msg = str_replace(' Use --force to import over them.', '', $e->getMessage());
        return admin_settings_render($db, $user, 422, $stored, [], ['importError' => $msg, 'stored' => $stored]);
    }
}

// ---------- Users ----------

function admin_users(PDO $db, string $method): HttpResponse
{
    $me = auth_require('admin', $db);
    $error = null;
    $form = ['email' => '', 'role' => 'seo'];
    if ($method === 'POST') {
        $action = (string) ($_POST['action'] ?? '');
        $id = (int) ($_POST['id'] ?? 0);
        try {
            if ($action === 'create') {
                $form = ['email' => (string) ($_POST['email'] ?? ''), 'role' => (string) ($_POST['role'] ?? '')];
                user_create($db, $form['email'], (string) ($_POST['password'] ?? ''), $form['role']);
                return admin_redirect('/admin/users', 'User added.');
            }
            if ($id === $me['id'] && $action !== 'password') {
                throw new InvalidArgumentException('You cannot change your own access. Ask another admin.');
            }
            if ($action === 'toggle') {
                $db->prepare('UPDATE users SET active = 1 - active WHERE id = ?')->execute([$id]);
                return admin_redirect('/admin/users', 'User updated.');
            }
            if ($action === 'password') {
                if (strlen((string) ($_POST['password'] ?? '')) < PASSWORD_MIN) {
                    throw new InvalidArgumentException('The password needs at least ' . PASSWORD_MIN . ' characters.');
                }
                $db->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([password_hash((string) $_POST['password'], PASSWORD_DEFAULT), $id]);
                return admin_redirect('/admin/users', 'Password changed.');
            }
        } catch (InvalidArgumentException $e) {
            $error = $e->getMessage();
        }
    }
    $users = $db->query('SELECT id, email, role, active, created_at FROM users ORDER BY id')->fetchAll();
    return new HttpResponse($error ? 422 : 200, admin_view('users', ['title' => 'Users', 'users' => $users, 'error' => $error, 'form' => $form, 'me' => $me], $me));
}
