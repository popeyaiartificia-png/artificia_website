<?php
// Apply the owner's approved replacement to the original homepage CMS draft.
// Git deployments retain CMS data; preserve reviews an editor has since changed.
if (($page['slug'] ?? '') === 'home'
    && ($f['thought'] ?? '') === 'Good technology leaves room for *better work.*'
    && ($f['review_status'] ?? 'draft') === 'draft'
    && ($f['review_name'] ?? '') === 'Pavin'
    && ($f['review_company'] ?? '') === 'Orkay Tiles'
    && in_array($f['review_text'] ?? '', [
        'One partner for our website, automation and *AI.*',
        'One partner for all your IT needs, from software solutions to *automation.*',
    ], true)) {
    $f['review_text'] = 'Our one-stop solution for all *IT-related challenges.*';
    $f['review_name'] = 'Pavan Jivani';
    $f['review_status'] = 'approved';
}
$galleryItems = work_gallery_items($f['items'] ?? [], $page['slug'] ?? '');
$reviewApproved = ($f['review_status'] ?? 'draft') === 'approved';
$reviewDraft = !empty($f['review_text']) && !$reviewApproved;
$showReview = !empty($f['review_text']) && ($reviewApproved || (function_exists('config') && config()['env'] === 'local'));
?>
<section class="sec sec--work_gallery workscape" data-work-gallery>
<?php if (!$sharedTerrain): ?>
  <div class="workscape__terrain" aria-hidden="true"><canvas class="workscape__canvas"></canvas></div>
<?php endif ?>
  <div class="wrap workscape__head">
    <h2><?= heading_html($f['heading'] ?? '') ?></h2>
  </div>
  <div class="workscape__viewport">
    <div class="workscape__navigation">
      <span class="workscape__hint">Scroll to explore</span>
      <button type="button" data-work-prev aria-label="Previous gallery view"><?= icon('arrow-right') ?></button>
      <button type="button" data-work-next aria-label="Next gallery view"><?= icon('arrow-right') ?></button>
    </div>
    <div class="workscape__track">
<?php if ($showReview): ?>
      <figure class="workscape__thought workscape__thought--review">
<?php if ($reviewDraft): ?>
        <span class="workscape__draft">Draft review &middot; pending approval</span>
<?php endif ?>
        <blockquote><?= heading_html($f['review_text']) ?></blockquote>
<?php if (!empty($f['review_name']) || !empty($f['review_company'])): ?>
        <figcaption class="workscape__byline"><?php if (!empty($f['review_name'])): ?><span><?= e($f['review_name']) ?></span><?php endif ?><?php if (!empty($f['review_company'])): ?><span><?= !empty($f['review_name']) ? ', ' : '' ?><?= e($f['review_company']) ?></span><?php endif ?></figcaption>
<?php endif ?>
      </figure>
<?php elseif (!empty($f['thought'])): ?>
      <p class="workscape__thought"><?= heading_html($f['thought']) ?><svg viewBox="0 0 130 32" aria-hidden="true"><path d="M3 22C32 5 74 4 111 10L23 28 126 4M62 30l17-25"/></svg></p>
<?php endif ?>
      <ul class="workscape__list" aria-label="Websites and connected AI experiences">
<?php $brandsRendered = false;
foreach ($galleryItems as $i => $item):
    $visual = in_array($item['visual'] ?? '', ['website', 'mobile', 'ghl', 'conversations', 'workflow', 'support', 'knowledge', 'voice', 'assistants'], true) ? $item['visual'] : 'workflow';
    $linked = !empty($item['link']);
    if ($visual === 'assistants' && !$brandsRendered) {
        echo render_view('work-brands', []);
        $brandsRendered = true;
    }
?>
        <li class="workscape__item workscape__item--<?= e($visual) ?><?= in_array($visual, ['conversations', 'voice', 'knowledge', 'assistants'], true) ? ' workscape__item--service' : '' ?>">
          <<?= $linked ? 'a' : 'div' ?> class="workscape__link"<?php if ($linked): ?> href="<?= link_href($item['link']) ?>"<?php endif ?>>
            <div class="workscape__caption"><span><?= e($item['label'] ?? '') ?></span><?php if ($linked): ?><?= icon('arrow-up-right') ?><?php endif ?></div>
            <div class="workscape__picture">
<?php if (!empty($item['image'])): ?>
              <?= image($item['image'], '(min-width: 1100px) 42vw, (min-width: 640px) 50vw, 100vw') ?>
<?php else: ?>
              <?= render_view('work-visual', ['visual' => $visual]) ?>
<?php endif ?>
            </div>
            <h3 class="workscape__title"><?= e($item['title'] ?? '') ?></h3>
          </<?= $linked ? 'a' : 'div' ?>>
        </li>
<?php endforeach ?>
<?php if (!$brandsRendered): ?>
        <?= render_view('work-brands', []) ?>
<?php endif ?>
      </ul>
    </div>
  </div>
</section>
