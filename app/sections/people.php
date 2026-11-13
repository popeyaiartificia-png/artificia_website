<?php
// "Label: text" per line → [label, text] pairs; blank lines skipped.
$pairs = fn(string $s): array => array_values(array_filter(array_map(
    fn($l) => array_map('trim', explode(':', $l, 2)) + [1 => ''],
    preg_split('/\R/', $s)
), fn($p) => $p[0] !== ''));
$isAbout = ($page['slug'] ?? '') === 'about';
?>
<section class="sec sec--people team">
  <div class="wrap">
    <?= sec_head($f['heading'] ?? '') ?>
    <ul class="people" data-reveal="list">
<?php foreach ($f['items'] ?? [] as $p): ?>
      <li class="person">
<?php if (!empty($p['photo'])): ?>
        <figure class="person__fig frame"><?= image($p['photo'], '(min-width: 1100px) 380px, (min-width: 720px) 45vw, 100vw') ?></figure>
<?php endif ?>
        <h3 class="person__name"><?= e($p['name'] ?? '') ?></h3>
        <p class="entry"><span><?= e($p['role'] ?? '') ?></span></p>
<?php if (!empty($p['bio'])): ?>
        <p class="person__bio"><?= e($p['bio']) ?></p>
<?php endif ?>
<?php if ($rows = $pairs($p['credentials'] ?? '')): ?>
        <dl class="person__facts">
<?php foreach ($rows as [$k, $v]): ?>
          <div><dt><?= e($k) ?></dt><dd><?= e($v) ?></dd></div>
<?php endforeach ?>
        </dl>
<?php endif ?>
<?php if ($rows = $pairs($p['more'] ?? '')): ?>
        <details class="person__more">
          <summary><span>Know more</span><i aria-hidden="true"></i></summary>
<?php if ($isAbout): ?>
          <div class="person__expansion">
<?php endif ?>
          <ul class="person__more-list">
<?php foreach ($rows as [$k, $v]): ?>
            <li><b><?= e($k) ?></b><?php if (str_contains($v, ' · ')): /* a " · " list shows as tags */ ?><span class="person__tags"><?php foreach (explode(' · ', $v) as $t): ?><span><?= e($t) ?></span><?php endforeach ?></span><?php else: ?><?= e($v) ?><?php endif ?></li>
<?php endforeach ?>
          </ul>
<?php if ($isAbout): ?>
<?php if (!empty($p['quote'])) include VIEWS_DIR . '/founder-philosophy.php'; ?>
          </div>
<?php endif ?>
        </details>
<?php endif ?>
<?php if (!$isAbout && !empty($p['quote'])): ?>
        <p class="person__quote"><span>Guiding philosophy</span><?= e($p['quote']) ?></p>
<?php endif ?>
<?php if (!empty($p['photo_2'])): ?>
        <figure class="person__fig2"><?= image($p['photo_2'], '(min-width: 900px) 280px, 70vw') ?></figure>
<?php endif ?>
<?php if (!empty($p['linkedin'])): ?>
        <a class="person__in" href="<?= link_href($p['linkedin'] ?? '') ?>" rel="noopener"><?= icon('linkedin-logo') ?>LinkedIn<span class="vh"> profile of <?= e($p['name'] ?? '') ?></span></a>
<?php endif ?>
      </li>
<?php endforeach ?>
    </ul>
  </div>
</section>
