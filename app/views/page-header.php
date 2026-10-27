<?php /** @var array $page */ ?>
<header class="sec pagehead">
  <div class="wrap">
    <div class="sec__head"><?= tick() ?><h1 class="h1 h1--page"><?= heading_html($page['h1'] !== '' ? $page['h1'] : $page['title']) ?></h1></div>
  </div>
</header>
