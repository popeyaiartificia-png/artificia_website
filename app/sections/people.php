<?php /** @var array $f, $page, $settings; @var int $s */ ?>
<section class="sec sec--people">
<?php if (!empty($f["heading"])): ?><h2><?= heading_html($f["heading"]) ?></h2><?php endif; ?>
<ul>
<?php foreach ($f['items'] ?? [] as $p): ?>
<li><h3><?= e($p['name'] ?? '') ?></h3> <?= e($p['role'] ?? '') ?> <p><?= e($p['bio'] ?? '') ?></p></li>
<?php endforeach; ?>
</ul>
</section>
