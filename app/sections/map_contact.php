<?php
$addr = trim($settings['org_address'] ?? '');
$phone = trim($settings['org_phone'] ?? '');
$email = trim($settings['org_email'] ?? '');
$wa = whatsapp_url($settings);
$gbp = trim($settings['gbp_url'] ?? '');
$showMap = ($f['show_map'] ?? 'yes') !== 'no' && $addr !== '';
?>
<section class="sec sec--map_contact contact">
  <div class="wrap">
    <?= sec_head(($f['heading'] ?? '') !== '' ? $f['heading'] : 'Contact') ?>
    <div class="contact__grid">
      <dl class="contact__list">
<?php if ($addr !== ''): ?>
        <div><dt>Address</dt><dd><?= nl2br(e($addr)) ?><?php if ($gbp !== ''): ?><br><a href="<?= link_href($gbp) ?>" rel="noopener">Open in Google Maps</a><?php endif ?></dd></div>
<?php endif ?>
<?php if ($phone !== ''): ?>
        <div><dt>Phone</dt><dd><a href="tel:<?= e(preg_replace('/[^\d+]/', '', $phone)) ?>"><?= e($phone) ?></a></dd></div>
<?php endif ?>
        <div><dt>WhatsApp</dt><dd><a href="<?= e($wa) ?>">Message us</a></dd></div>
<?php if ($email !== ''): ?>
        <div><dt>Email</dt><dd><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></dd></div>
<?php endif ?>
      </dl>
<?php if ($showMap): ?>
      <div class="map">
        <button class="map__facade" type="button" data-src="https://www.google.com/maps?q=<?= e(rawurlencode($addr)) ?>&amp;output=embed">
          <?= drawing('map') ?>
          <span class="btn"><?= icon('map-pin') ?>Show the map</span>
        </button>
      </div>
<?php endif ?>
    </div>
  </div>
</section>
