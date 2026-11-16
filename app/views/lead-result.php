<?php /** @var string $state ('ok' | 'errors' | 'failed'), $first, $back, $wa, $message; @var array $errors (field => message) */
$names = ['form_type' => 'Form', 'name' => 'Full name', 'phone' => 'Phone', 'email' => 'Email', 'company' => 'Company', 'i_am' => 'Which describes you',
    'track' => 'Track', 'employees' => 'Team size', 'adoption' => 'Where your team is with AI', 'location' => 'City', 'objectives' => 'What the training should achieve',
    'challenge' => 'Anything we should know', 'service' => 'What you need', 'message' => 'Message', 'consent' => 'Consent'];
?>
<section class="sec lead" id="enquire">
  <div class="wrap">
<?php if ($state === 'ok'): ?>
    <div class="lead-form plate is-done">
      <div class="logline">
        <p class="entry"><span>Enquiry saved</span></p>
        <h1 class="logline__msg"><?= tick() ?><span>Thanks<?= $first !== '' ? ', ' . e($first) : '' ?>. We&rsquo;ll message you on WhatsApp soon.</span></h1>
      </div>
    </div>
    <p class="lead__wa"><a href="<?= e($back) ?>">Back to the page</a></p>
<?php else: ?>
    <div class="plate prose">
      <h1 class="h2"><?= $state === 'errors' ? 'Please check your enquiry' : 'Your enquiry was not sent' ?></h1>
<?php if ($state === 'errors'): ?>
<?php foreach ($errors as $k => $msg): ?>
      <p class="field__err"><strong><?= e($names[$k] ?? $k) ?>:</strong> <?= e($msg) ?></p>
<?php endforeach ?>
<?php else: ?>
      <p><?= e($message) ?></p>
<?php endif ?>
      <p><a href="<?= e($wa) ?>" target="_blank" rel="noopener noreferrer">Message us on WhatsApp</a> or <a href="<?= e($back) ?>">go back to the form</a>.</p>
    </div>
<?php endif ?>
  </div>
</section>
