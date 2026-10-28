<?php /** @var array $rows, $v (view: tab form page source from to), $counts (tab => n), $options (page, source); @var int $total, $p, $pages; @var bool $isAdmin */
$query = fn(array $over = []) => http_build_query(array_filter($over + $v, fn($x) => $x !== '' && $x !== null));
$filtered = $v['form'] . $v['page'] . $v['source'] . $v['from'] . $v['to'] !== '';
$export = $isAdmin && $total > 0
    ? '<a class="btn" href="/admin/leads/export' . ($query() !== '' ? '?' . e($query()) : '') . '" download data-no-progress>Export ' . $total . ' to CSV</a>'
    : '';
$select = function (string $name, string $label, string $all, array $opts) use ($v): string {
    $o = '<option value="">' . e($all) . '</option>';
    foreach ($opts as $val => $text) {
        $o .= '<option value="' . e((string) $val) . '"' . ((string) $val === $v[$name] ? ' selected' : '') . '>' . e($text) . '</option>';
    }
    return '<label class="sr" for="lf-' . $name . '">' . e($label) . '</label><select id="lf-' . $name . '" name="' . $name . '">' . $o . '</select>';
};
$pageOpts = [];
foreach ($options['page'] as $url) {
    $pageOpts[$url] = lead_page_path($url) . (parse_url($url, PHP_URL_HOST) !== parse_url((string) config()['base_url'], PHP_URL_HOST) ? ' (' . parse_url($url, PHP_URL_HOST) . ')' : '');
}
?>
<?= admin_page_head('Leads', 'Leads', (string) $total . ($filtered ? ' in this view' : ''),
    'Every enquiry from the site forms, newest first. Spam is kept apart and is never emailed or forwarded on its own.', $export) ?>

<nav class="tabs" aria-label="Lead views">
<?php foreach (LEADS_TABS as $k => $label): $q = $query(['tab' => $k]); ?>
  <a class="tab<?= $v['tab'] === $k ? ' is-active' : '' ?>" href="/admin/leads<?= $q !== '' ? '?' . e($q) : '' ?>"<?= $v['tab'] === $k ? ' aria-current="page"' : '' ?>><?= e($label) ?> <span class="tab__count"><?= $counts[$k] ?></span></a>
<?php endforeach ?>
</nav>

<form class="filters" method="get" action="/admin/leads" role="search" data-no-busy>
<?php if ($v['tab'] !== ''): ?>
  <input type="hidden" name="tab" value="<?= e($v['tab']) ?>">
<?php endif ?>
  <?= $select('form', 'Form', 'All forms', array_combine(LEAD_TYPES, array_map('ucfirst', LEAD_TYPES))) ?>
  <?= $select('page', 'Page', 'All pages', $pageOpts) ?>
  <?= $select('source', 'Source (utm_source)', 'All sources', array_combine($options['source'], $options['source']) ?: []) ?>
  <label class="filters__date" for="lf-from"><span>From</span><input id="lf-from" type="date" name="from" value="<?= e($v['from']) ?>"></label>
  <label class="filters__date" for="lf-to"><span>To</span><input id="lf-to" type="date" name="to" value="<?= e($v['to']) ?>"></label>
  <button class="btn" type="submit">Filter</button>
<?php if ($filtered): ?>
  <a class="btn btn--ghost" href="/admin/leads<?= $v['tab'] !== '' ? '?tab=' . e($v['tab']) : '' ?>">Clear</a>
<?php endif ?>
</form>

<?php if (!$rows): ?>
<div class="empty"><p><?= $filtered
    ? 'No leads match these filters. Widen the dates or clear the filters.'
    : ['' => 'No leads yet. Enquiries from every form on the site land here the moment they are sent.',
       'unforwarded' => 'Nothing waiting: every lead has reached n8n.',
       'spam' => 'No spam caught. Submissions that fill the hidden field or arrive too fast land here.'][$v['tab']] ?></p></div>
<?php else: ?>
<div class="table-wrap">
<table class="table table--stack table--leads">
  <thead><tr><th scope="col">Received</th><th scope="col">Name</th><th scope="col">Phone</th><th scope="col">Form</th><th scope="col">Page</th><th scope="col">Source</th><th scope="col">Forwarded</th></tr></thead>
  <tbody>
<?php foreach ($rows as $r):
    [$pill, $state] = lead_forward_state($r); ?>
    <tr>
      <td data-label="Received"><?= e(admin_date($r['created_at'])) ?></td>
      <td data-label="Name"><a class="cell-title" href="/admin/leads/<?= (int) $r['id'] ?>"><?= e($r['name'] !== '' ? $r['name'] : 'No name') ?></a><?php if ($r['email'] !== ''): ?><span class="cell-sub"><?= e($r['email']) ?></span><?php endif ?></td>
      <td data-label="Phone"><?= e($r['phone']) ?></td>
      <td data-label="Form"><?= e(ucfirst($r['form_type'])) ?></td>
      <td data-label="Page"><?= $r['page_url'] !== '' ? '<code>' . e(lead_page_path($r['page_url'])) . '</code>' : '<span class="muted">Not known</span>' ?></td>
      <td data-label="Source"><?= $r['utm_source'] !== '' ? e($r['utm_source']) . ($r['utm_medium'] !== '' ? '<span class="cell-sub">' . e($r['utm_medium']) . '</span>' : '') : '<span class="muted">Direct</span>' ?></td>
      <td data-label="Forwarded"><span class="pill <?= $pill ?>"<?= $r['forward_error'] !== null ? ' title="' . e($r['forward_error']) . '"' : '' ?>><?= e($state) ?></span></td>
    </tr>
<?php endforeach ?>
  </tbody>
</table>
</div>
<?php if ($pages > 1): ?>
<nav class="pager" aria-label="Result pages">
<?php for ($n = 1; $n <= $pages; $n++): ?>
  <a href="/admin/leads?<?= e($query(['p' => $n])) ?>"<?= $n === $p ? ' aria-current="page"' : '' ?>><?= $n ?></a>
<?php endfor ?>
</nav>
<?php endif ?>
<?php endif ?>
<?php if (!$isAdmin): ?>
<p class="muted">Only an admin can export, delete or re-send leads.</p>
<?php endif ?>
