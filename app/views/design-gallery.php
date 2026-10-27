<?php /** @var array $sections, $page, $settings */ ?>
<nav class="gallery-nav" aria-label="Section types">
  <div class="wrap">
    <p class="foot__h">Section library</p>
    <ul>
<?php foreach ($sections as $i => $sec): ?>
      <li><a href="#s-<?= $i ?>"><?= e(section_registry()[$sec['type']]['label'] ?? $sec['type']) ?></a></li>
<?php endforeach ?>
    </ul>
  </div>
</nav>
<?php foreach ($sections as $i => $sec): ?>
<div id="s-<?= $i ?>" class="gallery-item" data-type="<?= e($sec['type']) ?>"><?= render_section_in($sec, $i, $page, $settings) ?></div>
<?php endforeach ?>
