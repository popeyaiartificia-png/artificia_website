<?php
$variant = ($f['variant'] ?? 'split') === 'centered' ? 'centered' : 'split';
$h1 = ($page['h1'] ?? '') !== '' ? $page['h1'] : ($page['title'] ?? '');
$doors = $f['doors'] ?? [];
$sketch = drawing((string) ($f['drawing'] ?? ''));
$media = $sketch;
if ($media === '' && !empty($f['image'])) {
    $media = '<div class="frame">' . image($f['image'], '(min-width: 1024px) 560px, 100vw', true) . '</div>';
}
?>
<section class="sec sec--hero hero hero--<?= $variant ?><?= $sketch !== '' ? ' hero--drawn' : '' ?>" data-section="hero">
  <div class="wrap hero__grid">
    <div class="hero__copy">
<?php $tag = $s === 0 ? 'h1' : 'h2'; // the page h1 belongs to a hero only when it opens the page ?>
      <div class="sec__head"><?= tick() ?><<?= $tag ?> class="h1"><?= heading_html($h1) ?></<?= $tag ?>></div>
<?php if (!empty($f['subheading'])): ?>
      <p class="lede hero__sub"><?= e($f['subheading']) ?></p>
<?php endif ?>
<?php if ($doors): ?>
      <ul class="doors" data-reveal="list">
<?php foreach ($doors as $d): ?>
        <li><a class="door" href="<?= link_href($d['link'] ?? '') ?>"><span class="door__label"><?= e($d['label'] ?? '') ?></span><?php if (!empty($d['note'])): ?><span class="door__note"><?= e($d['note']) ?></span><?php endif ?><?= icon('arrow-right') ?></a></li>
<?php endforeach ?>
      </ul>
<?php elseif (!empty($f['primary_cta_label']) || !empty($f['secondary_cta_label'])): ?>
      <div class="btns">
<?php if (!empty($f['primary_cta_label'])): ?>
        <a class="btn btn--lime" href="<?= link_href($f['primary_cta_link'] ?? '') ?>"><?= e($f['primary_cta_label']) ?><?= icon('arrow-right') ?></a>
<?php endif ?>
<?php if (!empty($f['secondary_cta_label'])): ?>
        <a class="btn" href="<?= link_href($f['secondary_cta_link'] ?? '') ?>"><?= e($f['secondary_cta_label']) ?></a>
<?php endif ?>
      </div>
<?php endif ?>
    </div>
<?php if ($media !== ''): ?>
    <div class="hero__media"><?= $media ?></div>
<?php endif ?>
<?php if (!empty($f['eyebrow'])): ?>
    <p class="hero__foot"><?= e($f['eyebrow']) ?></p>
<?php endif ?>
  </div>
</section>
