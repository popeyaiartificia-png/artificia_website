<?php /** @var array $values, $errors, $groups, $stored; @var ?string $importError */ ?>
<h1>Settings</h1>
<form method="post" action="/admin/settings" novalidate>
  <?= csrf_field() ?>
<?php foreach ($groups as $group => $fields): ?>
  <fieldset class="card">
    <legend><?= e($group) ?></legend>
<?php foreach ($fields as $key => [$label, $type, $hint]):
    $secret = $type === 'secret';
    $isSet = $secret && ($stored[$key] ?? '') !== '';
    echo admin_field($key, $label, $secret ? '' : (string) ($values[$key] ?? ''), $errors[$key] ?? null,
        $secret ? 'password' : ($type === 'textarea' ? 'textarea' : ($type === 'email' ? 'email' : 'text')),
        $secret ? ($isSet ? 'Saved. Leave blank to keep it.' : 'Not set.') : $hint,
        $secret ? 'autocomplete="new-password"' : ($type === 'digits' ? 'inputmode="numeric" placeholder="919104488859"' : ''));
    if ($isSet): ?>
    <p class="check"><label><input type="checkbox" name="clear_<?= e($key) ?>" value="1"> Clear the saved <?= e(strtolower($label)) ?></label></p>
<?php endif; endforeach ?>
  </fieldset>
<?php endforeach ?>
  <button class="btn" type="submit">Save settings</button>
</form>

<section class="card">
  <h2>Import seed content</h2>
  <p>Loads the starting pages, templates, redirects and images from the repository. It refuses if the site already has pages.</p>
<?php if (!empty($importError)): ?><p class="err" role="alert"><?= e($importError) ?></p><?php endif ?>
  <form method="post" action="/admin/settings/import">
    <?= csrf_field() ?>
    <p class="check"><label><input type="checkbox" name="force" value="1"> Replace existing pages</label></p>
    <button class="btn btn--quiet" type="submit">Import seed content</button>
  </form>
</section>
