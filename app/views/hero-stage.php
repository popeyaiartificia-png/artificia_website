<?php
/**
 * The stage hero (layout "stage"): a full-screen robot on a topographic ground. Liquid neon blobs follow the pointer
 * and reveal a second layer over the robot (robot-helmet.* when present, else a lime x-ray of the robot itself).
 * On scroll the robot shrinks into a card while the headline runs past behind it.
 * Sees $f, $s, $page, $settings, $h1, $doors from hero.php.
 */
$dir = VIEWS_DIR . '/../../public/assets/img/hero/';
$reveal = '';
foreach (['webp', 'png', 'jpg'] as $ext) {
    if (is_file($dir . 'robot-helmet.' . $ext)) {
        $reveal = '/assets/img/hero/robot-helmet.' . $ext;
        break;
    }
}
// The marquee runs the headline as two lines: the emphasised words in serif, the rest in sans.
preg_match_all('/\*([^*\n]+)\*/', $h1, $m);
$serifLine = trim(implode(' ', $m[1]));
$sansLine = trim(preg_replace('/\s+/', ' ', preg_replace('/\*[^*\n]+\*/', '', $h1)));
$tag = $s === 0 ? 'h1' : 'h2'; // the page h1 belongs to a hero only when it opens the page
?>
<section class="sec sec--hero hero hero--stage" data-section="hero">
  <div class="stage" data-stage>
    <canvas class="stage__topo" aria-hidden="true"></canvas>
    <div class="stage__marquee" aria-hidden="true">
<?php foreach (array_filter([['serif', $serifLine], ['sans', $sansLine]], fn($r) => $r[1] !== '') as [$kind, $line]): ?>
      <p class="stage__row stage__row--<?= $kind ?>"><?= str_repeat('<span>' . e($line) . '</span>', 4) ?></p>
<?php endforeach ?>
    </div>
    <div class="stage__card">
      <?php // The AI crown (a wireframe dome over the head, a livery band round the neck), drawn by site.js: the light sweep behind the robot, the band's front half over it. ?>
      <canvas class="stage__rig stage__rig--back" aria-hidden="true"></canvas>
      <img class="stage__robot" src="/assets/img/hero/robot.webp" width="1136" height="1408" alt="A hyperrealistic humanoid robot facing the camera" fetchpriority="high" decoding="async">
      <canvas class="stage__gl" aria-hidden="true"<?= $reveal !== '' ? ' data-reveal="' . e($reveal) . '"' : '' ?>></canvas>
      <canvas class="stage__rig stage__rig--front" aria-hidden="true"></canvas>
    </div>
    <?php // The copy sits left of the face, the doors bottom-right, nothing over the robot. ?>
    <div class="stage__ui">
    <?php // The automation loop, laid out and run by site.js on wide screens: the blueprint's everyday frictions come in on the left,
    // the robot reads them, and each leaves as its outcome in a live-run panel on the right that balances the copy. In k pairs
    // with out k. A labelled example, decorative. ?>
    <div class="stage__flows" aria-hidden="true">
      <svg class="stage__wires"></svg>
<?php foreach ([[icon('clock'), 'Manual tasks'], [icon('squares-four'), 'Disconnected tools'], [icon('database'), 'Scattered data']] as [$mark, $label]): ?>
      <span class="chip chip--in"><span class="chip__glow"></span><?= $mark ?><span class="chip__t"><?= e($label) ?></span></span>
<?php endforeach ?>
      <div class="run">
        <p class="run__head"><span class="run__dot"></span>Live run<span class="run__step"><b>0</b> / 3</span></p>
        <p class="run__title">AI automation</p>
        <ol class="run__rows">
<?php foreach ([[icon('file-text'), 'Data entry automated'], [icon('plugs-connected'), 'CRM & systems connected'], [icon('chart-line-up'), 'Reports & dashboards ready']] as [$mark, $label]): ?>
          <li class="run__row" data-state="wait"><?= $mark ?><span class="run__t"><?= e($label) ?></span><span class="run__st"></span></li>
<?php endforeach ?>
        </ol>
        <div class="run__chart"><?= str_repeat('<i></i>', 7) ?></div>
        <p class="run__foot"><span>Example run</span><span class="run__bar"><i></i></span></p>
      </div>
    </div>
    <div class="stage__copy">
<?php if (!empty($f['eyebrow'])): ?>
      <p class="stage__kicker"><?= e($f['eyebrow']) ?></p>
<?php endif ?>
      <<?= $tag ?> class="stage__title"><?= str_replace('<em>', '<br><em>', heading_html($h1)) // the highlighted words start their own line ?></<?= $tag ?>>
<?php if (!empty($f['subheading'])): ?>
      <p class="stage__sub"><?= e($f['subheading']) ?></p>
<?php endif ?>
<?php if (!empty($f['primary_cta_label']) || !empty($f['secondary_cta_label'])): ?>
      <div class="btns">
<?php if (!empty($f['primary_cta_label'])): ?>
        <a class="btn btn--lime" href="<?= link_href($f['primary_cta_link'] ?? '') ?>"><?= e($f['primary_cta_label']) ?><?= icon('arrow-right') ?></a>
<?php endif ?>
<?php if (!empty($f['secondary_cta_label'])): ?>
        <a class="btn btn--line" href="<?= link_href($f['secondary_cta_link'] ?? '') ?>"><?= e($f['secondary_cta_label']) ?></a>
<?php endif ?>
      </div>
<?php endif ?>
    </div>
<?php if ($doors): ?>
    <nav class="stage__doors" aria-label="Where to start">
      <ul>
<?php foreach ($doors as $d): ?>
        <li><a href="<?= link_href($d['link'] ?? '') ?>"<?= !empty($d['note']) ? ' title="' . e($d['note']) . '"' : '' ?>><span class="stage__door"><?= e($d['label'] ?? '') ?></span><?= icon('arrow-up-right') ?></a></li>
<?php endforeach ?>
      </ul>
    </nav>
<?php endif ?>
    <p class="stage__hint" aria-hidden="true"><span class="stage__dot"></span><span class="stage__hint-fine">Move to reveal</span><span class="stage__hint-touch">Touch to reveal</span></p>
    </div>
  </div>
</section>
