<?php /** @var ?string $message */ ?>
<section class="card card--narrow">
  <h1><?= e($title) ?></h1>
  <p><?= e($message ?? 'There is nothing at this address.') ?></p>
  <p class="aside"><a href="/admin">Back to the admin</a></p>
</section>
