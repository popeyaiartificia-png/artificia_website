<?php
/** About: the lines of work as large ruled rows, in the blueprint's order, measured on the left by one dimension line:
 *  one team. card_grid fields: title (`*words*` turn serif), text, link. */
?>
<section class="sec ab-work" id="work">
  <canvas class="topo-bg" aria-hidden="true"></canvas>
  <div class="wrap">
    <header class="ab-head">
      <p class="sol-eyebrow"><span class="sol-dot" aria-hidden="true"></span><?= e(($f['eyebrow'] ?? '') !== '' ? $f['eyebrow'] : 'What we do') ?></p>
      <?= sec_head($f['heading'] ?? '') ?>
<?php if (!empty($f['intro'])): ?>
      <p class="ab-head__intro"><?= e($f['intro']) ?></p>
<?php endif ?>
    </header>
    <div class="ab-work__body">
      <span class="ab-work__dim" aria-hidden="true"><span>One team</span></span>
      <ol class="ab-work__list" data-reveal="list">
<?php foreach (array_values($f['cards'] ?? []) as $i => $c): ?>
        <li class="ab-row">
          <span class="ab-row__n" aria-hidden="true"><?= sprintf('%02d', $i + 1) ?></span>
<?php if (!empty($c['link'])): ?>
          <h3 class="ab-row__t"><a href="<?= link_href($c['link']) ?>"><?= heading_html($c['title'] ?? '') ?></a></h3>
<?php else: ?>
          <h3 class="ab-row__t"><?= heading_html($c['title'] ?? '') ?></h3>
<?php endif ?>
          <p class="ab-row__text"><?= e($c['text'] ?? '') ?></p>
<?php if (!empty($c['link'])): ?>
          <span class="ab-row__go" aria-hidden="true"><?= icon('arrow-up-right') ?></span>
<?php endif ?>
        </li>
<?php endforeach ?>
      </ol>
    </div>
  </div>
</section>
