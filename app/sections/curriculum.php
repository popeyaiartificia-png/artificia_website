<?php
if (($f['variant'] ?? '') === 'course') {
    include VIEWS_DIR . '/course-curriculum.php';
    return;
}
$tracks = ['foundation' => ['30-day Foundation', 'Days 1 to 30'], 'full' => ['60-day Full program', 'Days 31 to 60, after the Foundation']];
$byTrack = ['foundation' => [], 'full' => []];
foreach ($f['modules'] ?? [] as $m) {
    $byTrack[($m['track'] ?? '') === 'full' ? 'full' : 'foundation'][] = $m;
}
?>
<section class="sec sec--curriculum curr" id="curriculum" data-section="curriculum">
  <div class="wrap">
    <?= sec_head($f['heading'] ?? '') ?>
<?php if (!empty($f['intro'])): ?>
    <p class="lede"><?= e($f['intro']) ?></p>
<?php endif ?>
    <div class="ruler" aria-hidden="true">
      <div class="ruler__ticks"></div>
      <div class="ruler__line"><span class="ruler__seg"></span><span class="ruler__seg"></span></div>
      <span class="ruler__hl"></span>
      <div class="ruler__labels"><span style="left:0">Day 1</span><span style="left:50%">Day 30</span><span style="left:100%">Day 60</span></div>
    </div>
    <div class="tracks">
<?php foreach ($byTrack as $key => $mods): if (!$mods) continue; ?>
      <div class="track">
        <h3 class="track__t"><?= e($tracks[$key][0]) ?></h3>
        <p class="track__note"><?= e($tracks[$key][1]) ?></p>
        <ol class="mods" data-reveal="list">
<?php foreach ($mods as $m): ?>
          <li class="mod" data-days="<?= e($m['day_range'] ?? '') ?>">
            <h4 class="mod__t"><?= e($m['title'] ?? '') ?></h4>
            <p class="entry"><span>Days <?= e($m['day_range'] ?? '') ?></span><span><?= $key === 'full' ? 'Full program' : 'Foundation' ?></span></p>
<?php if (!empty($m['topics'])): ?>
            <p class="mod__topics"><?= e($m['topics']) ?></p>
<?php endif ?>
<?php if (!empty($m['tools'])): ?>
            <p class="mod__tools"><span class="vh">Tools: </span><?= e($m['tools']) ?></p>
<?php endif ?>
          </li>
<?php endforeach ?>
        </ol>
      </div>
<?php endforeach ?>
    </div>
  </div>
</section>
