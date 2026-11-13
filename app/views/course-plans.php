<section class="course-plans" id="plans">
  <div class="wrap">
    <div class="course-section-head"><p class="course-eyebrow">04 / CHOOSE YOUR COURSE</p><?= sec_head($f['heading'] ?? '') ?><p class="lede"><?= e($f['intro'] ?? '') ?></p></div>
    <div class="course-plans__grid">
<?php foreach ($f['cards'] ?? [] as $i => $c): ?>
      <article class="course-plan<?= $i === 1 ? ' course-plan--full' : '' ?>">
        <div class="course-plan__top"><span><?= e($c['duration'] ?? '') ?></span><span><?= $i === 1 ? 'FOUNDATION + ADVANCED' : 'START HERE' ?></span></div>
        <h3><?= e($c['title'] ?? '') ?></h3><p class="course-plan__price"><?= e($c['price'] ?? '') ?><span>course fee</span></p>
        <ul><?php foreach (preg_split('/\R/u', trim($c['text'] ?? '')) as $point): if ($point === '') continue; ?><li><?= icon('check') ?><span><?= e($point) ?></span></li><?php endforeach ?></ul>
        <a class="btn" href="<?= link_href($c['link'] ?? '#enquire') ?>" data-course-track="<?= $i === 1 ? '60-day Full program' : '30-day Foundation' ?>">Enquire about <?= $i === 1 ? '60' : '30' ?> days <?= icon('arrow-up-right') ?></a>
      </article>
<?php endforeach ?>
    </div>
    <p class="course-plans__note">Not sure which course fits? <a href="#enquire">Tell us what you want to build.</a> Ask us about timings, payment terms and any separate tool subscriptions.</p>
  </div>
</section>
