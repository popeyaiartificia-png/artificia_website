<section class="sec sec--rich_text rich">
  <div class="wrap">
    <?= sec_head($f['heading'] ?? '') ?>
    <div class="prose rich__body" data-reveal="fade"><?= rich($f['body'] ?? '') ?></div>
  </div>
</section>
