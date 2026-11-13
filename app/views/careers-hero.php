<?php
/** Careers: an editorial introduction and a working vacancy sheet, in the homepage theme. */
$roles = $f['doors'] ?? [];
?>
<section class="sol-hero careers-hero" aria-labelledby="careers-title">
  <canvas class="topo-bg" aria-hidden="true"></canvas>
  <div class="wrap">
    <div class="sol-hero__top">
      <nav class="sol-crumb" aria-label="Breadcrumb"><ol><li><a href="/">Home</a></li><li aria-current="page"><?= e($page['title'] ?? 'Careers') ?></li></ol></nav>
<?php if (!empty($f['eyebrow'])): ?>
      <p class="sol-hero__eyebrow"><span class="sol-dot" aria-hidden="true"></span><?= e($f['eyebrow']) ?></p>
<?php endif ?>
    </div>
    <div class="careers-hero__grid">
      <div class="careers-hero__copy">
        <p class="careers-hero__kicker"><?= icon('arrow-up-right') ?>Build. Train. Teach.</p>
        <h1 id="careers-title" class="careers-hero__title"><?= heading_html($h1) ?></h1>
<?php if (!empty($f['statement'])): ?>
        <p class="careers-hero__statement"><?= e($f['statement']) ?></p>
<?php endif ?>
<?php if (!empty($f['subheading'])): ?>
        <p class="careers-hero__lede"><?= e($f['subheading']) ?></p>
<?php endif ?>
        <div class="careers-hero__actions">
<?php if (!empty($f['primary_cta_label'])): ?>
          <a class="btn btn--lime" href="<?= link_href($f['primary_cta_link'] ?? '#enquire') ?>"><?= e($f['primary_cta_label']) ?><?= icon('arrow-up-right') ?></a>
<?php endif ?>
<?php if (!empty($f['secondary_cta_label'])): ?>
          <a class="sol-text-link" href="<?= link_href($f['secondary_cta_link'] ?? '#enquire') ?>"><?= e($f['secondary_cta_label']) ?><?= icon('arrow-right') ?></a>
<?php endif ?>
        </div>
      </div>
      <div class="careers-sheet" role="group" aria-labelledby="careers-roles-title">
        <div class="careers-sheet__head">
          <div><p class="careers-sheet__eyebrow">Find your place</p><h2 id="careers-roles-title">Open <em>roles.</em></h2></div>
          <span class="careers-sheet__count" aria-label="<?= count($roles) ?> open roles"><?= sprintf('%02d', count($roles)) ?></span>
        </div>
        <ul class="careers-sheet__roles">
<?php foreach ($roles as $n => $role): $label = (string) ($role['label'] ?? ''); ?>
          <li><a class="career-role" href="<?= link_href($role['link'] ?? '#enquire') ?>"<?= in_array($label, LEAD_OPTIONS['role'], true) ? ' data-pick-role="' . e($label) . '"' : '' ?>>
            <span class="career-role__number" aria-hidden="true"><?= sprintf('%02d', $n + 1) ?></span>
            <span class="career-role__copy"><h3><?= e($label) ?></h3><?php if (!empty($role['note'])): ?><span><?= e($role['note']) ?></span><?php endif ?></span>
            <span class="career-role__arrow" aria-hidden="true"><?= icon('arrow-up-right') ?></span>
          </a></li>
<?php endforeach ?>
        </ul>
        <div class="careers-sheet__foot"><span><?= icon('map-pin') ?>Vadodara, Gujarat</span><span>Choose a role to apply <?= icon('arrow-right') ?></span></div>
      </div>
    </div>
    <div class="careers-path" role="group" aria-labelledby="careers-path-title">
      <p id="careers-path-title">Your next step<span>One role. One resume link.</span></p>
      <ol>
        <li><span class="careers-path__number">01</span><span>Choose <strong>your role</strong></span><?= icon('arrow-right') ?></li>
        <li><span class="careers-path__number">02</span><span>Share <strong>your resume link</strong></span><?= icon('arrow-right') ?></li>
        <li><span class="careers-path__number">03</span><span>Send <strong>your application</strong></span><?= icon('check') ?></li>
      </ol>
    </div>
  </div>
</section>
