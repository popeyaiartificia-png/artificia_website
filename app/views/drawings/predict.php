<?php
// The hero's live lesson: a sentence written the way a language model writes, one likely word at a time.
// The odds are illustrative and say so; site.js makes the words clickable, and without it the first step still reads.
$tree = ['start' => 'To really understand AI, you should', 'next' => [
    ['w' => 'try it', 'p' => 44, 'next' => [['w' => 'yourself.', 'p' => 57], ['w' => 'every day.', 'p' => 31], ['w' => 'with friends.', 'p' => 12]]],
    ['w' => 'build', 'p' => 29, 'next' => [['w' => 'something real.', 'p' => 52], ['w' => 'a small project.', 'p' => 36], ['w' => 'an app.', 'p' => 12]]],
    ['w' => 'ask', 'p' => 18, 'next' => [['w' => 'good questions.', 'p' => 61], ['w' => 'how it works.', 'p' => 27], ['w' => 'a teacher.', 'p' => 12]]],
    ['w' => 'read', 'p' => 9, 'next' => [['w' => 'less, try more.', 'p' => 66], ['w' => 'about it.', 'p' => 22], ['w' => 'the news.', 'p' => 12]]],
]];
?>
<figure class="predict" data-predict>
  <figcaption class="predict__cap"><span>How a chatbot writes</span><span>Odds are illustrative</span></figcaption>
  <p class="predict__text" aria-live="polite"><span class="predict__said"><?= e($tree['start']) ?></span><span class="predict__caret" aria-hidden="true"></span></p>
  <ol class="predict__opts" aria-label="Likely next words">
<?php foreach ($tree['next'] as $o): ?>
    <li><button type="button" style="--p:<?= $o['p'] / 100 ?>"><span class="predict__w"><?= e($o['w']) ?></span><span class="predict__bar" aria-hidden="true"></span><span class="predict__p"><?= $o['p'] ?>%</span></button></li>
<?php endforeach ?>
  </ol>
  <p class="predict__note" data-end="That is how ChatGPT, Claude and Gemini write: a likely next word, then the next, until the answer is done. Real models weigh tens of thousands of options at every step. Day 1 of the masterclass starts here.">An AI model writes one word at a time, choosing from odds like these. Pick a word to carry on the sentence.</p>
  <button class="predict__again btn" type="button" hidden>Start again</button>
  <script type="application/json"><?= json_encode($tree, JSON_HEX_TAG | JSON_UNESCAPED_UNICODE) ?></script>
</figure>
