<?php $thoughts = preg_split('/(?<=[.!?])\s+/u', trim($p['quote']), -1, PREG_SPLIT_NO_EMPTY); ?>
<div class="person__quote person__quote--manifesto">
  <p class="philosophy__label">Guiding philosophy</p>
  <div class="philosophy__layout">
    <blockquote class="philosophy__words">
      <ol>
<?php foreach ($thoughts as $n => $thought): ?>
        <li style="--thought-index: <?= $n ?>"<?= $n === count($thoughts) - 1 ? ' class="philosophy__final"' : '' ?>><span class="philosophy__number" aria-hidden="true"><?= sprintf('%02d', $n + 1) ?></span><span class="philosophy__sentence"><?= e($thought) ?></span></li>
<?php endforeach ?>
      </ol>
    </blockquote>
  </div>
</div>
