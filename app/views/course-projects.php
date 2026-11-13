<?php $projects = array_values($f['steps'] ?? []); ?>
<section class="sec course-projects" id="overview" data-course-projects aria-labelledby="course-projects-title" aria-roledescription="carousel">
  <div class="wrap">
    <header class="course-projects__head">
      <div><p class="course-eyebrow">01 / WHAT YOU’LL BUILD</p><h2 class="h2" id="course-projects-title"><?= heading_html($f['heading'] ?? '') ?></h2></div>
<?php if (!empty($f['intro'])): ?><p class="course-projects__intro"><?= e($f['intro']) ?></p><?php endif ?>
    </header>
    <div class="course-projects__track" id="course-project-slides" data-project-track tabindex="0" role="group" aria-label="Course projects. Use the left and right arrow keys to browse.">
<?php foreach ($projects as $index => $project):
    $visual = (string) ($project['visual'] ?? 'website');
    if (!in_array($visual, ['website', 'content', 'assistant', 'automation'], true)) $visual = 'website';
?>
      <article class="course-project" data-course-project role="group" aria-roledescription="slide" aria-label="<?= $index + 1 ?> of <?= count($projects) ?>">
        <div class="course-project__preview">
          <div class="course-project__meta"><span>COURSE PROJECT <?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span><span><?= e($project['day_range'] ?? '') ?></span></div>
          <div class="course-project__art" aria-hidden="true"><?php include VIEWS_DIR . '/course-project-visual.php'; ?></div>
        </div>
        <div class="course-project__copy">
          <h3><?= e($project['title'] ?? '') ?></h3>
          <p><?= e($project['text'] ?? '') ?></p>
<?php if (!empty($project['tools'])): ?><p class="course-project__tools"><?= e($project['tools']) ?></p><?php endif ?>
        </div>
      </article>
<?php endforeach ?>
    </div>
    <div class="course-projects__controls" data-project-controls hidden>
      <div class="course-projects__position"><span data-project-status aria-live="polite" aria-atomic="true"></span><div class="course-projects__pages" data-project-pages role="group" aria-label="Choose project slides"></div></div>
      <div class="course-projects__arrows">
        <button class="course-projects__arrow course-projects__arrow--prev" type="button" data-project-prev aria-label="Previous projects" aria-disabled="true" aria-controls="course-project-slides"><?= icon('arrow-right') ?></button>
        <button class="course-projects__arrow" type="button" data-project-next aria-label="Next projects" aria-disabled="false" aria-controls="course-project-slides"><?= icon('arrow-right') ?></button>
      </div>
    </div>
    <footer class="course-projects__foot">
      <p>Course project examples.<br>Build your capstone around your own idea.</p>
      <a class="btn btn--lime" href="<?= link_href($f['cta_link'] ?? '#curriculum') ?>"><?= e($f['cta_label'] ?? 'Explore the 60-day curriculum') ?><?= icon('arrow-right') ?></a>
    </footer>
  </div>
</section>
