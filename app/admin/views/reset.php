<?php /** @var string $token; @var ?string $error */ ?>
<section class="card narrow">
  <h1>Choose a new password</h1>
  <form method="post" action="/admin/reset">
    <?= csrf_field() ?>
    <input type="hidden" name="token" value="<?= e($token) ?>">
<?php if ($error): ?><p class="err" role="alert"><?= e($error) ?></p><?php endif ?>
    <?= admin_field('password', 'New password', '', null, 'password', 'At least ' . PASSWORD_MIN . ' characters.', 'autocomplete="new-password" required autofocus') ?>
    <?= admin_field('password2', 'New password again', '', null, 'password', '', 'autocomplete="new-password" required') ?>
    <button class="btn" type="submit">Save password</button>
  </form>
</section>
