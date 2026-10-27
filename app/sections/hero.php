<?php /** @var array $f, $page, $settings; @var int $s */ ?>
<section class="sec sec--hero">
<?php if (!empty($f['eyebrow'])): ?><p><?= e($f['eyebrow']) ?></p><?php endif; ?>
<?php // The page h1 belongs to the first section only; a later hero gets an h2. ?>
<?php $tag = $s === 0 ? 'h1' : 'h2'; ?>
<<?= $tag ?>><?= heading_html(($page['h1'] ?? '') !== '' ? $page['h1'] : ($page['title'] ?? '')) ?></<?= $tag ?>>
<?php if (!empty($f['subheading'])): ?><p><?= e($f['subheading']) ?></p><?php endif; ?>
<?php foreach ([['primary_cta_label', 'primary_cta_link'], ['secondary_cta_label', 'secondary_cta_link']] as [$label, $link]): ?>
<?php if (!empty($f[$label])): ?><p><a href="<?= link_href($f[$link] ?? '') ?>"><?= e($f[$label]) ?></a></p><?php endif; ?>
<?php endforeach; ?>
<?php if (!empty($f['doors'])): ?>
<ul>
<?php foreach ($f['doors'] as $d): ?>
<li><a href="<?= link_href($d['link'] ?? '') ?>"><?= e($d['label'] ?? '') ?></a> <?= e($d['note'] ?? '') ?></li>
<?php endforeach; ?>
</ul>
<?php endif; ?>
</section>
