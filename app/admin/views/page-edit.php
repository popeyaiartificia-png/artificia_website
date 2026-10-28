<?php /** @var PDO $db; @var array $page (saved row), $form (values shown), $errors (editor_errors), $templates; @var ?string $lead, $by; @var string $tab; @var bool $dirty */
$post = $page['kind'] === 'post';
$noun = $post ? 'post' : 'page';
$live = $page['status'] === 'published';
$path = page_path($page);
$fe = $errors['fields'];
$fixed = !$post && in_array($page['slug'], EDITOR_FIXED_SLUGS, true);
$registry = section_registry();
$host = parse_url((string) config()['base_url'], PHP_URL_HOST) ?: 'artificiaai.com';
$segs = array_filter(explode('/', trim($path, '/')));
$descFallback = trim(strip_tags((string) ($form['excerpt'] ?? '')));
$tabs = ['content' => 'Content', 'seo' => 'SEO &amp; meta', 'settings' => 'Settings'];
$hidden = fn(string $key) => $key === $tab ? '' : ' hidden';
$slugHint = $fixed ? "The router serves this page at $path, so its address can't change."
    : 'Lowercase letters, numbers and hyphens' . ($post ? '; it lives at /blog/your-address.' : ', up to two slashes (services/seo).')
    . ($live ? " This $noun is live: if you change its address, the old one is redirected to the new one (301) when you update." : '');
?>
<a class="back" href="<?= $post ? '/admin/posts' : '/admin/pages' ?>">&larr; All <?= $noun ?>s</a>
<?= admin_page_head($post ? 'Edit post' : 'Edit page', heading_plain($page['title']) ?: 'Untitled') ?>

<?php if ($errors['all']): ?>
<div class="notice notice--error" role="alert">
  <p class="notice__lead"><?= e($lead ?? 'Not saved.') ?></p>
  <ul><?php foreach ($errors['all'] as $m): ?><li><?= e($m) ?></li><?php endforeach ?></ul>
</div>
<?php endif ?>

<div class="editor">
  <div class="editor__main">
    <form id="page-form" method="post" action="<?= e(admin_edit_url($page)) ?>" data-dirty-guard<?= $dirty ? ' data-dirty' : '' ?> novalidate>
      <?= csrf_field() ?>
      <?php // Enter in a field submits through this first button: it saves and keeps the status (never publishes). ?>
      <button type="submit" class="sr" tabindex="-1" aria-hidden="true">Save</button>
      <input type="hidden" name="tab" value="<?= e($tab) ?>" data-dirty-ignore>
      <?= admin_field('title', 'Title', (string) $form['title'], $fe['title'] ?? null, 'text', '', 'maxlength="255" autocomplete="off"') ?>
      <p class="loc"><span class="loc__label">Address</span><code class="loc__url"><?= e($path) ?></code>
<?php if ($live): ?>
        <a href="<?= e($path) ?>" target="_blank" rel="noopener" data-no-progress>Open the live <?= $noun ?><?= icon('arrow-square-out') ?></a>
<?php else: ?>
        <span class="muted">Not live yet. Preview shows the saved draft.</span>
<?php endif ?>
      </p>

      <div class="tabs" role="tablist" aria-label="Editor">
<?php foreach ($tabs as $key => $label): $on = $key === $tab; ?>
        <button type="button" class="tab" role="tab" id="tab-<?= $key ?>" aria-controls="panel-<?= $key ?>" aria-selected="<?= $on ? 'true' : 'false' ?>"<?= $on ? '' : ' tabindex="-1"' ?> data-tab="<?= $key ?>"><?= $label ?><?= $key === 'content' ? ' <span class="tab__count" data-sec-count>' . count($form['sections']) . '</span>' : '' ?></button>
<?php endforeach ?>
      </div>

      <div class="tabpanel" role="tabpanel" id="panel-content" aria-labelledby="tab-content"<?= $hidden('content') ?>>
        <div class="sections" id="sections">
<?php foreach ($form['sections'] as $i => $s) {
    if (isset($registry[$s['type']])) {
        echo editor_section_card($db, $s, $i, $errors['sections'][$i] ?? []);
    }
} ?>
        </div>
        <div class="empty" id="sections-empty"<?= $form['sections'] ? ' hidden' : '' ?>><p>No sections yet. Choose a type below and add the first one; a hero usually comes first.</p></div>
        <div class="add-section">
          <label for="add-type">Add a section at the end</label>
          <div class="add-section__row">
            <select id="add-type" data-dirty-ignore>
              <option value="">Choose a section type</option>
<?php foreach ($registry as $type => $def): ?>
              <option value="<?= e($type) ?>"><?= e($def['label']) ?></option>
<?php endforeach ?>
            </select>
            <button type="button" class="btn" id="add-section"><?= icon('plus') ?>Add section</button>
          </div>
        </div>
      </div>

      <div class="tabpanel" role="tabpanel" id="panel-seo" aria-labelledby="tab-seo"<?= $hidden('seo') ?>>
        <div class="snippet" data-snippet data-host="<?= e($host) ?>" data-prefix="<?= $post ? '/blog/' : '/' ?>">
          <p class="snippet__label">Google preview</p>
          <p class="snippet__url" data-snip-url><?= e($host . ($segs ? ' › ' . implode(' › ', $segs) : '')) ?></p>
          <p class="snippet__title" data-snip-title><?= e(mb_strimwidth(heading_plain((string) ($form['meta_title'] ?: $form['title'])), 0, 60, '…')) ?></p>
          <p class="snippet__desc" data-snip-desc><?= e(mb_strimwidth(heading_plain((string) $form['meta_description']) ?: $descFallback, 0, 160, '…')) ?></p>
        </div>
        <?= admin_field('h1', 'Page heading (H1)', (string) $form['h1'], $fe['h1'] ?? null, 'text', 'The one big heading at the top of the page. Put *stars* around words to colour them. Required to publish.', 'maxlength="255" placeholder="' . e((string) $form['title']) . '"') ?>
        <?= admin_field('meta_title', 'Meta title', (string) $form['meta_title'], $fe['meta_title'] ?? null, 'text', 'The blue link in Google results: 30 to 60 characters. Required to publish.', 'maxlength="255" data-count="30-60" placeholder="' . e(heading_plain((string) $form['title'])) . '"') ?>
        <?= admin_field('meta_description', 'Meta description', (string) $form['meta_description'], $fe['meta_description'] ?? null, 'textarea', 'The grey text under the link: 70 to 160 characters. Required to publish.', 'maxlength="500" data-count="70-160" data-fallback="' . e($descFallback) . '" placeholder="' . e($descFallback ?: 'Empty: Google picks some text from the page.') . '"') ?>
<?php if ($post): ?>
        <p class="hint">Social image: the post's cover image (Settings tab) is used when it is shared.</p>
<?php else: ?>
        <?= editor_field($db, ['type' => 'image', 'label' => 'Social image (shown when the page is shared)'], 'og_image_id', 'f-og_image_id', $form['og_image_id']) ?>
<?php endif ?>
        <div class="check">
          <label><input type="checkbox" name="noindex" value="1"<?= !empty($form['noindex']) ? ' checked' : '' ?>> Hide from search engines (noindex)</label>
          <p class="hint">Adds a robots noindex tag and leaves the <?= $noun ?> out of sitemap.xml. Visitors with the link can still open it.</p>
        </div>
      </div>

      <div class="tabpanel" role="tabpanel" id="panel-settings" aria-labelledby="tab-settings"<?= $hidden('settings') ?>>
        <?= admin_field('slug', 'Web address', (string) $form['slug'], $fe['slug'] ?? null, 'text', $slugHint, $fixed ? 'readonly' : 'maxlength="190" autocomplete="off" spellcheck="false"') ?>
<?php if ($post): ?>
        <?= admin_field('excerpt', 'Excerpt', (string) ($form['excerpt'] ?? ''), null, 'textarea', 'One or two sentences for the blog list. Also the description Google shows when the meta description is empty.', 'maxlength="1000"') ?>
        <?= editor_field($db, ['type' => 'image', 'label' => 'Cover image (also the social image)'], 'og_image_id', 'f-og_image_id', $form['og_image_id']) ?>
<?php elseif ($tplId = positive_int($page['template_id'])): ?>
        <div class="field">
          <span class="label">Template</span>
          <p>The &ldquo;<?= e(array_column($templates, 'name', 'id')[$tplId] ?? "#$tplId") ?>&rdquo; template</p>
          <p class="hint">This page was built from it, so it can't be published until the template's page-specific fields are rewritten. The link stays.</p>
        </div>
<?php else: ?>
        <div class="field">
          <label for="f-template_id">Template</label>
          <select id="f-template_id" name="template_id">
            <option value="">None</option>
<?php foreach ($templates as $t): ?>
            <option value="<?= (int) $t['id'] ?>"<?= positive_int($form['template_id']) === (int) $t['id'] ? ' selected' : '' ?>><?= e($t['name']) ?></option>
<?php endforeach ?>
          </select>
          <p class="hint">A page built from a template must rewrite the template's page-specific fields before it can be published.</p>
        </div>
        <details class="tpl-save">
          <summary>Save this page as a template</summary>
          <p class="hint">Uses the last saved version. Tick the fields every new page must rewrite; the rest are copied as they are.</p>
          <div class="field"><label for="tpl-name">Template name</label><input type="text" id="tpl-name" name="name" form="tpl-form" maxlength="190" required autocomplete="off"></div>
          <fieldset class="tpl-save__fields">
            <legend>Page-specific fields</legend>
<?php foreach (editor_template_fields($page['sections']) as $fpath => $flabel): ?>
            <div class="check"><label><input type="checkbox" name="unique[]" value="<?= e($fpath) ?>" form="tpl-form"> <?= e($flabel) ?></label></div>
<?php endforeach ?>
          </fieldset>
          <button type="submit" class="btn" form="tpl-form" data-needs-saved="page-form" data-busy="Saving&hellip;">Save as template</button>
        </details>
<?php endif ?>
      </div>
    </form>
<?php if (!$post): ?>
    <form id="tpl-form" method="post" action="/admin/pages/<?= (int) $page['id'] ?>/template"><?= csrf_field() ?></form>
<?php endif ?>
  </div>

  <aside class="side" aria-label="Publishing">
    <section class="card pub">
      <h2>Publishing</h2>
      <p class="pub__state"><span class="pill <?= $live ? 'pill--published' : 'pill--draft' ?>"><?= $live ? 'Published' : 'Draft' ?></span><?= !empty($page['noindex']) ? ' <span class="pill pill--warn">noindex</span>' : '' ?></p>
      <p class="pub__dirty" data-dirty-note data-for="page-form" hidden>Unsaved changes. Preview and Duplicate use the saved version, so save first.</p>
      <div class="pub__btns">
        <button class="btn btn--primary btn--block" type="submit" form="page-form" name="action" value="publish"><?= $live ? 'Update' : 'Publish' ?></button>
<?php if ($live && $fixed): ?>
        <p class="hint">Always live: the site needs this page at <?= e($path) ?>.</p>
<?php elseif ($live): ?>
        <button class="btn btn--block" type="submit" form="page-form" name="action" value="draft" data-confirm="Click again: the address will answer 404">Unpublish</button>
<?php else: ?>
        <button class="btn btn--block" type="submit" form="page-form" name="action" value="draft">Save draft</button>
<?php endif ?>
        <a class="btn btn--ghost btn--block" href="/admin/preview/<?= (int) $page['id'] ?>" target="_blank" rel="noopener" data-no-progress data-needs-saved="page-form">Preview<?= icon('arrow-square-out') ?></a>
        <form method="post" action="/admin/pages/<?= (int) $page['id'] ?>/duplicate"><?= csrf_field() ?><button class="btn btn--ghost btn--block" type="submit" data-needs-saved="page-form" data-busy="Copying&hellip;">Duplicate</button></form>
      </div>
      <dl class="pub__facts">
        <dt>Last saved</dt><dd><?= e(admin_date($page['updated_at'])) ?><?= $by ? ' by ' . e($by) : '' ?></dd>
<?php if ($page['published_at']): ?>
        <dt>First published</dt><dd><?= e(admin_date($page['published_at'])) ?></dd>
<?php endif ?>
      </dl>
    </section>
<?php $issues = seo_page_issues($db, settings($db), (int) $page['id']);
$failed = array_fill_keys(array_column($issues, 'check'), true); ?>
    <section class="card score">
      <h2>SEO checks <span class="pill <?= $issues ? 'pill--warn' : 'pill--published' ?>"><?= count(SEO_CHECK_NAMES) - count($failed) ?> of <?= count(SEO_CHECK_NAMES) ?></span></h2>
      <p class="hint">Checked against the saved version; it updates when you save.</p>
      <ul class="score__list">
<?php foreach (SEO_CHECK_NAMES as $key => $label): ?>
        <li><span class="pill <?= isset($failed[$key]) ? 'pill--warn' : 'pill--published' ?>"><?= isset($failed[$key]) ? 'Fix' : 'Pass' ?></span> <?= e($label) ?>
<?php foreach ($issues as $i): if ($i['check'] === $key): ?>
          <p class="score__tip"><?= e($i['message']) ?></p>
<?php endif; endforeach ?>
        </li>
<?php endforeach ?>
      </ul>
    </section>
  </aside>
</div>
<link rel="stylesheet" href="/assets/vendor/quill/quill.snow.css">
<script src="/assets/vendor/quill/quill.js" defer></script>
<script src="/assets/js/media.js" defer></script>
