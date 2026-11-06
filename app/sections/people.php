<section class="sec sec--people team">
  <div class="wrap">
    <?= sec_head($f['heading'] ?? '') ?>
    <ul class="people" data-reveal="list">
<?php foreach ($f['items'] ?? [] as $p): ?>
      <li class="person">
<?php if (!empty($p['photo'])): ?>
        <figure class="person__fig frame"><?= image($p['photo'], '(min-width: 1100px) 380px, (min-width: 720px) 45vw, 100vw') ?></figure>
<?php endif ?>
        <h3 class="person__name"><?= e($p['name'] ?? '') ?></h3>
        <p class="entry"><span><?= e($p['role'] ?? '') ?></span></p>
<?php if (!empty($p['bio'])): ?>
        <p class="person__bio"><?= e($p['bio']) ?></p>
<?php endif ?>
<?php if (!empty($p['photo_2'])): ?>
        <figure class="person__fig2"><?= image($p['photo_2'], '(min-width: 900px) 280px, 70vw') ?></figure>
<?php endif ?>
<?php if (!empty($p['linkedin'])): ?>
        <a class="person__in" href="<?= link_href($p['linkedin'] ?? '') ?>" rel="noopener"><?= icon('linkedin-logo') ?>LinkedIn<span class="vh"> profile of <?= e($p['name'] ?? '') ?></span></a>
<?php endif ?>
      </li>
<?php endforeach ?>
    </ul>
  </div>
</section>
