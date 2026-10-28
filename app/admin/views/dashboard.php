<?php /** @var array $user */ ?>
<h1>Dashboard</h1>
<p>Logged in as <strong><?= e($user['email']) ?></strong> (<?= e($user['role']) ?>).</p>
<ul class="links">
  <li><a href="/" target="_blank" rel="noopener">View the site</a></li>
  <li><a href="/_design">Section library</a></li>
<?php if ($user['role'] === 'admin'): ?>
  <li><a href="/admin/settings">Settings</a></li>
  <li><a href="/admin/users">Users</a></li>
<?php endif ?>
</ul>
