<?php
$type = in_array($f['form_type'] ?? '', ['masterclass', 'corporate', 'services', 'contact'], true) ? $f['form_type'] : 'contact';
$id = 'lf' . $s;
$buttons = ['masterclass' => 'Send my enquiry', 'corporate' => 'Ask for the free pilot', 'services' => 'Book the free flow audit', 'contact' => 'Send message'];
$label = trim($f['button_label'] ?? '') !== '' ? $f['button_label'] : $buttons[$type];
$wa = whatsapp_url($settings);
$stageForm = ($page['slug'] ?? '') === 'home' || in_array($page['sections'][0]['fields']['variant'] ?? '', ['course', 'solutions', 'about', 'corporate', 'contact'], true);
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
<section class="sec sec--lead_form lead<?= $stageForm ? ' lead--stage' : '' ?>" id="enquire" data-section="lead">
  <div class="wrap lead__grid">
    <div class="lead__side">
<?php if ($stageForm): ?>
      <p class="lead__eyebrow"><?= icon('arrow-up-right') ?>Start a conversation</p>
<?php endif ?>
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
    <form class="lead-form plate" data-form-type="<?= $type ?>" data-wa="<?= e($wa) ?>" method="post" action="/lead" novalidate>
<?php if ($stageForm): ?>
      <p class="lead-form__eyebrow">Your enquiry<?= icon('arrow-up-right') ?></p>
<?php endif ?>
      <input type="hidden" name="form_type" value="<?= $type ?>">
      <input type="hidden" name="ts" value="<?= e(lead_ts((string) config()['app_secret'])) ?>">
<?php foreach (array_keys(LEAD_TRACKING) as $h): // site.js fills these on submit ?>
      <input type="hidden" name="<?= $h ?>" value="">
<?php endforeach ?>
      <div class="hp" aria-hidden="true"><label for="<?= $id ?>-website_url">Website</label><input type="text" id="<?= $id ?>-website_url" name="website_url" tabindex="-1" autocomplete="off"></div>
<?php if ($type === 'masterclass'): ?>
      <?= $field('name', 'Full name', 'text', true, 'name') ?>
      <div class="lead-form__row"><?= $field('phone', 'WhatsApp number', 'tel', true, 'tel') ?><?= $field('email', 'Email', 'email', true, 'email') ?></div>
      <div class="lead-form__row">
        <?= $select('i_am', 'Which describes you?', LEAD_OPTIONS['i_am']) ?>
        <?= $select('track', 'Track', LEAD_OPTIONS['track'], false) ?>
      </div>
<?php elseif ($type === 'corporate'): ?>
      <div class="lead-form__row"><?= $field('name', 'Full name', 'text', true, 'name') ?><?= $field('company', 'Company', 'text', true, 'organization') ?></div>
      <div class="lead-form__row"><?= $field('email', 'Work email', 'email', true, 'email') ?><?= $field('phone', 'Phone', 'tel', true, 'tel') ?></div>
      <div class="lead-form__row">
        <?= $select('employees', 'Team size', LEAD_OPTIONS['employees']) ?>
        <?= $field('location', 'City', 'text', true, 'address-level2') ?>
      </div>
      <?= $select('adoption', 'Where is your team with AI today?', LEAD_OPTIONS['adoption']) ?>
      <fieldset class="fieldset">
        <legend>What should the training achieve? <span class="opt">(optional)</span></legend>
<?php foreach (LEAD_OPTIONS['objectives'] as $k => $o): ?>
        <label class="check" for="<?= $id ?>-obj<?= $k ?>"><input type="checkbox" id="<?= $id ?>-obj<?= $k ?>" name="objectives[]" value="<?= e($o) ?>"><span><?= e($o) ?></span></label>
<?php endforeach ?>
      </fieldset>
      <?= $area('challenge', 'Anything we should know?', false) ?>
<?php elseif ($type === 'services'): ?>
      <?= $field('name', 'Full name', 'text', true, 'name') ?>
      <div class="lead-form__row"><?= $field('phone', 'WhatsApp number', 'tel', true, 'tel') ?><?= $field('email', 'Email', 'email', true, 'email') ?></div>
      <div class="lead-form__row">
        <?= $field('company', 'Company', 'text', false, 'organization') ?>
        <?= $select('service', 'What do you need?', LEAD_OPTIONS['service']) ?>
      </div>
      <?= $area('message', 'Tell us about the process', false) ?>
<?php else: ?>
      <div class="lead-form__row"><?= $field('name', 'Full name', 'text', true, 'name') ?><?= $field('phone', 'Phone or WhatsApp', 'tel', true, 'tel') ?></div>
      <?= $field('email', 'Email', 'email', false, 'email') ?>
      <?= $area('message', 'Message', true) ?>
<?php endif ?>
      <div class="field">
        <label class="check check--consent" for="<?= $id ?>-consent"><input type="checkbox" id="<?= $id ?>-consent" name="consent" value="1" required aria-describedby="<?= $id ?>-consent-err"><span>I agree to be contacted about my enquiry by WhatsApp, phone or email, as described in the <a href="/privacy">Privacy Policy</a>.</span></label>
        <p class="field__err" id="<?= $id ?>-consent-err"></p>
      </div>
      <button class="btn btn--lime btn--block" type="submit"><?= e($label) ?><?= icon('arrow-right') ?></button>
      <p class="lead-form__status" role="status" aria-live="polite"></p>
      <div class="logline" tabindex="-1" hidden>
        <p class="entry"><span>Enquiry saved</span><span class="logline__time"></span></p>
        <p class="logline__msg"><?= tick() ?><span data-thanks></span></p>
      </div>
    </form>
  </div>
</section>
