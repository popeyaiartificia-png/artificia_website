<?php
// Built-in figures: cut-outs in public/assets/img/versus, with their sizes.
$figures = ['person' => ['person-profile-mature', 800, 1067], 'robot' => ['robot', 883, 1200]];
// Neon marker strokes over each side's big word: before AI it is struck out, with AI it is circled.
$marks = [
    '<path pathLength="1" d="M4 62 L20 30 L30 70 L48 26 L58 74 L76 28 L86 68 L97 40"/>',
    '<path pathLength="1" d="M31 9 C61 0 95 11 98 37 C104 65 80 87 49 91 C20 97 1 78 4 51 C6 26 31 6 59 7"/>',
];
$sides = array_slice(array_values($f['sides'] ?? []), 0, 2);
?>
<section class="sec sec--versus versus">
  <div class="versus__dawn" aria-hidden="true"></div>
  <canvas class="topo-bg" aria-hidden="true"></canvas>
  <div class="versus__dusk" aria-hidden="true"><canvas class="topo-bg"></canvas></div>
  <div class="versus__figs" aria-hidden="true">
<?php foreach ($sides as $i => $sd): $fig = (string) ($sd['figure'] ?? ''); ?>
    <div class="versus__fig versus__fig--<?= $i ? 'b' : 'a' ?>"><?php if (!empty($sd['image'])): ?><?= image($sd['image'], '40vw') ?><?php elseif (isset($figures[$fig])): ?><img src="/assets/img/versus/<?= $figures[$fig][0] ?>.webp"<?php if ($fig === 'person'): ?> srcset="/assets/img/versus/<?= $figures[$fig][0] ?>-small.webp 420w, /assets/img/versus/<?= $figures[$fig][0] ?>.webp 800w" sizes="(min-width: 1100px) 36vw, (min-width: 667px) 330px, 50vw"<?php endif ?> width="<?= $figures[$fig][1] ?>" height="<?= $figures[$fig][2] ?>" alt="" loading="lazy" decoding="async"><?php endif ?></div>
<?php endforeach ?>
  </div>
  <div class="wrap versus__grid">
<?php foreach ($sides as $i => $sd):
    // "*Before* AI": the emphasised word is the small serif line, the rest the big word that takes the mark.
    $title = (string) ($sd['title'] ?? '');
    [$kicker, $word] = preg_match('/^\s*\*([^*]+)\*\s*(.*)$/s', $title, $m) ? [$m[1], $m[2]] : ['', heading_plain($title)];
?>
    <div class="versus__side versus__side--<?= $i ? 'b' : 'a' ?>">
      <h2 class="versus__t"><?php if ($kicker !== ''): ?><span class="versus__k"><?= e($kicker) ?></span> <?php endif ?><span class="versus__w"><?= e($word) ?><svg class="versus__mark" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true" focusable="false"><?= $marks[$i] ?></svg></span></h2>
<?php if (!empty($sd['text'])): ?>
      <p class="versus__p"><?= e($sd['text']) ?></p>
<?php endif ?>
<?php if (!empty($sd['status_label'])): ?>
      <p class="versus__status"><?= icon('clock') ?><span><?= e($sd['status_label']) ?></span></p>
<?php elseif (!empty($sd['link'])): ?>
      <a class="versus__go" href="<?= link_href($sd['link']) ?>" aria-label="<?= e(($sd['link_label'] ?? '') ?: heading_plain($title)) ?>"><?= icon('arrow-up-right') ?></a>
<?php endif ?>
    </div>
<?php endforeach ?>
  </div>
</section>
