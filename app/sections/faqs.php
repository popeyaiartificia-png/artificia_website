<section class="sec sec--faqs faq" id="faq">
<?php if (($page['sections'][0]['fields']['variant'] ?? '') === 'solutions'): ?>
  <canvas class="topo-bg" aria-hidden="true"></canvas>
<?php endif ?>
  <div class="wrap faq__grid">
    <?= sec_head($f['heading'] ?? '') ?>
    <div class="qa">
<?php foreach ($f['items'] ?? [] as $q): ?>
      <details class="qa__item">
        <summary><span><span class="qa__q"><?= e($q['question'] ?? '') ?></span></span><span class="qa__icon" aria-hidden="true"></span></summary>
        <div class="qa__a"><p><?= nl2br(e($q['answer'] ?? '')) ?></p></div>
      </details>
<?php endforeach ?>
    </div>
  </div>
</section>
