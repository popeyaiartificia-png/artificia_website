<?php
// Videos and written quotes are laid out as two rows so cards of one kind line up.
// Only Drive and YouTube links become players; anything else is treated as a written quote.
$items = array_map(fn($it) => ['src' => video_embed_src((string) ($it['video_url'] ?? ''))] + $it, $f['items'] ?? []);
$videos = array_values(array_filter($items, fn($it) => $it['src'] !== ''));
$quotes = array_values(array_filter($items, fn($it) => $it['src'] === ''));
$sig = function (array $it, bool $photo): string {
    return '<div class="wit__sig">' . ($photo && !empty($it['photo']) ? image($it['photo'], '48px', false, 'wit__photo') : '')
        . '<div><p class="wit__name">' . e($it['name'] ?? '') . '</p><p class="wit__role">' . e($it['role'] ?? '') . '</p></div></div>';
};
?>
<section class="sec sec--testimonials quotes">
  <div class="wrap">
    <?= sec_head($f['heading'] ?? '') ?>
<?php if ($videos): ?>
    <ul class="wits wits--video" tabindex="0" aria-label="Video testimonials">
<?php foreach ($videos as $it): $name = ($it['name'] ?? '') !== '' ? $it['name'] : 'a student'; ?>
      <li class="wit">
        <button class="vid<?= empty($it['photo']) ? ' vid--blank' : '' ?>" type="button" data-src="<?= e($it['src']) ?>" data-title="Video from <?= e($name) ?>" aria-label="Play video from <?= e($name) ?>">
          <?= !empty($it['photo']) ? image($it['photo'], '(min-width: 900px) 360px, 84vw') : '<span class="vid__name" aria-hidden="true">' . e($name) . '</span>' ?>
          <span class="vid__play"><?= icon('play') ?>Play video</span>
        </button>
        <?= $sig($it, false) ?>
      </li>
<?php endforeach ?>
    </ul>
<?php endif ?>
<?php if ($quotes): ?>
    <ul class="wits wits--quote">
<?php foreach ($quotes as $it): ?>
      <li class="wit">
<?php if (!empty($it['quote'])): ?>
        <blockquote class="wit__q"><p><?= e($it['quote']) ?></p></blockquote>
<?php endif ?>
        <?= $sig($it, true) ?>
      </li>
<?php endforeach ?>
    </ul>
<?php endif ?>
  </div>
</section>
