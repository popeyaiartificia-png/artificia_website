<?php
/** Contact and Careers share the frame; the conversation layout is exclusive to Contact. */
$isContact = ($f['variant'] ?? '') !== 'careers';
$addr = trim($settings['org_address'] ?? '');
$phone = trim($settings['org_phone'] ?? '');
$email = trim($settings['org_email'] ?? '');
$lines = [];
if ($phone !== '') $lines[] = ['Call', $phone, 'Talk to the team', 'tel:' . preg_replace('/[^\d+]/', '', $phone), 'phone'];
if ($email !== '') $lines[] = ['Email', $email, 'For documents and longer briefs', 'mailto:' . $email, 'envelope-simple'];
if ($addr !== '') $lines[] = ['Visit', 'Vadodara office', explode("\n", $addr)[1] ?? $addr, '#visit', 'map-pin'];
?>
<section class="sol-hero contact-hero<?= $isContact ? ' contact-hero--connect' : '' ?>" aria-labelledby="ct-title">
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
          <a class="btn btn--lime" href="<?= link_href($f['primary_cta_link'] ?? '#enquire') ?>"<?= contact_link_target($f['primary_cta_link'] ?? '#enquire') ?>><?= e($f['primary_cta_label']) ?><?= icon('arrow-up-right') ?></a>
<?php endif ?>
<?php if (!empty($f['secondary_cta_label'])): ?>
          <a class="sol-text-link" href="<?= link_href($f['secondary_cta_link'] ?? '#visit') ?>"<?= contact_link_target($f['secondary_cta_link'] ?? '#visit') ?>><?= e($f['secondary_cta_label']) ?><?= icon('arrow-right') ?></a>
<?php endif ?>
        </div>
<?php if (!empty($f['stat_value'])): ?>
        <p class="contact-hero__note"><?php if ($isContact): ?><span class="contact-hero__note-icon" aria-hidden="true"><?= icon('arrow-up-right') ?></span><?php endif ?><span><?= e(trim($f['stat_value'] . ' ' . ($f['stat_unit'] ?? ''))) ?></span> <?= e($f['stat_label'] ?? '') ?></p>
<?php endif ?>
      </div>
<?php if (($f['variant'] ?? '') === 'careers'):
    // Careers (13 Nov 2026): the role board. Each card is a hero door (label = role, note = one line) and picks that
    // role in the application form below (site.js); the strip under them shows how to apply: one resume link.
    $roles = $f['doors'] ?? [];
    $roleIcon = function (string $role): string {
        foreach (['engineer' => 'tree-structure', 'market' => 'megaphone', 'seo' => 'magnifying-glass', 'teach' => 'chalkboard-teacher'] as $word => $name) {
            if (stripos($role, $word) !== false) return $name;
        }
        return 'briefcase';
    };
?>
      <div class="contact-direct careers-board" role="group" aria-label="Open roles">
        <div class="contact-direct__head"><h2>Open roles</h2><p><i class="contact-direct__live" aria-hidden="true"></i>Now hiring · Vadodara</p></div>
        <ul class="contact-direct__list careers-board__roles">
<?php foreach ($roles as $n => $role): $label = (string) ($role['label'] ?? ''); ?>
          <li><a class="contact-direct__link" href="<?= link_href($role['link'] ?? '#enquire') ?>"<?= in_array($label, LEAD_OPTIONS['role'], true) ? ' data-pick-role="' . e($label) . '"' : '' ?>>
            <span class="contact-direct__symbol" aria-hidden="true"><?= icon($roleIcon($label)) ?></span>
            <span class="contact-direct__text"><span class="contact-direct__label">Role <?= sprintf('%02d', $n + 1) ?></span><strong><?= e($label) ?></strong><?php if (!empty($role['note'])): ?><span class="contact-direct__sub"><?= e($role['note']) ?></span><?php endif ?></span>
            <span class="contact-direct__arrow" aria-hidden="true"><?= icon('arrow-right') ?></span>
          </a></li>
<?php endforeach ?>
        </ul>
        <a class="careers-board__resume" href="#enquire">
          <span class="contact-direct__symbol" aria-hidden="true"><?= icon('file-text') ?></span>
          <span class="careers-board__field"><span class="contact-direct__label">How to apply <span>Share one link to your resume</span></span><span class="careers-board__url" aria-hidden="true"><span>drive.google.com/your-resume</span></span></span>
          <span class="careers-board__ok" aria-hidden="true"><?= icon('check') ?></span>
          <span class="contact-direct__arrow" aria-hidden="true"><?= icon('arrow-right') ?></span>
        </a>
      </div>
<?php else: ?>
      <div class="contact-card" role="group" aria-labelledby="contact-card-title">
        <div class="contact-card__head"><h2 id="contact-card-title">Direct lines</h2><span class="contact-card__time"><i aria-hidden="true"></i>Vadodara <span data-clock hidden></span></span></div>
        <a class="contact-wa" href="<?= e(whatsapp_url($settings)) ?>" target="_blank" rel="noopener noreferrer">
          <span class="contact-wa__icon" aria-hidden="true"><?= icon('whatsapp-logo') ?></span>
          <span class="contact-wa__label">WhatsApp <span>Our usual reply channel</span></span>
          <strong class="contact-wa__title">Chat with us<span aria-hidden="true">.</span></strong>
          <span class="contact-wa__go" aria-hidden="true"><?= icon('arrow-up-right') ?></span>
          <span class="contact-wa__foot"><?php if ($phone !== ''): ?><span><?= e($phone) ?></span><?php endif ?><span>Let's talk on WhatsApp</span></span>
        </a>
<?php if ($lines): ?>
        <ul class="contact-lines">
<?php foreach ($lines as $n => [$label, $value, $sub, $href, $symbol]): ?>
          <li><a class="contact-line" href="<?= e($href) ?>"<?= contact_link_target($href) ?>>
            <span class="contact-line__no" aria-hidden="true"><?= sprintf('%02d', $n + 1) ?></span>
            <span class="contact-line__main"><span class="contact-line__label"><?= icon($symbol) ?><?= e($label) ?></span><strong><?= e($value) ?></strong></span>
            <span class="contact-line__sub"><?= e($sub) ?></span>
            <span class="contact-line__arrow" aria-hidden="true"><?= icon($href === '#visit' ? 'arrow-right' : 'arrow-up-right') ?></span>
          </a></li>
<?php endforeach ?>
        </ul>
<?php endif ?>
      </div>
<?php endif ?>
    </div>
  </div>
</section>
