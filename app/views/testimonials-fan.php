<?php
$items = array_values($f['items'] ?? []);
$videos = array_values(array_filter($items, fn($it) => video_embed_src((string) ($it['video_url'] ?? '')) !== ''));
$written = array_values(array_filter($items, fn($it) => !empty($it['quote']) && video_embed_src((string) ($it['video_url'] ?? '')) === ''));
$hasPhotoNotes = array_filter($items, fn($it) => trim((string) ($it['photo_note'] ?? '')) !== '') !== [];
// Lead with one video in the centre, with the other stories paired on either side.
$cards = count($videos) === 2 && count($written) === 3
    ? [$videos[0], $videos[1], $written[0], $written[1], $written[2]]
    : $items;
$positions = count($videos) === 2 && count($written) === 3 ? [0, .86, -.86, -1.64, 1.64] : [];
?>
<section class="sec sec--testimonials voices" data-testimonial-fan aria-label="Testimonials">
  <canvas class="topo-bg" aria-hidden="true"></canvas>
  <div class="voices__stage">
  <div class="voices__heading wrap">
    <p class="voices__eyebrow">Testimonials</p>
    <h2><?= heading_html($f['heading'] ?? 'Hear what *people say*') ?></h2>
  </div>
  <ul class="voices__fan" aria-label="Reviews from our learners and professionals">
<?php foreach ($cards as $i => $it):
    $name = (string) ($it['name'] ?? 'Artificia AI learner');
    $src = video_embed_src((string) ($it['video_url'] ?? ''));
    $photoNote = trim((string) ($it['photo_note'] ?? ''));
    $x = $positions[$i] ?? ($i === 0 ? 0 : ceil($i / 2) * ($i % 2 ? .82 : -.82));
    $depth = (int) ceil(abs($x));
    $angle = $x * 11;
    $drop = abs($x) * abs($x) * 30;
    $z = max(1, 5 - $depth);
    $words = preg_split('/\s+/', trim($name), -1, PREG_SPLIT_NO_EMPTY);
    $initials = $words ? mb_strtoupper(mb_substr($words[0], 0, 1) . (count($words) > 1 ? mb_substr(end($words), 0, 1) : '')) : '';
?>
    <li class="voices__card" style="--fan-x: <?= $x ?>; --fan-angle: <?= $angle ?>deg; --fan-drop: <?= $drop ?>px; --fan-z: <?= $z ?>; --fan-depth: <?= $depth ?>">
      <a class="voices__surface<?= empty($it['photo']) ? ' voices__surface--type' : '' ?>" href="<?= $src !== '' ? e($src) : '#written-testimonials' ?>"<?= $src !== '' ? ' target="_blank" rel="noopener"' : '' ?>
        data-review-name="<?= e($name) ?>" data-review-role="<?= e($it['role'] ?? '') ?>"<?= $photoNote !== '' ? ' data-review-note="' . e($photoNote) . '"' : '' ?><?= $src !== '' ? ' data-review-src="' . e($src) . '"' : ' data-review-quote="' . e($it['quote'] ?? '') . '"' ?>>
<?php if (!empty($it['photo'])): ?>
        <?= image($it['photo'], '(min-width: 900px) min(23vw, 460px), min(88vw, 380px)') ?>
<?php if ($photoNote !== ''): ?>
        <span class="voices__photo-note<?= $x > 0 ? ' voices__photo-note--right' : '' ?>" title="<?= e($photoNote) ?>">Illustrative portrait<span class="vh">: <?= e($photoNote) ?></span></span>
<?php endif ?>
<?php else: ?>
        <span class="voices__initials" aria-hidden="true"><?= e($initials) ?></span>
        <span class="voices__excerpt"><?= e($it['quote'] ?? '') ?></span>
<?php endif ?>
        <span class="voices__caption"><span class="voices__name"><?= e($name) ?></span><span class="voices__role"><?= e($it['role'] ?? '') ?></span></span>
        <span class="voices__action"><?= icon($src !== '' ? 'play' : 'arrow-up-right') ?><span><?= $src !== '' ? 'Watch story' : 'Read review' ?></span></span>
      </a>
<?php if ($i === 0 && count($cards) > 1): ?>
      <span class="voices__stamp" aria-hidden="true"><span>“</span>Many stories.<br>One Artificia.</span>
<?php endif ?>
    </li>
<?php endforeach ?>
  </ul>
  <p class="voices__hint"><?= $hasPhotoNotes ? 'Two portraits are AI-generated.' : 'Real people. In their own words.' ?></p>
  </div>
  <noscript>
    <div id="written-testimonials" class="wrap wits wits--quote">
<?php foreach ($written as $it): ?>
      <figure class="wit"><blockquote class="wit__q"><?= e($it['quote']) ?></blockquote><figcaption><strong><?= e($it['name'] ?? '') ?></strong><p><?= e($it['role'] ?? '') ?></p><?php if (trim((string) ($it['photo_note'] ?? '')) !== ''): ?><p class="wit__photo-note"><?= e($it['photo_note']) ?></p><?php endif ?></figcaption></figure>
<?php endforeach ?>
    </div>
  </noscript>
</section>
