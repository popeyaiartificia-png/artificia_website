<?php
$items = $f['items'] ?? [];
$families = [
    'workflow' => ['Operations', 'Tools connected. Work flowing.', 'Workflows / Integrations'],
    'agentic' => ['Intelligence', 'Your knowledge. Put to work.', 'AI agents / Knowledge'],
    'knowledge' => ['Knowledge systems', 'Your knowledge. Put to work.', 'Documents / AI answers'],
    'software' => ['Digital products', 'Built around your business.', 'Websites / Apps / Dashboards'],
    'marketing' => ['Customer journeys', 'Every conversation connected.', 'CRM / Follow-ups'],
    'search' => ['Discoverability', 'Be found. Be the answer.', 'Search / AI discovery'],
    'cloud' => ['Infrastructure', 'AI, on your terms.', 'Cloud / Private deployment'],
];
?>
<section class="sec sec--solutions solutions" data-solutions>
  <canvas class="topo-bg" aria-hidden="true"></canvas>
  <div class="wrap">
    <div class="solutions__eyebrow"><span class="solutions__signal" aria-hidden="true"></span> OUR SOLUTIONS <span class="solutions__index"><?= sprintf('%02d', count($items)) ?> ways forward</span></div>
    <div class="solutions__head">
      <?= sec_head($f['heading'] ?? '') ?>
<?php if (!empty($f['intro'])): ?>
      <p class="solutions__intro"><?= e($f['intro']) ?></p>
<?php endif ?>
    </div>
    <ul class="solutions__track" id="solutions-<?= $s ?>" aria-label="Business solutions">
<?php foreach ($items as $i => $item):
    $linked = !empty($item['link']);
    $visual = $item['visual'] ?? 'workflow';
    $family = $families[$visual] ?? ['Business systems', 'Built for the way you work.', 'Custom solutions'];
?>
      <li class="solution solution--<?= e($visual) ?>">
        <<?= $linked ? 'a' : 'div' ?> class="solution__link"<?php if ($linked): ?> href="<?= link_href($item['link']) ?>"<?php endif ?>>
          <div class="solution__meta"><span><?= sprintf('%02d', $i + 1) ?> / <?= e($family[0]) ?></span><span class="solution__spark" aria-hidden="true">&#10035;</span></div>
          <div class="solution__body">
            <h3 class="solution__title"><?= e($item['title'] ?? '') ?></h3>
            <p class="solution__text"><?= e($item['text'] ?? '') ?></p>
          </div>
          <div class="solution__visual" aria-hidden="true">
            <div class="solution__halo"></div>
            <?= render_view('solution-visual', ['visual' => $visual]) ?>
            <span class="solution__caption"><?= e($family[2]) ?></span>
          </div>
          <div class="solution__bottom">
            <span><?= e($family[1]) ?></span>
<?php if ($linked): ?>
            <span class="solution__go" aria-hidden="true"><?= icon('arrow-up-right') ?></span>
<?php endif ?>
          </div>
        </<?= $linked ? 'a' : 'div' ?>>
      </li>
<?php endforeach ?>
    </ul>
    <div class="solutions__foot">
      <p class="solutions__note"><span aria-hidden="true">&#8627;</span> One connected approach. Built around you.</p>
<?php if (!empty($f['link']) && !empty($f['link_label'])): ?>
      <a class="solutions__all" href="<?= link_href($f['link']) ?>"><?= e($f['link_label']) ?><?= icon('arrow-up-right') ?></a>
<?php endif ?>
    </div>
  </div>
</section>
