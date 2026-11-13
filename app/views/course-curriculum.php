<section class="course-curriculum" id="curriculum">
  <div class="wrap">
    <div class="course-section-head"><p class="course-eyebrow">02 / THE LEARNING JOURNEY</p><?= sec_head($f['heading'] ?? '') ?><p class="lede"><?= e($f['intro'] ?? '') ?></p></div>
    <div class="course-timeline" aria-hidden="true"><span>DAY 01</span><div><i></i></div><span>DAY 30</span><div></div><span>DAY 60</span></div>
<?php foreach (['foundation' => ['01', 'The foundation.', 'Days 1–30 / Included in both courses'], 'full' => ['02', 'Go further. Build systems.', 'Days 31–60 / Full program only']] as $track => [$num, $title, $note]): ?>
    <div class="course-chapter"><span class="course-chapter__number"><?= $num ?></span><div><h3><?= e($title) ?></h3><p><?= e($note) ?></p></div><?= icon($track === 'foundation' ? 'graduation-cap' : 'flow-arrow') ?></div>
    <div class="course-modules">
<?php foreach ($f['modules'] ?? [] as $i => $m): if (($m['track'] ?? 'foundation') !== $track) continue; ?>
      <details class="course-module"<?= $i === 0 ? ' open' : '' ?>>
        <summary><span class="course-module__days">DAYS <?= e($m['day_range'] ?? '') ?></span><span class="course-module__title"><?= e($m['title'] ?? '') ?></span><span class="course-module__plus" aria-hidden="true"></span></summary>
        <div class="course-module__body"><p><?= e($m['topics'] ?? '') ?></p><?php if (($m['tools'] ?? '') !== ''): ?><p class="course-module__tools"><?= e($m['tools']) ?></p><?php endif ?></div>
      </details>
<?php endforeach ?>
    </div>
<?php endforeach ?>
    <div class="course-curriculum__end"><span class="course-dot"></span><p>Finish with something you can show. <strong>Your project. Your portfolio.</strong></p><a href="#plans">Find your course <?= icon('arrow-up-right') ?></a></div>
  </div>
</section>
