<?php
/** One animated visual per inner page, in the centre of the shared journey hero (journey-hero.php), each acting out what
 *  that page offers. Chosen by the hero's `visual` field. CSS only (public/assets/css/journey.css); without motion every
 *  visual rests on its finished state. All wording restates the page's own approved copy. */
$jvKey = (string) ($f['visual'] ?? '');
$jvCheck = '<span class="jv-check" aria-hidden="true"></span>';
$jvStar = '<svg class="jv-star" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 0c1.6 7.7 4.3 10.4 12 12-7.7 1.6-10.4 4.3-12 12-1.6-7.7-4.3-10.4-12-12C7.7 10.4 10.4 7.7 12 0z"/></svg>';
$jvDoc = '<span class="jv-docicon" aria-hidden="true"><i></i><i></i><i></i></span>';
// Solutions is the one visual that is also navigation: its six chips link to the solution chapters below.
$jvTag = $jvKey === 'solutions' ? 'nav' : 'div';
?>
<<?= $jvTag ?> class="jv jv--<?= e($jvKey) ?>" <?= $jvTag === 'nav' ? 'aria-label="Our solutions"' : 'aria-hidden="true"' ?>>
<?php if ($jvKey === 'solutions'):
    // The friction switchboard (12 Nov): each friction (a hero door's note) runs in as a tangled wire, Artificia
    // straightens it, and it leaves as a neon line to its solution (the door's label). Drawn in a 690 x 330 box:
    // rows spread from y 48 to 308, the core at (338, 178), chips from x 452.
    $jvIcons = [
        'automation' => '<path d="M20 11a8 8 0 0 0-14.3-4.9M4 13a8 8 0 0 0 14.3 4.9M5 3v4h4M19 21v-4h-4"/>',
        'agentic' => '<path d="m10 3.5 1.8 4.7 4.7 1.8-4.7 1.8L10 16.5l-1.8-4.7L3.5 10l4.7-1.8zM18 14.5l.9 2.1 2.1.9-2.1.9-.9 2.1-.9-2.1-2.1-.9 2.1-.9z"/>',
        'software' => '<rect x="3" y="4.5" width="18" height="15" rx="2"/><path d="M3 9h18M7.5 13.5h5M7.5 16h3"/>',
        'marketing' => '<path d="M21 3 10.5 13.5M21 3l-6.5 18-4-7.5L3 9.5z"/>',
        'search' => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="m20 20-4.8-4.8"/>',
        'private' => '<path d="m12 3 7 3v5.5c0 4.3-2.9 7.7-7 9.5-4.1-1.8-7-5.2-7-9.5V6z"/><rect x="9" y="11" width="6" height="5" rx="1"/><path d="M10.5 11V9.5a1.5 1.5 0 0 1 3 0V11"/>',
    ];
    $jvIcon = function (string $label) use ($jvIcons): string {
        foreach (['agentic' => 'agentic', 'software' => 'software', 'app' => 'software', 'marketing' => 'marketing', 'seo' => 'search', 'search' => 'search', 'cloud' => 'private', 'private' => 'private'] as $word => $key) {
            if (stripos($label, $word) !== false) return $jvIcons[$key];
        }
        return $jvIcons['automation'];
    };
    // A wire from the label (x 184) to the core with a pen scribble in it: loops of uneven size drifting along the wire
    // (deterministic, so the page never changes between loads), then straight into the core.
    $jvCoil = function (float $y, int $i): string {
        $dx = 154; $dy = 178 - $y; $len = hypot($dx, $dy); $ux = $dx / $len; $uy = $dy / $len;
        $at = fn(float $u, float $v): string => round(184 + $ux * $u - $uy * $v, 1) . ' ' . round($y + $uy * $u + $ux * $v, 1);
        $c = $len * (.38 + $i % 3 * .05); $n = 96;
        $d = 'M' . $at(0, 0);
        for ($s = 0; $s <= $n; $s++) {
            $th = M_PI + 2 * M_PI * 5.3 * $s / $n;
            $r = 12 * (.62 + .22 * sin(.37 * $s + $i) + .16 * sin(.91 * $s + 2 * $i));
            $d .= 'L' . $at($c + 40 * ($s / $n - .5) + $r * cos($th), 3 * sin(.13 * $s + $i) + .8 * $r * sin($th));
        }
        return $d . 'L' . $at($len, 0);
    };
    $jvDoors = array_values($doors);
    $jvPitch = count($jvDoors) > 1 ? 260 / (count($jvDoors) - 1) : 0;
?>
  <div class="jv-sw">
    <p class="jv-head jv-head--in" aria-hidden="true">Your friction</p>
    <p class="jv-head jv-head--out" aria-hidden="true">Our solution</p>
<?php foreach ($jvDoors as $i => $d):
    $y = $jvPitch ? 48 + $i * $jvPitch : 178;
    $coil = $jvCoil($y, $i);
    $out = 'M338 178C392 178 398 ' . round($y, 1) . ' 452 ' . round($y, 1);
    $label = (string) ($d['label'] ?? ''); $note = (string) ($d['note'] ?? ''); ?>
    <a class="jv-lane" href="<?= link_href($d['link'] ?? '#solutions') ?>" style="--i: <?= $i ?>; --y: <?= round($y / 3.3, 2) ?>%" aria-label="<?= e($label . ($note !== '' ? ': for ' . mb_strtolower($note) : '')) ?>">
      <svg class="jv-wire" viewBox="0 0 690 330" preserveAspectRatio="none" aria-hidden="true"><path class="jv-wire__in" pathLength="1" d="<?= $coil ?>"/><path class="jv-wire__out" pathLength="1" d="<?= $out ?>"/><path class="jv-wire__pulse jv-wire__pulse--in" pathLength="1" d="<?= $coil ?>"/><path class="jv-wire__pulse jv-wire__pulse--out" pathLength="1" d="<?= $out ?>"/></svg>
      <span class="jv-fric" aria-hidden="true"><span><?= e($note) ?></span></span>
      <span class="jv-sol" aria-hidden="true"><span class="jv-ico"><svg viewBox="0 0 24 24"><?= $jvIcon($label) ?></svg></span><b><?= e($label) ?></b><span class="jv-num"><?= sprintf('%02d', $i + 1) ?></span></span>
    </a>
<?php endforeach ?>
    <span class="jv-core" aria-hidden="true"><?= $jvStar ?></span>
    <span class="jv-core__name" aria-hidden="true">Artificia</span>
  </div>

<?php elseif ($jvKey === 'map'): // About: one team, three lines of work. course.js draws the orbit, the comet and the dot hologram that re-forms at each station (positions match its ellipse) ?>
  <canvas class="jv-morph"></canvas>
<?php foreach ([['Build', 'AI systems', 50, 10], ['Train', 'Your teams', 81.2, 70], ['Teach', 'Our courses', 18.8, 70]] as $i => [$a, $b, $x, $y]): ?>
  <span class="jv-node" style="left: <?= $x ?>%; top: <?= $y ?>%"><i>0<?= $i + 1 ?></i><b><?= $a ?></b><?= $b ?></span>
<?php endforeach ?>

<?php elseif ($jvKey === 'pipeline'): // GoHighLevel: one lead moves through the pipeline, followed up on WhatsApp ?>
  <div class="jv-card jv-board">
    <p class="jv-bar"><span class="jv-k">GoHighLevel · Pipeline</span><span class="jv-live">Live</span></p>
    <div class="jv-cols">
<?php foreach (['New lead', 'Contacted', 'Booked', 'Followed up'] as $c): ?>
      <div class="jv-col"><span class="jv-k"><?= $c ?></span><i></i><i></i></div>
<?php endforeach ?>
    </div>
    <div class="jv-lead"><span class="jv-k">Dealer enquiry</span><b class="jv-t">Owner assigned</b></div>
  </div>
  <div class="jv-card jv-toast"><span class="jv-wa"></span><span><span class="jv-k">WhatsApp</span><b class="jv-t">Follow-up sent</b></span></div>

<?php elseif ($jvKey === 'website'): // a visit becomes an enquiry ?>
  <div class="jv-card jv-browser">
    <p class="jv-browser__bar"><i></i><i></i><i></i><span>yourcompany.com</span></p>
    <div class="jv-browser__page">
      <i class="jv-line jv-line--h"></i><i class="jv-line" style="width: 46%"></i><i class="jv-line" style="width: 38%"></i>
      <span class="jv-cta">Get a quote</span>
      <div class="jv-tiles"><i></i><i></i><i></i></div>
    </div>
    <span class="jv-ripple"></span>
    <svg class="jv-cursor" viewBox="0 0 24 24"><path d="M4 2l16 9-7 2-3 7z"/></svg>
  </div>
  <div class="jv-card jv-toast"><?= $jvCheck ?><span><span class="jv-k">New enquiry</span><b class="jv-t">From your website</b></span></div>

<?php elseif ($jvKey === 'content'): // a post, an email and a slide, written in-house with AI ?>
  <span class="jv-pill"><?= $jvStar ?>Your brand voice</span>
<?php foreach (['Social post', 'Campaign email', 'Deck slide'] as $i => $c): ?>
  <div class="jv-card jv-post" style="--i: <?= $i ?>"><span class="jv-k"><?= $c ?></span><i class="jv-post__img"></i><i class="jv-line"></i><i class="jv-line" style="width: 70%"></i><i class="jv-line" style="width: 82%"></i><span class="jv-post__ok">Ready</span></div>
<?php endforeach ?>

<?php elseif ($jvKey === 'answers'): // an answer quoted from your own documents, with its source ?>
<?php foreach (['Policies.pdf', 'Price list.xlsx', 'Process manual.docx'] as $i => $d): ?>
  <div class="jv-card jv-doc" style="--i: <?= $i ?>"><?= $jvDoc ?><span class="jv-t"><?= $d ?></span></div>
<?php endforeach ?>
  <svg class="jv-beam" viewBox="0 0 100 100" preserveAspectRatio="none"><path pathLength="1" d="M33 22C42 22 40 72 50 72"/></svg>
  <div class="jv-card jv-chat">
    <p class="jv-bubble jv-bubble--q">What does our policy say about returns?</p>
    <p class="jv-typing"><i></i><i></i><i></i></p>
    <p class="jv-bubble jv-bubble--a">Here is the answer, quoted from your own policy.</p>
    <p class="jv-source"><?= $jvDoc ?>Source · Policies.pdf · page 4</p>
  </div>

<?php elseif ($jvKey === 'care'): // ward admin done with AI, under clear rules on patient data ?>
  <div class="jv-card jv-ecg"><svg viewBox="0 0 300 60" preserveAspectRatio="none"><path pathLength="1" d="M0 34h70l10-16 12 34 12-44 10 26h46l8-10 9 10h123"/></svg></div>
  <div class="jv-card jv-list">
    <p class="jv-k">Ward admin · with AI</p>
<?php foreach (['Shift handover notes', 'Discharge summary draft', 'Patient letter in plain words'] as $i => $t): ?>
    <p class="jv-row" style="--i: <?= $i ?>"><?= $jvCheck ?><span class="jv-t"><?= $t ?></span></p>
<?php endforeach ?>
  </div>
  <div class="jv-card jv-badge"><span class="jv-lock"></span><span><span class="jv-k">Patient data</span><b class="jv-t">Clear rules, approved tools</b></span></div>

<?php elseif ($jvKey === 'factory'): // a sheet read row by row into a quotation ?>
  <div class="jv-card jv-sheet">
    <p class="jv-k">Plant data.xlsx</p>
<?php for ($r = 0; $r < 6; $r++): ?>
    <p class="jv-sheet__row" style="--i: <?= $r ?>"><i></i><i></i><i></i><i></i></p>
<?php endfor ?>
  </div>
  <span class="jv-gear"><svg viewBox="0 0 24 24"><path d="M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8zm8.9 5.2-2-.3a7 7 0 0 1-.8 1.9l1.2 1.6-1.6 1.6-1.6-1.2a7 7 0 0 1-1.9.8l-.3 2h-2.3l-.3-2a7 7 0 0 1-1.9-.8l-1.6 1.2-1.6-1.6 1.2-1.6a7 7 0 0 1-.8-1.9l-2-.3v-2.3l2-.3a7 7 0 0 1 .8-1.9L4.3 6.3l1.6-1.6 1.6 1.2a7 7 0 0 1 1.9-.8l.3-2h2.3l.3 2a7 7 0 0 1 1.9.8l1.6-1.2 1.6 1.6-1.2 1.6a7 7 0 0 1 .8 1.9l2 .3z"/></svg></span>
  <div class="jv-card jv-quote">
    <p class="jv-k">Quotation</p>
    <i class="jv-line jv-line--h"></i><i class="jv-line"></i><i class="jv-line" style="width: 76%"></i><i class="jv-line" style="width: 64%"></i>
    <p class="jv-quote__total"><span class="jv-k">Total</span><i class="jv-line" style="width: 30%"></i></p>
    <span class="jv-stamp">Ready to send</span>
  </div>

<?php elseif ($jvKey === 'projects'): // the course projects, dealt into a portfolio ?>
  <span class="jv-pill"><?= $jvStar ?>Your projects</span>
<?php foreach (['Website', 'Content kit', 'Document assistant', 'Automation'] as $i => $p): ?>
  <div class="jv-card jv-proj" style="--i: <?= $i ?>"><span class="jv-proj__art"><i></i><i></i><i></i></span><span class="jv-t"><?= $p ?></span><?= $jvCheck ?></div>
<?php endforeach ?>

<?php elseif ($jvKey === 'desk'): // the repetitive work of a day, done with AI ?>
  <div class="jv-card jv-list jv-todo">
    <p class="jv-k">Today's repetitive work</p>
<?php foreach (['Weekly report', 'Meeting notes', 'Email replies', 'Data entry', 'Slide deck'] as $i => $t): ?>
    <p class="jv-row" style="--i: <?= $i ?>"><?= $jvCheck ?><span class="jv-t"><?= $t ?></span><span class="jv-chip">Done with AI</span></p>
<?php endforeach ?>
  </div>
  <div class="jv-card jv-badge jv-badge--time"><span class="jv-clock"></span><span><span class="jv-k">Your day</span><b class="jv-t">Time back for real work</b></span></div>

<?php elseif ($jvKey === 'business'): // your business at the centre, AI working in every part of it ?>
  <svg class="jv-spokes" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M50 50 20 22M50 50 80 22M50 50 20 80M50 50 80 80"/></svg>
  <span class="jv-hub"><?= $jvStar ?><b>Your business</b></span>
<?php foreach ([['Sales', 'Follow-ups sent', 20, 22], ['Marketing', 'Posts drafted', 80, 22], ['Accounts', 'Invoices read', 20, 80], ['Support', 'Questions answered', 80, 80]] as $i => [$a, $b, $x, $y]): ?>
  <div class="jv-card jv-tile" style="--i: <?= $i ?>; left: <?= $x ?>%; top: <?= $y ?>%"><span class="jv-k"><?= $a ?></span><b class="jv-t"><?= $b ?></b></div>
<?php endforeach ?>

<?php elseif ($jvKey === 'orkay'): // Orkay's live lead system, step by step (claims from the case study only) ?>
  <svg class="jv-route" viewBox="0 0 100 100" preserveAspectRatio="none"><path pathLength="1" d="M17 26H83C95 26 95 74 83 74H17"/></svg>
<?php foreach ([['Lead arrives', 'Meta form or WhatsApp', 17, 26], ['GoHighLevel', 'An owner is assigned', 50, 26], ['Routed', 'By the lead’s language', 83, 26],
    ['Arya calls', 'In Hindi, by AI voice', 83, 74], ['Followed up', '18 hours later', 50, 74], ['Reported', 'Every morning', 17, 74]] as $i => [$a, $b, $x, $y]): ?>
  <div class="jv-card jv-step" style="--i: <?= $i ?>; left: <?= $x ?>%; top: <?= $y ?>%"><span class="jv-k"><?= sprintf('%02d', $i + 1) ?> · <?= $a ?></span><b class="jv-t"><?= $b ?></b></div>
<?php endforeach ?>
<?php endif ?>
</<?= $jvTag ?>>
