<?php
/** Corporate training programmes as a row of tall panels: the one you point at, focus or tap opens wide (corporate.js keeps
 *  the last one open); the rest stand as spines. Below 1100px, and without JS, every panel is open and they stack.
 *  solutions fields: title, text (who it is for), includes (one per line), link, link_label. */
$items = array_values($items);
$n = count($items);
$check = '<svg class="co-prog__check" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>';
?>
<section class="sec co-progs" id="programmes">
  <canvas class="topo-bg" aria-hidden="true"></canvas>
  <div class="wrap">
    <header class="co-head">
      <p class="sol-eyebrow"><span class="sol-dot" aria-hidden="true"></span>Programmes <span class="sol-eyebrow__count"><?= sprintf('%02d', $n) ?></span></p>
      <?= sec_head($f['heading'] ?? '') ?>
<?php if (!empty($f['intro'])): ?>
      <p class="co-head__intro"><?= e($f['intro']) ?></p>
<?php endif ?>
    </header>
    <ol class="co-progs__list" data-co-progs>
<?php foreach ($items as $i => $item): $includes = array_values(array_filter(array_map('trim', explode("\n", (string) ($item['includes'] ?? ''))), 'strlen')); ?>
      <li class="co-prog<?= $i ? '' : ' is-on' ?>">
        <div class="co-prog__spine" aria-hidden="true"><span class="co-prog__n"><?= sprintf('%02d', $i + 1) ?></span><span class="co-prog__vt"><?= e($item['title'] ?? '') ?></span></div>
        <div class="co-prog__body">
          <p class="co-prog__n"><?= sprintf('%02d', $i + 1) ?> <span>/ <?= sprintf('%02d', $n) ?></span></p>
          <h3 class="co-prog__t"><?= e($item['title'] ?? '') ?></h3>
<?php if (!empty($item['text'])): ?>
          <p class="co-prog__for"><?= e($item['text']) ?></p>
<?php endif ?>
<?php if ($includes): ?>
          <ul class="co-prog__inc">
<?php foreach ($includes as $k => $line): ?>
            <li style="--k: <?= $k ?>"><?= $check ?><?= e($line) ?></li>
<?php endforeach ?>
          </ul>
<?php endif ?>
<?php if (!empty($item['link'])): ?>
          <a class="co-prog__go" href="<?= link_href($item['link']) ?>"><?= e(($item['link_label'] ?? '') !== '' ? $item['link_label'] : 'Ask about this programme') ?><span class="co-prog__arrow" aria-hidden="true"><?= icon('arrow-up-right') ?></span></a>
<?php endif ?>
        </div>
        <span class="co-prog__big" aria-hidden="true"><?= sprintf('%02d', $i + 1) ?></span>
      </li>
<?php endforeach ?>
    </ol>
  </div>
</section>
