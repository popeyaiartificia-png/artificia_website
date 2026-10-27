<?php /** @var array $f, $page, $settings; @var int $s */ ?>
<section class="sec sec--use_cases">
<?php if (!empty($f["heading"])): ?><h2><?= heading_html($f["heading"]) ?></h2><?php endif; ?>
<?php if (!empty($f["intro"])): ?><p><?= e($f["intro"]) ?></p><?php endif; ?>
<ul>
<?php foreach ($f['items'] ?? [] as $i): ?>
<li><h3><?= e($i['title'] ?? '') ?></h3> <p>Before: <?= e($i['before'] ?? '') ?></p> <p>After: <?= e($i['after'] ?? '') ?></p></li>
<?php endforeach; ?>
</ul>
</section>
