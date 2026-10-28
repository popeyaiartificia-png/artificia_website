<?php /** @var array $rows */ ?>
<?= admin_page_head('Content', 'Templates', (string) count($rows), 'Starting points for new pages. A page made from a template can\'t be published until its marked page-specific fields differ from the template.', '<a class="btn btn--primary" href="/admin/pages/new">New page</a>') ?>

<?php if (!$rows): ?>
<div class="empty"><p>No templates yet. Open a finished page, go to its Settings tab and use Save as template.</p></div>
<?php else: ?>
<div class="table-wrap">
<table class="table table--stack">
  <thead><tr><th scope="col">Name</th><th scope="col" class="num">Sections</th><th scope="col" class="num">Page-specific fields</th><th scope="col" class="num">Pages using it</th><th scope="col">Created</th><th scope="col"><span class="sr">Actions</span></th></tr></thead>
  <tbody>
<?php foreach ($rows as $t): ?>
    <tr>
      <td data-label="Name"><?= e($t['name']) ?></td>
      <td data-label="Sections" class="num"><?= count(json_decode($t['sections'], true) ?: []) ?></td>
      <td data-label="Page-specific fields" class="num"><?= count(json_decode($t['unique_fields'], true) ?: []) ?></td>
      <td data-label="Pages using it" class="num"><?= (int) $t['used'] ?></td>
      <td data-label="Created"><?= e(admin_date($t['created_at'])) ?></td>
      <td><div class="actions"><a class="btn btn--sm" href="/admin/pages/new?template=<?= (int) $t['id'] ?>">New page from it</a></div></td>
    </tr>
<?php endforeach ?>
  </tbody>
</table>
</div>
<?php endif ?>
