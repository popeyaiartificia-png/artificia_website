<?php /** @var array $user, $stats, $seo (seo_checks rows), $noindex (page ids); @var string $filter (a check key or '') */
// A tile links to its section once that section's route exists (admin_has_route); until then it is a plain tile.
$tiles = [
    ['/admin/pages', 'file-text', $stats['pages'], '', 'Pages', $stats['page_drafts'] . ' ' . ($stats['page_drafts'] === 1 ? 'draft' : 'drafts')],
    ['/admin/posts', 'newspaper', $stats['posts'], '', 'Posts', $stats['post_drafts'] . ' ' . ($stats['post_drafts'] === 1 ? 'draft' : 'drafts')],
    ['/admin/leads', 'tray', $stats['leads_week'], 'this week', 'Leads', $stats['leads'] . ' in total'],
    ['/admin/redirects', 'arrows-left-right', $stats['redirects'], '', 'Redirects', 'Old addresses sent to new ones'],
    ['/admin/media', 'image', $stats['media'], '', 'Media', 'Images in the library'],
];
$passing = count(array_filter($seo, fn($r) => !$r['issues']));
$failing = count($seo) - $passing;
$counts = [];
foreach ($seo as $r) {
    foreach (array_unique(array_column($r['issues'], 'check')) as $c) {
        $counts[$c] = ($counts[$c] ?? 0) + 1;
    }
}
$shown = $filter === '' ? $seo : array_filter($seo, fn($r) => in_array($filter, array_column($r['issues'], 'check'), true));
$tiles[] = ['#seo-table', 'check', $passing, 'of ' . count($seo), 'Pages passing all checks', $failing . ' with something to fix'];
?>
<?= admin_page_head('Start', 'Dashboard', null, 'How the site stands today.') ?>

<div class="stats">
<?php foreach ($tiles as [$path, $ic, $num, $unit, $label, $hint]):
    $tag = $path[0] === '#' || admin_has_route($path) ? 'a' : 'div'; ?>
  <<?= $tag ?> class="stat"<?= $tag === 'a' ? ' href="' . e($path) . '"' : '' ?>>
    <?= icon($ic, 'icon stat__icon') ?>
    <span class="stat__num"><?= (int) $num ?><?= $unit !== '' ? ' <span class="stat__unit">' . e($unit) . '</span>' : '' ?></span>
    <span class="stat__label"><?= e($label) ?></span>
    <span class="stat__hint"><?= e($hint) ?></span>
  </<?= $tag ?>>
<?php endforeach ?>
</div>

<div class="cols">
  <section class="card">
    <h2>Quick actions</h2>
    <div class="btn-row">
      <a class="btn" href="/" target="_blank" rel="noopener" data-no-progress>View website</a>
      <a class="btn btn--ghost" href="/_design">Section library</a>
    </div>
  </section>
  <section class="card">
    <h2>SEO health</h2>
<?php if (!$counts): ?>
    <p class="muted">Every page passes every check.</p>
<?php else: ?>
    <ul class="health">
<?php foreach (SEO_CHECK_LABELS as $key => $label): if (empty($counts[$key])) continue; ?>
      <li><a href="/admin?check=<?= e($key) ?>#seo-table"><strong><?= (int) $counts[$key] ?></strong> <?= $counts[$key] === 1 ? 'page' : 'pages' ?>: <?= e($label) ?></a></li>
<?php endforeach ?>
    </ul>
<?php endif ?>
  </section>
</div>

<section id="seo-table" class="seo">
  <h2>SEO overview</h2>
  <p class="muted"><?= count($seo) ?> pages, <?= $failing ?> with something to fix.
<?php if ($filter !== ''): ?>
    Showing the <?= count($shown) ?> with &ldquo;<?= e(SEO_CHECK_LABELS[$filter]) ?>&rdquo;. <a href="/admin#seo-table">Show all</a>
<?php endif ?>
  </p>
  <div class="table-wrap">
    <table class="table table--stack">
      <thead><tr><th>Page</th><th>Status</th><th class="num">Issues</th><th>What to fix</th><th></th></tr></thead>
      <tbody>
<?php foreach ($shown as $r): $p = $r['page']; ?>
        <tr>
          <td data-label="Page"><strong><?= e(heading_plain($p['title']) ?: 'Untitled') ?></strong><br><code><?= e($p['path']) ?></code></td>
          <td data-label="Status"><span class="pill <?= $p['status'] === 'published' ? 'pill--published' : 'pill--draft' ?>"><?= $p['status'] === 'published' ? 'Published' : 'Draft' ?></span><?= in_array($p['id'], $noindex, true) ? ' <span class="pill pill--warn">noindex</span>' : '' ?></td>
          <td data-label="Issues" class="num"><?= count($r['issues']) ?></td>
          <td data-label="What to fix">
<?php if ($r['issues']): ?>
            <ul class="issues"><?php foreach ($r['issues'] as $i): ?><li><?= e($i['message']) ?></li><?php endforeach ?></ul>
<?php else: ?>
            <span class="muted">All checks pass</span>
<?php endif ?>
          </td>
          <td><a class="btn btn--ghost" href="<?= e(admin_edit_url($p)) ?>">Edit</a></td>
        </tr>
<?php endforeach ?>
      </tbody>
    </table>
  </div>
</section>
