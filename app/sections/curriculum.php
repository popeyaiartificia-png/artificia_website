<?php /** @var array $f, $page, $settings; @var int $s */ ?>
<section class="sec sec--curriculum">
<?php if (!empty($f["heading"])): ?><h2><?= heading_html($f["heading"]) ?></h2><?php endif; ?>
<?php if (!empty($f["intro"])): ?><p><?= e($f["intro"]) ?></p><?php endif; ?>
<ul>
<?php foreach ($f['modules'] ?? [] as $m): ?>
<li><h3><?= e(($m['day_range'] ?? '') . ': ' . ($m['title'] ?? '')) ?></h3> <p><?= e($m['topics'] ?? '') ?></p> <p><?= e($m['tools'] ?? '') ?></p></li>
<?php endforeach; ?>
</ul>
</section>
