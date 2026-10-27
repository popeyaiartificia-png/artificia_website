<?php
$type = in_array($f['form_type'] ?? '', ['masterclass', 'corporate', 'services', 'contact'], true) ? $f['form_type'] : 'contact';
$id = 'lf' . $s;
$buttons = ['masterclass' => 'Send my enquiry', 'corporate' => 'Ask for the free pilot', 'services' => 'Book the free flow audit', 'contact' => 'Send message'];
$label = trim($f['button_label'] ?? '') !== '' ? $f['button_label'] : $buttons[$type];
$wa = whatsapp_url($settings);
$field = function (string $name, string $text, string $type = 'text', bool $req = true, string $auto = '') use ($id): string {
    $fid = $id . '-' . $name;
    $attrs = ' id="' . $fid . '" name="' . $name . '" type="' . $type . '"' . ($req ? ' required' : '') . ($auto ? ' autocomplete="' . $auto . '"' : '');
    if ($type === 'tel') { $attrs .= ' inputmode="tel"'; }
    return '<div class="field"><label for="' . $fid . '">' . e($text) . ($req ? ' <span class="req" aria-hidden="true">*</span>' : ' <span class="opt">(optional)</span>') . '</label>'
        . '<input class="input"' . $attrs . ' aria-describedby="' . $fid . '-err"><p class="field__err" id="' . $fid . '-err"></p></div>';
};
$select = function (string $name, string $text, array $opts, bool $req = true) use ($id): string {
    $fid = $id . '-' . $name;
    $o = '<option value="">Choose one</option>';
    foreach ($opts as $v) { $o .= '<option>' . e($v) . '</option>'; }
    return '<div class="field"><label for="' . $fid . '">' . e($text) . ($req ? ' <span class="req" aria-hidden="true">*</span>' : ' <span class="opt">(optional)</span>') . '</label>'
        . '<select class="input" id="' . $fid . '" name="' . $name . '"' . ($req ? ' required' : '') . ' aria-describedby="' . $fid . '-err">' . $o . '</select><p class="field__err" id="' . $fid . '-err"></p></div>';
};
$area = function (string $name, string $text, bool $req) use ($id): string {
    $fid = $id . '-' . $name;
    return '<div class="field"><label for="' . $fid . '">' . e($text) . ($req ? ' <span class="req" aria-hidden="true">*</span>' : ' <span class="opt">(optional)</span>') . '</label>'
        . '<textarea class="input" id="' . $fid . '" name="' . $name . '" rows="4"' . ($req ? ' required' : '') . ' aria-describedby="' . $fid . '-err"></textarea><p class="field__err" id="' . $fid . '-err"></p></div>';
};
?>
<section class="sec sec--lead_form lead" id="enquire" data-section="lead">
  <div class="wrap lead__grid">
    <div class="lead__side">
      <?= sec_head($f['heading'] ?? '') ?>
<?php if (!empty($f['intro'])): ?>
      <p class="lede"><?= e($f['intro']) ?></p>
<?php endif ?>
<?php if (!empty($f['side_points'])): ?>
      <ul class="points">
<?php foreach ($f['side_points'] as $p): ?>
        <li><?= e($p['text'] ?? '') ?></li>
<?php endforeach ?>
      </ul>
<?php endif ?>
      <p class="lead__wa">Prefer WhatsApp? <a href="<?= e($wa) ?>">Message us directly</a></p>
    </div>
    <form class="lead-form plate" data-form-type="<?= $type ?>" method="post" action="/lead" novalidate>
      <input type="hidden" name="form_type" value="<?= $type ?>">
<?php if ($type === 'masterclass'): ?>
      <?= $field('name', 'Full name', 'text', true, 'name') ?>
      <div class="lead-form__row"><?= $field('phone', 'WhatsApp number', 'tel', true, 'tel') ?><?= $field('email', 'Email', 'email', true, 'email') ?></div>
      <div class="lead-form__row">
        <?= $select('i_am', 'Which describes you?', ['Student / Recent graduate', 'Working professional / Manager', 'Business owner / Founder', 'Freelancer / Creator', 'Job seeker', 'Other']) ?>
        <?= $select('track', 'Track', ['30-day Foundation', '60-day Full program', 'Not sure yet'], false) ?>
      </div>
<?php elseif ($type === 'corporate'): ?>
      <div class="lead-form__row"><?= $field('name', 'Full name', 'text', true, 'name') ?><?= $field('company', 'Company', 'text', true, 'organization') ?></div>
      <div class="lead-form__row"><?= $field('email', 'Work email', 'email', true, 'email') ?><?= $field('phone', 'Phone', 'tel', true, 'tel') ?></div>
      <div class="lead-form__row">
        <?= $select('employees', 'Team size', ['1–10', '11–25', '26–50', '51–100', '100+']) ?>
        <?= $field('location', 'City', 'text', true, 'address-level2') ?>
      </div>
      <?= $select('adoption', 'Where is your team with AI today?', ['Not using AI yet', 'Exploring (ChatGPT, Claude)', 'Some tools or automations', 'Custom automations or models']) ?>
      <fieldset class="fieldset">
        <legend>What should the training achieve? <span class="opt">(optional)</span></legend>
<?php foreach (['Automate repetitive work', 'Upskill the team on AI', 'Build AI agents or chatbots', 'Integrate n8n / Make / APIs', 'Something else'] as $k => $o): ?>
        <label class="check" for="<?= $id ?>-obj<?= $k ?>"><input type="checkbox" id="<?= $id ?>-obj<?= $k ?>" name="objectives[]" value="<?= e($o) ?>"><span><?= e($o) ?></span></label>
<?php endforeach ?>
      </fieldset>
      <?= $area('challenge', 'Anything we should know?', false) ?>
<?php elseif ($type === 'services'): ?>
      <?= $field('name', 'Full name', 'text', true, 'name') ?>
      <div class="lead-form__row"><?= $field('phone', 'WhatsApp number', 'tel', true, 'tel') ?><?= $field('email', 'Email', 'email', true, 'email') ?></div>
      <div class="lead-form__row">
        <?= $field('company', 'Company', 'text', false, 'organization') ?>
        <?= $select('service', 'What do you need?', ['GoHighLevel setup', 'Website development', 'Content and marketing', 'RAG or custom automation', 'AI automation', 'Not sure yet']) ?>
      </div>
      <?= $area('message', 'Tell us about the process', false) ?>
<?php else: ?>
      <div class="lead-form__row"><?= $field('name', 'Full name', 'text', true, 'name') ?><?= $field('phone', 'Phone or WhatsApp', 'tel', true, 'tel') ?></div>
      <?= $field('email', 'Email', 'email', false, 'email') ?>
      <?= $area('message', 'Message', true) ?>
<?php endif ?>
      <label class="check check--consent" for="<?= $id ?>-consent"><input type="checkbox" id="<?= $id ?>-consent" name="consent" value="1" required><span>I agree to be contacted about my enquiry by WhatsApp, phone or email, as described in the <a href="/privacy">Privacy Policy</a>.</span></label>
      <button class="btn btn--lime btn--block" type="submit"><?= e($label) ?><?= icon('arrow-right') ?></button>
      <p class="lead-form__status" role="status" aria-live="polite"></p>
      <div class="logline" tabindex="-1">
        <p class="entry"><span>Saved</span><span class="logline__time"></span></p>
        <p>Thank you. Your enquiry is saved and we will reply on WhatsApp.</p>
      </div>
    </form>
  </div>
</section>
