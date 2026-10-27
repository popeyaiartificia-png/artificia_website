<section class="sec sec--faqs faq" id="faq">
  <div class="wrap faq__grid">
    <?= sec_head($f['heading'] ?? '') ?>
    <div class="qa">
<?php foreach ($f['items'] ?? [] as $q): ?>
      <details class="qa__item">
        <summary><span><?= e($q['question'] ?? '') ?></span><span class="qa__icon" aria-hidden="true"></span></summary>
        <div class="qa__a"><p><?= nl2br(e($q['answer'] ?? '')) ?></p></div>
      </details>
<?php endforeach ?>
    </div>
  </div>
</section>
