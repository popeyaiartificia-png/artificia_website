<?php /** @var array $users, $form, $me; @var ?string $error */ ?>
<h1>Users</h1>
<?php if ($error): ?><p class="err" role="alert"><?= e($error) ?></p><?php endif ?>
<table>
  <thead><tr><th scope="col">Email</th><th scope="col">Role</th><th scope="col">Status</th><th scope="col">Actions</th></tr></thead>
  <tbody>
<?php foreach ($users as $u): ?>
    <tr>
      <td><?= e($u['email']) ?><?= (int) $u['id'] === $me['id'] ? ' (you)' : '' ?></td>
      <td><?= e($u['role']) ?></td>
      <td><?= $u['active'] ? 'Active' : 'Deactivated' ?></td>
      <td class="actions">
<?php if ((int) $u['id'] !== $me['id']): ?>
        <form method="post" action="/admin/users"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= (int) $u['id'] ?>"><button class="btn btn--quiet" type="submit"><?= $u['active'] ? 'Deactivate' : 'Activate' ?></button></form>
<?php endif ?>
        <form method="post" action="/admin/users" class="inline"><?= csrf_field() ?><input type="hidden" name="action" value="password"><input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
          <label class="sr" for="pw-<?= (int) $u['id'] ?>">New password for <?= e($u['email']) ?></label>
          <input id="pw-<?= (int) $u['id'] ?>" name="password" type="password" placeholder="New password" autocomplete="new-password">
          <button class="btn btn--quiet" type="submit">Set password</button></form>
      </td>
    </tr>
<?php endforeach ?>
  </tbody>
</table>

<section class="card">
  <h2>Add a user</h2>
  <form method="post" action="/admin/users">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="create">
    <?= admin_field('email', 'Email', $form['email'], null, 'email', '', 'autocomplete="off" required') ?>
    <?= admin_field('password', 'Password', '', null, 'password', 'At least ' . PASSWORD_MIN . ' characters.', 'autocomplete="new-password" required') ?>
    <div class="field">
      <label for="f-role">Role</label>
      <p class="hint" id="h-role">SEO users edit pages and media. Admins also manage settings, users and leads.</p>
      <select id="f-role" name="role" aria-describedby="h-role">
        <option value="seo"<?= $form['role'] === 'seo' ? ' selected' : '' ?>>SEO</option>
        <option value="admin"<?= $form['role'] === 'admin' ? ' selected' : '' ?>>Admin</option>
      </select>
    </div>
    <button class="btn" type="submit">Add user</button>
  </form>
</section>
