<?php /** @var string $content, $title; @var ?array $user; @var ?array $flash {msg, kind} */
$head = '<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>' . e($title) . ' | Artificia admin</title>
<link rel="preload" href="/assets/fonts/roboto-flex-var-latin.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="/assets/css/admin.css">
<link rel="icon" href="/assets/img/favicon.png" type="image/png">
<script src="/assets/js/admin.js" defer></script>
</head>';
$logo = '<img src="/assets/img/artificia-logo.png" width="492" height="138" alt="Artificia AI admin">';
$toasts = '<div class="toasts" id="toasts">'
    . ($flash ? '<div class="toast toast--' . e($flash['kind']) . '" role="' . ($flash['kind'] === 'error' ? 'alert' : 'status') . '" data-ttl="' . ($flash['kind'] === 'error' ? 6000 : 3000) . '"><span class="toast__msg">' . e($flash['msg']) . '</span><button class="toast__x" type="button" aria-label="Dismiss">' . icon('x') . '</button></div>' : '')
    . '</div>';
echo $head;
if (!$user): ?>
<body class="auth">
<div class="progress" id="progress" aria-hidden="true"></div>
<main id="main" class="auth__in">
  <a class="auth__logo" href="/admin"><?= $logo ?></a>
<?= $content ?>
</main>
<?= $toasts ?>
</body>
</html>
<?php return; endif;
$groups = admin_nav_groups($user, request_path());
?>
<body class="shell">
<a class="skip" href="#main">Skip to content</a>
<div class="progress" id="progress" aria-hidden="true"></div>
<aside class="rail" id="rail" tabindex="-1" aria-label="Admin menu">
  <div class="rail__brand">
    <a href="/admin"><?= $logo ?></a>
    <span class="rail__caption">Admin</span>
  </div>
  <nav class="nav" aria-label="Admin">
<?php foreach ($groups as $group => $items): ?>
    <div class="nav__group">
      <p class="nav__group-title"><?= e($group) ?></p>
      <ul>
<?php foreach ($items as $item): ?>
        <li><a class="nav__link" href="<?= e($item['path']) ?>"<?= $item['active'] ? ' aria-current="page"' : '' ?>><?= icon($item['icon'], 'icon nav__icon') ?><span><?= e($item['label']) ?></span><?= $item['count'] > 0 ? '<span class="nav__badge">' . $item['count'] . '</span>' : '' ?></a></li>
<?php endforeach ?>
      </ul>
    </div>
<?php endforeach ?>
  </nav>
</aside>
<div class="scrim" id="scrim" hidden></div>
<div class="main">
  <div class="topbar">
    <button class="btn btn--ghost btn--sm menu-btn" type="button" id="menu-btn" aria-controls="rail" aria-expanded="false"><?= icon('list') ?><span>Menu</span></button>
    <div class="topbar__shortcuts">
<?php foreach (admin_shortcuts_for($user) as $s): ?>
      <a class="btn btn--ghost btn--sm" href="<?= e($s['href']) ?>"<?= $s['external'] ? ' target="_blank" rel="noopener" data-no-progress' : '' ?>><?= e($s['label']) ?><?= $s['external'] ? icon('arrow-square-out') : '' ?></a>
<?php endforeach ?>
    </div>
    <div class="topbar__who">
      <span class="topbar__name"><?= e($user['email']) ?> &middot; <?= e(ucfirst($user['role'])) ?></span>
      <form method="post" action="/admin/logout" data-no-busy><?= csrf_field() ?><button class="btn btn--ghost btn--sm" type="submit">Sign out</button></form>
    </div>
  </div>
  <main id="main" class="content">
<?= $content ?>
  </main>
</div>
<?= $toasts ?>
</body>
</html>
