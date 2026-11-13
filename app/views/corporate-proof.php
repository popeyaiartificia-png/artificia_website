<?php
/** Corporate training proof: the trained count as a field of a hundred people who light up one by one (corporate.js),
 *  beside what a participant said. The heading carries the count; the dots are decoration. testimonials fields. */
$items = array_values(array_filter($f['items'] ?? [], fn($it) => trim((string) ($it['quote'] ?? '')) !== ''));
?>
<section class="sec co-proof">
  <canvas class="topo-bg" aria-hidden="true"></canvas>
  <div class="wrap co-proof__grid">
    <div class="co-proof__count">
      <p class="sol-eyebrow"><span class="sol-dot" aria-hidden="true"></span>Trained so far</p>
      <div class="co-proof__field" aria-hidden="true" data-co-dots>
<?php for ($i = 0; $i < 100; $i++): ?><span style="--i: <?= $i ?>"></span><?php endfor ?>
        <b>+</b>
      </div>
      <?= sec_head($f['heading'] ?? '') ?>
    </div>
<?php foreach ($items as $it): ?>
    <figure class="co-proof__quote">
      <blockquote><p><?= e($it['quote']) ?></p></blockquote>
      <figcaption>
        <?= !empty($it['photo']) ? image($it['photo'], '64px', false, 'co-proof__photo') : '' ?>
        <span><b><?= e($it['name'] ?? '') ?></b><?= e($it['role'] ?? '') ?></span>
      </figcaption>
    </figure>
<?php endforeach ?>
  </div>
</section>
