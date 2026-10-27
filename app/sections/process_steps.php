<?php /** @var array $f, $page, $settings; @var int $s */ ?>
<section class="sec sec--process_steps">
<?php if (!empty($f["heading"])): ?><h2><?= heading_html($f["heading"]) ?></h2><?php endif; ?>
<?php if (!empty($f["intro"])): ?><p><?= e($f["intro"]) ?></p><?php endif; ?>
<ol>
<?php foreach ($f['steps'] ?? [] as $st): ?>
<li><h3><?= e($st['title'] ?? '') ?></h3> <?= e($st['text'] ?? '') ?></li>
<?php endforeach; ?>
</ol>
</section>
