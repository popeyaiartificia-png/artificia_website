<?php
/** The six solutions as a deck: each card pins under the masthead and the next one slides over it, beside a sticky index.
 *  Uses the homepage's card palettes and illustrations ($families and $items come from sections/solutions.php). */
$items = array_values($items);
$check = '<svg class="sol-card__check" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path pathLength="1" d="M5 12.5l4.5 4.5L19 7.5"/></svg>';
?>
<section class="sec sol-ch" id="solutions" data-sol-chapters>
  <canvas class="topo-bg" aria-hidden="true"></canvas>
  <div class="wrap">
    <header class="sol-ch__head">
      <p class="sol-eyebrow"><span class="sol-dot" aria-hidden="true"></span>Our solutions <span class="sol-eyebrow__count"><?= sprintf('%02d', count($items)) ?></span></p>
      <?= sec_head($f['heading'] ?? '') ?>
<?php if (!empty($f['intro'])): ?>
      <p class="sol-ch__intro"><?= e($f['intro']) ?></p>
<?php endif ?>
    </header>
    <div class="sol-ch__body">
      <div class="sol-ch__index" aria-hidden="true">
        <p class="sol-ch__big"><span class="sol-ch__digits" data-sol-num>01</span><span class="sol-ch__of">/ <?= sprintf('%02d', count($items)) ?></span></p>
        <ol class="sol-ch__names">
<?php foreach ($items as $i => $item): ?>
          <li data-sol-name><span><?= sprintf('%02d', $i + 1) ?></span><?= e($item['title'] ?? '') ?></li>
<?php endforeach ?>
        </ol>
        <div class="sol-ch__meter"><span data-sol-meter></span></div>
      </div>
      <ol class="sol-ch__list">
<?php foreach ($items as $i => $item):
    $visual = $item['visual'] ?? 'workflow';
    $family = $families[$visual] ?? ['Business systems', 'Built for the way you work.', 'Custom solutions'];
    $includes = array_values(array_filter(array_map('trim', explode("\n", (string) ($item['includes'] ?? ''))), 'strlen'));
    $label = trim((string) ($item['link_label'] ?? '')) !== '' ? $item['link_label'] : 'Explore ' . ($item['title'] ?? '');
?>
        <li class="sol-ch__item solution solution--<?= e($visual) ?>" id="solution-<?= $i + 1 ?>" style="--i: <?= $i ?>" data-sol-card>
          <article class="sol-card">
            <div class="sol-card__copy">
              <p class="sol-card__meta"><span><?= sprintf('%02d', $i + 1) ?> / <?= e($family[0]) ?></span><span class="sol-card__spark" aria-hidden="true">&#10035;</span></p>
              <h3 class="sol-card__title"><?= e($item['title'] ?? '') ?></h3>
              <p class="sol-card__text"><?= e($item['text'] ?? '') ?></p>
<?php if ($includes): ?>
              <ul class="sol-card__inc" aria-label="What <?= e($item['title'] ?? 'it') ?> includes">
<?php foreach ($includes as $n => $line): ?>
                <li style="--n: <?= $n ?>"><?= $check ?><?= e($line) ?></li>
<?php endforeach ?>
              </ul>
<?php endif ?>
<?php if (!empty($item['link'])): ?>
              <a class="sol-card__go" href="<?= link_href($item['link']) ?>"><?= e($label) ?><span class="sol-card__arrow" aria-hidden="true"><?= icon('arrow-up-right') ?></span></a>
<?php endif ?>
            </div>
            <div class="sol-card__visual" aria-hidden="true">
              <div class="solution__halo"></div>
              <?= render_view('solution-visual', ['visual' => $visual]) ?>
              <p class="sol-card__tag"><span><?= e($family[2]) ?></span><?= e($family[1]) ?></p>
            </div>
          </article>
        </li>
<?php endforeach ?>
      </ol>
    </div>
  </div>
</section>
