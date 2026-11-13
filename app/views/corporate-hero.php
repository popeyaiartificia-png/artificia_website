<?php /** Corporate training: real company work, learned together, put into practice. */ ?>
<section class="sol-hero co-hero" aria-labelledby="co-title">
  <canvas class="topo-bg" aria-hidden="true"></canvas>
  <div class="wrap">
    <div class="sol-hero__top">
      <nav class="sol-crumb" aria-label="Breadcrumb"><ol><li><a href="/">Home</a></li><li aria-current="page"><?= e($page['title'] ?? 'Corporate AI training') ?></li></ol></nav>
      <p class="sol-hero__eyebrow"><span class="sol-dot" aria-hidden="true"></span><?= e($f['eyebrow'] ?? 'Corporate AI training') ?></p>
    </div>
    <div class="co-workplace">
      <div class="co-workplace__intro">
        <h1 id="co-title" class="co-hero__title"><?= heading_html($h1) ?></h1>
        <p class="co-workplace__lede"><?= e($f['subheading'] ?? '') ?></p>
        <div class="co-workplace__ctas">
          <a class="btn btn--lime" href="<?= link_href($f['primary_cta_link'] ?? '#enquire') ?>"><?= e($f['primary_cta_label'] ?? 'Discuss corporate training') ?><?= icon('arrow-up-right') ?></a>
          <a class="sol-text-link" href="<?= link_href($f['secondary_cta_link'] ?? '#pilot') ?>"><?= e($f['secondary_cta_label'] ?? 'Start with a free 1-hour pilot') ?><?= icon('arrow-right') ?></a>
        </div>
      </div>
      <figure class="co-workplace__scene" data-co-scene aria-label="Your team before and after the training">
        <?php include VIEWS_DIR . '/corporate-workplace.php'; ?>
        <button class="co-scene-pause" type="button" data-co-motion aria-pressed="false" hidden>Pause motion</button>

      </figure>
    </div>
    <div class="co-team-nav">
      <p>Built for <em>every team.</em><span>Explore what yours can do</span></p>
      <nav aria-label="Explore training by department">
<?php foreach (array_values($doors) as $i => $door): ?>
        <a href="<?= link_href($door['link'] ?? '#departments') ?>" title="<?= e($door['note'] ?? '') ?>"><?= e($door['label'] ?? '') ?><?= icon('arrow-up-right') ?></a>
<?php endforeach ?>
      </nav>
    </div>
  </div>
</section>
