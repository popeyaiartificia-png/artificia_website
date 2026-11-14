<?php /** @var array $pages, $filters, $errors (id => msg), $draft (id => posted row); @var int $total; @var string $show, $q */
$qs = $_SERVER['QUERY_STRING'] ?? '';
?>
<?= admin_page_head('SEO tools', 'Meta manager', (string) $total, 'Every page and post in one table: focus keyword, meta title, H1, description and the noindex switch. Edit any rows, then Save once. Nothing saves until you do.') ?>

<form class="filters" method="get" action="/admin/seo" role="search">
  <label class="sr" for="q">Find a page</label>
  <input id="q" name="q" type="search" value="<?= e($q) ?>" placeholder="Find a page by name or address">
  <label class="sr" for="show">Show</label>
  <select id="show" name="show">
    <option value="">All pages</option>
<?php foreach ($filters as $key => [$label]): ?>
    <option value="<?= e($key) ?>"<?= $show === $key ? ' selected' : '' ?>><?= e($label) ?></option>
<?php endforeach ?>
  </select>
  <button class="btn btn--sm" type="submit">Show</button>
<?php if ($show !== '' || $q !== ''): ?><a class="btn btn--ghost btn--sm" href="/admin/seo">Clear</a><?php endif ?>
</form>

<?php if (!$pages): ?>
<div class="empty"><p>No page matches. <a href="/admin/seo">Show all pages</a>.</p></div>
<?php else: ?>
<form id="meta-form" method="post" action="/admin/seo<?= $qs !== '' ? '?' . e($qs) : '' ?>" data-dirty-guard>
<?= csrf_field() ?>
<div class="table-wrap">
<table class="table table--stack meta">
  <thead><tr><th scope="col">Page</th><th scope="col">Keyword &amp; H1</th><th scope="col">Google result</th><th scope="col">Noindex</th></tr></thead>
  <tbody>
<?php foreach ($pages as $p):
    $id = (int) $p['id'];
    $v = array_merge($p, array_map(fn($x) => is_string($x) ? $x : '', $draft[$id] ?? []));
    $err = $errors[$id] ?? null;
    $n = fn(string $f) => 'rows[' . $id . '][' . $f . ']';
    $i = fn(string $f) => 'm' . $id . '-' . $f; ?>
    <tr<?= $err ? ' class="has-error"' : '' ?>>
      <td data-label="Page"><a href="<?= e(admin_edit_url($p) . '?tab=seo') ?>"><strong><?= e(heading_plain($p['title']) ?: 'Untitled') ?></strong></a><br><code><?= e(page_path($p)) ?></code>
        <span class="pill <?= $p['status'] === 'published' ? 'pill--published' : 'pill--draft' ?>"><?= $p['status'] === 'published' ? 'Published' : 'Draft' ?></span></td>
      <td data-label="Keyword &amp; H1"><label class="meta__label" for="<?= $i('focus_keyword') ?>">Focus keyword</label>
        <input id="<?= $i('focus_keyword') ?>" name="<?= $n('focus_keyword') ?>" type="text" maxlength="100" value="<?= e($v['focus_keyword']) ?>" autocomplete="off">
        <label class="meta__label" for="<?= $i('h1') ?>">H1</label>
        <input id="<?= $i('h1') ?>" name="<?= $n('h1') ?>" type="text" maxlength="255" value="<?= e($v['h1']) ?>" placeholder="<?= e($p['title']) ?>"></td>
      <td data-label="Google result"><label class="meta__label" for="<?= $i('meta_title') ?>">Meta title</label>
        <input id="<?= $i('meta_title') ?>" name="<?= $n('meta_title') ?>" type="text" maxlength="255" data-count="30-60" value="<?= e($v['meta_title']) ?>" placeholder="<?= e(heading_plain($p['title'])) ?>">
        <label class="meta__label" for="<?= $i('meta_description') ?>">Meta description</label>
        <textarea id="<?= $i('meta_description') ?>" name="<?= $n('meta_description') ?>" rows="3" maxlength="500" data-count="70-160"><?= e($v['meta_description']) ?></textarea></td>
      <td data-label="Noindex"><label class="check"><input type="checkbox" name="<?= $n('noindex') ?>" value="1"<?= !empty($v['noindex']) ? ' checked' : '' ?>><span class="sr">Hide <?= e(page_path($p)) ?> from Google</span></label></td>
    </tr>
<?php if ($err): ?>
    <tr class="row-err"><td colspan="4"><p class="err" role="alert"><?= e($err) ?></p></td></tr>
<?php endif ?>
<?php endforeach ?>
  </tbody>
</table>
</div>
<div class="btn-row">
  <button class="btn btn--primary" type="submit" data-busy="Saving&hellip;">Save changes</button>
  <p class="hint" data-dirty-note hidden>Unsaved changes.</p>
</div>
</form>
<?php endif ?>
