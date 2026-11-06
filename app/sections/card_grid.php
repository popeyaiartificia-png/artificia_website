<?php
$views = ['course-plans' => 'course-plans', 'pillars' => 'about-work', 'notes' => 'about-notes', 'kit' => 'corporate-kit'];
if (isset($views[$f['variant'] ?? ''])) {
    include VIEWS_DIR . '/' . $views[$f['variant']] . '.php';
    return;
}
$cols = in_array($f['columns'] ?? '', ['2', '3', '4'], true) ? $f['columns'] : '3';
$photo = array_filter($f['cards'] ?? [], fn($c) => !empty($c['image'])) !== [];
$drawn = !$photo && array_filter($f['cards'] ?? [], fn($c) => drawing((string) ($c['drawing'] ?? '')) !== '') !== [];
$layout = $photo ? 'index--photo index--' . $cols : ($drawn ? 'index--drawn' : 'index--' . $cols);
?>
<section class="sec sec--card_grid cards">
  <div class="wrap">
    <?= sec_head($f['heading'] ?? '') ?>
<?php if (!empty($f['intro'])): ?>
    <p class="lede"><?= e($f['intro']) ?></p>
<?php endif ?>
    <ul class="index <?= $layout ?>" data-reveal="list">
<?php foreach ($f['cards'] ?? [] as $c): ?>
      <li class="index__item">
<?php $sketch = drawing((string) ($c['drawing'] ?? '')); ?>
<?php if (!empty($c['image'])): ?>
        <figure class="index__photo"><?= image($c['image'], '(min-width: 1024px) 400px, (min-width: 720px) 45vw, 100vw') ?></figure>
<?php elseif ($sketch !== ''): ?>
        <div class="index__draw"><?= $sketch ?></div>
<?php else: ?>
        <?= icon((string) ($c['icon'] ?? ''), 'icon index__icon') ?>
<?php endif ?>
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
