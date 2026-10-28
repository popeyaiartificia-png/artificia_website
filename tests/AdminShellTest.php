<?php
declare(strict_types=1);

const SHELL_TEST_PW = 'correct horse 1';

function test_nav_hides_links_without_routes_and_by_role(): void
{
    $db = test_db();
    $seo = user_create($db, 'seo@example.com', SHELL_TEST_PW, 'seo');
    $admin = user_create($db, 'admin@example.com', SHELL_TEST_PW, 'admin');

    $_SESSION = ['uid' => $seo];
    $nav = admin_handle($db, 'GET', '/admin')->body;
    ok(str_contains($nav, 'href="/admin"'), 'SEO sees the dashboard');
    ok(!str_contains($nav, 'href="/admin/settings"'), 'SEO has no Settings link');
    ok(!str_contains($nav, 'href="/admin/users"'), 'SEO has no Users link');
    ok(!str_contains($nav, 'nav__group-title">System<'), 'a group with no drawable links is not drawn');

    $_SESSION = ['uid' => $admin];
    $nav = admin_handle($db, 'GET', '/admin')->body;
    ok(str_contains($nav, 'href="/admin/settings"') && str_contains($nav, 'href="/admin/users"'), 'admin sees both');
    ok(str_contains($nav, 'nav__group-title">System<'), 'System group drawn for admin');
    foreach (['/admin/leads'] as $later) {
        ok(!str_contains($nav, 'href="' . $later . '"'), "no link to $later until its route exists");
    }
    foreach (['/admin/pages', '/admin/posts', '/admin/templates', '/admin/media', '/admin/redirects', '/admin/pages/new', '/admin/posts/new'] as $built) {
        ok(str_contains($nav, 'href="' . $built . '"'), "$built is linked once its route exists");
    }
    $_SESSION = [];
}

function test_flash_renders_as_toast_once(): void
{
    $db = test_db();
    $admin = user_create($db, 'admin@example.com', SHELL_TEST_PW, 'admin');
    $_SESSION = ['uid' => $admin];
    eq(admin_redirect('/admin/settings', 'Settings saved.')->status, 302);
    $first = admin_handle($db, 'GET', '/admin/settings')->body;
    eq(substr_count($first, 'Settings saved.'), 1, 'shown once on the next page');
    ok(str_contains($first, 'class="toast') && str_contains($first, 'role="status"'), 'as a toast');
    $second = admin_handle($db, 'GET', '/admin/settings')->body;
    ok(!str_contains($second, 'Settings saved.'), 'gone on the page after');

    admin_redirect('/admin', 'Could not save.', 'error');
    ok(str_contains(admin_handle($db, 'GET', '/admin')->body, 'role="alert"'), 'errors use role=alert');
    $_SESSION = [];
}
