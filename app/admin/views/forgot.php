<?php /** @var bool $sent */ ?>
<section class="card">
  <h1>Forgot password</h1>
<?php if ($sent): ?>
  <p role="status">If that email belongs to an account, a link to choose a new password is on its way. It works for one hour.</p>
<?php else: ?>
  <form method="post" action="/admin/forgot">
    <?= csrf_field() ?>
    <?= admin_field('email', 'Email', '', null, 'email', '', 'autocomplete="username" required autofocus') ?>
    <button class="btn btn--primary btn--block" type="submit" data-busy="Sending&hellip;">Send the link</button>
  </form>
<?php endif ?>
  <p class="aside"><a href="/admin/login">Back to log in</a></p>
</section>
