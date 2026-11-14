<?php
declare(strict_types=1);

// The admin shell's data and small view helpers. Later tasks extend admin_nav() and admin_shortcuts(); no markup changes needed.

/**
 * The left rail, in order. Keys: group, label, path, icon (a name in app/views/icons.php), role (minimum role),
 * badge (null, an int, or a callable returning an int: drawn as a lime count pill). A link is drawn only when
 * "GET <path>" is a registered route and the user's role is high enough; a group with no drawn links is not drawn.
 */
function admin_nav(): array
{
    return [
        ['group' => 'Start', 'label' => 'Dashboard', 'path' => '/admin', 'icon' => 'squares-four', 'role' => 'seo', 'badge' => null],
        ['group' => 'Content', 'label' => 'Pages', 'path' => '/admin/pages', 'icon' => 'file-text', 'role' => 'seo', 'badge' => null],
        ['group' => 'Content', 'label' => 'Posts', 'path' => '/admin/posts', 'icon' => 'newspaper', 'role' => 'seo', 'badge' => null],
        ['group' => 'Content', 'label' => 'Templates', 'path' => '/admin/templates', 'icon' => 'stack', 'role' => 'seo', 'badge' => null],
        ['group' => 'Content', 'label' => 'Media', 'path' => '/admin/media', 'icon' => 'image', 'role' => 'seo', 'badge' => null],
        ['group' => 'SEO', 'label' => 'Meta manager', 'path' => '/admin/seo', 'icon' => 'magnifying-glass', 'role' => 'seo', 'badge' => null],
        ['group' => 'SEO', 'label' => 'Schema', 'path' => '/admin/seo/schema', 'icon' => 'tree-structure', 'role' => 'seo', 'badge' => null],
        ['group' => 'SEO', 'label' => 'robots & llms.txt', 'path' => '/admin/seo/files', 'icon' => 'globe', 'role' => 'seo', 'badge' => null],
        ['group' => 'SEO', 'label' => 'Redirects', 'path' => '/admin/redirects', 'icon' => 'arrows-left-right', 'role' => 'seo', 'badge' => null],
        ['group' => 'Leads', 'label' => 'Leads', 'path' => '/admin/leads', 'icon' => 'tray', 'role' => 'seo', 'badge' => fn() => leads_recent_count(db())],
        ['group' => 'System', 'label' => 'Settings', 'path' => '/admin/settings', 'icon' => 'gear-six', 'role' => 'admin', 'badge' => null],
        ['group' => 'System', 'label' => 'Users', 'path' => '/admin/users', 'icon' => 'users-three', 'role' => 'admin', 'badge' => null],
    ];
}

/**
 * Buttons on the left of the top bar. Keys: label, href, role, external (open in a new tab). An internal href is
 * drawn only when "GET <href>" is a registered route.
 */
function admin_shortcuts(): array
{
    return [
        ['label' => '+ Page', 'href' => '/admin/pages/new', 'role' => 'seo', 'external' => false],
        ['label' => '+ Post', 'href' => '/admin/posts/new', 'role' => 'seo', 'external' => false],
        ['label' => 'View site', 'href' => '/', 'role' => 'seo', 'external' => true],
    ];
}

function admin_has_route(string $path): bool
{
    return isset(admin_routes()["GET $path"]);
}

function admin_role_allows(array $user, string $min): bool
{
    return ROLE_RANK[$user['role']] >= ROLE_RANK[$min];
}

/** @return array<string,list<array>> group => drawable links, each with 'active' and 'count' added. */
function admin_nav_groups(array $user, string $current): array
{
    $groups = [];
    $paths = array_column(admin_nav(), 'path');
    foreach (admin_nav() as $item) {
        if (!admin_has_route($item['path']) || !admin_role_allows($user, $item['role'])) {
            continue;
        }
        // A section owns the addresses below it, except those another nav item owns (/admin/seo vs /admin/seo/schema).
        $item['active'] = $current === $item['path'] || ($item['path'] !== '/admin' && str_starts_with($current, $item['path'] . '/')
            && !array_filter($paths, fn($p) => strlen($p) > strlen($item['path']) && ($current === $p || str_starts_with($current, "$p/"))));
        $item['count'] = is_callable($item['badge']) ? (int) ($item['badge'])() : (int) $item['badge'];
        $groups[$item['group']][] = $item;
    }
    return $groups;
}

/** @return list<array> the top-bar shortcuts this user can use. */
function admin_shortcuts_for(array $user): array
{
    return array_values(array_filter(admin_shortcuts(), fn($s) => admin_role_allows($user, $s['role']) && ($s['external'] || admin_has_route($s['href']))));
}

/**
 * Eyebrow (section name), H1 with an optional muted count, a one-line description and an optional right-aligned
 * action. $actionHtml is trusted markup, e.g. '<a class="btn btn--primary" href="...">New page</a>'.
 */
function admin_page_head(string $eyebrow, string $title, ?string $count = null, string $description = '', string $actionHtml = ''): string
{
    return '<header class="head"><div class="head__text">'
        . '<p class="head__eyebrow">' . e($eyebrow) . '</p>'
        . '<h1>' . e($title) . ($count !== null ? ' <span class="head__count">' . e($count) . '</span>' : '') . '</h1>'
        . ($description !== '' ? '<p class="head__desc">' . e($description) . '</p>' : '')
        . '</div>' . ($actionHtml !== '' ? '<div class="head__action">' . $actionHtml . '</div>' : '') . '</header>';
}
