<?php // A list being crossed off with the site's neon marker: heavy problem, light outcome. The last line stays open for the visitor's own task. ?>
<section class="sec sec--crossoff crossoff">
  <div class="wrap">
    <div class="crossoff__head">
      <?= sec_head($f['heading'] ?? '') ?>
<?php if (!empty($f['intro'])): ?>
      <p class="crossoff__intro"><?= e($f['intro']) ?></p>
<?php endif ?>
    </div>
    <ul class="tasks">
<?php foreach ($f['items'] ?? [] as $it): ?>
      <li class="task">
        <h3 class="task__t"><span class="task__word" data-t="<?= e($it['title'] ?? '') ?>"><?= e($it['title'] ?? '') ?><span class="task__strike" data-t="<?= e($it['title'] ?? '') ?>" aria-hidden="true"></span></span></h3>
        <p class="task__o"><span><?= e($it['outcome'] ?? '') ?></span></p>
<?php if (!empty($it['text'])): ?>
        <p class="task__p"><?= e($it['text']) ?></p>
<?php endif ?>
      </li>
<?php endforeach ?>
<?php if (!empty($f['open_title'])): ?>
      <li class="task task--open">
        <h3 class="task__t"><span class="task__word" data-t="<?= e($f['open_title']) ?>"><?= e($f['open_title']) ?><span class="task__strike" data-t="<?= e($f['open_title']) ?>" aria-hidden="true"></span></span></h3>
<?php if (!empty($f['open_outcome'])): ?>
        <p class="task__o"><span><?= e($f['open_outcome']) ?></span></p>
<?php endif ?>
        <div class="task__p">
<?php if (!empty($f['open_text'])): ?>
          <p><?= e($f['open_text']) ?></p>
<?php endif ?>
<?php if (!empty($f['cta_label']) && !empty($f['cta_link'])): ?>
          <a class="btn btn--lime" href="<?= link_href($f['cta_link']) ?>"><?= e($f['cta_label']) ?><?= icon('arrow-right') ?></a>
<?php endif ?>
        </div>
      </li>
<?php endif ?>
    </ul>
  </div>
</section>
