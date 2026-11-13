<?php
/** Corporate training programmes as a row of tall panels: the one you point at, focus or tap opens wide (corporate.js keeps
 *  the last one open); the rest stand as spines. Below 1100px, and without JS, every panel is open and they stack.
 *  solutions fields: title, text (who it is for), includes (one per line), link, link_label. */
$items = array_values($items);
$n = count($items);
$check = '<svg class="co-prog__check" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>';
// ponytail: an icon and a drawing per programme, keyed by position; move to the item's own field if the CMS reorders them.
$marks = ['briefcase', 'users-three', 'buildings', 'tree-structure', 'magnifying-glass', 'graduation-cap'];
$arts = [
    // leadership: an impact/effort map, the quick win, the road to the goal
    '<path class="p-l" d="M30 20v128h150"/><path class="p-s p-d" d="M105 24v120M34 84h142"/><text class="p-t" x="105" y="166">EFFORT</text><text class="p-t" x="16" y="84" transform="rotate(-90 16 84)">IMPACT</text><circle class="p-l" cx="58" cy="122" r="4"/><circle class="p-l" cx="82" cy="104" r="4"/><circle class="p-l" cx="138" cy="126" r="4"/><circle class="p-l" cx="152" cy="104" r="4"/><circle class="p-l" cx="128" cy="62" r="4"/><path class="p-l p-d" d="M78 42c22-6 44 4 76-8"/><circle class="p-f" cx="68" cy="46" r="10"/><path class="p-k" d="M63 46l3.5 3.5 6.5-7"/><path class="p-l" d="M162 54V20"/><path class="p-f" d="M162 20h16l-5 6 5 6h-16z"/>',
    // teams: a prompt, an answer, the next prompt
    '<rect class="p-l" x="20" y="16" width="160" height="148" rx="8"/><path class="p-l" d="M20 36h160"/><circle class="p-f" cx="32" cy="26" r="2.6"/><circle class="p-f" cx="41" cy="26" r="2.6"/><circle class="p-f" cx="50" cy="26" r="2.6"/><rect class="p-f" x="92" y="48" width="76" height="20" rx="10"/><path class="p-k" d="M103 58h50"/><path class="p-f" d="M36 81l2.6 6.4L45 90l-6.4 2.6L36 99l-2.6-6.4L27 90l6.4-2.6z"/><path class="p-l" d="M54 84h100M54 96h86M54 108h94"/><path class="p-s" d="M54 120h58"/><rect class="p-l" x="30" y="134" width="140" height="20" rx="10"/><path class="p-s" d="M42 144h52"/><circle class="p-f" cx="159" cy="144" r="6.5"/><path class="p-k" d="M156 144h6m-2.5-2.8 2.8 2.8-2.8 2.8"/>',
    // departments: one hub, every team
    '<path class="p-s" d="M100 90V28M100 90l54-31M100 90l54 31M100 90v62M100 90l-54 31M100 90 46 59"/><circle class="p-s p-d" cx="100" cy="90" r="34"/><circle class="p-f" cx="100" cy="90" r="19"/><text class="p-tk" x="100" y="94.5">AI</text><rect class="p-f" x="80" y="20" width="40" height="16" rx="8"/><text class="p-tk p-sm" x="100" y="30.6">SALES</text><rect class="p-n" x="134" y="51" width="40" height="16" rx="8"/><text class="p-t" x="154" y="61.6">MKT</text><rect class="p-n" x="134" y="113" width="40" height="16" rx="8"/><text class="p-t" x="154" y="123.6">FIN</text><rect class="p-n" x="80" y="144" width="40" height="16" rx="8"/><text class="p-t" x="100" y="154.6">HR</text><rect class="p-n" x="26" y="113" width="40" height="16" rx="8"/><text class="p-t" x="46" y="123.6">OPS</text><rect class="p-n" x="26" y="51" width="40" height="16" rx="8"/><text class="p-t" x="46" y="61.6">IT</text>',
    // automation: trigger, agent, tools, a person approves, results checked back
    '<text class="p-t" x="24" y="70">TRIGGER</text><circle class="p-l" cx="24" cy="90" r="12"/><path class="p-f" d="M26 81l-7 11h5l-2 8 7-11h-5z"/><path class="p-l" d="M37 90h18m-4-4 4 4-4 4"/><rect class="p-f" x="58" y="68" width="44" height="44" rx="10"/><text class="p-tk p-sm" x="80" y="93">AGENT</text><path class="p-l" d="M102 90h10M112 56v68M112 56h10M112 90h10M112 124h10M152 56h8M152 90h8M152 124h8M160 56v68M160 90h7"/><rect class="p-n" x="122" y="47" width="30" height="18" rx="4"/><rect class="p-n" x="122" y="81" width="30" height="18" rx="4"/><rect class="p-n" x="122" y="115" width="30" height="18" rx="4"/><path class="p-s" d="M129 56h16M129 90h12M129 124h14"/><text class="p-t" x="179" y="70">APPROVE</text><circle class="p-f" cx="179" cy="90" r="11"/><path class="p-k" d="M174 90l3.5 3.5 6.5-7"/><path class="p-s p-d" d="M179 102v50H80v-36m-4 5 4-5 4 5"/><text class="p-t" x="130" y="168">EVALUATE</text>',
    // marketing and search: a query, the AI answer that cites you, the results under it
    '<rect class="p-l" x="18" y="16" width="164" height="26" rx="13"/><circle class="p-l" cx="33" cy="29" r="5"/><path class="p-l" d="M36.5 32.5l4 4"/><path class="p-s" d="M48 29h72"/><rect class="p-f" x="18" y="52" width="164" height="56" rx="6"/><path class="p-kf" d="M32 59l2 5 5 2-5 2-2 5-2-5-5-2 5-2z"/><path class="p-k" d="M46 66h96M30 82h136M30 94h104"/><rect class="p-l" x="18" y="118" width="12" height="12" rx="3"/><path class="p-l" d="M38 121h80"/><path class="p-s" d="M38 128h124"/><rect class="p-l" x="18" y="144" width="12" height="12" rx="3"/><path class="p-l" d="M38 147h62"/><path class="p-s" d="M38 154h112"/>',
    // academy: modules that step up, a person on each, the cap at the top
    '<path class="p-l" d="M14 160h172"/><rect class="p-l" x="22" y="130" width="34" height="30"/><rect class="p-l" x="62" y="110" width="34" height="50"/><rect class="p-l" x="102" y="90" width="34" height="70"/><rect class="p-f" x="142" y="66" width="34" height="94"/><text class="p-t" x="39" y="151">01</text><text class="p-t" x="79" y="151">02</text><text class="p-t" x="119" y="151">03</text><text class="p-tk p-sm" x="159" y="151">04</text><circle class="p-l" cx="39" cy="120" r="4"/><path class="p-l" d="M32 130a7 7 0 0 1 14 0"/><circle class="p-l" cx="79" cy="100" r="4"/><path class="p-l" d="M72 110a7 7 0 0 1 14 0"/><circle class="p-l" cx="119" cy="80" r="4"/><path class="p-l" d="M112 90a7 7 0 0 1 14 0"/><path class="p-f" d="M141 42l18-9 18 9-18 9z"/><path class="p-l" d="M149 47v8c0 4 20 4 20 0v-8M177 42v12"/><circle class="p-f" cx="177" cy="56" r="2"/>',
];
?>
<section class="sec co-progs" id="programmes">
  <canvas class="topo-bg" aria-hidden="true"></canvas>
  <div class="wrap">
    <header class="co-head">
      <p class="sol-eyebrow"><span class="sol-dot" aria-hidden="true"></span>Programmes <span class="sol-eyebrow__count"><?= sprintf('%02d', $n) ?></span></p>
      <?= sec_head($f['heading'] ?? '') ?>
<?php if (!empty($f['intro'])): ?>
      <p class="co-head__intro"><?= e($f['intro']) ?></p>
<?php endif ?>
    </header>
    <ol class="co-progs__list" data-co-progs>
<?php foreach ($items as $i => $item): $includes = array_values(array_filter(array_map('trim', explode("\n", (string) ($item['includes'] ?? ''))), 'strlen')); ?>
      <li class="co-prog<?= $i ? '' : ' is-on' ?>">
        <div class="co-prog__spine" aria-hidden="true"><span class="co-prog__n"><?= sprintf('%02d', $i + 1) ?></span><?= icon($marks[$i] ?? '', 'icon co-prog__mark') ?><span class="co-prog__vt"><?= e($item['title'] ?? '') ?></span></div>
        <div class="co-prog__body">
          <p class="co-prog__n"><?= sprintf('%02d', $i + 1) ?> <span>/ <?= sprintf('%02d', $n) ?></span></p>
          <h3 class="co-prog__t"><?= e($item['title'] ?? '') ?></h3>
<?php if (!empty($item['text'])): ?>
          <p class="co-prog__for"><?= e($item['text']) ?></p>
<?php endif ?>
<?php if ($includes): ?>
          <ul class="co-prog__inc">
<?php foreach ($includes as $k => $line): ?>
            <li style="--k: <?= $k ?>"><?= $check ?><?= e($line) ?></li>
<?php endforeach ?>
          </ul>
<?php endif ?>
<?php if (!empty($item['link'])): ?>
          <a class="co-prog__go" href="<?= link_href($item['link']) ?>"><?= e(($item['link_label'] ?? '') !== '' ? $item['link_label'] : 'Ask about this programme') ?><span class="co-prog__arrow" aria-hidden="true"><?= icon('arrow-up-right') ?></span></a>
<?php endif ?>
<?php if (isset($arts[$i])): ?>
          <svg class="co-prog__art" viewBox="0 0 200 180" aria-hidden="true" focusable="false"><?= $arts[$i] ?></svg>
<?php endif ?>
        </div>
        <span class="co-prog__big" aria-hidden="true"><?= sprintf('%02d', $i + 1) ?></span>
      </li>
<?php endforeach ?>
    </ol>
  </div>
</section>
