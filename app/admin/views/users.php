<?php /** @var array $users, $form, $me; @var ?string $error */ ?>
<?= admin_page_head('System', 'Users', (string) count($users), 'Everyone who can log in to this admin.') ?>
<?php if ($error): ?><p class="notice notice--error" role="alert"><?= e($error) ?></p><?php endif ?>

<div class="table-wrap">
<table class="table table--stack">
  <thead><tr><th scope="col">Email</th><th scope="col">Role</th><th scope="col">Active</th><th scope="col">Since</th><th scope="col">Actions</th></tr></thead>
  <tbody>
<?php foreach ($users as $u): $self = (int) $u['id'] === $me['id']; ?>
    <tr>
      <td data-label="Email"><?= e($u['email']) ?><?= $self ? ' <span class="pill pill--you">you</span>' : '' ?></td>
      <td data-label="Role"><?= e(ucfirst($u['role'])) ?></td>
      <td data-label="Active"><span class="pill <?= $u['active'] ? 'pill--published' : 'pill--draft' ?>"><?= $u['active'] ? 'Active' : 'Deactivated' ?></span></td>
      <td data-label="Since"><?= e(date('d/m/y', strtotime((string) $u['created_at']))) ?></td>
      <td data-label="Actions" class="actions">
        <form method="post" action="/admin/users"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= (int) $u['id'] ?>"><button class="btn btn--ghost btn--sm" type="submit"<?= $self ? ' disabled title="You cannot change your own access"' : '' ?>><?= $u['active'] ? 'Deactivate' : 'Activate' ?></button></form>
        <form method="post" action="/admin/users" class="inline"><?= csrf_field() ?><input type="hidden" name="action" value="password"><input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
          <label class="sr" for="pw-<?= (int) $u['id'] ?>">New password for <?= e($u['email']) ?></label>
          <input id="pw-<?= (int) $u['id'] ?>" name="password" type="password" placeholder="New password" autocomplete="new-password">
          <button class="btn btn--ghost btn--sm" type="submit">Set password</button></form>
      </td>
    </tr>
<?php endforeach ?>
  </tbody>
</table>
</div>

<section class="card">
  <h2>Add a login</h2>
  <form method="post" action="/admin/users">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="create">
    <div class="form-grid">
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
    </div>
    <button class="btn btn--primary" type="submit" data-busy="Adding&hellip;">Add login</button>
  </form>
</section>
