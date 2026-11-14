<?php
/** One continuous ribbon connects scattered ideas with finished AI work. */
$learningCurves = [
    [[185, 505], [450, 610], [800, 490], [735, 385]],
    [[735, 385], [670, 280], [260, 335], [320, 220]],
    [[320, 220], [380, 105], [610, 155], [665, 85]],
];
$learningPoint = static function (array $curve, float $t): array {
    $u = 1 - $t;
    return [
        $u ** 3 * $curve[0][0] + 3 * $u ** 2 * $t * $curve[1][0] + 3 * $u * $t ** 2 * $curve[2][0] + $t ** 3 * $curve[3][0],
        $u ** 3 * $curve[0][1] + 3 * $u ** 2 * $t * $curve[1][1] + 3 * $u * $t ** 2 * $curve[2][1] + $t ** 3 * $curve[3][1],
    ];
};
$learningSamples = [];
$learningDistance = 0.0;
$learningLast = null;
foreach ($learningCurves as $curve) {
    for ($j = 0; $j <= 240; $j++) {
        $point = $learningPoint($curve, $j / 240);
        if ($learningLast !== null) $learningDistance += hypot($point[0] - $learningLast[0], $point[1] - $learningLast[1]);
        $learningSamples[] = [$learningDistance, $point[0], $point[1]];
        $learningLast = $point;
    }
}
$learningAt = static function (float $distance) use ($learningSamples): array {
    foreach ($learningSamples as $j => $sample) {
        if ($sample[0] < $distance) continue;
        if ($j === 0) return [$sample[1], $sample[2]];
        $prior = $learningSamples[$j - 1];
        $mix = ($distance - $prior[0]) / max(.001, $sample[0] - $prior[0]);
        return [$prior[1] + ($sample[1] - $prior[1]) * $mix, $prior[2] + ($sample[2] - $prior[2]) * $mix];
    }
    $last = end($learningSamples);
    return [$last[1], $last[2]];
};
$learningPoly = static fn(array $points): string => implode(' ', array_map(static fn(array $p): string => number_format($p[0], 2, '.', '') . ',' . number_format($p[1], 2, '.', ''), $points));
$learningLeft = $learningRight = [];
$learningSubdivision = 360; // Dense shared cross-sections keep both ribbon edges smooth.
for ($sample = 0; $sample <= $learningSubdivision; $sample++) {
    $fraction = $sample / $learningSubdivision;
    $distance = $learningDistance * $fraction;
    $center = $learningAt($distance);
    $before = $learningAt(max(0, $distance - .8));
    $after = $learningAt(min($learningDistance, $distance + .8));
    // Unproject the tangent, take its normal, then project into the paper plane.
    $dx = $after[0] - $before[0];
    $dy = ($after[1] - $before[1]) / .6;
    $length = max(.001, hypot($dx, $dy));
    $halfWidth = 55 - 13 * $fraction;
    $normal = [-$dy / $length * $halfWidth, $dx / $length * $halfWidth * .6];
    $learningLeft[] = [$center[0] - $normal[0], $center[1] - $normal[1]];
    $learningRight[] = [$center[0] + $normal[0], $center[1] + $normal[1]];
}
$learningRibbon = array_merge($learningLeft, array_reverse($learningRight));
$learningDrop = static fn(array $p): array => [$p[0], $p[1] + 6];
$learningLeftWall = array_merge($learningLeft, array_reverse(array_map($learningDrop, $learningLeft)));
$learningRightWall = array_merge($learningRight, array_reverse(array_map($learningDrop, $learningRight)));
$learningPath = 'M185 505 C450 610 800 490 735 385 C670 280 260 335 320 220 C380 105 610 155 665 85';
?>
<svg class="learning-sculpture" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 650" aria-hidden="true" focusable="false">
  <defs>
    <linearGradient id="learning-paper-light" gradientUnits="userSpaceOnUse" x1="340" y1="65" x2="540" y2="565"><stop stop-color="#f0f3e7"/><stop offset=".35" stop-color="#d7dfc8"/><stop offset=".61" stop-color="#eef1e3"/><stop offset="1" stop-color="#c5d0b4"/></linearGradient>
    <linearGradient id="learning-paper-lime" gradientUnits="userSpaceOnUse" x1="300" y1="70" x2="620" y2="550"><stop stop-color="var(--lime-hi)"/><stop offset="1" stop-color="var(--lime)"/></linearGradient>
    <linearGradient id="learning-ribbon-edge" x1="0" y1="0" x2="0" y2="1"><stop stop-color="#a3b080"/><stop offset=".55" stop-color="#607343"/><stop offset="1" stop-color="#25351b"/></linearGradient>
    <linearGradient id="learning-before-paper" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#35432c"/><stop offset="1" stop-color="#1c2919"/></linearGradient>
    <linearGradient id="learning-after-paper" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#f1f5e8"/><stop offset="1" stop-color="#cfdcbc"/></linearGradient>
    <radialGradient id="learning-ground-shadow"><stop stop-color="#080d07" stop-opacity=".55"/><stop offset="1" stop-color="#080d07" stop-opacity="0"/></radialGradient>
    <filter id="learning-energy-glow" x="-10%" y="-10%" width="120%" height="120%" color-interpolation-filters="sRGB"><feGaussianBlur stdDeviation="2.8"/></filter>
    <radialGradient id="learning-sheen-light"><stop stop-color="#f7ffe0" stop-opacity=".6"/><stop offset=".45" stop-color="#f7ffe0" stop-opacity=".24"/><stop offset="1" stop-color="#f7ffe0" stop-opacity="0"/></radialGradient>
    <clipPath id="learning-ribbon-clip"><polygon points="<?= $learningPoly($learningRibbon) ?>"/></clipPath>
  </defs>
  <ellipse cx="470" cy="557" rx="342" ry="49" fill="url(#learning-ground-shadow)"/>
  <path class="learning-ribbon__shadow" d="<?= $learningPath ?>" transform="translate(4 18)" fill="none" stroke="#0a1107" stroke-width="34" stroke-linecap="round" opacity=".22"/>
  <polygon class="learning-ribbon__side learning-ribbon__side--left" points="<?= $learningPoly($learningLeftWall) ?>" fill="#495c33"/>
  <polygon class="learning-ribbon__side learning-ribbon__side--right" points="<?= $learningPoly($learningRightWall) ?>" fill="url(#learning-ribbon-edge)"/>
  <polygon class="learning-ribbon__underlay" points="<?= $learningPoly($learningRibbon) ?>" fill="url(#learning-paper-light)" stroke="#d2dcc0" stroke-width=".65" stroke-linejoin="round"/>
  <path class="learning-fill" d="<?= $learningPath ?>" pathLength="1" fill="none" stroke="url(#learning-paper-lime)" stroke-width="130" stroke-linecap="butt" stroke-dasharray="1" stroke-dashoffset="1" clip-path="url(#learning-ribbon-clip)"/>
  <g class="learning-energy" clip-path="url(#learning-ribbon-clip)">
    <ellipse class="learning-energy-sheen" cx="185" cy="505" rx="76" ry="48" fill="url(#learning-sheen-light)"/>
    <path class="learning-energy-halo" d="<?= $learningPath ?>" pathLength="1" fill="none" stroke="#f2ffb4" stroke-width="15" stroke-linecap="round" stroke-dasharray=".08 .92" stroke-dashoffset="1" opacity=".48" filter="url(#learning-energy-glow)"/>
    <path class="learning-energy-core" d="<?= $learningPath ?>" pathLength="1" fill="none" stroke="#fbffe9" stroke-width="2" stroke-linecap="round" stroke-dasharray=".08 .92" stroke-dashoffset="1" opacity=".95"/>
  </g>
  <polyline class="learning-ribbon__rim learning-ribbon__rim--left" points="<?= $learningPoly($learningLeft) ?>" fill="none" stroke="#f6f8ed" stroke-width=".8" opacity=".75"/>
  <polyline class="learning-ribbon__rim learning-ribbon__rim--right" points="<?= $learningPoly($learningRight) ?>" fill="none" stroke="#c7d4ad" stroke-width=".8" opacity=".75"/>
  <path class="learning-trace" d="<?= $learningPath ?>" pathLength="1" fill="none" stroke="var(--lime)" stroke-width="1.7" stroke-linecap="round" opacity=".8"/>
  <circle class="learning-marker" cx="185" cy="505" r="4.5" fill="var(--lime)" stroke="#344820" stroke-width="2"/>
  <g class="learning-orbit-particles" fill="#f2ffc5">
    <circle class="learning-spark" data-spark="0" cx="185" cy="505" r="3.2" opacity=".9"/>
    <circle class="learning-spark" data-spark="1" cx="185" cy="505" r="2.4" opacity=".7"/>
    <circle class="learning-spark" data-spark="2" cx="185" cy="505" r="1.7" opacity=".5"/>
  </g>
  <g class="learning-before-art" transform="translate(181 510)">
    <ellipse cx="4" cy="63" rx="84" ry="18" fill="#0b1308" opacity=".35"/>
    <g class="learning-before-art__sheet" transform="translate(-63 -34) rotate(-19)">
      <path d="M0 0H59L75 16V91H0Z" fill="url(#learning-before-paper)" stroke="#70835b" stroke-width="1"/>
      <path d="M59 0V16H75" fill="#45573a" stroke="#70835b" stroke-width=".75"/>
      <path d="M13 24H49M13 32H57M13 40H42M13 65H58M13 73H48" fill="none" stroke="#7a8c67" stroke-width="2" opacity=".52"/>
    </g>
    <g class="learning-before-art__sheet" transform="translate(25 -24) rotate(18)">
      <path d="M0 0H60V82H0Z" fill="url(#learning-before-paper)" stroke="#7d8c67" stroke-width="1"/>
      <path d="M12 18H47M12 27H37M12 55H46M12 64H39" fill="none" stroke="#84936f" stroke-width="2" opacity=".5"/>
      <path d="m24 37 11 8m0-8-11 8" fill="none" stroke="#9cad83" stroke-width="1.8" opacity=".6"/>
    </g>
    <g class="learning-before-art__sheet" transform="translate(-30 -48) rotate(3)">
      <path d="M0 0H67V103H0Z" fill="url(#learning-before-paper)" stroke="#8d9c77" stroke-width="1.2"/>
      <path d="M13 17H52M13 24H44M13 85H53M13 92H38" fill="none" stroke="#899b72" stroke-width="2" opacity=".55"/>
      <path d="M25 44C25 34 44 33 45 43C46 50 35 51 35 59" fill="none" stroke="#aabb8e" stroke-width="3" stroke-linecap="round"/>
      <circle cx="35" cy="69" r="1.8" fill="#aabb8e"/>
    </g>
  </g>
  <circle class="learning-arrival-ring" cx="665" cy="85" r="46" fill="none" stroke="var(--lime)" stroke-width="1.6" opacity="0"/>
  <g class="learning-after-art" transform="translate(665 85)">
    <ellipse cx="12" cy="44" rx="95" ry="14" fill="#0b1308" opacity=".25"/>
    <g class="learning-after-art__website" transform="translate(-76 -58) rotate(-6)">
      <path d="M0 6Q0 0 6 0H146Q152 0 152 6V102H0Z" fill="#15220f" opacity=".4" transform="translate(4 5)"/>
      <path d="M0 6Q0 0 6 0H146Q152 0 152 6V102H0Z" fill="url(#learning-after-paper)" stroke="#e4edd6" stroke-width="1"/>
      <path d="M0 6Q0 0 6 0H146Q152 0 152 6V16H0Z" fill="#354b25"/>
      <g fill="#b5c99a"><circle cx="9" cy="8" r="1.5"/><circle cx="15" cy="8" r="1.5"/><circle cx="21" cy="8" r="1.5"/></g>
      <path d="M120 8H141" stroke="#8ea972" stroke-width="2"/>
      <path d="M12 31H61M12 38H53M12 45H65" fill="none" stroke="#314624" stroke-width="3"/>
      <path d="M12 54H53M12 60H43" fill="none" stroke="#8ca278" stroke-width="1.5"/>
      <path d="M12 69H44V78H12Z" fill="#587d2c"/>
      <path d="M84 29H140V78H84Z" fill="#b8d086"/>
      <path d="M92 67V54H101V67M108 67V44H117V67M124 67V36H133V67" fill="#344e22"/>
      <path d="M12 89H43M55 89H86M98 89H140" fill="none" stroke="#8ca278" stroke-width="3" opacity=".65"/>
    </g>
    <g class="learning-after-art__automation" transform="translate(82 20)">
      <path d="M-27 7H-8Q0 7 0 15V22Q0 30 8 30H22" fill="none" stroke="#c4dd9c" stroke-width="1.8"/>
      <rect x="-38" y="-5" width="23" height="23" rx="3" fill="#415d2c" stroke="#bad38e" stroke-width=".8"/>
      <path d="M-26-1c1.2 5.1 2.8 6.8 8 8-5.2 1.2-6.8 2.8-8 8-1.2-5.2-2.8-6.8-8-8 5.2-1.2 6.8-2.9 8-8Z" fill="var(--lime)"/>
      <rect x="20" y="14" width="33" height="33" rx="4" fill="var(--lime)" stroke="#e0f99e" stroke-width="1"/>
      <path class="learning-complete-check" d="m28 30 6 6 11-13" pathLength="1" fill="none" stroke="#30471d" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="1" stroke-dashoffset="0"/>
      <circle cx="3" cy="26" r="2.3" fill="var(--lime)"/>
    </g>
  </g>
</svg>
