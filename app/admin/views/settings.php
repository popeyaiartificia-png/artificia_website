<?php /** @var array $values, $errors, $groups, $stored; @var ?string $importError; @var bool $canImport (the site has no pages yet) */ ?>
<?= admin_page_head('System', 'Settings', null, 'Tracking, lead routing and the business details the site shows.') ?>

<form method="post" action="/admin/settings" id="settings-form" novalidate data-dirty-guard>
  <?= csrf_field() ?>
<?php foreach ($groups as $group => $fields): ?>
  <section class="card">
    <h2><?= e($group) ?></h2>
    <div class="form-grid">
<?php foreach ($fields as $key => [$label, $type, $hint]):
    $secret = $type === 'secret';
    $isSet = $secret && ($stored[$key] ?? '') !== '';
    echo '<div class="cell">';
    echo admin_field($key, $label, $secret ? '' : (string) ($values[$key] ?? ''), $errors[$key] ?? null,
        $secret ? 'password' : ($type === 'textarea' ? 'textarea' : ($type === 'email' ? 'email' : 'text')),
        $secret ? ($isSet ? 'Saved. Leave blank to keep it.' : 'Not set.') : $hint,
        $secret ? 'autocomplete="new-password"' : ($type === 'digits' ? 'inputmode="numeric" placeholder="919104488859"' : ''));
    if ($isSet): ?>
      <p class="check"><label><input type="checkbox" name="clear_<?= e($key) ?>" value="1"> Clear the saved <?= e(strtolower($label)) ?></label></p>
<?php endif; echo '</div>'; endforeach ?>
    </div>
  </section>
<?php endforeach ?>

<?php if ($canImport || !empty($importError)): ?>
  <section class="card">
    <h2>Seed content</h2>
<?php if (!empty($importError)): ?><p class="notice notice--error" role="alert"><?= e($importError) ?></p><?php endif ?>
<?php if ($canImport): ?>
    <p class="hint">Loads the starting pages, templates, redirects and images from the repository. Offered only while the site has no pages.</p>
    <button class="btn btn--sm" type="submit" form="import-form" data-busy="Importing&hellip;">Import seed content</button>
<?php endif ?>
  </section>
<?php endif ?>

  <div class="savebar">
    <button class="btn btn--primary" type="submit">Save changes</button>
    <span class="savebar__note" data-dirty-note hidden>Unsaved changes</span>
  </div>
</form>
<?php if ($canImport): ?><form method="post" action="/admin/settings/import" id="import-form"><?= csrf_field() ?></form><?php endif ?>
