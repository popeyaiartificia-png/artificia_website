<?php /** @var array $f, $page, $settings; @var int $s */ ?>
<section class="sec sec--map_contact">
<h2><?= heading_html($f['heading'] ?? 'Contact us') ?></h2>
<ul>
<li><?= e($settings['org_address'] ?? '') ?></li>
<li><?= e($settings['org_phone'] ?? '') ?></li>
<li><?= e($settings['org_email'] ?? '') ?></li>
</ul>
</section>
