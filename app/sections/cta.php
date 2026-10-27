<section class="sec sec--cta cta">
  <div class="wrap cta__in">
    <div>
      <?= sec_head($f['heading'] ?? '') ?>
<?php if (!empty($f['text'])): ?>
      <p class="lede"><?= e($f['text']) ?></p>
<?php endif ?>
    </div>
<?php if (($f['button_label'] ?? '') !== '' && ($f['button_link'] ?? '') !== ''): ?>
    <div class="btns"><a class="btn btn--lime" href="<?= link_href($f['button_link']) ?>"><?= e($f['button_label']) ?><?= icon('arrow-right') ?></a></div>
<?php endif ?>
  </div>
</section>
