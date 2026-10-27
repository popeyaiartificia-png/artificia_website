<section class="sec sec--use_cases usecases">
  <div class="wrap">
    <?= sec_head($f['heading'] ?? '') ?>
<?php if (!empty($f['intro'])): ?>
    <p class="lede"><?= e($f['intro']) ?></p>
<?php endif ?>
    <ul class="cases" data-reveal="list">
<?php foreach ($f['items'] ?? [] as $it): ?>
      <li class="case">
        <h3 class="h3"><?= e($it['title'] ?? '') ?></h3>
        <div class="case__b"><span class="entry"><span>Before</span></span><p><?= e($it['before'] ?? '') ?></p></div>
        <svg class="case__arrow draw" viewBox="0 0 64 22" aria-hidden="true"><path class="ln" pathLength="1" d="M2 11h56M50 4l8 7-8 7"/></svg>
        <div class="case__a"><span class="entry"><span>After</span></span><p><?= e($it['after'] ?? '') ?></p></div>
      </li>
<?php endforeach ?>
    </ul>
  </div>
</section>
