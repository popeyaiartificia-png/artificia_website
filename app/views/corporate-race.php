<?php
/** Corporate training hero visual: the same team and the same four everyday tasks, done two ways. Before training the
 *  work is done by hand: days pass on the calendar and the first task is still in progress. After AI training the same
 *  people finish all four in minutes. The tasks restate the page's approved department lines (Sales, Finance, Support,
 *  Operations). No figures are claimed: time is shown as days passing versus minutes. CSS only (corporate.css);
 *  reduced motion shows the end of the loop. */
$coTasks = ['Client proposal', 'Invoice data entry', 'Customer replies', 'Weekly status report'];
$coPerson = '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="7.5" r="4"/><path d="M4 21c0-4.4 3.6-7.5 8-7.5s8 3.1 8 7.5z"/></svg>';
?>
<div class="co-race">
  <div class="co-race__in">
    <div class="co-race__lane co-race__lane--before">
      <div class="co-race__head">
        <span class="co-race__who"><?= $coPerson ?></span>
        <span class="co-race__title"><small>Before training</small><b>By hand</b></span>
        <span class="co-race__cal" aria-label="Days pass"><span class="co-race__cal-top"></span><span class="co-race__days" aria-hidden="true"><i>Mon</i><i>Tue</i><i>Wed</i><i>Thu</i></span></span>
      </div>
      <ol class="co-race__tasks">
<?php foreach ($coTasks as $i => $t): ?>
        <li<?= $i ? ' class="is-waiting"' : ' class="is-slow"' ?>><span class="co-race__name"><?= e($t) ?></span><span class="co-race__state"><?= $i ? 'Waiting' : 'In progress' ?></span><span class="co-race__bar"><i></i></span></li>
<?php endforeach ?>
      </ol>
      <p class="co-race__still">Days later, still on the first task.</p>
    </div>
    <div class="co-race__lane co-race__lane--after">
      <div class="co-race__head">
        <span class="co-race__who"><?= $coPerson ?><svg class="co-race__spark" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 0c1.6 7.7 4.3 10.4 12 12-7.7 1.6-10.4 4.3-12 12-1.6-7.7-4.3-10.4-12-12C7.7 10.4 10.4 7.7 12 0z"/></svg></span>
        <span class="co-race__title"><small>After AI training</small><b>With AI</b></span>
        <span class="co-race__watch"><i aria-hidden="true"></i>Minutes</span>
      </div>
      <ol class="co-race__tasks">
<?php foreach ($coTasks as $i => $t): ?>
        <li class="is-fast" style="--i: <?= $i ?>"><span class="co-race__name"><?= e($t) ?></span><span class="co-race__ok" aria-label="Done"></span><span class="co-race__bar"><i></i></span></li>
<?php endforeach ?>
      </ol>
      <p class="co-race__done">All four done. Time back for real work.</p>
    </div>
  </div>
  <p class="co-race__foot">Same people. Same work. <em>Trained to use AI.</em></p>
</div>
