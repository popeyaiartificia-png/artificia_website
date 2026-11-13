<?php
/** A compact contact hero. Contact details stay tied to the same settings as the footer. */
$addr = trim($settings['org_address'] ?? '');
$phone = trim($settings['org_phone'] ?? '');
$email = trim($settings['org_email'] ?? '');
$lines = [];
if ($phone !== '') $lines[] = ['Call', $phone, 'Talk to the team', 'tel:' . preg_replace('/[^\d+]/', '', $phone), 'phone'];
if ($email !== '') $lines[] = ['Email', $email, 'For documents and longer briefs', 'mailto:' . $email, 'envelope-simple'];
if ($addr !== '') $lines[] = ['Visit', 'Vadodara office', explode("\n", $addr)[1] ?? $addr, '#visit', 'map-pin'];
?>
<section class="sol-hero contact-hero" aria-labelledby="ct-title">
  <canvas class="topo-bg" aria-hidden="true"></canvas>
  <div class="wrap">
    <div class="sol-hero__top">
      <nav class="sol-crumb" aria-label="Breadcrumb"><ol><li><a href="/">Home</a></li><li aria-current="page"><?= e($page['title'] ?? 'Contact') ?></li></ol></nav>
<?php if (!empty($f['eyebrow'])): ?>
      <p class="sol-hero__eyebrow"><span class="sol-dot" aria-hidden="true"></span><?= e($f['eyebrow']) ?></p>
<?php endif ?>
    </div>
    <div class="contact-hero__grid">
      <div class="contact-hero__copy">
        <h1 id="ct-title" class="contact-hero__title"><?= heading_html($h1) ?></h1>
<?php if (!empty($f['statement'])): ?>
        <p class="contact-hero__statement"><?= e($f['statement']) ?></p>
<?php endif ?>
<?php if (!empty($f['subheading'])): ?>
        <p class="contact-hero__lede"><?= e($f['subheading']) ?></p>
<?php endif ?>
        <div class="contact-hero__actions">
<?php if (!empty($f['primary_cta_label'])): ?>
          <a class="btn btn--lime" href="<?= link_href($f['primary_cta_link'] ?? '#enquire') ?>"><?= e($f['primary_cta_label']) ?><?= icon('arrow-up-right') ?></a>
<?php endif ?>
<?php if (!empty($f['secondary_cta_label'])): ?>
          <a class="sol-text-link" href="<?= link_href($f['secondary_cta_link'] ?? '#visit') ?>"><?= e($f['secondary_cta_label']) ?><?= icon('arrow-right') ?></a>
<?php endif ?>
        </div>
<?php if (!empty($f['stat_value'])): ?>
        <p class="contact-hero__note"><span><?= e(trim($f['stat_value'] . ' ' . ($f['stat_unit'] ?? ''))) ?></span> <?= e($f['stat_label'] ?? '') ?></p>
<?php endif ?>
      </div>
      <div class="contact-direct" role="group" aria-label="Direct contact options">
        <div class="contact-direct__head"><h2>Direct lines</h2><p><i class="contact-direct__live" aria-hidden="true"></i>Vadodara <span data-clock hidden></span></p></div>
        <a class="contact-direct__chat" href="<?= e(whatsapp_url($settings)) ?>" rel="noopener">
          <span class="contact-direct__symbol" aria-hidden="true"><?= icon('whatsapp-logo') ?></span>
          <span class="contact-direct__text"><span class="contact-direct__label">WhatsApp <span>Our usual reply channel</span></span><strong>Chat with us</strong><?php if ($phone !== ''): ?><span class="contact-direct__sub"><?= e($phone) ?></span><?php endif ?></span>
          <span class="contact-direct__thread" aria-hidden="true">
            <span class="contact-direct__bubble">Hi! Can AI take work off my team?<svg class="contact-direct__ticks" viewBox="0 0 16 10"><path d="M1 5.5l3 3 6.5-7M7.5 8.5l6.5-7"/></svg></span>
            <span class="contact-direct__reply"><span class="contact-direct__bubble contact-direct__bubble--in contact-direct__typing"><i></i><i></i><i></i></span><span class="contact-direct__bubble contact-direct__bubble--in contact-direct__answer">Yes! Let's start with a free AI consultation.</span></span>
          </span>
          <span class="contact-direct__arrow" aria-hidden="true"><?= icon('arrow-up-right') ?></span>
        </a>
        <ul class="contact-direct__list">
<?php foreach ($lines as [$label, $value, $sub, $href, $symbol]): ?>
          <li><a class="contact-direct__link" href="<?= e($href) ?>">
            <span class="contact-direct__symbol" aria-hidden="true"><?= icon($symbol) ?></span>
            <span class="contact-direct__text"><span class="contact-direct__label"><?= e($label) ?></span><strong><?= e($value) ?></strong><span class="contact-direct__sub"><?= e($sub) ?></span></span>
            <span class="contact-direct__arrow" aria-hidden="true"><?= icon($href === '#visit' ? 'arrow-right' : 'arrow-up-right') ?></span>
          </a></li>
<?php endforeach ?>
        </ul>
      </div>
    </div>
  </div>
</section>
