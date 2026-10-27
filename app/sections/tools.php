<section class="sec sec--tools tools">
  <div class="wrap">
    <?= sec_head($f['heading'] ?? '') ?>
<?php if (!empty($f['intro'])): ?>
    <p class="lede"><?= e($f['intro']) ?></p>
<?php endif ?>
    <ul class="parts" data-reveal="list">
<?php foreach ($f['tools'] ?? [] as $t): ?>
      <li class="part"><?= !empty($t['logo']) ? image($t['logo'], '22px') : '' ?><span class="part__name"><?= e($t['name'] ?? '') ?></span><?php if (!empty($t['note'])): ?><span class="part__lead" aria-hidden="true"></span><span class="part__note"><?= e($t['note']) ?></span><?php endif ?></li>
<?php endforeach ?>
    </ul>
  </div>
</section>
