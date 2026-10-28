<?php
declare(strict_types=1);

const LEAD_TEST_PW = 'correct horse 1';
const LEAD_TEST_DEAD_URL = 'http://127.0.0.1:9/webhook/artificia-lead'; // the discard port: nothing listens, so curl fails at once

/** A valid submission of $type (ts 10 s old), with $over applied on top. */
function lead_test_in(string $type, array $over = []): array
{
    $common = [
        'form_type' => $type, 'name' => 'Asha Patel', 'phone' => '98765 43210', 'email' => 'asha@example.com', 'consent' => '1',
        'website_url' => '', 'ts' => lead_ts((string) config()['app_secret'], time() - 10),
        'page_url' => 'https://artificiaai.com/ai-masterclass', 'referrer' => 'https://www.google.com/',
        'utm_source' => 'google', 'utm_medium' => 'cpc', 'utm_campaign' => 'oct', 'utm_term' => '', 'utm_content' => '',
        'gclid' => 'g123', 'fbclid' => '', 'fbp' => '', 'fbc' => '',
    ];
    $extra = [
        'masterclass' => ['i_am' => 'Job seeker', 'track' => 'Not sure yet'],
        'corporate' => ['company' => 'Orkay', 'employees' => '26–50', 'adoption' => 'Not using AI yet', 'location' => 'Vadodara',
            'objectives' => ['Automate repetitive work', 'Upskill the team on AI'], 'challenge' => 'Too many spreadsheets'],
        'services' => ['company' => 'Orkay', 'service' => 'AI automation', 'message' => 'Quotes take a day'],
        'contact' => ['message' => 'Call me back please'],
    ];
    return array_merge($common, $extra[$type] ?? [], $over);
}

function lead_test_server(string $ip = '203.0.113.7'): array
{
    return ['REMOTE_ADDR' => $ip, 'HTTP_USER_AGENT' => 'LeadsTest/1.0'];
}

function lead_test_row(PDO $db, int $id): array|false
{
    $st = $db->prepare('SELECT * FROM leads WHERE id = ?');
    $st->execute([$id]);
    return $st->fetch();
}

/** Saves a valid lead straight through the endpoint logic and returns its id. */
function lead_test_saved(PDO $db, string $type = 'masterclass', array $over = []): int
{
    $r = lead_handle($db, lead_test_in($type, $over), lead_test_server('198.51.100.' . random_int(1, 250)), []);
    eq($r['status'], 200, 'saved');
    return $r['id'];
}

function lead_test_login(PDO $db, string $role): int
{
    $uid = user_create($db, "$role-" . bin2hex(random_bytes(3)) . '@example.com', LEAD_TEST_PW, $role);
    $_SESSION = ['uid' => $uid, 'csrf' => 'tok'];
    return $uid;
}

function lead_test_admin(PDO $db, string $method, string $path, array $data = []): HttpResponse
{
    if ($method === 'POST') {
        $_POST = $data + ['_csrf' => 'tok'];
    } else {
        $_GET = $data;
    }
    try {
        return admin_handle($db, $method, $path);
    } finally {
        $_POST = $_GET = [];
    }
}

function test_validate_required_per_type(): void
{
    $required = [
        'masterclass' => ['name', 'phone', 'email', 'i_am'],
        'corporate' => ['name', 'email', 'phone', 'company', 'employees', 'adoption', 'location'],
        'services' => ['name', 'phone', 'email', 'service'],
        'contact' => ['name', 'phone', 'message'],
    ];
    foreach ($required as $type => $fields) {
        $v = lead_validate($type, lead_test_in($type));
        ok($v['ok'], "$type: a complete form passes, got " . json_encode($v['errors']));
        eq($v['data']['phone'], '+919876543210', "$type: phone normalised");
        foreach ($fields as $f) {
            $v = lead_validate($type, lead_test_in($type, [$f => '']));
            ok(!$v['ok'] && isset($v['errors'][$f]), "$type: $f is required");
        }
        $v = lead_validate($type, lead_test_in($type, ['consent' => '']));
        ok(isset($v['errors']['consent']), "$type: consent is required");
    }
    // Optional fields really are optional.
    ok(lead_validate('masterclass', lead_test_in('masterclass', ['track' => '']))['ok']);
    ok(lead_validate('contact', lead_test_in('contact', ['email' => '']))['ok']);
    ok(lead_validate('services', lead_test_in('services', ['company' => '', 'message' => '']))['ok']);
    ok(lead_validate('corporate', lead_test_in('corporate', ['objectives' => [], 'challenge' => '']))['ok']);

    // Selects accept only their own options; email and phone are checked; unknown types are refused.
    ok(isset(lead_validate('masterclass', lead_test_in('masterclass', ['i_am' => 'Astronaut']))['errors']['i_am']));
    ok(isset(lead_validate('corporate', lead_test_in('corporate', ['objectives' => ['Hack']]))['errors']['objectives']));
    ok(isset(lead_validate('services', lead_test_in('services', ['email' => 'nope']))['errors']['email']));
    ok(isset(lead_validate('contact', lead_test_in('contact', ['phone' => '12345']))['errors']['phone']));
    ok(isset(lead_validate('webinar', lead_test_in('contact'))['errors']['form_type']));
    ok(isset(lead_validate('contact', lead_test_in('contact', ['name' => ['x']]))['errors']['name']), 'an array where a string belongs is refused');

    // The stored data: columns plus this form's answers, capped to the column sizes, tracking kept, junk URLs dropped.
    $v = lead_validate('corporate', lead_test_in('corporate', ['name' => str_repeat('a', 300), 'utm_source' => str_repeat('s', 400),
        'page_url' => 'javascript:alert(1)', 'track' => 'Not sure yet', 'i_am' => 'Job seeker']));
    ok($v['ok']);
    eq(mb_strlen($v['data']['name']), 255);
    eq(mb_strlen($v['data']['utm_source']), 255);
    eq($v['data']['page_url'], '', 'only http(s) addresses are kept');
    eq($v['data']['company'], 'Orkay');
    eq($v['data']['fields'], ['employees' => '26–50', 'adoption' => 'Not using AI yet', 'location' => 'Vadodara',
        'objectives' => ['Automate repetitive work', 'Upskill the team on AI'], 'challenge' => 'Too many spreadsheets'], "other forms' fields are dropped");
    ok(mb_check_encoding(lead_validate('contact', lead_test_in('contact', ['message' => "ok \xC3"]))['data']['fields']['message'], 'UTF-8'), 'invalid UTF-8 is scrubbed');
}

function test_normalize_phone(): void
{
    eq(normalize_phone('98765 43210'), '+919876543210');
    eq(normalize_phone('919876543210'), '+919876543210');
    eq(normalize_phone('+971501234567'), '+971501234567');
    eq(normalize_phone('+91 98765-43210'), '+919876543210');
    eq(normalize_phone('12345'), null);
    eq(normalize_phone('5876543210'), null, 'Indian mobiles start 6 to 9');
    eq(normalize_phone('+1234567'), null, 'too short');
    eq(normalize_phone('98765x43210'), null);
    eq(normalize_phone(''), null);
}

function test_honeypot_saves_as_spam(): void
{
    $db = test_db();
    $r = lead_handle($db, lead_test_in('masterclass', ['website_url' => 'https://spam.example']), lead_test_server(), []);
    eq($r['status'], 200);
    eq($r['body']['ok'], true, 'the visitor (or bot) sees success');
    ok(preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $r['body']['event_id']) === 1, 'event_id is a UUID v4');
    lead_followups($db, $r['id'], ['n8n_webhook_url' => LEAD_TEST_DEAD_URL, 'lead_alert_email' => ''], 'https://artificiaai.com');
    $row = lead_test_row($db, $r['id']);
    eq((int) $row['spam'], 1);
    eq([$row['forwarded_at'], $row['forward_error'], $row['emailed_at'], $row['capi_sent_at'], $row['capi_error']], [null, null, null, null, null], 'no forward attempt, no mail, no CAPI');
    eq($row['event_id'], $r['body']['event_id']);
    eq($row['ip'], '203.0.113.7');
    ok($row['consent_at'] !== null, 'consent time recorded');
}

function test_fast_submit_is_spam(): void
{
    $db = test_db();
    $secret = (string) config()['app_secret'];
    $spam = fn(string $ts) => lead_spam_flags(lead_test_in('contact', ['ts' => $ts]), '192.0.2.' . random_int(1, 250), $db, $secret)['spam'];
    eq($spam(lead_ts($secret, time() - 1)), true, '1 s is too fast for a person');
    eq($spam(lead_ts($secret, time() - 10)), false);
    eq($spam(lead_ts($secret, time() - 90000)), true, 'over 24 h old');
    eq($spam(lead_ts('another-secret', time() - 10)), true, 'a forged token');
    eq($spam(''), true, 'no token');

    $r = lead_handle($db, lead_test_in('contact', ['ts' => lead_ts($secret, time() - 1)]), lead_test_server(), []);
    eq([$r['status'], $r['body']['ok']], [200, true]);
    eq((int) lead_test_row($db, $r['id'])['spam'], 1);
}

function test_rate_limit_sixth_request(): void
{
    $db = test_db();
    for ($i = 1; $i <= 5; $i++) {
        eq(lead_handle($db, lead_test_in('contact'), lead_test_server('203.0.113.9'), [])['status'], 200, "request $i");
    }
    $r = lead_handle($db, lead_test_in('contact'), lead_test_server('203.0.113.9'), ['whatsapp_number' => '919104488859']);
    eq($r['status'], 429);
    eq($r['body']['ok'], false);
    eq($r['body']['whatsapp'], 'https://wa.me/919104488859');
    ok(($r['body']['message'] ?? '') !== '', 'says why');
    eq($r['id'], null);
    eq((int) $db->query('SELECT COUNT(*) FROM leads')->fetchColumn(), 5, 'the sixth is not saved');
    eq(lead_handle($db, lead_test_in('contact'), lead_test_server('203.0.113.10'), [])['status'], 200, 'another address is not limited');
}

function test_lead_db_failure_is_not_success(): void
{
    $db = test_db();
    $db->exec('DROP TABLE leads'); // every insert now throws
    $r = lead_handle($db, lead_test_in('masterclass'), lead_test_server(), ['whatsapp_number' => '919104488859']);
    eq($r['status'], 500);
    eq($r['body']['ok'], false);
    eq($r['body']['whatsapp'], 'https://wa.me/919104488859');
    ok(!isset($r['body']['event_id']), 'no event id for a lead that was not saved');
    eq($r['id'], null, 'nothing to follow up');
}

function test_capi_payload_hashes(): void
{
    $lead = ['event_id' => 'e-1', 'email' => ' A@B.com ', 'phone' => '+919876543210', 'fbclid' => 'abc', 'fbc' => '', 'fbp' => 'fb.1.1700000000000.42',
        'ip' => '203.0.113.7', 'user_agent' => 'UA/1', 'created_at' => '2026-10-27 15:30:00'];
    $p = meta_capi_payload($lead, 'https://artificiaai.com/ai-masterclass', 'TEST123');
    eq($p['test_event_code'], 'TEST123');
    $ev = $p['data'][0];
    eq([$ev['event_name'], $ev['action_source'], $ev['event_id'], $ev['event_source_url']], ['Lead', 'website', 'e-1', 'https://artificiaai.com/ai-masterclass']);
    eq($ev['event_time'], strtotime('2026-10-27 15:30:00'));
    $u = $ev['user_data'];
    eq($u['em'], hash('sha256', 'a@b.com'));
    eq($u['ph'], hash('sha256', '919876543210'));
    eq($u['fbc'], 'fb.1.' . (strtotime('2026-10-27 15:30:00') * 1000) . '.abc', 'built from fbclid');
    eq([$u['client_ip_address'], $u['client_user_agent'], $u['fbp']], ['203.0.113.7', 'UA/1', 'fb.1.1700000000000.42']);

    $p = meta_capi_payload(['fbc' => 'fb.1.123.cookie', 'fbclid' => 'abc', 'email' => '', 'fbp' => ''] + $lead, 'https://x');
    eq($p['data'][0]['user_data']['fbc'], 'fb.1.123.cookie', 'the _fbc cookie wins');
    ok(!isset($p['data'][0]['user_data']['em']) && !isset($p['data'][0]['user_data']['fbp']), 'empty values are left out');
    ok(!isset($p['test_event_code']), 'no test code unless given');
}

function test_n8n_payload_shape(): void
{
    $db = test_db();
    $id = lead_test_saved($db, 'corporate');
    $p = n8n_payload(lead_find($db, $id), 'https://artificiaai.com');
    eq(array_keys($p), ['lead_id', 'event_id', 'form_type', 'name', 'email', 'phone', 'company', 'fields', 'page_url', 'referrer', 'utm',
        'gclid', 'fbclid', 'submitted_at', 'admin_url']);
    eq(array_keys($p['utm']), ['source', 'medium', 'campaign', 'term', 'content']);
    eq($p['lead_id'], $id);
    eq([$p['form_type'], $p['name'], $p['phone'], $p['company'], $p['gclid']], ['corporate', 'Asha Patel', '+919876543210', 'Orkay', 'g123']);
    eq($p['utm']['source'], 'google');
    eq($p['admin_url'], "https://artificiaai.com/admin/leads/$id");
    ok(preg_match('/^\d{4}-\d\d-\d\dT\d\d:\d\d:\d\d[+-]\d\d:\d\d$/', $p['submitted_at']) === 1, 'ISO 8601 with offset');
    $json = json_decode(json_encode($p), true);
    eq($json['fields']['objectives'], ['Automate repetitive work', 'Upskill the team on AI']);

    $contact = n8n_payload(lead_find($db, lead_test_saved($db, 'contact', ['message' => 'Hi'])), 'https://artificiaai.com');
    ok(str_contains(json_encode($contact), '"fields":{'), 'fields is always a JSON object');
}

function test_forward_failure_keeps_lead(): void
{
    $db = test_db();
    $id = lead_test_saved($db);
    lead_followups($db, $id, ['n8n_webhook_url' => LEAD_TEST_DEAD_URL, 'n8n_webhook_token' => 'sekret-token-xyz', 'lead_alert_email' => ''], 'https://artificiaai.com');
    $row = lead_test_row($db, $id);
    ok($row !== false, 'the lead is still there');
    eq($row['forwarded_at'], null);
    ok((string) $row['forward_error'] !== '', 'the error is recorded');
    ok(!str_contains((string) $row['forward_error'], 'sekret'), 'the token is never stored');
    eq(lead_forward($db, lead_find($db, $id), ['n8n_webhook_url' => LEAD_TEST_DEAD_URL], 'https://artificiaai.com') !== null, true, 'a retry reports the error');
}

function test_no_webhook_marks_not_forwarded(): void
{
    $db = test_db();
    $id = lead_test_saved($db, 'services', ['name' => 'Ravi Shah']);
    lead_followups($db, $id, ['lead_alert_email' => ''], 'https://artificiaai.com');
    $row = lead_test_row($db, $id);
    eq([$row['forwarded_at'], $row['forward_error']], [null, 'no webhook configured']);

    lead_test_login($db, 'admin');
    $page = lead_test_admin($db, 'GET', '/admin/leads', ['tab' => 'unforwarded']);
    eq($page->status, 200);
    ok(str_contains($page->body, 'Ravi Shah'), 'listed under Not forwarded');
    $_SESSION = [];
}

function test_seo_cannot_export_or_delete(): void
{
    $db = test_db();
    $id = lead_test_saved($db, 'contact', ['name' => '=HYPERLINK("x") <b>Bold</b>']);

    lead_test_login($db, 'seo');
    $list = lead_test_admin($db, 'GET', '/admin/leads');
    eq($list->status, 200, 'SEO sees the list');
    ok(str_contains($list->body, '&lt;b&gt;Bold&lt;/b&gt;') && !str_contains($list->body, '<b>Bold</b>'), 'names are escaped');
    ok(!str_contains($list->body, '/admin/leads/export'), 'no export button for SEO');
    $detail = lead_test_admin($db, 'GET', "/admin/leads/$id");
    eq($detail->status, 200, 'SEO sees the detail');
    ok(!str_contains($detail->body, 'name="action"'), 'read-only: no delete or retry');
    eq(lead_test_admin($db, 'GET', '/admin/leads/export')->status, 403);
    eq(lead_test_admin($db, 'POST', "/admin/leads/$id", ['action' => 'delete'])->status, 403);
    eq(lead_test_admin($db, 'POST', "/admin/leads/$id", ['action' => 'retry'])->status, 403);
    ok(lead_test_row($db, $id) !== false, 'still there');

    lead_test_login($db, 'admin');
    $csv = lead_test_admin($db, 'GET', '/admin/leads/export');
    eq($csv->status, 200);
    ok(str_starts_with($csv->type, 'text/csv'));
    ok(str_starts_with($csv->body, "\xEF\xBB\xBF"), 'UTF-8 with BOM for Excel');
    ok(str_contains($csv->body, "\"'=HYPERLINK(\"\"x\"\") <b>Bold</b>\""), 'formula-looking cells are defused');
    ok(str_contains($csv->body, ',+919876543210,'), 'phone numbers stay as they are');
    ok(str_contains(lead_test_admin($db, 'GET', "/admin/leads/$id")->body, 'name="action"'), 'admin gets the actions');

    eq(lead_test_admin($db, 'POST', "/admin/leads/$id", ['action' => 'delete'])->location, '/admin/leads');
    eq(lead_test_row($db, $id), false, 'admin can delete');
    eq(lead_test_admin($db, 'GET', "/admin/leads/$id")->status, 404);
    $_SESSION = [];
}

function test_inbox_tabs_filters_and_badge(): void
{
    $db = test_db();
    $a = lead_test_saved($db, 'masterclass', ['name' => 'Mira Masterclass', 'utm_source' => 'facebook']);
    lead_test_saved($db, 'corporate', ['name' => 'Cora Corporate', 'page_url' => 'https://artificiaai.com/corporate-ai-training']);
    $spam = lead_handle($db, lead_test_in('contact', ['name' => 'Sam Spam', 'website_url' => 'x']), lead_test_server(), [])['id'];
    $db->prepare('UPDATE leads SET forwarded_at = NOW() WHERE id = ?')->execute([$a]);
    $db->prepare('UPDATE leads SET created_at = NOW() - INTERVAL 3 DAY WHERE id = ?')->execute([$a]);

    lead_test_login($db, 'admin');
    $all = lead_test_admin($db, 'GET', '/admin/leads')->body;
    ok(str_contains($all, 'Mira Masterclass') && str_contains($all, 'Cora Corporate') && !str_contains($all, 'Sam Spam'), 'All leaves spam out');
    ok(str_contains($all, 'Export 2 to CSV'));
    $spamTab = lead_test_admin($db, 'GET', '/admin/leads', ['tab' => 'spam'])->body;
    ok(str_contains($spamTab, 'Sam Spam') && !str_contains($spamTab, 'Mira Masterclass'));
    $unfwd = lead_test_admin($db, 'GET', '/admin/leads', ['tab' => 'unforwarded'])->body;
    ok(str_contains($unfwd, 'Cora Corporate') && !str_contains($unfwd, 'Mira Masterclass'), 'forwarded leads leave Not forwarded');
    ok(str_contains(lead_test_admin($db, 'GET', '/admin/leads', ['source' => 'facebook'])->body, 'Export 1 to CSV'));
    ok(!str_contains(lead_test_admin($db, 'GET', '/admin/leads', ['form' => 'corporate'])->body, 'Mira Masterclass'));
    ok(!str_contains(lead_test_admin($db, 'GET', '/admin/leads', ['page' => 'https://artificiaai.com/corporate-ai-training'])->body, 'Mira Masterclass'));
    $recent = lead_test_admin($db, 'GET', '/admin/leads', ['from' => date('Y-m-d')])->body;
    ok(str_contains($recent, 'Cora Corporate') && !str_contains($recent, 'Mira Masterclass'), 'date range');
    ok(str_contains(lead_test_admin($db, 'GET', "/admin/leads/$spam")->body, 'Sam Spam'), 'spam detail opens');

    // R34: the nav badge counts non-spam leads from the last 24 hours (Cora only).
    $prev = db_swap($db);
    try {
        ok(preg_match('#href="/admin/leads"[^>]*>.*?<span class="nav__badge">1</span>#s', lead_test_admin($db, 'GET', '/admin')->body) === 1, 'badge shows 1');
    } finally {
        db_swap($prev);
    }
    $_SESSION = [];
}

function test_form_renders_hidden_fields_and_token(): void
{
    $html = render_section_in(['type' => 'lead_form', 'fields' => ['heading' => 'Talk to us', 'form_type' => 'corporate']], 3, [], []);
    foreach (['website_url', 'ts', 'page_url', 'referrer', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'fbclid', 'fbp', 'fbc'] as $f) {
        ok(str_contains($html, 'name="' . $f . '"'), "has $f");
    }
    ok(preg_match('/name="ts" value="([^"]+)"/', $html, $m) === 1);
    eq(lead_spam_flags(['ts' => html_entity_decode($m[1]), 'website_url' => ''], '', test_db(), (string) config()['app_secret'])['spam'], true, 'a fresh token is too fast to submit');
    eq(lead_ts_age(html_entity_decode($m[1]), (string) config()['app_secret']) <= 1, true, 'the token verifies');
    ok(str_contains($html, 'I agree to be contacted about my enquiry by WhatsApp, phone or email, as described in the'), 'consent line');
    foreach (LEAD_OPTIONS['adoption'] as $o) {
        ok(str_contains($html, '<option>' . e($o) . '</option>'), "option $o");
    }
}
