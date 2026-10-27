<section class="sec sec--process_steps steps" data-section="steps">
  <div class="wrap steps__grid">
    <div class="steps__head">
      <?= sec_head($f['heading'] ?? '') ?>
<?php if (!empty($f['intro'])): ?>
      <p class="lede"><?= e($f['intro']) ?></p>
<?php endif ?>
    </div>
    <div class="steps__track">
      <span class="steps__rail" aria-hidden="true"></span><span class="steps__fill" aria-hidden="true"></span>
      <ol class="steps__list">
<?php foreach ($f['steps'] ?? [] as $n => $st): ?>
        <li class="step"><span class="step__n" aria-hidden="true"><?= $n + 1 ?></span><h3 class="h3"><?= e($st['title'] ?? '') ?></h3><p><?= e($st['text'] ?? '') ?></p></li>
<?php endforeach ?>
      </ol>
    </div>
  </div>
</section>
