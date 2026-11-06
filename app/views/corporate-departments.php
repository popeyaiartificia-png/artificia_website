<?php
/** Corporate training: one panel per department, the work as it is today beside the same work after the training.
 *  With JS the names become tabs (corporate.js); without it every panel is shown. Each panel's id is dept-<title slug>,
 *  which the hero's department links point at. use_cases fields: title, before, after. */
$items = array_values($f['items'] ?? []);
$slug = fn(string $t) => 'dept-' . (trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower($t)), '-') ?: 'team');
?>
<section class="sec co-dept" id="departments">
  <div class="wrap">
    <header class="co-head">
      <p class="sol-eyebrow"><span class="sol-dot" aria-hidden="true"></span>By department</p>
      <?= sec_head($f['heading'] ?? '') ?>
<?php if (!empty($f['intro'])): ?>
      <p class="co-head__intro"><?= e($f['intro']) ?></p>
<?php endif ?>
    </header>
    <div class="co-dept__body" data-co-dept>
      <div class="co-dept__tabs" role="tablist" aria-label="Departments" hidden>
<?php foreach ($items as $i => $it): $id = $slug((string) ($it['title'] ?? '')); ?>
        <button class="co-dept__tab" type="button" role="tab" id="<?= $id ?>-tab" aria-controls="<?= $id ?>" aria-selected="<?= $i ? 'false' : 'true' ?>"<?= $i ? ' tabindex="-1"' : '' ?>><span aria-hidden="true"><?= sprintf('%02d', $i + 1) ?></span><?= e($it['title'] ?? '') ?></button>
<?php endforeach ?>
      </div>
      <div class="co-dept__panels">
<?php foreach ($items as $i => $it): $id = $slug((string) ($it['title'] ?? '')); ?>
        <article class="co-dept__panel" id="<?= $id ?>" aria-labelledby="<?= $id ?>-name">
          <h3 class="co-dept__name" id="<?= $id ?>-name"><?= e($it['title'] ?? '') ?></h3>
          <div class="co-dept__pair">
            <div class="co-dept__side co-dept__today"><p class="co-dept__k">Today</p><p class="co-dept__txt"><?= e($it['before'] ?? '') ?></p></div>
            <svg class="co-dept__arrow" viewBox="0 0 120 40" aria-hidden="true" focusable="false"><path pathLength="1" d="M4 20C34 4 62 36 92 20"/><path d="M84 11l12 9-12 9"/></svg>
            <div class="co-dept__side co-dept__after"><p class="co-dept__k">After the training</p><p class="co-dept__txt"><?= e($it['after'] ?? '') ?></p></div>
          </div>
        </article>
<?php endforeach ?>
      </div>
    </div>
  </div>
</section>
