<?php /** @var list<array{page: array, url: string, graph: array, types: list<string>}> $rows */ ?>
<?= admin_page_head('SEO tools', 'Schema', (string) count($rows), 'The structured data (JSON-LD) every live page serves, exactly as Google reads it. Change it on a page\'s SEO & meta tab: switch the automatic schema off, or add your own.') ?>

<p class="hint">Google's Rich Results test shows only the types Google turns into rich results (FAQ, Course, Article, Breadcrumb, Event, Video and a few more). Check a Service or Organization in the Schema.org validator. While the site is behind a password, paste the JSON into the test's Code tab.</p>

<div class="table-wrap">
<table class="table table--stack">
  <thead><tr><th scope="col">Page</th><th scope="col">Types served</th><th scope="col">JSON-LD</th><th scope="col"><span class="sr">Actions</span></th></tr></thead>
  <tbody>
<?php foreach ($rows as $r): $p = $r['page']; ?>
    <tr>
      <td data-label="Page"><strong><?= e(heading_plain($p['title']) ?: 'Untitled') ?></strong><br><code><?= e(page_path($p)) ?></code>
        <?= !$p['schema_auto'] ? '<span class="pill pill--warn">automatic off</span>' : '' ?></td>
      <td data-label="Types"><?php if ($r['types']): foreach ($r['types'] as $t): ?><span class="pill pill--published"><?= e($t) ?></span> <?php endforeach; else: ?><span class="muted">None</span><?php endif ?></td>
      <td data-label="JSON-LD"><?php if ($r['graph']): ?><details><summary>Show</summary><pre class="mono"><?= e(json_encode(['@context' => 'https://schema.org', '@graph' => $r['graph']], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ?></pre></details><?php endif ?></td>
      <td><div class="actions">
        <a class="btn btn--ghost btn--sm" href="<?= e(admin_edit_url($p) . '?tab=seo') ?>">Edit</a>
        <a class="btn btn--ghost btn--sm" href="https://search.google.com/test/rich-results?url=<?= e(rawurlencode($r['url'])) ?>" target="_blank" rel="noopener" data-no-progress>Rich Results<?= icon('arrow-square-out') ?></a>
        <a class="btn btn--ghost btn--sm" href="https://validator.schema.org/#url=<?= e(rawurlencode($r['url'])) ?>" target="_blank" rel="noopener" data-no-progress>Validator<?= icon('arrow-square-out') ?></a>
      </div></td>
    </tr>
<?php endforeach ?>
  </tbody>
</table>
</div>
<?php if (!$rows): ?><div class="empty"><p>No published pages yet.</p></div><?php endif ?>
