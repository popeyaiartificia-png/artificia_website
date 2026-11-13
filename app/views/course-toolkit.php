<section class="sec sec--tools tools tools--course-toolkit" id="course-tools" aria-labelledby="course-toolkit-title">
  <h2 class="vh" id="course-toolkit-title"><?= e($f['heading'] ?? 'Tools you will learn in the 60-day AI Masterclass') ?></h2>
  <div class="course-toolkit__rows">
<?php
$courseTools = array_values($f['tools'] ?? []);
$toolRows = array_chunk($courseTools, max(1, (int) ceil(count($courseTools) / 2)));
foreach ($toolRows as $row => $tools): ?>
    <div class="course-toolkit__viewport" data-toolkit-row data-direction="<?= $row === 0 ? '1' : '-1' ?>" tabindex="0" role="region" aria-label="Course tools, row <?= $row + 1 ?> of 2. Scroll horizontally to explore.">
      <div class="course-toolkit__track" data-toolkit-track>
        <ul class="course-toolkit__group" data-toolkit-group role="list">
<?php foreach ($tools as $tool):
    $mark = !empty($tool['logo']) ? image($tool['logo'], '52px') : brand((string) ($tool['brand'] ?? ''));
?>
          <li class="course-toolkit__item"><span class="course-toolkit__mark" aria-hidden="true"><?= $mark ?: icon((string) ($tool['symbol'] ?? 'gear-six')) ?></span><span><?= e($tool['name'] ?? '') ?></span></li>
<?php endforeach ?>
        </ul>
      </div>
    </div>
<?php endforeach ?>
  </div>
</section>
