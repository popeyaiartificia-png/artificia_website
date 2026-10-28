<?php /** @var string $content, $title; @var ?array $user; @var ?string $flash */
// Add a page: one line here, [href, label, minimum role]. Later tasks extend this list.
$nav = [
    ['/admin', 'Dashboard', 'seo'],
    ['/admin/settings', 'Settings', 'admin'],
    ['/admin/users', 'Users', 'admin'],
];
$current = request_path();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?> | Artificia admin</title>
<link rel="preload" href="/assets/fonts/roboto-flex-var-latin.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="/assets/css/admin.css">
<link rel="icon" href="/assets/img/favicon.png" type="image/png">
</head>
<body>
<header class="bar">
  <a class="brand" href="/admin"><img src="/assets/img/artificia-logo.png" width="225" height="46" alt="Artificia AI admin"></a>
<?php if ($user): ?>
  <nav aria-label="Admin">
    <ul>
<?php foreach ($nav as [$href, $label, $min]): if (ROLE_RANK[$user['role']] < ROLE_RANK[$min]) continue; ?>
      <li><a href="<?= e($href) ?>"<?= $current === $href || ($href !== '/admin' && str_starts_with($current, $href . '/')) ? ' aria-current="page"' : '' ?>><?= e($label) ?></a></li>
<?php endforeach ?>
      <li><form method="post" action="/admin/logout"><?= csrf_field() ?><button class="link" type="submit">Log out</button></form></li>
    </ul>
  </nav>
<?php endif ?>
</header>
<main id="main">
<?php if ($flash): ?>
  <p class="flash" role="status"><?= e($flash) ?></p>
<?php endif ?>
<?= $content ?>
</main>
</body>
</html>
