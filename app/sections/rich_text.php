<?php /** @var array $f, $page, $settings; @var int $s */ ?>
<section class="sec sec--rich_text">
<?php if (!empty($f["heading"])): ?><h2><?= heading_html($f["heading"]) ?></h2><?php endif; ?>
<?= rich($f['body'] ?? '') ?>
</section>
