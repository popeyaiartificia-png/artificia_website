<?php /** @var array $user, $stats */
// A tile links to its section once that section's route exists (admin_has_route); until then it is a plain tile.
$tiles = [
    ['/admin/pages', 'file-text', $stats['pages'], '', 'Pages', $stats['page_drafts'] . ' ' . ($stats['page_drafts'] === 1 ? 'draft' : 'drafts')],
    ['/admin/posts', 'newspaper', $stats['posts'], '', 'Posts', $stats['post_drafts'] . ' ' . ($stats['post_drafts'] === 1 ? 'draft' : 'drafts')],
    ['/admin/leads', 'tray', $stats['leads_week'], 'this week', 'Leads', $stats['leads'] . ' in total'],
    ['/admin/redirects', 'arrows-left-right', $stats['redirects'], '', 'Redirects', 'Old addresses sent to new ones'],
    ['/admin/media', 'image', $stats['media'], '', 'Media', 'Images in the library'],
];
?>
<?= admin_page_head('Start', 'Dashboard', null, 'How the site stands today.') ?>

<div class="stats">
<?php foreach ($tiles as [$path, $ic, $num, $unit, $label, $hint]):
    $tag = admin_has_route($path) ? 'a' : 'div'; ?>
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
    <h2>SEO overview</h2>
    <div class="empty">
      <p>Missing titles, long descriptions and pages set to noindex show up here once the page editor is built.</p>
    </div>
  </section>
</div>
