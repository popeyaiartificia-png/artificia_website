<?php /** @var array $f, $page, $settings; @var int $s */ ?>
<section class="sec sec--stats">
<?php if (!empty($f["heading"])): ?><h2><?= heading_html($f["heading"]) ?></h2><?php endif; ?>
<ul>
<?php foreach ($f['items'] ?? [] as $i): ?>
<li><strong><?= e($i['value'] ?? '') ?></strong> <?= e($i['label'] ?? '') ?></li>
<?php endforeach; ?>
</ul>
<p><?= e($f['note'] ?? 'Typical outcomes for AI-enabled teams.') ?></p>
</section>
