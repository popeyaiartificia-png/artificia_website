<?php
/** Contact's direct lines as a board (from contact-hero.php), shown in the centre of the shared journey hero.
 *  Numbers and address come from settings, so the board never drifts from the footer. */
$addr = trim($settings['org_address'] ?? '');
$phone = trim($settings['org_phone'] ?? '');
$email = trim($settings['org_email'] ?? '');
$lines = [['WhatsApp', 'Chat with us', $phone !== '' ? $phone . ' · our usual reply channel' : 'Our usual reply channel', whatsapp_url($settings), true]];
if ($phone !== '') $lines[] = ['Call', $phone, 'Talk to the team', 'tel:' . preg_replace('/[^\d+]/', '', $phone), false];
if ($email !== '') $lines[] = ['Email', $email, 'For documents and longer briefs', 'mailto:' . $email, false];
if ($addr !== '') $lines[] = ['Visit', 'Vadodara office', explode("\n", $addr)[1] ?? $addr, '#visit', false];
?>
<div class="ct-board">
  <p class="ct-board__bar"><span><i aria-hidden="true"></i>Direct lines</span><span>Vadodara <b data-clock hidden></b></span></p>
  <ul class="ct-lines">
<?php foreach ($lines as $i => [$k, $v, $sub, $href, $typing]): ?>
    <li class="ct-line" style="--i:<?= $i ?>">
      <a href="<?= e($href) ?>"<?= contact_link_target($href) ?>>
        <span class="ct-line__k"><?= e($k) ?></span>
        <span class="ct-line__v"><?= e($v) ?><?php if ($typing): ?><span class="ct-line__typing" aria-hidden="true"><i></i><i></i><i></i></span><?php endif ?></span>
        <span class="ct-line__s"><?= e($sub) ?></span>
        <span class="ct-line__go" aria-hidden="true"><?= icon($href === '#visit' ? 'arrow-right' : 'arrow-up-right') ?></span>
      </a>
    </li>
<?php endforeach ?>
  </ul>
</div>
