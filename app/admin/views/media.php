<?php /** @var array $items, $altFor; @var ?string $uploadError, $altError; @var string $altDraft */ ?>
<?= admin_page_head('Content', 'Media', (string) count($items), 'Every image on the site. Each one needs a short description (alt text): it helps Google and screen-reader users.') ?>
<div id="media-page"<?= $altError ? ' data-reopen="' . (int) $altFor['id'] . '"' : '' ?>>

<section class="card">
  <h2>Upload an image</h2>
  <form method="post" action="/admin/media" enctype="multipart/form-data" id="upload-form">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="upload">
<?php if ($uploadError): ?><p class="notice notice--error" role="alert"><?= e($uploadError) ?></p><?php endif ?>
    <div class="dropzone" id="dropzone">
      <p class="dropzone__text"><?= icon('image', 'icon') ?> <span id="dropzone-name">Drag an image here, or choose a file.</span></p>
      <label class="sr" for="f-file">Image file</label>
      <input id="f-file" name="file" type="file" accept="image/jpeg,image/png,image/webp" required aria-describedby="h-file">
      <p class="hint" id="h-file">JPEG, PNG or WebP, up to 15 MB. It is resized and saved as WebP.</p>
    </div>
    <?= admin_field('alt', 'Alt text', $altDraft, null, 'text', 'What the image shows, in a few words. Required.', 'required maxlength="500" autocomplete="off"') ?>
    <button class="btn btn--primary" type="submit" data-busy="Uploading&hellip;">Upload image</button>
  </form>
</section>

<?php if ($altError): ?><p class="notice notice--error" role="alert"><?= e($altError) ?></p><?php endif ?>

<?php if (!$items): ?>
<div class="empty"><p>No images yet. Upload the first one above and add a short description.</p></div>
<?php else: ?>
<div class="filters">
  <label class="sr" for="media-search">Search images</label>
  <input id="media-search" type="search" placeholder="Find by name or description" autocomplete="off">
  <span class="hint" id="media-count" aria-live="polite"></span>
</div>
<ul class="media" id="media-grid">
<?php foreach ($items as $m):
    $name = (string) $m['name']; $noAlt = trim((string) $m['alt']) === ''; ?>
  <li class="media__item" data-search="<?= e(mb_strtolower($name . ' ' . $m['alt'])) ?>">
    <button type="button" class="media__open" data-id="<?= (int) $m['id'] ?>" data-alt="<?= e($m['alt']) ?>" data-url="<?= e($m['url']) ?>" data-thumb="<?= e($m['thumb']) ?>"
      data-w="<?= (int) $m['width'] ?>" data-h="<?= (int) $m['height'] ?>" data-kb="<?= (int) $m['kb'] ?>" data-name="<?= e($name) ?>">
      <span class="media__thumb"><img src="<?= e($m['thumb']) ?>" alt="" loading="lazy" decoding="async"></span>
      <span class="media__name"><?= e($name) ?></span>
      <span class="media__meta"><?= (int) $m['width'] ?> &times; <?= (int) $m['height'] ?><?= $noAlt ? ' <span class="pill pill--warn">No alt</span>' : '' ?></span>
    </button>
  </li>
<?php endforeach ?>
</ul>
<div class="empty" id="media-none" hidden><p>No image matches that search.</p></div>

<dialog class="dlg" id="media-detail" aria-labelledby="media-detail-title">
  <form method="post" action="/admin/media" data-no-busy>
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="alt">
    <input type="hidden" name="id" id="md-id" value="<?= (int) $altFor['id'] ?>">
    <h2 id="media-detail-title">Image details</h2>
    <div class="dlg__body">
      <div class="dlg__preview"><img id="md-img" src="data:," alt=""></div>
      <div>
        <div class="field<?= $altError ? ' has-error' : '' ?>"><label for="md-alt">Alt text (required)</label>
          <p class="hint" id="md-hint">Describe what the image shows, in a few words.</p>
          <textarea id="md-alt" name="alt" rows="3" required maxlength="500" aria-describedby="md-hint"><?= e($altFor['alt']) ?></textarea></div>
        <dl class="dlg__facts">
          <dt>Size</dt><dd id="md-size"></dd>
          <dt>Address</dt><dd><a id="md-url" href="#" target="_blank" rel="noopener" data-no-progress></a></dd>
        </dl>
      </div>
    </div>
    <div class="btn-row">
      <button class="btn btn--primary" type="submit" data-busy="Saving&hellip;">Save description</button>
      <button class="btn btn--ghost" type="button" data-close>Close</button>
    </div>
  </form>
</dialog>
<?php endif ?>
</div>
<script src="/assets/js/media.js" defer></script>
