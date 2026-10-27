<?php $cols = in_array($f['columns'] ?? '', ['2', '3', '4'], true) ? $f['columns'] : '3'; ?>
<section class="sec sec--card_grid cards">
  <div class="wrap">
    <?= sec_head($f['heading'] ?? '') ?>
<?php if (!empty($f['intro'])): ?>
    <p class="lede"><?= e($f['intro']) ?></p>
<?php endif ?>
    <ul class="index index--<?= $cols ?>" data-reveal="list">
<?php foreach ($f['cards'] ?? [] as $c): ?>
      <li class="index__item">
        <?= icon($c['icon'] ?? '', 'icon index__icon') ?>
<?php if (!empty($c['link'])): ?>
        <h3 class="h3"><a href="<?= link_href($c['link'] ?? '') ?>"><?= e($c['title'] ?? '') ?></a></h3>
        <?= icon('arrow-up-right', 'icon index__go') ?>
<?php else: ?>
        <h3 class="h3"><?= e($c['title'] ?? '') ?></h3>
<?php endif ?>
        <p><?= e($c['text'] ?? '') ?></p>
      </li>
<?php endforeach ?>
    </ul>
  </div>
</section>
