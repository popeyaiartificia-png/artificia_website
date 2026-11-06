<?php
/** About hero: one card, two sides. Side A is a system we build for a client; it turns over and the same system is
 *  side B, a lesson for a team. Example only (no client, no numbers). The Vadodara tag keeps the page's survey sheet. */
$ab_steps = [['New enquiry comes in', 'form'], ['AI reads and sorts it', 'spark'], ['CRM updated', 'db'], ['Reply sent on WhatsApp', 'chat']];
$ab_icon = [
    'form' => '<rect x="4" y="3" width="16" height="18"/><path d="M8 8h8M8 12h8M8 16h5"/>',
    'spark' => '<path d="M12 3v5M12 16v5M3 12h5M16 12h5M6 6l3 3M15 15l3 3M18 6l-3 3M9 15l-3 3"/>',
    'db' => '<ellipse cx="12" cy="6" rx="7" ry="3"/><path d="M5 6v12c0 1.7 3.1 3 7 3s7-1.3 7-3V6M5 12c0 1.7 3.1 3 7 3s7-1.3 7-3"/>',
    'chat' => '<path d="M4 5h16v11H9l-5 4z"/><path d="M8 10h8"/>',
];
?>
<section class="sol-hero ab-hero" aria-labelledby="ab-title">
  <canvas class="topo-bg" aria-hidden="true"></canvas>
  <div class="wrap">
    <div class="sol-hero__top">
      <nav class="sol-crumb" aria-label="Breadcrumb"><ol><li><a href="/">Home</a></li><li aria-current="page"><?= e($page['title'] ?? 'About') ?></li></ol></nav>
<?php if (!empty($f['eyebrow'])): ?>
      <p class="sol-hero__eyebrow"><span class="sol-dot" aria-hidden="true"></span><?= e($f['eyebrow']) ?></p>
<?php endif ?>
    </div>
    <div class="ab-hero__grid">
      <div class="ab-hero__copy">
        <h1 id="ab-title" class="sol-hero__title ab-hero__title"><?= heading_html($h1) ?></h1>
<?php if (!empty($f['subheading'])): ?>
        <p class="sol-hero__lede ab-hero__lede"><?= e($f['subheading']) ?></p>
<?php endif ?>
        <div class="sol-hero__ctas">
<?php if (!empty($f['primary_cta_label'])): ?>
          <a class="btn btn--lime" href="<?= link_href($f['primary_cta_link'] ?? '#enquire') ?>"><?= e($f['primary_cta_label']) ?><?= icon('arrow-up-right') ?></a>
<?php endif ?>
<?php if (!empty($f['secondary_cta_label'])): ?>
          <a class="sol-text-link" href="<?= link_href($f['secondary_cta_link'] ?? '#work') ?>"><?= e($f['secondary_cta_label']) ?><?= icon('arrow-right') ?></a>
<?php endif ?>
        </div>
      </div>
      <figure class="ab-flip" aria-label="Example: the same enquiry system, built for a client and taught to a team.">
        <span class="ab-flip__rings" aria-hidden="true"></span>
        <div class="ab-flip__card" aria-hidden="true">
          <div class="ab-face ab-face--a">
            <p class="ab-face__bar"><span><i></i>Side A · We build</span><b>For a client</b></p>
            <p class="ab-face__title">An enquiry system, <em>running.</em></p>
            <ol class="ab-flow">
<?php foreach ($ab_steps as $i => [$label, $ic]): ?>
              <li style="--i:<?= $i ?>"><svg viewBox="0 0 24 24"><?= $ab_icon[$ic] ?></svg><?= e($label) ?><span>Done</span></li>
<?php endforeach ?>
            </ol>
            <p class="ab-face__foot"><span>AI automation · Business software</span><b><i></i>Live</b></p>
          </div>
          <div class="ab-face ab-face--b">
            <p class="ab-face__bar"><span><i></i>Side B · We teach</span><b>For a team</b></p>
            <p class="ab-face__title">The same system, <em>taught.</em></p>
            <ol class="ab-lesson">
<?php foreach ($ab_steps as $i => [$label]): ?>
              <li style="--i:<?= $i ?>"><span><?= sprintf('%02d', $i + 1) ?></span><?= e($label) ?><svg viewBox="0 0 24 24"><path d="M5 12.5l4.5 4.5L19 7" pathLength="1"/></svg></li>
<?php endforeach ?>
            </ol>
            <div class="ab-face__foot ab-class"><span class="ab-class__seats"><?php for ($i = 0; $i < 6; $i++): ?><i style="--i:<?= $i ?>"></i><?php endfor ?></span><span>Corporate training · AI courses</span></div>
          </div>
        </div>
        <figcaption class="ab-flip__tag"><b>Artificia</b>Vadodara, Gujarat<i>22.31° N · 73.18° E</i></figcaption>
      </figure>
    </div>
  </div>
</section>
