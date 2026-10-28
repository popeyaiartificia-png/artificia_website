<?php
declare(strict_types=1);

/** Basic-auth credentials from the request; falls back to the Authorization header, which CGI/FastPHP hosts don't always parse. */
function staging_authorized(array $server, array $auth): bool
{
    $user = $server['PHP_AUTH_USER'] ?? null;
    $pass = $server['PHP_AUTH_PW'] ?? null;
    $header = $server['HTTP_AUTHORIZATION'] ?? $server['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    if ($user === null && preg_match('/^Basic (.+)$/i', $header, $m)) {
        [$user, $pass] = explode(':', (string) base64_decode($m[1]), 2) + [1 => ''];
    }
    // Both comparisons always run, so timing doesn't reveal which half was right.
    $userOk = hash_equals((string) $auth['user'], (string) $user);
    $passOk = hash_equals((string) $auth['pass'], (string) $pass);
    return $auth['user'] !== '' && $auth['pass'] !== '' && $userOk && $passOk;
}

function staging_guard(): void
{
    $c = config();
    if ($c['env'] !== 'staging') {
        return;
    }
    header('X-Robots-Tag: noindex, nofollow');
    if (!staging_authorized($_SERVER, $c['staging_auth'])) {
        header('WWW-Authenticate: Basic realm="Artificia staging"');
        http_response_code(401);
        exit('Authentication required');
    }
}

function send_html(string $html, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: text/html; charset=utf-8');
    echo $html;
}

function send_page(PDO $db, array $page): void
{
    send_html(render_page($page, settings($db)));
}

function send_404(PDO $db): void
{
    send_html(render_404(settings($db)), 404);
}

function dispatch(string $method, string $path): void
{
    staging_guard();
    $c = config();
    $db = db();

    if ($path === '/sitemap.xml') {
        header('Content-Type: application/xml; charset=utf-8');
        echo sitemap_xml($db, $c['base_url']);
        return;
    }
    if ($path === '/robots.txt') {
        header('Content-Type: text/plain; charset=utf-8');
        echo robots_txt($c['env'], $c['base_url']);
        return;
    }
    if ($path === '/admin' || str_starts_with($path, '/admin/')) {
        admin_dispatch($db, $method, $path);
        return;
    }
    // Task 9 adds POST /lead; until then it is a plain 404.
    if ($method === 'POST' && $path === '/lead') {
        send_404($db);
        return;
    }
    // The section library: open locally, and to anyone logged in to the admin elsewhere.
    if ($path === '/_design') {
        auth_session_resume();
        if ($c['env'] === 'local' || auth_user($db)) {
            header('Cache-Control: no-store');
            send_html(render_design_gallery($db, settings($db)));
        } else {
            send_404($db);
        }
        return;
    }
    if (preg_match('#^/blog/([^/]+)/?$#', $path, $m)) {
        $post = page_find_published($db, $m[1]);
        if ($post && $post['kind'] === 'post') {
            send_page($db, $post);
        } else {
            send_404($db);
        }
        return;
    }

    $slug = trim($path, '/');
    if ($slug === 'home') {
        redirect(redirect_location('/', (string) ($_SERVER['QUERY_STRING'] ?? '')), 301);
    }
    $page = page_find_published($db, $slug === '' ? 'home' : $slug);
    if ($page && $page['kind'] === 'page') {
        send_page($db, $page);
        return;
    }
    $hit = redirect_match($db, $path);
    if ($hit) {
        redirect(redirect_location($hit['to'], (string) ($_SERVER['QUERY_STRING'] ?? '')), $hit['code']);
    }
    send_404($db);
}
