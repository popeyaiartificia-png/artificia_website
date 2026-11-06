<?php
/** The free pilot as a pass for the team: the offer on the ticket, its facts on the stub (cta `details`), a perforation
 *  between them. It settles straight as it scrolls in. cta fields: heading, text, button_label, button_link, details. */
$details = array_values(array_filter($f['details'] ?? [], fn($d) => trim((string) ($d['value'] ?? '')) !== ''));
?>
<section class="sec co-pass" id="pilot">
  <div class="wrap">
    <div class="co-pass__ticket">
      <div class="co-pass__main">
        <p class="co-pass__kicker"><span class="sol-dot" aria-hidden="true"></span>Start here</p>
        <?= sec_head($f['heading'] ?? '') ?>
<?php if (!empty($f['text'])): ?>
        <p class="co-pass__text"><?= e($f['text']) ?></p>
<?php endif ?>
<?php if (($f['button_label'] ?? '') !== '' && ($f['button_link'] ?? '') !== ''): ?>
        <a class="btn co-pass__btn" href="<?= link_href($f['button_link']) ?>"><?= e($f['button_label']) ?><?= icon('arrow-up-right') ?></a>
<?php endif ?>
      </div>
<?php if ($details): ?>
      <div class="co-pass__stub">
        <dl class="co-pass__facts">
<?php foreach ($details as $d): ?>
          <div><dt><?= e($d['label'] ?? '') ?></dt><dd><?= e($d['value']) ?></dd></div>
<?php endforeach ?>
        </dl>
        <span class="co-pass__code" aria-hidden="true"></span>
      </div>
<?php endif ?>
    </div>
  </div>
</section>
