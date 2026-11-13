<?php
/** About: the principles as tall ruled columns, like the notes block on a drawing sheet. Each card's icon picks a small
 *  drawn diagram (site.js draws the neon .ln lines as they arrive); an icon without one falls back to the plain icon. */
$art = [
    // the bottleneck in a flow, found
    'magnifying-glass' => '<path class="ab-g" d="M14 50H146"/><circle class="ab-g ab-f" cx="20" cy="50" r="5"/><circle class="ab-g ab-f" cx="58" cy="50" r="5"/><circle class="ab-g ab-f" cx="140" cy="50" r="5"/><circle class="ab-dot" cx="100" cy="50" r="6"/><circle class="ln" pathLength="1" cx="100" cy="50" r="18"/><path class="ln" pathLength="1" d="M113 63l15 15"/>',
    // tested, then kept running
    'gear-six' => '<path class="ab-g" d="M80 18A32 32 0 1 1 52.3 34M46 37l6.3-3 .6 7"/><path class="ln" pathLength="1" d="M66 51l9.5 9.5L95 40"/>',
    // inside your own accounts
    'lock-key' => '<rect class="ld" x="22" y="18" width="116" height="64"/><path class="ab-g" d="M46 62L70 40l26 18"/><circle class="ab-g ab-f" cx="46" cy="62" r="4.5"/><circle class="ab-g ab-f" cx="70" cy="40" r="4.5"/><circle class="ab-g ab-f" cx="96" cy="58" r="4.5"/><rect class="ln ab-mask" pathLength="1" x="130" y="47" width="16" height="13" rx="1.5"/><path class="ln" pathLength="1" d="M133.5 47v-4a4.5 4.5 0 0 1 9 0v4"/>',
    // one system, the whole team trained on it
    'users-three' => '<rect class="ab-g" x="68" y="10" width="24" height="20"/><path class="ab-g" d="M80 30v12M36 42h88M36 42v12M80 42v12M124 42v12"/><circle class="ln" pathLength="1" cx="36" cy="62" r="5"/><circle class="ln" pathLength="1" cx="80" cy="62" r="5"/><circle class="ln" pathLength="1" cx="124" cy="62" r="5"/><path class="ln" pathLength="1" d="M28 82a8 8 0 0 1 16 0M72 82a8 8 0 0 1 16 0M116 82a8 8 0 0 1 16 0"/>',
    // the key is yours
    'handshake' => '<circle class="ln" pathLength="1" cx="52" cy="50" r="15"/><circle class="ab-g" cx="52" cy="50" r="5"/><path class="ln" pathLength="1" d="M67 50H128M112 50v12M124 50v8"/>',
];
?>
<section class="sec ab-notes">
  <div class="wrap">
    <header class="ab-head">
      <p class="sol-eyebrow"><span class="sol-dot" aria-hidden="true"></span>How we work</p>
      <?= sec_head($f['heading'] ?? '') ?>
<?php if (!empty($f['intro'])): ?>
      <p class="ab-head__intro"><?= e($f['intro']) ?></p>
<?php endif ?>
    </header>
    <ol class="ab-notes__grid" data-reveal="list" style="--cols: <?= max(1, min(5, count($f['cards'] ?? []))) ?>">
<?php foreach (array_values($f['cards'] ?? []) as $i => $c): $icon = (string) ($c['icon'] ?? ''); $cardId = 'ab-note-' . ($s ?? 0) . '-' . $i; ?>
      <li class="ab-note">
        <span class="ab-note__n" aria-hidden="true"><?= sprintf('%02d', $i + 1) ?></span>
<?php if (isset($art[$icon])): ?>
        <svg class="draw ab-note__art" viewBox="0 0 160 100" aria-hidden="true" focusable="false"><?= $art[$icon] ?></svg>
<?php else: ?>
        <?= icon($icon, 'icon ab-note__icon') ?>
<?php endif ?>
        <h3 class="ab-note__t" id="<?= e($cardId) ?>-title"><?= e($c['title'] ?? '') ?></h3>
        <p class="ab-note__text" id="<?= e($cardId) ?>-text"><?= e($c['text'] ?? '') ?></p>
        <button class="ab-note__toggle" type="button" aria-labelledby="<?= e($cardId) ?>-title" aria-describedby="<?= e($cardId) ?>-text" aria-pressed="false"></button>
      </li>
<?php endforeach ?>
    </ol>
  </div>
</section>
