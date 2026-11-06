<?php
/** Solutions hero: each kind of friction runs as a tangle into one core and leaves as a clean lane to the solution that fixes it.
 *  The doors carry the pairs (note = the friction, label = the solution). The canvas is decoration; the list is the content. */
// Where each friction sits in the tangle, as a share of the stage (a fixed composition; more doors reuse it).
$spots = [[4, 13], [24, 31], [7, 47], [27, 64], [3, 80], [21, 92]];
?>
<section class="sol-hero" data-sol-hero aria-labelledby="sol-title">
  <canvas class="topo-bg" aria-hidden="true"></canvas>
  <div class="wrap">
    <div class="sol-hero__top">
      <nav class="sol-crumb" aria-label="Breadcrumb"><ol><li><a href="/">Home</a></li><li aria-current="page"><?= e($page['title'] ?? 'Solutions') ?></li></ol></nav>
<?php if (!empty($f['eyebrow'])): ?>
      <p class="sol-hero__eyebrow"><span class="sol-dot" aria-hidden="true"></span><?= e($f['eyebrow']) ?></p>
<?php endif ?>
    </div>
    <h1 id="sol-title" class="sol-hero__title"><?= heading_html($h1) ?></h1>
  </div>
  <div class="sol-flow" data-sol-flow>
    <div class="sol-flow__stage">
      <canvas class="sol-flow__canvas" aria-hidden="true"></canvas>
      <ul class="sol-flow__in" aria-hidden="true">
<?php foreach ($doors as $i => $door): [$x, $y] = $spots[$i % count($spots)]; ?>
        <li style="--x: <?= $x ?>%; --y: <?= $y ?>%" data-from="<?= $i ?>"><span class="sol-flow__knot"></span><?= e($door['note'] ?? '') ?></li>
<?php endforeach ?>
      </ul>
      <div class="sol-flow__core" aria-hidden="true">
        <span class="sol-flow__ring"></span>
        <svg class="sol-flow__star" viewBox="0 0 24 24"><path d="M12 0c1.6 7.7 4.3 10.4 12 12-7.7 1.6-10.4 4.3-12 12-1.6-7.7-4.3-10.4-12-12C7.7 10.4 10.4 7.7 12 0z"/></svg>
        <span class="sol-flow__core-label">Artificia</span>
      </div>
    </div>
    <ol class="sol-flow__out" aria-label="Our solutions" style="--n: <?= max(1, count($doors)) ?>">
<?php foreach ($doors as $i => $door): ?>
      <li><a href="<?= link_href($door['link'] ?? '#solutions') ?>" data-lane="<?= $i ?>"><span class="sol-flow__num" aria-hidden="true"><?= sprintf('%02d', $i + 1) ?></span><span class="sol-flow__name"><?php if (!empty($door['note'])): ?><span class="sol-flow__from"><span class="sol-sr">For </span><?= e($door['note']) ?><span class="sol-sr">: </span></span><?php endif ?><?= e($door['label'] ?? '') ?></span><?= icon('arrow-up-right', 'icon sol-flow__go') ?></a></li>
<?php endforeach ?>
    </ol>
  </div>
  <div class="wrap sol-hero__foot">
<?php if (!empty($f['subheading'])): ?>
    <p class="sol-hero__lede"><?= e($f['subheading']) ?></p>
<?php endif ?>
    <div class="sol-hero__ctas">
<?php if (!empty($f['primary_cta_label'])): ?>
      <a class="btn btn--lime" href="<?= link_href($f['primary_cta_link'] ?? '#enquire') ?>"><?= e($f['primary_cta_label']) ?><?= icon('arrow-up-right') ?></a>
<?php endif ?>
<?php if (!empty($f['secondary_cta_label'])): ?>
      <a class="sol-text-link" href="<?= link_href($f['secondary_cta_link'] ?? '#solutions') ?>"><?= e($f['secondary_cta_label']) ?><?= icon('arrow-right') ?></a>
<?php endif ?>
    </div>
  </div>
</section>
