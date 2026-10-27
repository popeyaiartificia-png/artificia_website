<?php /** @var array $f, $page, $settings; @var int $s */ ?>
<section class="sec sec--card_grid">
<?php if (!empty($f["heading"])): ?><h2><?= heading_html($f["heading"]) ?></h2><?php endif; ?>
<?php if (!empty($f["intro"])): ?><p><?= e($f["intro"]) ?></p><?php endif; ?>
<ul>
<?php foreach ($f['cards'] ?? [] as $c): ?>
<li><h3><?= e($c['title'] ?? '') ?></h3> <?= e($c['text'] ?? '') ?><?php if (!empty($c['link'])): ?> <a href="<?= link_href($c['link']) ?>">More</a><?php endif; ?></li>
<?php endforeach; ?>
</ul>
</section>
