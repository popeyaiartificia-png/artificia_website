<?php /** @var string $kind, $q, $status; @var array $rows, $counts; @var int $total, $p, $pages */
$base = $kind === 'post' ? '/admin/posts' : '/admin/pages';
$noun = $kind === 'post' ? 'post' : 'page';
$filtered = $q !== '' || $status !== '';
$query = fn(array $over) => http_build_query(array_filter(['q' => $q, 'status' => $status] + $over));
?>
<?= admin_page_head('Content', $kind === 'post' ? 'Posts' : 'Pages', (string) $total . ($filtered ? ' in this view' : ''),
    $kind === 'post' ? 'Blog posts, newest edit first. A post lives at /blog/ and its own address.' : 'Every page on the site, newest edit first. Drafts show only here and in Preview.',
    '<a class="btn btn--primary" href="' . $base . '/new">New ' . $noun . '</a>') ?>

<nav class="tabs" aria-label="Pages or posts">
  <a class="tab<?= $kind === 'page' ? ' is-active' : '' ?>" href="/admin/pages"<?= $kind === 'page' ? ' aria-current="page"' : '' ?>>Pages <span class="tab__count"><?= (int) ($counts['page'] ?? 0) ?></span></a>
  <a class="tab<?= $kind === 'post' ? ' is-active' : '' ?>" href="/admin/posts"<?= $kind === 'post' ? ' aria-current="page"' : '' ?>>Posts <span class="tab__count"><?= (int) ($counts['post'] ?? 0) ?></span></a>
</nav>

<form class="filters" method="get" action="<?= $base ?>" role="search" data-no-busy>
  <label class="sr" for="list-q">Search</label>
  <input id="list-q" type="search" name="q" value="<?= e($q) ?>" placeholder="Find by title or address">
  <label class="sr" for="list-status">Status</label>
  <select id="list-status" name="status">
    <option value="">All statuses</option>
    <option value="published"<?= $status === 'published' ? ' selected' : '' ?>>Published</option>
    <option value="draft"<?= $status === 'draft' ? ' selected' : '' ?>>Drafts</option>
  </select>
  <button class="btn" type="submit">Filter</button>
<?php if ($filtered): ?>
  <a class="btn btn--ghost" href="<?= $base ?>">Clear</a>
<?php endif ?>
</form>

<?php if (!$rows): ?>
<div class="empty"><p><?= $filtered
    ? 'Nothing matches that search. Try part of the title or address, or clear the filters.'
    : ($kind === 'post' ? 'No posts yet. Write the first one with New post: give it a title, then add its text.' : 'No pages yet. Add one with New page.') ?></p></div>
<?php else: ?>
<div class="table-wrap">
<table class="table table--stack">
  <thead><tr><th scope="col">Title</th><th scope="col">Status</th><th scope="col">Updated</th><th scope="col"><span class="sr">Actions</span></th></tr></thead>
  <tbody>
<?php foreach ($rows as $r):
    $path = page_path($r);
    $edit = admin_edit_url($r);
    $live = $r['status'] === 'published'; ?>
    <tr>
      <td data-label="Title"><a class="cell-title" href="<?= e($edit) ?>"><?= e(heading_plain($r['title']) ?: 'Untitled') ?></a><span class="cell-sub"><?= e($path) ?></span></td>
      <td data-label="Status"><span class="pill <?= $live ? 'pill--published' : 'pill--draft' ?>"><?= $live ? 'Published' : 'Draft' ?></span><?= $r['noindex'] ? ' <span class="pill pill--warn">noindex</span>' : '' ?></td>
      <td data-label="Updated"><?= e(admin_date($r['updated_at'])) ?><?php if ($r['editor']): ?><span class="cell-sub">by <?= e($r['editor']) ?></span><?php endif ?></td>
      <td><div class="actions">
<?php if ($live): ?>
        <a class="btn btn--ghost btn--sm" href="<?= e($path) ?>" target="_blank" rel="noopener" data-no-progress>View<?= icon('arrow-square-out') ?></a>
<?php endif ?>
        <form method="post" action="/admin/pages/<?= (int) $r['id'] ?>/duplicate"><?= csrf_field() ?><button class="btn btn--ghost btn--sm" type="submit" data-busy="Copying&hellip;">Duplicate</button></form>
        <a class="btn btn--sm" href="<?= e($edit) ?>">Edit</a>
      </div></td>
    </tr>
<?php endforeach ?>
  </tbody>
</table>
</div>
<?php if ($pages > 1): ?>
<nav class="pager" aria-label="Result pages">
<?php for ($n = 1; $n <= $pages; $n++): ?>
  <a href="<?= $base ?>?<?= e($query(['p' => $n])) ?>"<?= $n === $p ? ' aria-current="page"' : '' ?>><?= $n ?></a>
<?php endfor ?>
</nav>
<?php endif ?>
<?php endif ?>
