<?php
/** Corporate training: what the company keeps, as five documents dealt from one pile into a spread as the section scrolls
 *  in. Each card's icon picks its document type and a small drawn page (an unknown icon falls back to the plain icon).
 *  card_grid fields: icon, title, text. */
$docs = [
    // a workflow: three steps, the middle one the team's own
    'flow-arrow' => ['Workflow', '<rect class="k-soft" x="8" y="44" width="46" height="32"/><path class="k-soft" d="M16 56h28M16 64h18"/><rect class="k-neon" x="77" y="36" width="46" height="48"/><path class="k-ink" d="M85 50h30M85 59h22M85 68h27"/><rect class="k-soft" x="146" y="44" width="46" height="32"/><path class="k-soft" d="M154 56h28M154 64h14"/><path class="k-ink" d="M57 60h15m-5-4 5 4-5 4M126 60h15m-5-4 5 4-5 4"/>'],
    // the rules: two approved, one never uploaded
    'lock-key' => ['Policy', '<rect class="k-neon" x="34" y="14" width="74" height="9"/><rect class="k-ink" x="38" y="38" width="11" height="11"/><path class="k-ink" d="M40.5 43.5l2.5 2.5 4.5-5M58 43.5h86"/><rect class="k-ink" x="38" y="60" width="11" height="11"/><path class="k-ink" d="M40.5 65.5l2.5 2.5 4.5-5M58 65.5h70"/><rect class="k-soft" x="38" y="82" width="11" height="11"/><path class="k-soft" d="M40.5 84.5l6 6m0-6-6 6M58 87.5h60"/><path class="k-ink" d="M56 87.5h64"/><rect class="k-ink" x="150" y="83" width="13" height="10"/><path class="k-ink" d="M152.5 83v-3.5a4 4 0 0 1 8 0V83"/>'],
    // a week with the same habit every day
    'clock' => ['Routine', '<path class="k-soft" d="M20 24h160M20 102h160"/><g class="k-days"><rect class="k-soft" x="22" y="64" width="26" height="12"/><rect class="k-soft" x="86" y="80" width="26" height="12"/><rect class="k-soft" x="150" y="68" width="26" height="12"/></g><rect class="k-neon" x="22" y="38" width="26" height="14"/><rect class="k-neon" x="54" y="38" width="26" height="14"/><rect class="k-neon" x="86" y="38" width="26" height="14"/><rect class="k-neon" x="118" y="38" width="26" height="14"/><rect class="k-neon" x="150" y="38" width="26" height="14"/><text class="k-lbl" x="35" y="17">M</text><text class="k-lbl" x="67" y="17">T</text><text class="k-lbl" x="99" y="17">W</text><text class="k-lbl" x="131" y="17">T</text><text class="k-lbl" x="163" y="17">F</text>'],
    // a finished piece of work, stamped done
    'file-text' => ['Project', '<path class="k-ink" d="M52 10h66l22 22v80H52z"/><path class="k-ink" d="M118 10v22h22"/><rect class="k-neon" x="62" y="40" width="44" height="8"/><path class="k-soft" d="M62 58h64M62 68h56M62 78h60M62 88h38"/><circle class="k-stamp" cx="138" cy="88" r="17"/><path class="k-ink" d="M130 88l5.5 5.5 10-11"/>'],
    // what comes next, step by step
    'chart-line-up' => ['Plan', '<path class="k-soft" d="M22 78h156"/><path class="k-ink" d="M22 78h104"/><circle class="k-dot" cx="30" cy="78" r="5"/><circle class="k-dot" cx="78" cy="78" r="5"/><circle class="k-dot" cx="126" cy="78" r="5"/><circle class="k-neon k-ring" cx="172" cy="78" r="8"/><path class="k-soft" d="M22 96h22M70 96h22M118 96h22M162 96h20"/><path class="k-ink k-dash" d="M30 62C70 60 90 40 126 36s40-16 46-22"/>'],
];
$cards = array_values($f['cards'] ?? []);
?>
<section class="sec co-kit" id="outcomes">
  <div class="wrap">
    <header class="co-head">
      <p class="sol-eyebrow"><span class="sol-dot" aria-hidden="true"></span>What you keep</p>
      <?= sec_head($f['heading'] ?? '') ?>
<?php if (!empty($f['intro'])): ?>
      <p class="co-head__intro"><?= e($f['intro']) ?></p>
<?php endif ?>
    </header>
    <ol class="co-kit__spread" style="--n: <?= max(1, count($cards)) ?>">
<?php foreach ($cards as $i => $c): $icon = (string) ($c['icon'] ?? ''); [$type, $art] = $docs[$icon] ?? ['Note', '']; ?>
      <li class="co-doc" style="--i: <?= $i ?>">
        <p class="co-doc__type"><span><?= sprintf('%02d', $i + 1) ?></span><?= e($type) ?></p>
<?php if ($art !== ''): ?>
        <svg class="co-doc__art" viewBox="0 0 200 120" aria-hidden="true" focusable="false"><?= $art ?></svg>
<?php else: ?>
        <?= icon($icon, 'icon co-doc__icon') ?>
<?php endif ?>
        <h3 class="co-doc__t"><?= e($c['title'] ?? '') ?></h3>
        <p class="co-doc__text"><?= e($c['text'] ?? '') ?></p>
      </li>
<?php endforeach ?>
    </ol>
  </div>
</section>
