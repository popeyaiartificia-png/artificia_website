<?php $posts = posts_recent(db(), (int) ($f['count'] ?? 0) ?: 3); ?>
<section class="sec sec--blog_list blog">
  <div class="wrap">
    <?= sec_head($f['heading'] ?? '') ?>
<?php if (!$posts): ?>
    <p class="empty">No posts yet. The first one is on its way.</p>
<?php else: ?>
    <ol class="posts" data-reveal="list">
<?php foreach ($posts as $p): ?>
      <li class="post">
        <p class="entry"><time datetime="<?= e(substr($p['published_at'] ?? '', 0, 10)) ?>"><?= e(date('j M Y', strtotime($p['published_at'] ?? 'now'))) ?></time></p>
        <div><h3 class="h3"><a href="/blog/<?= e($p['slug']) ?>"><?= e($p['title']) ?></a></h3><?php if (!empty($p['excerpt'])): ?><p><?= e($p['excerpt']) ?></p><?php endif ?></div>
      </li>
<?php endforeach ?>
    </ol>
<?php endif ?>
  </div>
</section>
