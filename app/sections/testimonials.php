<?php /** @var array $f, $page, $settings; @var int $s */ ?>
<section class="sec sec--testimonials">
<?php if (!empty($f["heading"])): ?><h2><?= heading_html($f["heading"]) ?></h2><?php endif; ?>
<ul>
<?php foreach ($f['items'] ?? [] as $i): ?>
<li><blockquote><?= e($i['quote'] ?? '') ?></blockquote> <?= e(($i['name'] ?? '') . ', ' . ($i['role'] ?? '')) ?></li>
<?php endforeach; ?>
</ul>
</section>
