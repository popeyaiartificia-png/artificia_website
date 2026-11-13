<?php
/** Corporate training hero visual: one week planner that the training collapses. By hand, four everyday tasks are long
 *  amber bars stretched across the week. A switch flips to "With AI training", a sweep crosses the board, and each bar
 *  collapses to a lime sliver on Monday morning ("in minutes"), handing the rest of the week back. At the scale of a
 *  week, work done with AI is a sliver: that is the point. Tasks restate the approved department lines; no figures are
 *  claimed (bar lengths show days). CSS only (corporate.css); without motion it rests on the "with AI" week. */
$coWeek = [ // task, start and length by hand, as shares of the Mon-Fri track
    ['Client proposal', 0, 52], ['Invoice data entry', 8, 70], ['Customer replies', 22, 78], ['Weekly status report', 46, 46],
];
?>
<div class="co-week">
  <p class="co-week__toggle"><span class="co-week__off">By hand</span><span class="co-week__switch" aria-hidden="true"></span><span class="co-week__on">With AI training</span></p>
  <div class="co-week__board">
    <div class="co-week__head"><span class="co-week__k">This week</span><span class="co-week__days" aria-hidden="true"><i>Mon</i><i>Tue</i><i>Wed</i><i>Thu</i><i>Fri</i></span></div>
    <ol class="co-week__rows">
<?php foreach ($coWeek as $i => [$task, $from, $len]): ?>
      <li class="co-week__row" style="--i: <?= $i ?>; --from: <?= $from ?>%; --len: <?= $len ?>%">
        <span class="co-week__task"><?= e($task) ?></span>
        <span class="co-week__track"><span class="co-week__bar"></span><span class="co-week__tag"><i aria-hidden="true"></i>In minutes</span></span>
      </li>
<?php endforeach ?>
      <li class="co-week__free" aria-hidden="true"><span>Time back for real work</span></li>
    </ol>
    <span class="co-week__sweep" aria-hidden="true"></span>
  </div>
  <p class="co-week__chip co-week__chip--days"><span class="co-week__cal" aria-hidden="true"></span><span><small>By hand</small>Days</span></p>
  <p class="co-week__chip co-week__chip--mins"><span class="co-week__watch" aria-hidden="true"></span><span><small>With AI</small>Minutes</span></p>
  <p class="co-week__foot">Same people. Same work. <em>Trained to use AI.</em></p>
</div>
