<?php
/** The hero visual: one team card in two states, before and after the training. An "AI training" line sweeps across it
 *  and everything it passes turns from manual work into AI-assisted work; visitors can drag it themselves.
 *  The rows restate the approved department copy. CSS animates it; corporate.js adds the drag. */
$coPerson = '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="7.5" r="4"/><path d="M4 21c0-4.4 3.6-7.5 8-7.5s8 3.1 8 7.5z"/></svg>';
$coRows = [
    ['Sales', 'Proposals written from a blank page', 'Proposals drafted in your voice'],
    ['Finance', 'Invoices typed in line by line', 'Invoice details extracted for you'],
    ['Support', 'Same questions answered from memory', 'Answers drafted from your documents'],
    ['Operations', 'Status reports re-typed by hand', 'Reports without re-typing'],
];
$coLayer = function (bool $after) use ($coPerson, $coRows): string {
    $h = '<div class="co-ba__layer co-ba__layer--' . ($after ? 'after' : 'before') . '">';
    $h .= '<div class="co-ba__top"><span class="co-ba__team">' . str_repeat($coPerson, 5) . '<b>Your team</b></span>'
        . '<span class="co-ba__tag">' . ($after ? 'After the training' : 'Before the training') . '</span></div><ul class="co-ba__rows">';
    foreach ($coRows as [$team, $before, $afterText]) {
        $h .= '<li><span class="co-ba__ico" aria-hidden="true"></span><span><small>' . e($team) . '</small>' . e($after ? $afterText : $before) . '</span></li>';
    }
    return $h . '</ul><p class="co-ba__status"><span aria-hidden="true"></span>' . ($after ? 'AI-ready. Trained on your own files.' : 'Manual work, every day.') . '</p></div>';
};
?>
<div class="co-ba" data-co-ba>
  <div class="co-ba__card">
    <?= $coLayer(false) ?>
    <?= $coLayer(true) ?>
    <div class="co-ba__beam" aria-hidden="true">
      <span class="co-ba__knob"><svg viewBox="0 0 24 24"><path d="M12 0c1.6 7.7 4.3 10.4 12 12-7.7 1.6-10.4 4.3-12 12-1.6-7.7-4.3-10.4-12-12C7.7 10.4 10.4 7.7 12 0z"/></svg></span>
      <span class="co-ba__label">AI training</span>
    </div>
  </div>
  <p class="co-ba__hint" aria-hidden="true">Watch the training sweep through your team, or drag the line to compare.</p>
</div>
