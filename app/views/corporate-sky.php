<?php
/** Corporate training hero visual: "the mountain of work", an open line-art scene with no frame. A person sits at a
 *  desk beside a tall pile of papers (the work). By hand, the sun crosses the sky day after day (Mon, Tue, Wed) and
 *  only one paper a day reaches the Done column. Then the training arrives as a shooting star: the person lights up,
 *  the rest of the pile flies into Done in seconds while the sun barely moves, and the person cheers. Simple enough for
 *  a child to follow; no figures are claimed (days are shown by the sun, the result says minutes).
 *  SVG + CSS only (corporate.css, generated sheet/tick timings), 7s loop, theme colours only: before is dim cream and
 *  grey, with AI is neon. A Before / With AI switch names the two halves. Reduced motion shows the finished scene. */
$coStack = []; // eight papers, bottom (0) to top (7); the top ones leave first
for ($k = 0; $k < 8; $k++) $coStack[] = 321 - $k * 11;
?>
<div class="co-sky" role="img" aria-label="A person at a desk with a tall pile of work. By hand, days pass and the pile barely shrinks. After AI training, the whole pile is done in minutes and the person cheers.">
  <div class="co-sky__top" aria-hidden="true">
    <p class="co-sky__switch"><span class="co-sky__thumb"></span><span class="co-sky__seg co-sky__seg--before">Before</span><span class="co-sky__seg co-sky__seg--after">With AI</span></p>
    <p class="co-sky__cap co-sky__cap--before">Days of work, by hand</p>
    <p class="co-sky__cap co-sky__cap--after">Done in minutes</p>
  </div>
  <svg class="co-sky__svg" viewBox="0 0 690 420" aria-hidden="true">
    <defs>
      <linearGradient id="co-ground" x1="0" x2="1"><stop offset="0" stop-color="#c7d1bb" stop-opacity="0"/><stop offset=".5" stop-color="#c7d1bb" stop-opacity=".35"/><stop offset="1" stop-color="#c7d1bb" stop-opacity="0"/></linearGradient>
      <radialGradient id="co-glow"><stop offset="0" stop-color="var(--lime)" stop-opacity=".35"/><stop offset="1" stop-color="var(--lime)" stop-opacity="0"/></radialGradient>
    </defs>
    <!-- the sky: one arc the sun crosses once a day -->
    <path class="co-sk__arc" d="M90 330A230 230 0 0 1 550 330"/>
    <g class="co-sk__days"><text class="co-sk__day co-sk__day--1" x="320" y="86">Mon</text><text class="co-sk__day co-sk__day--2" x="320" y="86">Tue</text><text class="co-sk__day co-sk__day--3" x="320" y="86">Wed</text><text class="co-sk__day co-sk__day--ai" x="320" y="86">Minutes later</text></g>
    <g class="co-sk__sun"><circle cx="90" cy="330" r="22" class="co-sk__sunglow"/><circle cx="90" cy="330" r="11" class="co-sk__suncore"/></g>
    <!-- the ground and the desk -->
    <path d="M40 410H650" stroke="url(#co-ground)" stroke-width="1.5"/>
    <circle class="co-sk__aura" cx="300" cy="290" r="120" fill="url(#co-glow)"/>
    <path class="co-sk__chair" d="M252 332V286a48 48 0 0 1 96 0V332"/>
    <g class="co-sk__plant"><path d="M211 313c-2-14-12-20-22-21 1 11 9 18 22 21zM211 313c1-17 9-26 21-28 1 13-7 24-21 28z"/><rect x="200" y="312" width="22" height="18" rx="3"/></g>
    <g class="co-sk__person">
      <circle class="co-sk__body" cx="300" cy="244" r="22"/>
      <path class="co-sk__body" d="M262 332C262 297 277 280 300 280S338 297 338 332Z"/>
      <g class="co-sk__arms-down"><path d="M266 300C251 312 249 324 256 331M334 300C349 312 351 324 344 331"/></g>
      <g class="co-sk__arms-up"><path d="M268 292C251 276 245 257 250 238M332 292C349 276 355 257 350 238"/><circle cx="250" cy="234" r="5"/><circle cx="350" cy="234" r="5"/></g>
      <rect class="co-sk__laptop" x="268" y="296" width="64" height="36" rx="4"/>
      <path class="co-sk__logo" d="M300 305c.9 4.2 2.3 5.6 6.5 6.5-4.2.9-5.6 2.3-6.5 6.5-.9-4.2-2.3-5.6-6.5-6.5 4.2-.9 5.6-2.3 6.5-6.5z"/>
    </g>
    <rect class="co-sk__desk" x="180" y="330" width="300" height="8" rx="3"/>
    <path class="co-sk__legs" d="M196 338V410M464 338V410"/>
    <!-- the pile of work -->
<?php foreach ($coStack as $k => $y): ?>
    <g class="co-sk__sheet co-sk__sheet--<?= $k ?>" style="transform-origin: 424px <?= $y + 4.5 ?>px"><rect x="390" y="<?= $y ?>" width="68" height="9" rx="1.5" transform="rotate(<?= $k % 2 ? 1.6 : -1.4 ?> 424 <?= $y + 4.5 ?>)"/></g>
<?php endforeach ?>
    <!-- Done: one circle per paper -->
    <text class="co-sk__done-label" x="612" y="136">Done</text>
<?php for ($j = 0; $j < 8; $j++): ?>
    <g class="co-sk__tick co-sk__tick--<?= $j ?>" style="transform-origin: 612px <?= 322 - $j * 24 ?>px"><circle cx="612" cy="<?= 322 - $j * 24 ?>" r="9"/><path d="M607.5 <?= 322 - $j * 24 ?>.5l3 3 6-6.5"/></g>
<?php endfor ?>
    <!-- the training arrives -->
    <g class="co-sk__star"><path d="M300 210c1.6 7.7 4.3 10.4 12 12-7.7 1.6-10.4 4.3-12 12-1.6-7.7-4.3-10.4-12-12 7.7-1.6 10.4-4.3 12-12z"/></g>
    <circle class="co-sk__burst" cx="300" cy="262" r="46"/>
    <g class="co-sk__cheer"><rect x="566" y="84" width="92" height="26" rx="13"/><text x="612" y="101.5">All done!</text></g>
  </svg>
  <p class="co-sky__foot">Same people. Same work. <em>Trained to use AI.</em></p>
</div>
