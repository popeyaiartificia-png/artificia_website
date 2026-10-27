<?php $note = trim($f['note'] ?? '') !== '' ? $f['note'] : 'Typical outcomes for AI-enabled teams.'; ?>
<section class="sec sec--stats stats" data-section="stats">
  <div class="wrap">
    <?= sec_head($f['heading'] ?? '') ?>
    <p class="stats__note"><?= e($note) ?></p>
    <dl class="dims">
<?php foreach ($f['items'] ?? [] as $it):
    $v = (string) ($it['value'] ?? '');
    preg_match('/^([\d.,]+(?:\s?[–-]\s?[\d.,]+)?\s?[%×x+]?)\s*(.*)$/u', $v, $m);
    $big = rtrim($m[1] ?? $v);
    $small = $m[2] ?? '';
?>
      <div class="dim">
        <dt class="dim__label"><?= e($it['label'] ?? '') ?></dt>
        <dd class="dim__val"><span class="vh"><?= e($v) ?></span><span class="dim__rule dim__rule--l" aria-hidden="true"></span><span class="dim__num" aria-hidden="true" data-count><?= e($big) ?></span><?php if ($small !== ''): ?><small aria-hidden="true"><?= e($small) ?></small><?php endif ?><span class="dim__rule dim__rule--r" aria-hidden="true"></span></dd>
      </div>
<?php endforeach ?>
    </dl>
  </div>
</section>
