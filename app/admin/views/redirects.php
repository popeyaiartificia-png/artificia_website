<?php /** @var array $rows, $errors, $draft, $orphan */
$total = count($rows);
// One table row: its own form (Save), a Delete form, and inputs tied to the Save form by the form attribute.
$row = function (string $key, array $v, ?string $error) {
    $new = $key === 'new';
    $fid = 'rf-' . $key;
    $i = fn(string $name, string $label, string $attrs = '') => '<label class="sr" for="' . $name . '-' . $key . '">' . e($label) . '</label>'
        . '<input id="' . $name . '-' . $key . '" name="' . $name . '" type="text" form="' . $fid . '" value="' . e($v[$name]) . '" autocomplete="off" required' . ($error ? ' aria-invalid="true" aria-describedby="re-' . $key . '"' : '') . ' ' . $attrs . '>';
    ob_start(); ?>
    <tr<?= $error ? ' class="has-error"' : '' ?>>
      <td data-label="From"><?= $i('from_path', 'Old address', 'placeholder="/old-page"') ?></td>
      <td data-label="To"><?= $i('to_path', 'New address', 'placeholder="/new-page"') ?></td>
      <td data-label="Type"><label class="sr" for="match_type-<?= $key ?>">Match type</label>
        <select id="match_type-<?= $key ?>" name="match_type" form="<?= $fid ?>">
          <option value="exact"<?= $v['match_type'] === 'exact' ? ' selected' : '' ?>>Exact</option>
          <option value="prefix"<?= $v['match_type'] === 'prefix' ? ' selected' : '' ?>>Prefix</option>
        </select></td>
      <td data-label="Code"><label class="sr" for="code-<?= $key ?>">Redirect code</label>
        <select id="code-<?= $key ?>" name="code" form="<?= $fid ?>">
          <option value="301"<?= (int) $v['code'] === 301 ? ' selected' : '' ?>>301</option>
          <option value="302"<?= (int) $v['code'] === 302 ? ' selected' : '' ?>>302</option>
        </select></td>
      <td><div class="actions">
        <form id="<?= $fid ?>" method="post" action="/admin/redirects"><?= csrf_field() ?><input type="hidden" name="action" value="save"><?= $new ? '' : '<input type="hidden" name="id" value="' . (int) $key . '">' ?></form>
        <button class="btn btn--sm<?= $new ? ' btn--primary' : '' ?>" type="submit" form="<?= $fid ?>" data-busy="Saving&hellip;"><?= $new ? 'Add redirect' : 'Save' ?></button>
<?php if (!$new): ?>
        <form method="post" action="/admin/redirects"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $key ?>">
          <button class="btn btn--sm btn--danger" type="submit" data-confirm="Click again to delete" data-busy="Deleting&hellip;">Delete</button></form>
<?php endif ?>
      </div></td>
    </tr>
<?php if ($error): ?>
    <tr class="row-err"><td colspan="5"><p class="err" id="re-<?= $key ?>" role="alert"><?= e($error) ?></p></td></tr>
<?php endif;
    return ob_get_clean();
};
$blank = ['from_path' => '', 'to_path' => '', 'match_type' => 'exact', 'code' => '301'];
?>
<?= admin_page_head('SEO', 'Redirects', (string) $total, 'Send an old address to a new one. Exact matches one address; Prefix also catches everything below it. Use 301 for a permanent move, 302 for a temporary one.') ?>
<?php foreach ($orphan as $msg): ?><p class="notice notice--error" role="alert"><?= e($msg) ?></p><?php endforeach ?>

<div class="table-wrap">
<table class="table table--stack">
  <thead><tr><th scope="col">From</th><th scope="col">To</th><th scope="col">Type</th><th scope="col">Code</th><th scope="col"><span class="sr">Actions</span></th></tr></thead>
  <tbody>
<?= $row('new', ($draft['new'] ?? []) + $blank, $errors['new'] ?? null) ?>
<?php foreach ($rows as $r): $k = (string) $r['id']; ?>
<?= $row($k, ($draft[$k] ?? []) + $r, $errors[$k] ?? null) ?>
<?php endforeach ?>
  </tbody>
</table>
</div>
<?php if (!$rows): ?>
<div class="empty"><p>No redirects yet. Add one in the top row: the old address a visitor might still use, and the page it should land on.</p></div>
<?php endif ?>
