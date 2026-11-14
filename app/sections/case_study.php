<section class="sec sec--case_study cs" data-section="case">
  <div class="wrap cs__grid">
    <canvas class="topo-bg" aria-hidden="true"></canvas>
    <div class="cs__copy">
<?php if (!empty($f['logo'])): ?>
      <div class="cs__logo"><?= image($f['logo'], '160px') ?></div>
<?php endif ?>
      <?= sec_head($f['heading'] ?? '') ?>
      <p class="entry"><span>Case study</span><span><?= e($f['client'] ?? '') ?></span></p>
      <p class="lede"><?= e($f['summary'] ?? '') ?></p>
<?php if (!empty($f['challenge'])): ?>
      <div class="prose"><?= rich($f['challenge']) ?></div>
<?php endif ?>
<?php if (!empty($f['results'])): ?>
      <dl class="cs__results">
<?php foreach ($f['results'] as $r): ?>
        <div><dt><?= e($r['label'] ?? '') ?></dt><dd><?= e($r['value'] ?? '') ?></dd></div>
<?php endforeach ?>
      </dl>
<?php endif ?>
<?php if (!empty($f['link_label']) && !empty($f['link'])): ?>
      <div class="btns"><a class="btn" href="<?= link_href($f['link'] ?? '') ?>"><?= e($f['link_label']) ?><?= icon('arrow-right') ?></a></div>
<?php endif ?>
    </div>
    <div class="cs__work">
<?php if (!empty($f['image'])): ?>
    <figure class="cs__shot frame"><?= image($f['image'], '(min-width: 1024px) 640px, 100vw') ?></figure>
<?php endif ?>
    <ol class="flow" aria-label="What we built">
<?php foreach ($f['built'] ?? [] as $b): ?>
      <li class="flow__node"><h3><?= e($b['title'] ?? '') ?></h3><p><?= e($b['text'] ?? '') ?></p><span class="flow__link" aria-hidden="true"></span></li>
<?php endforeach ?>
    </ol>
    </div>
  </div>
</section>
