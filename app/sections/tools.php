<?php /** @var array $f, $page, $settings; @var int $s */ ?>
<section class="sec sec--tools">
<?php if (!empty($f["heading"])): ?><h2><?= heading_html($f["heading"]) ?></h2><?php endif; ?>
<?php if (!empty($f["intro"])): ?><p><?= e($f["intro"]) ?></p><?php endif; ?>
<ul>
<?php foreach ($f['tools'] ?? [] as $t): ?>
<li><?= e($t['name'] ?? '') ?> <?= e($t['note'] ?? '') ?></li>
<?php endforeach; ?>
</ul>
</section>
