<?php /** @var ?string $error; @var string $email */ ?>
<section class="card">
  <h1>Log in</h1>
  <form method="post" action="/admin/login" novalidate>
    <?= csrf_field() ?>
<?php if ($error): ?><p class="err" role="alert"><?= e($error) ?></p><?php endif ?>
    <?= admin_field('email', 'Email', $email, null, 'email', '', 'autocomplete="username" required autofocus') ?>
    <?= admin_field('password', 'Password', '', null, 'password', '', 'autocomplete="current-password" required') ?>
    <button class="btn btn--primary btn--block" type="submit" data-busy="Logging in&hellip;">Log in</button>
  </form>
  <p class="aside"><a href="/admin/forgot">Forgot your password?</a></p>
</section>
