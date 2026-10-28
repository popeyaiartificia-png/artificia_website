<?php /** @var string $token, $email; @var ?string $error */ ?>
<section class="card">
  <h1>First-run setup</h1>
  <p>Create the first admin. This page stops working once a user exists.</p>
  <form method="post" action="/admin/setup">
    <?= csrf_field() ?>
    <input type="hidden" name="token" value="<?= e($token) ?>">
<?php if ($error): ?><p class="err" role="alert"><?= e($error) ?></p><?php endif ?>
    <?= admin_field('email', 'Email', $email, null, 'email', '', 'autocomplete="username" required autofocus') ?>
    <?= admin_field('password', 'Password', '', null, 'password', 'At least ' . PASSWORD_MIN . ' characters.', 'autocomplete="new-password" required') ?>
    <button class="btn btn--primary btn--block" type="submit" data-busy="Saving&hellip;">Create admin</button>
  </form>
</section>
