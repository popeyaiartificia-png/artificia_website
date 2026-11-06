<?php
/** Contact hero: About's frame (copy left), with the direct lines as a board on the right. A signal tours the lines;
 *  each is a real link. Numbers and address come from settings, so the board never drifts from the footer. */
$addr = trim($settings['org_address'] ?? '');
$phone = trim($settings['org_phone'] ?? '');
$email = trim($settings['org_email'] ?? '');
$lines = [['WhatsApp', 'Chat with us', $phone !== '' ? $phone . ' · our usual reply channel' : 'Our usual reply channel', whatsapp_url($settings), true]];
if ($phone !== '') $lines[] = ['Call', $phone, 'Talk to the team', 'tel:' . preg_replace('/[^\d+]/', '', $phone), false];
if ($email !== '') $lines[] = ['Email', $email, 'For documents and longer briefs', 'mailto:' . $email, false];
if ($addr !== '') $lines[] = ['Visit', 'Vadodara office', explode("\n", $addr)[1] ?? $addr, '#visit', false];
?>
<section class="sol-hero ab-hero ct-hero" aria-labelledby="ct-title">
  <canvas class="topo-bg" aria-hidden="true"></canvas>
  <div class="wrap">
    <div class="sol-hero__top">
      <nav class="sol-crumb" aria-label="Breadcrumb"><ol><li><a href="/">Home</a></li><li aria-current="page"><?= e($page['title'] ?? 'Contact') ?></li></ol></nav>
<?php if (!empty($f['eyebrow'])): ?>
      <p class="sol-hero__eyebrow"><span class="sol-dot" aria-hidden="true"></span><?= e($f['eyebrow']) ?></p>
<?php endif ?>
    </div>
    <div class="ab-hero__grid">
      <div class="ab-hero__copy">
        <h1 id="ct-title" class="sol-hero__title ab-hero__title"><?= heading_html($h1) ?></h1>
<?php if (!empty($f['subheading'])): ?>
        <p class="sol-hero__lede ab-hero__lede"><?= e($f['subheading']) ?></p>
<?php endif ?>
        <div class="sol-hero__ctas">
<?php if (!empty($f['primary_cta_label'])): ?>
          <a class="btn btn--lime" href="<?= link_href($f['primary_cta_link'] ?? '#enquire') ?>"><?= e($f['primary_cta_label']) ?><?= icon('arrow-up-right') ?></a>
<?php endif ?>
<?php if (!empty($f['secondary_cta_label'])): ?>
          <a class="sol-text-link" href="<?= link_href($f['secondary_cta_link'] ?? '#visit') ?>"><?= e($f['secondary_cta_label']) ?><?= icon('arrow-right') ?></a>
<?php endif ?>
        </div>
      </div>
      <div class="ct-board">
        <p class="ct-board__bar"><span><i aria-hidden="true"></i>Direct lines</span><span>Vadodara <b data-clock hidden></b></span></p>
        <ul class="ct-lines">
<?php foreach ($lines as $i => [$k, $v, $sub, $href, $typing]): ?>
          <li class="ct-line" style="--i:<?= $i ?>">
            <a href="<?= e($href) ?>"<?= str_starts_with($href, 'http') ? ' rel="noopener"' : '' ?>>
              <span class="ct-line__k"><?= e($k) ?></span>
              <span class="ct-line__v"><?= e($v) ?><?php if ($typing): ?><span class="ct-line__typing" aria-hidden="true"><i></i><i></i><i></i></span><?php endif ?></span>
              <span class="ct-line__s"><?= e($sub) ?></span>
              <span class="ct-line__go" aria-hidden="true"><?= icon($href === '#visit' ? 'arrow-right' : 'arrow-up-right') ?></span>
            </a>
          </li>
<?php endforeach ?>
        </ul>
      </div>
    </div>
  </div>
</section>
