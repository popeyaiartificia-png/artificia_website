<?php /** @var array $f, $page, $settings; @var int $s */ ?>
<section class="sec sec--lead_form">
<?php if (!empty($f["heading"])): ?><h2><?= heading_html($f["heading"]) ?></h2><?php endif; ?>
<?php if (!empty($f["intro"])): ?><p><?= e($f["intro"]) ?></p><?php endif; ?>
<ul>
<?php foreach ($f['side_points'] ?? [] as $p): ?>
<li><?= e($p['text'] ?? '') ?></li>
<?php endforeach; ?>
</ul>
<?php // The form itself arrives with the lead handler (Task 9). ?>
<p><?= e($f['button_label'] ?? 'Send') ?></p>
</section>
