<?php
/** Outcomes as lines that straighten: each row's scribble (the friction) is pulled straight as it reaches the reading line.
 *  The use_cases fields: title = the outcome, before / after = the working day either side of it. */
// A looping scribble from the left end to the right, deterministic per row so the page never shifts between loads.
$scribble = function (int $i): string {
    $d = '';
    $loops = 6 + $i % 3;
    for ($k = 0; $k <= 96; $k++) {
        $u = $k / 96;
        $env = sin(M_PI * $u) ** .6;
        $a = $loops * 2 * M_PI * $u + $i * 1.7;
        $x = $u * 400 + 15 * $env * cos($a);
        $y = 30 + 17 * $env * sin($a) + 5 * $env * sin($u * 23 + $i);
        $d .= ($k ? 'L' : 'M') . round($x, 1) . ' ' . round($y, 1);
    }
    return $d;
};
?>
<section class="sec sol-out" data-sol-out>
  <canvas class="topo-bg" aria-hidden="true"></canvas>
  <div class="wrap">
    <header class="sol-out__head">
      <p class="sol-eyebrow"><span class="sol-dot" aria-hidden="true"></span>What changes</p>
      <?= sec_head($f['heading'] ?? '') ?>
<?php if (!empty($f['intro'])): ?>
      <p class="sol-out__intro"><?= e($f['intro']) ?></p>
<?php endif ?>
    </header>
    <ol class="sol-out__list">
<?php foreach (array_values($f['items'] ?? []) as $i => $it): ?>
      <li class="sol-out__row" data-sol-row>
        <span class="sol-out__n" aria-hidden="true"><?= sprintf('%02d', $i + 1) ?></span>
        <h3 class="sol-out__t"><?= e($it['title'] ?? '') ?></h3>
        <svg class="sol-out__line" viewBox="-8 0 416 60" preserveAspectRatio="none" aria-hidden="true" focusable="false">
          <path class="sol-out__mess" pathLength="1" d="<?= $scribble($i) ?>"/>
          <path class="sol-out__calm" pathLength="1" d="M0 30H400"/>
        </svg>
        <div class="sol-out__copy">
<?php if (!empty($it['before'])): ?>
          <p class="sol-out__before"><span>Before</span><?= e($it['before']) ?></p>
<?php endif ?>
<?php if (!empty($it['after'])): ?>
          <p class="sol-out__after"><span>After</span><?= e($it['after']) ?></p>
<?php endif ?>
        </div>
      </li>
<?php endforeach ?>
    </ol>
  </div>
</section>
