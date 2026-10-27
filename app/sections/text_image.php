<?php /** @var array $f, $page, $settings; @var int $s */ ?>
<section class="sec sec--text_image">
<?php if (!empty($f["heading"])): ?><h2><?= heading_html($f["heading"]) ?></h2><?php endif; ?>
<?= rich($f['body'] ?? '') ?>
<?php if (!empty($f['cta_label'])): ?><p><a href="<?= link_href($f['cta_link'] ?? '') ?>"><?= e($f['cta_label']) ?></a></p><?php endif; ?>
</section>
