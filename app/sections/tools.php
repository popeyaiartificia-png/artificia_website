<?php if (($f['variant'] ?? 'standard') === 'course-toolkit') { include VIEWS_DIR . '/course-toolkit.php'; return; } ?>
<section class="sec sec--tools tools<?= !empty($f['flow']) ? ' tools--flow' : '' ?>"<?= ($page['sections'][0]['fields']['variant'] ?? '') === 'course' ? ' id="course-tools"' : '' ?>>
<?php if (!empty($f['flow'])): ?>
  <canvas class="topo-bg" aria-hidden="true"></canvas>
<?php endif ?>
  <div class="wrap">
<?php if (($page['sections'][0]['fields']['variant'] ?? '') === 'course'): ?><p class="course-eyebrow">03 / YOUR AI TOOLKIT</p><?php endif ?>
    <?= sec_head($f['heading'] ?? '') ?>
<?php if (!empty($f['intro'])): ?>
    <p class="lede"><?= e($f['intro']) ?></p>
<?php endif ?>
<?php if (!empty($f['flow'])): ?>
    <?php // One piece of work through the system, step by step; a pulse runs the line on CSS alone. ?>
    <ol class="pipe" style="--n: <?= count($f['flow']) ?>" data-reveal="list">
<?php foreach (array_values($f['flow']) as $i => $st): ?>
      <li class="pipe__step" style="--i: <?= $i ?>"><span class="pipe__node" aria-hidden="true"></span><span class="pipe__t"><?= e($st['title'] ?? '') ?></span><?php if (!empty($st['note'])): ?><span class="pipe__n"><?= e($st['note']) ?></span><?php endif ?></li>
<?php endforeach ?>
    </ol>
    <p class="tools__on"><?= e(trim($f['tools_label'] ?? '') !== '' ? $f['tools_label'] : 'Built on') ?></p>
<?php endif ?>
    <ul class="parts" data-reveal="list">
<?php foreach ($f['tools'] ?? [] as $t): ?>
      <li class="part"><?= !empty($t['logo']) ? image($t['logo'], '28px') : brand((string) ($t['brand'] ?? '')) ?><span class="part__name"><?= e($t['name'] ?? '') ?></span><?php if (!empty($t['note'])): ?><span class="part__lead" aria-hidden="true"></span><span class="part__note"><?= e($t['note']) ?></span><?php endif ?></li>
<?php endforeach ?>
    </ul>
  </div>
</section>
