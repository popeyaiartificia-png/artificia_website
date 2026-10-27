<?php /** @var array $f, $page, $settings; @var int $s */ ?>
<section class="sec sec--blog_list">
<h2><?= heading_html($f['heading'] ?? 'Latest from the blog') ?></h2>
<ul>
<?php foreach (posts_recent(db(), (int) ($f['count'] ?? 3)) as $p): ?>
<li><a href="/blog/<?= e(rawurlencode($p['slug'])) ?>"><?= e($p['title']) ?></a> <?= e($p['excerpt'] ?? '') ?></li>
<?php endforeach; ?>
</ul>
</section>
