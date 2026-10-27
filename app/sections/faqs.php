<?php /** @var array $f, $page, $settings; @var int $s */ ?>
<section class="sec sec--faqs">
<?php if (!empty($f["heading"])): ?><h2><?= heading_html($f["heading"]) ?></h2><?php endif; ?>
<ul>
<?php foreach ($f['items'] ?? [] as $i): ?>
<li><h3><?= e($i['question'] ?? '') ?></h3> <p><?= e($i['answer'] ?? '') ?></p></li>
<?php endforeach; ?>
</ul>
</section>
