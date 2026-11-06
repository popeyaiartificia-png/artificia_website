<?php
/** The masterclass hero layout, shared by every inner page (user, 6 Nov 2026): breadcrumb and eyebrow, one large
 *  centred title, then a short statement and the buttons on the left, the before-and-after ribbon in the middle and one
 *  verified figure on the right. course.js animates the ribbon on any page that carries data-masterclass.
 *  Callers may set $j (defaults for empty fields) and $journeyVisual (a view that replaces the ribbon). */
$j = ($j ?? []) + ['format' => '', 'stat_value' => '', 'stat_unit' => '', 'stat_label' => '', 'before_label' => 'Before', 'before_text' => '',
    'after_label' => 'After', 'after_text' => '', 'primary_cta_link' => '#enquire', 'secondary_cta_link' => '#enquire'];
$jf = fn(string $k) => trim((string) ($f[$k] ?? '')) !== '' ? (string) $f[$k] : (string) ($j[$k] ?? '');
$statement = $jf('statement') !== '' ? $jf('statement') : $jf('subheading');
// The small line under the statement: its own field, else the longer subheading when a short statement replaced it.
$small = $jf('format') !== '' ? $jf('format') : ($jf('statement') !== '' ? $jf('subheading') : '');
$long = mb_strlen(heading_plain($h1)) > 18;
$visual = $journeyVisual ?? '';
?>
<section class="course-hero journey-hero" data-masterclass data-journey-phase="after" aria-labelledby="journey-title">
  <div class="masterclass-terrain" aria-hidden="true"><span></span><span></span><span></span></div>
  <div class="wrap">
    <div class="course-hero__top"><nav class="course-breadcrumb" aria-label="Breadcrumb"><ol><li><a href="/">Home</a></li><li aria-current="page"><?= e($page['title'] ?? '') ?></li></ol></nav><?php if ($jf('eyebrow') !== ''): ?><p><span class="course-dot"></span><?= e($jf('eyebrow')) ?></p><?php endif ?></div>
    <h1 id="journey-title" class="masterclass-title<?= $long ? ' journey-title--wrap' : '' ?>"><?= heading_html($h1) ?></h1>
    <div class="masterclass-stage<?= $visual !== '' ? ' journey-stage--custom' : '' ?>">
      <div class="masterclass-intro">
<?php if ($statement !== ''): ?>
        <p class="masterclass-intro__statement"><?= e($statement) ?></p>
<?php endif ?>
<?php if ($small !== ''): ?>
        <p class="masterclass-intro__format"><?= e($small) ?></p>
<?php endif ?>
<?php if ($jf('primary_cta_label') !== ''): ?>
        <a class="btn btn--lime" href="<?= link_href($jf('primary_cta_link')) ?>"><?= e($jf('primary_cta_label')) ?><?= icon('arrow-up-right') ?></a>
<?php endif ?>
<?php if ($jf('secondary_cta_label') !== ''): ?>
        <a class="course-text-link" href="<?= link_href($jf('secondary_cta_link')) ?>"><?= e($jf('secondary_cta_label')) ?><?= icon('arrow-right') ?></a>
<?php endif ?>
      </div>
<?php if ($visual !== ''): ?>
      <div class="masterclass-sculpture journey-visual"><?php include VIEWS_DIR . '/' . $visual . '.php'; ?></div>
<?php else: ?>
      <div class="masterclass-sculpture" data-learning-sculpture>
        <div class="masterclass-sculpture__halo" aria-hidden="true"></div>
        <?php include VIEWS_DIR . '/course-learning-sculpture.php'; ?>
<?php if ($jf('before_text') !== ''): ?>
        <p class="journey-caption journey-caption--before"><span><?= e($jf('before_label')) ?></span><?= e($jf('before_text')) ?></p>
<?php endif ?>
<?php if ($jf('after_text') !== ''): ?>
        <p class="journey-caption journey-caption--after"><span><?= e($jf('after_label')) ?></span><?= e($jf('after_text')) ?></p>
<?php endif ?>
        <div class="journey-controls" data-journey-controls hidden>
          <button type="button" class="journey-replay" data-journey-replay aria-label="Replay the before and after animation"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 7v5h-5M20 12a8 8 0 1 0-2.4 5.7"/></svg>Replay</button>
          <button type="button" class="journey-pause" data-journey-pause aria-label="Pause path animation"><svg viewBox="0 0 24 24" aria-hidden="true"><path class="journey-pause__bars" d="M8 5v14M16 5v14"/><path class="journey-pause__play" d="m8 5 11 7-11 7Z"/></svg><span data-journey-pause-label>Pause</span></button>
        </div>
      </div>
<?php endif ?>
<?php if ($jf('stat_value') !== ''): ?>
      <aside class="masterclass-duration" aria-label="<?= e(trim($jf('stat_value') . ' ' . $jf('stat_unit') . ' ' . $jf('stat_label'))) ?>">
        <p class="masterclass-duration__number<?= mb_strlen($jf('stat_value')) > 3 ? ' journey-stat--word' : '' ?>"><strong><?= e($jf('stat_value')) ?></strong><?php if ($jf('stat_unit') !== ''): ?><span><?= e($jf('stat_unit')) ?></span><?php endif ?></p>
        <p class="masterclass-duration__name"><?= e($jf('stat_label')) ?></p>
      </aside>
<?php endif ?>
    </div>
  </div>
</section>
