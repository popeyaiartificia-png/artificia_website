<?php /** @var array $page */
/** Pages without a hero (blog, legal, posts) open with the masterclass hero's frame: breadcrumb and one large centred
 *  title on the dark ground, with no visual. */
$h1 = $page['h1'] !== '' ? $page['h1'] : $page['title'];
?>
<header class="course-hero journey-hero journey-hero--plain">
  <div class="masterclass-terrain" aria-hidden="true"><span></span><span></span><span></span></div>
  <div class="wrap">
    <div class="course-hero__top"><nav class="course-breadcrumb" aria-label="Breadcrumb"><ol><li><a href="/">Home</a></li><li aria-current="page"><?= e(heading_plain($page['title'])) ?></li></ol></nav></div>
    <h1 class="masterclass-title<?= mb_strlen(heading_plain($h1)) > 18 ? ' journey-title--wrap' : '' ?>"><?= heading_html($h1) ?></h1>
  </div>
</header>
