<?php /** @var array $f, $page, $settings; @var int $s */ ?>
<section class="sec sec--cta">
<?php if (!empty($f["heading"])): ?><h2><?= heading_html($f["heading"]) ?></h2><?php endif; ?>
<?php if (!empty($f['text'])): ?><p><?= e($f['text']) ?></p><?php endif; ?>
<?php if (!empty($f['button_label'])): ?><p><a href="<?= link_href($f['button_link'] ?? '') ?>"><?= e($f['button_label']) ?></a></p><?php endif; ?>
</section>
