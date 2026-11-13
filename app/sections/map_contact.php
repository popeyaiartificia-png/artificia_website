<?php
$addr = trim($settings['org_address'] ?? '');
$phone = trim($settings['org_phone'] ?? '');
$email = trim($settings['org_email'] ?? '');
$wa = whatsapp_url($settings);
$mapUrl = trim($settings['org_map_url'] ?? '') ?: trim($settings['gbp_url'] ?? '');
$mapQuery = trim($settings['org_map_query'] ?? '') ?: $addr;
$showMap = ($f['show_map'] ?? 'yes') !== 'no' && $addr !== '';
if (($f['details'] ?? 'all') === 'address') {
    $phone = $email = $wa = '';
}
?>
<section class="sec sec--map_contact contact" id="visit">
  <div class="wrap">
    <?= sec_head(($f['heading'] ?? '') !== '' ? $f['heading'] : 'Contact') ?>
    <div class="contact__grid">
      <dl class="contact__list">
<?php if ($addr !== ''): ?>
        <div><dt>Address</dt><dd><?= nl2br(e($addr)) ?><?php if ($mapUrl !== ''): ?><br><a href="<?= link_href($mapUrl) ?>" rel="noopener">Open in Google Maps</a><?php endif ?></dd></div>
<?php endif ?>
<?php if ($phone !== ''): ?>
        <div><dt>Phone</dt><dd><a href="tel:<?= e(preg_replace('/[^\d+]/', '', $phone)) ?>"><?= e($phone) ?></a></dd></div>
<?php endif ?>
<?php if ($wa !== ''): ?>
        <div><dt>WhatsApp</dt><dd><a href="<?= e($wa) ?>">Message us</a></dd></div>
<?php endif ?>
<?php if ($email !== ''): ?>
        <div><dt>Email</dt><dd><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></dd></div>
<?php endif ?>
      </dl>
<?php if ($showMap): ?>
      <div class="map">
        <iframe src="https://www.google.com/maps?q=<?= e(rawurlencode($mapQuery)) ?>&amp;output=embed" title="Artificia AI office location on Google Maps" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
      </div>
<?php endif ?>
    </div>
  </div>
</section>
