<?php $side = ($f['image_side'] ?? 'right') === 'left' ? 'left' : 'right'; ?>
<section class="sec sec--text_image ti ti--<?= $side ?>">
  <div class="wrap ti__grid">
    <div class="ti__copy">
      <?= sec_head($f['heading'] ?? '') ?>
      <div class="prose" data-reveal="fade"><?= rich($f['body'] ?? '') ?></div>
<?php if (!empty($f['cta_label'])): ?>
      <div class="btns"><a class="btn" href="<?= link_href($f['cta_link'] ?? '') ?>"><?= e($f['cta_label']) ?><?= icon('arrow-right') ?></a></div>
<?php endif ?>
    </div>
<?php if (!empty($f['image'])): ?>
    <figure class="ti__fig frame" data-reveal="fade"><?= image($f['image'], '(min-width: 900px) 640px, 100vw') ?></figure>
<?php endif ?>
  </div>
</section>
