<section class="sec sec--rich_text rich">
<?php if (in_array($page['slug'] ?? '', ['privacy', 'terms'], true)): // long legal pages: one screen-tall contour canvas, sticky ?>
  <div class="privacy-terrain" aria-hidden="true"><canvas class="topo-bg"></canvas></div>
<?php endif ?>
  <div class="wrap">
    <?= sec_head($f['heading'] ?? '') ?>
    <div class="prose rich__body" data-reveal="fade"><?= rich($f['body'] ?? '') ?></div>
  </div>
</section>
