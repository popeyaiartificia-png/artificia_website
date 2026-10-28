<?php /** @var array $lead (lead_find row, fields decoded); @var bool $isAdmin */
$id = (int) $lead['id'];
[$pill, $state] = lead_forward_state($lead);
$dash = '<span class="muted">None</span>';
$text = fn(?string $s) => $s !== null && $s !== '' ? e($s) : $dash;
$when = fn(?string $ts) => $ts ? e(admin_date($ts)) : '';
$url = fn(string $u) => $u !== '' ? '<a href="' . link_href($u) . '" target="_blank" rel="noopener noreferrer" data-no-progress>' . e($u) . '</a>' : $dash;
$facts = function (array $rows): string {
    $h = '<dl class="facts">';
    foreach ($rows as $label => $html) {
        $h .= '<dt>' . e($label) . '</dt><dd>' . $html . '</dd>';
    }
    return $h . '</dl>';
};
$actions = '';
if ($isAdmin) {
    if ($lead['forwarded_at'] === null) {
        $actions .= '<form method="post" action="/admin/leads/' . $id . '">' . csrf_field() . '<input type="hidden" name="action" value="retry">'
            . '<button class="btn btn--primary" type="submit" data-busy="Sending&hellip;">' . ($lead['spam'] ? 'Forward anyway' : 'Retry forward') . '</button></form>';
    }
    $actions .= '<form method="post" action="/admin/leads/' . $id . '">' . csrf_field() . '<input type="hidden" name="action" value="delete">'
        . '<button class="btn btn--danger" type="submit" data-confirm="Click again to delete this lead" data-busy="Deleting&hellip;">Delete</button></form>';
}
$digits = preg_replace('/\D/', '', $lead['phone']);
$answers = [];
foreach ($lead['fields'] as $k => $v) {
    $answers[LEAD_LABELS[$k] ?? $k] = e(lead_field_text($v));
}
?>
<a class="back" href="/admin/leads">&larr; All leads</a>
<?= admin_page_head(ucfirst($lead['form_type']) . ' enquiry', $lead['name'] !== '' ? $lead['name'] : 'No name', null,
    'Received ' . admin_date($lead['created_at']) . ($lead['page_url'] !== '' ? ' on ' . lead_page_path($lead['page_url']) : '') . '.', $actions) ?>

<?php if ($lead['spam']): ?>
<p class="notice">Saved as spam because <?= e(lead_spam_reason_text($lead['spam_reason'])) ?>. Spam is never emailed or forwarded on its own. If a real person sent it, an admin can use Forward anyway.</p>
<?php endif ?>

<div class="cols">
  <section class="card">
    <h2>Contact</h2>
    <?= $facts([
        'Name' => $text($lead['name']),
        'Phone' => $lead['phone'] !== '' ? '<a href="tel:' . e($lead['phone']) . '">' . e($lead['phone']) . '</a> &nbsp; <a href="https://wa.me/' . $digits . '" target="_blank" rel="noopener" data-no-progress>WhatsApp</a>' : $dash,
        'Email' => $lead['email'] !== '' ? '<a href="mailto:' . e($lead['email']) . '">' . e($lead['email']) . '</a>' : $dash,
        'Company' => $text($lead['company']),
    ] + $answers) ?>
  </section>

  <section class="card">
    <h2>Delivery</h2>
    <?= $facts([
        'n8n forward' => '<span class="pill ' . $pill . '">' . e($state) . '</span>' . ($lead['forwarded_at'] ? ' ' . $when($lead['forwarded_at']) : '')
            . ($lead['forward_error'] !== null ? '<span class="cell-sub">' . e($lead['forward_error']) . '</span>' : ''),
        'Email alert' => $lead['emailed_at'] ? 'Sent ' . $when($lead['emailed_at']) : '<span class="muted">' . ($lead['spam'] ? 'Not sent (spam)' : 'Not sent: no alert email set, or the mail failed') . '</span>',
        'Meta Conversions API' => $lead['capi_sent_at'] ? 'Sent ' . $when($lead['capi_sent_at'])
            : ($lead['capi_error'] !== null ? '<span class="pill pill--error">Failed</span><span class="cell-sub">' . e($lead['capi_error']) . '</span>'
            : '<span class="muted">' . ($lead['spam'] ? 'Not sent (spam)' : 'Not sent: Meta Pixel ID or token not set') . '</span>'),
    ]) ?>
  </section>

  <section class="card">
    <h2>Where it came from</h2>
    <?= $facts([
        'Page' => $url($lead['page_url']),
        'Referrer' => $url($lead['referrer']),
        'UTM source' => $text($lead['utm_source']),
        'UTM medium' => $text($lead['utm_medium']),
        'UTM campaign' => $text($lead['utm_campaign']),
        'UTM term' => $text($lead['utm_term']),
        'UTM content' => $text($lead['utm_content']),
        'gclid' => $text($lead['gclid']),
        'fbclid' => $text($lead['fbclid']),
        'Meta fbp' => $text($lead['fbp']),
        'Meta fbc' => $text($lead['fbc']),
    ]) ?>
  </section>

  <section class="card">
    <h2>Record</h2>
    <?= $facts([
        'Lead ID' => (string) $id,
        'Event ID' => '<code>' . e($lead['event_id']) . '</code>',
        'Received' => $when($lead['created_at']),
        'Consent given' => $lead['consent_at'] ? $when($lead['consent_at']) : $dash,
        'Spam' => $lead['spam'] ? 'Yes: ' . e(lead_spam_reason_text($lead['spam_reason'])) : 'No',
        'IP address' => $text($lead['ip']),
        'Browser' => $text($lead['user_agent']),
    ]) ?>
  </section>
</div>
<?php if (!$isAdmin): ?>
<p class="muted">Only an admin can delete or re-send a lead.</p>
<?php endif ?>
