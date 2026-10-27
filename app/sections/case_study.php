<?php /** @var array $f, $page, $settings; @var int $s */ ?>
<section class="sec sec--case_study">
<?php if (!empty($f["heading"])): ?><h2><?= heading_html($f["heading"]) ?></h2><?php endif; ?>
<p><?= e($f['client'] ?? '') ?></p>
<p><?= e($f['summary'] ?? '') ?></p>
<?= rich($f['challenge'] ?? '') ?>
<ul>
<?php foreach ($f['built'] ?? [] as $b): ?>
<li><h3><?= e($b['title'] ?? '') ?></h3> <?= e($b['text'] ?? '') ?></li>
<?php endforeach; ?>
</ul>
<ul>
<?php foreach ($f['results'] ?? [] as $r): ?>
<li><strong><?= e($r['value'] ?? '') ?></strong> <?= e($r['label'] ?? '') ?></li>
<?php endforeach; ?>
</ul>
<?php if (!empty($f['link_label'])): ?><p><a href="<?= link_href($f['link'] ?? '') ?>"><?= e($f['link_label']) ?></a></p><?php endif; ?>
</section>
