<?php
declare(strict_types=1);

// Leads: POST /lead (validate, rate limit, save, answer), then the follow-ups: Meta Conversions API, the alert
// email and the n8n forward. The visitor's answer depends only on the saved row; follow-ups never change it.

const LEAD_TYPES = ['masterclass', 'corporate', 'services', 'contact'];

/** Each form's fields => required. name, email, phone and company are columns; the rest go in `fields`. */
const LEAD_FORMS = [
    'masterclass' => ['name' => true, 'phone' => true, 'email' => true, 'i_am' => true, 'track' => false],
    'corporate' => ['name' => true, 'email' => true, 'phone' => true, 'company' => true, 'employees' => true, 'adoption' => true,
        'location' => true, 'objectives' => false, 'challenge' => false],
    'services' => ['name' => true, 'phone' => true, 'email' => true, 'company' => false, 'service' => true, 'message' => false],
    'contact' => ['name' => true, 'phone' => true, 'email' => false, 'message' => true],
];

/** The options of each select (and the objectives checkboxes): lead_form.php draws these, lead_validate() accepts only these. */
const LEAD_OPTIONS = [
    'i_am' => ['Student / Recent graduate', 'Working professional / Manager', 'Business owner / Founder', 'Freelancer / Creator', 'Job seeker', 'Other'],
    'track' => ['30-day Foundation', '60-day Full program', 'Not sure yet'],
    'employees' => ['1–10', '11–25', '26–50', '51–100', '100+'],
    'adoption' => ['Not using AI yet', 'Exploring (ChatGPT, Claude)', 'Some tools or automations', 'Custom automations or models'],
    'objectives' => ['Automate repetitive work', 'Upskill the team on AI', 'Build AI agents or chatbots', 'Integrate n8n / Make / APIs', 'Something else'],
    'service' => ['GoHighLevel setup', 'Website development', 'Content and marketing', 'RAG or custom automation', 'AI automation', 'Not sure yet'],
];

/** Readable names for the `fields` answers (alert email, admin detail, CSV). */
const LEAD_LABELS = [
    'i_am' => 'Which describes them', 'track' => 'Track', 'employees' => 'Team size', 'adoption' => 'AI today', 'location' => 'City',
    'objectives' => 'Objectives', 'challenge' => 'Anything we should know', 'service' => 'Service', 'message' => 'Message',
];

/** Tracking fields every form posts (hidden), with their column sizes. */
const LEAD_TRACKING = [
    'page_url' => 1000, 'referrer' => 1000, 'utm_source' => 255, 'utm_medium' => 255, 'utm_campaign' => 255, 'utm_term' => 255,
    'utm_content' => 255, 'gclid' => 255, 'fbclid' => 255, 'fbp' => 255, 'fbc' => 255,
];

const LEAD_COLUMNS = ['form_type', 'name', 'email', 'phone', 'company', 'page_url', 'referrer', 'utm_source', 'utm_medium', 'utm_campaign',
    'utm_term', 'utm_content', 'gclid', 'fbclid', 'fbp', 'fbc', 'ip', 'user_agent', 'consent_at', 'spam'];

/** 10 digits starting 6-9, or 91 + 10 digits, become +91…; + and 8-15 digits stays; anything else is null. */
function normalize_phone(string $raw): ?string
{
    $s = preg_replace('/[\s().-]+/', '', trim($raw));
    if (preg_match('/^[6-9]\d{9}$/', $s)) {
        return '+91' . $s;
    }
    if (preg_match('/^91\d{10}$/', $s)) {
        return '+' . $s;
    }
    return preg_match('/^\+\d{8,15}$/', $s) ? $s : null;
}

/** A posted string, valid UTF-8, trimmed and cut to $max characters; anything else (arrays) is ''. */
function lead_str(mixed $v, int $max): string
{
    return is_string($v) ? mb_substr(trim(mb_scrub($v, 'UTF-8')), 0, $max, 'UTF-8') : '';
}

/**
 * Checks one submission against its form. data holds the row's columns (name, email, phone, company, the tracking
 * fields) plus `fields`: this form's other answers, empty ones left out.
 * @return array{ok:bool, errors:array<string,string>, data:array}
 */
function lead_validate(string $type, array $in): array
{
    if (!isset(LEAD_FORMS[$type])) {
        return ['ok' => false, 'errors' => ['form_type' => 'This form is not recognised. Reload the page and try again.'], 'data' => []];
    }
    $errors = [];
    $data = ['form_type' => $type, 'name' => '', 'email' => '', 'phone' => '', 'company' => '', 'fields' => []];
    foreach (LEAD_FORMS[$type] as $key => $required) {
        if ($key === 'objectives') {
            $picked = is_array($in[$key] ?? null) ? array_values(array_filter($in[$key], fn($v) => is_string($v) && $v !== '')) : [];
            if (array_diff($picked, LEAD_OPTIONS[$key])) {
                $errors[$key] = 'Choose from the listed options.';
            } elseif ($picked) {
                $data['fields'][$key] = array_values(array_intersect(LEAD_OPTIONS[$key], $picked)); // listed order, no repeats
            }
            continue;
        }
        if (isset($in[$key]) && !is_string($in[$key])) {
            $errors[$key] = 'Fill this in as text.';
            continue;
        }
        $v = lead_str($in[$key] ?? '', in_array($key, ['message', 'challenge'], true) ? 5000 : 255);
        if ($v === '') {
            if ($required) {
                $errors[$key] = isset(LEAD_OPTIONS[$key]) ? 'Choose one.' : 'Fill this in.';
            }
            continue;
        }
        if ($key === 'email' && !filter_var($v, FILTER_VALIDATE_EMAIL)) {
            $errors[$key] = 'Enter a valid email address, like name@example.com.';
        } elseif ($key === 'phone' && ($v = normalize_phone($v)) === null) {
            $errors[$key] = 'Enter a 10-digit mobile number, or the full number starting with + and the country code.';
        } elseif (isset(LEAD_OPTIONS[$key]) && !in_array($v, LEAD_OPTIONS[$key], true)) {
            $errors[$key] = 'Choose one of the listed options.';
        } elseif (array_key_exists($key, $data)) {
            $data[$key] = $v;
        } else {
            $data['fields'][$key] = $v;
        }
    }
    if (($in['consent'] ?? '') !== '1') {
        $errors['consent'] = 'Tick this box so we can reply to you.';
    }
    foreach (LEAD_TRACKING as $key => $max) {
        $data[$key] = lead_str($in[$key] ?? '', $max);
    }
    foreach (['page_url', 'referrer'] as $key) { // shown as links in the admin, so only web addresses
        if (!preg_match('#^https?://#i', $data[$key])) {
            $data[$key] = '';
        }
    }
    return ['ok' => !$errors, 'errors' => $errors, 'data' => $data];
}

/** The form's time token, "<unix time>.<hmac>", printed by lead_form.php. */
function lead_ts(string $secret, ?int $time = null): string
{
    $t = (string) ($time ?? time());
    return $t . '.' . hash_hmac('sha256', $t, $secret);
}

/** Seconds since a genuine token was issued; null for a missing or forged one. */
function lead_ts_age(string $ts, string $secret): ?int
{
    if (!preg_match('/^(\d{1,12})\.([0-9a-f]{64})$/D', $ts, $m) || !hash_equals(hash_hmac('sha256', $m[1], $secret), $m[2])) {
        return null;
    }
    return time() - (int) $m[1];
}

/**
 * spam: the honeypot is filled, or the token is missing, forged, under 3 s or over 24 h old.
 * rate_limited: this is more than the 5th submission from $ip in 10 minutes (every call counts one).
 * @return array{spam:bool, rate_limited:bool}
 */
function lead_spam_flags(array $in, string $ip, PDO $db, string $secret): array
{
    $age = lead_ts_age(is_string($in['ts'] ?? null) ? $in['ts'] : '', $secret);
    $honeypot = $in['website_url'] ?? '';
    $spam = !is_string($honeypot) || trim($honeypot) !== '' || $age === null || $age < 3 || $age > 86400;
    try {
        $db->exec('DELETE FROM lead_rate WHERE created_at < NOW() - INTERVAL 1 DAY');
        $db->prepare('INSERT INTO lead_rate (ip) VALUES (?)')->execute([$ip]);
        $st = $db->prepare('SELECT COUNT(*) FROM lead_rate WHERE ip = ? AND created_at > NOW() - INTERVAL 10 MINUTE');
        $st->execute([$ip]);
        $limited = (int) $st->fetchColumn() > 5;
    } catch (PDOException $e) {
        error_log('Lead rate limit skipped: ' . $e->getMessage()); // a broken limiter must not cost a lead
        $limited = false;
    }
    return ['spam' => $spam, 'rate_limited' => $limited];
}

/** Inserts the lead (keys from LEAD_COLUMNS plus `fields`) with a fresh UUID v4 event_id. @return array{id:int, event_id:string} */
function lead_save(PDO $db, array $lead): array
{
    $b = random_bytes(16);
    $b[6] = chr(ord($b[6]) & 0x0f | 0x40);
    $b[8] = chr(ord($b[8]) & 0x3f | 0x80);
    $eventId = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($b), 4));
    $row = ['event_id' => $eventId] + array_intersect_key($lead, array_flip(LEAD_COLUMNS))
        + ['fields' => json_encode((object) ($lead['fields'] ?? []), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)];
    $db->prepare('INSERT INTO leads (' . implode(', ', array_keys($row)) . ') VALUES (' . implode(', ', array_fill(0, count($row), '?')) . ')')
       ->execute(array_values($row));
    return ['id' => (int) $db->lastInsertId(), 'event_id' => $eventId];
}

function lead_find(PDO $db, int $id): ?array
{
    $st = $db->prepare('SELECT * FROM leads WHERE id = ?');
    $st->execute([$id]);
    $row = $st->fetch();
    if (!$row) {
        return null;
    }
    $row['fields'] = json_decode((string) $row['fields'], true) ?: [];
    return $row;
}

/**
 * POST /lead without the output: validate (422), rate limit (429), save (500 if it fails), then 200 with the event id,
 * spam included. id is the saved lead (follow-ups run for it), or null.
 * @return array{status:int, body:array, id:?int}
 */
function lead_handle(PDO $db, array $in, array $server, array $settings): array
{
    $wa = whatsapp_url($settings);
    $v = lead_validate(is_string($in['form_type'] ?? null) ? $in['form_type'] : '', $in);
    if (!$v['ok']) {
        return ['status' => 422, 'body' => ['ok' => false, 'errors' => $v['errors']], 'id' => null];
    }
    $ip = substr((string) ($server['REMOTE_ADDR'] ?? ''), 0, 45);
    $flags = lead_spam_flags($in, $ip, $db, (string) config()['app_secret']);
    if ($flags['rate_limited']) {
        return ['status' => 429, 'body' => ['ok' => false, 'message' => 'Too many enquiries from this connection in the last few minutes. Please message us on WhatsApp instead.', 'whatsapp' => $wa], 'id' => null];
    }
    try {
        $saved = lead_save($db, $v['data'] + [
            'ip' => $ip, 'user_agent' => lead_str($server['HTTP_USER_AGENT'] ?? '', 500),
            'consent_at' => date('Y-m-d H:i:s'), 'spam' => (int) $flags['spam'],
        ]);
    } catch (Throwable $e) {
        error_log('Lead not saved: ' . $e->getMessage());
        return ['status' => 500, 'body' => ['ok' => false, 'message' => 'Your enquiry could not be saved just now. Please send it to us on WhatsApp instead.', 'whatsapp' => $wa], 'id' => null];
    }
    return ['status' => 200, 'body' => ['ok' => true, 'event_id' => $saved['event_id']], 'id' => $saved['id']];
}

/** The router's POST /lead: answers the visitor first, then runs the follow-ups after the connection is released. */
function lead_endpoint(PDO $db): void
{
    $settings = settings($db);
    $r = lead_handle($db, $_POST, $_SERVER, $settings);
    http_response_code($r['status']);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($r['body'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($r['id'] === null) {
        return;
    }
    ignore_user_abort(true);
    if (function_exists('litespeed_finish_request')) {
        litespeed_finish_request();
    } elseif (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    }
    lead_followups($db, $r['id'], $settings, (string) config()['base_url']);
}

/** For a saved, non-spam lead: Meta CAPI (if pixel and token are set), the alert email, the n8n forward. Each writes its own columns. */
function lead_followups(PDO $db, int $id, array $settings, string $baseUrl): void
{
    $lead = lead_find($db, $id);
    if (!$lead || $lead['spam']) {
        return;
    }
    $steps = [
        'capi' => function () use ($db, $lead, $settings) {
            $pixel = tracking_id($settings, 'meta_pixel_id');
            $token = (string) ($settings['meta_capi_token'] ?? '');
            if ($pixel === '' || $token === '') {
                return;
            }
            $body = meta_capi_payload($lead, $lead['page_url'], (string) ($settings['meta_test_event_code'] ?? '')) + ['access_token' => $token];
            $r = lead_post_json("https://graph.facebook.com/v23.0/$pixel/events", $body);
            $db->prepare($r['ok'] ? 'UPDATE leads SET capi_sent_at = NOW(), capi_error = NULL WHERE id = ?' : 'UPDATE leads SET capi_error = ? WHERE id = ?')
               ->execute($r['ok'] ? [$lead['id']] : [$r['error'], $lead['id']]);
        },
        'email' => function () use ($db, $lead, $settings, $baseUrl) {
            $to = trim((string) ($settings['lead_alert_email'] ?? ''));
            if ($to !== '' && send_mail($to, lead_alert_subject($lead), lead_alert_text($lead, $baseUrl))) {
                $db->prepare('UPDATE leads SET emailed_at = NOW() WHERE id = ?')->execute([$lead['id']]);
            }
        },
        'forward' => fn() => lead_forward($db, $lead, $settings, $baseUrl),
    ];
    foreach ($steps as $name => $step) {
        try {
            $step();
        } catch (Throwable $e) {
            error_log("Lead $id $name failed: " . $e->getMessage()); // one failing step must not stop the others
        }
    }
}

/** POSTs the lead to the n8n webhook and records the result. @return ?string null when n8n took it, else the error */
function lead_forward(PDO $db, array $lead, array $settings, string $baseUrl): ?string
{
    $url = trim((string) ($settings['n8n_webhook_url'] ?? ''));
    if ($url === '') {
        $error = 'no webhook configured';
    } else {
        $r = lead_post_json($url, n8n_payload($lead, $baseUrl), ['X-Artificia-Token: ' . ($settings['n8n_webhook_token'] ?? '')]);
        $error = $r['ok'] ? null : $r['error'];
    }
    $db->prepare($error === null ? 'UPDATE leads SET forwarded_at = NOW(), forward_error = NULL WHERE id = ?' : 'UPDATE leads SET forward_error = ? WHERE id = ?')
       ->execute($error === null ? [$lead['id']] : [$error, $lead['id']]);
    return $error;
}

/** JSON POST with a 5 s limit. A non-2xx answer or a timeout is a failure. @return array{ok:bool, error:string} */
function lead_post_json(string $url, array $body, array $headers = []): array
{
    if (!preg_match('#^https?://#i', $url)) {
        return ['ok' => false, 'error' => 'the webhook address must start with http:// or https://'];
    }
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        CURLOPT_HTTPHEADER => array_merge(['Content-Type: application/json', 'Accept: application/json'], $headers),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 5,
    ]);
    $resp = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    if ($resp === false) {
        return ['ok' => false, 'error' => curl_error($ch) ?: 'request failed'];
    }
    if ($code < 200 || $code > 299) {
        return ['ok' => false, 'error' => "HTTP $code: " . mb_substr(trim(mb_scrub((string) $resp, 'UTF-8')), 0, 500, 'UTF-8')];
    }
    return ['ok' => true, 'error' => ''];
}

/** The Conversions API body for one lead: {data: [event], test_event_code?}. The token is added at send time. */
function meta_capi_payload(array $lead, string $pageUrl, string $testCode = ''): array
{
    $time = isset($lead['created_at']) ? (int) strtotime((string) $lead['created_at']) : time();
    $email = strtolower(trim((string) ($lead['email'] ?? '')));
    $digits = preg_replace('/\D/', '', (string) ($lead['phone'] ?? ''));
    $fbc = (string) ($lead['fbc'] ?? '');
    if ($fbc === '' && ($lead['fbclid'] ?? '') !== '') {
        $fbc = 'fb.1.' . ($time * 1000) . '.' . $lead['fbclid'];
    }
    $user = array_filter([
        'em' => $email !== '' ? hash('sha256', $email) : '',
        'ph' => $digits !== '' ? hash('sha256', $digits) : '',
        'client_ip_address' => (string) ($lead['ip'] ?? ''),
        'client_user_agent' => (string) ($lead['user_agent'] ?? ''),
        'fbp' => (string) ($lead['fbp'] ?? ''),
        'fbc' => $fbc,
    ], fn($v) => $v !== '');
    $body = ['data' => [[
        'event_name' => 'Lead', 'event_time' => $time, 'event_id' => (string) $lead['event_id'],
        'action_source' => 'website', 'event_source_url' => $pageUrl, 'user_data' => $user,
    ]]];
    if ($testCode !== '') {
        $body['test_event_code'] = $testCode;
    }
    return $body;
}

/** The n8n webhook body. A fixed contract with the separate n8n workflow: change it only together with that workflow. */
function n8n_payload(array $lead, string $baseUrl): array
{
    $fields = is_array($lead['fields'] ?? null) ? $lead['fields'] : (json_decode((string) ($lead['fields'] ?? ''), true) ?: []);
    return [
        'lead_id' => (int) $lead['id'],
        'event_id' => (string) $lead['event_id'],
        'form_type' => (string) $lead['form_type'],
        'name' => (string) $lead['name'],
        'email' => (string) $lead['email'],
        'phone' => (string) $lead['phone'],
        'company' => (string) $lead['company'],
        'fields' => (object) $fields,
        'page_url' => (string) $lead['page_url'],
        'referrer' => (string) $lead['referrer'],
        'utm' => [
            'source' => (string) $lead['utm_source'], 'medium' => (string) $lead['utm_medium'], 'campaign' => (string) $lead['utm_campaign'],
            'term' => (string) $lead['utm_term'], 'content' => (string) $lead['utm_content'],
        ],
        'gclid' => (string) $lead['gclid'],
        'fbclid' => (string) $lead['fbclid'],
        'submitted_at' => date('c', (int) strtotime((string) $lead['created_at'])),
        'admin_url' => rtrim($baseUrl, '/') . '/admin/leads/' . (int) $lead['id'],
    ];
}

/** A `fields` answer as one line of text (lists joined with commas). */
function lead_field_text(mixed $v): string
{
    return is_array($v) ? implode(', ', $v) : (string) $v;
}

function lead_alert_subject(array $lead): string
{
    return 'New ' . $lead['form_type'] . ' lead: ' . ($lead['name'] !== '' ? $lead['name'] : $lead['phone']);
}

function lead_alert_text(array $lead, string $baseUrl): string
{
    $lines = ["A new {$lead['form_type']} enquiry came in on the website.", ''];
    foreach (['Name' => $lead['name'], 'Phone' => $lead['phone'], 'Email' => $lead['email'], 'Company' => $lead['company']] as $k => $v) {
        if ($v !== '') {
            $lines[] = "$k: $v";
        }
    }
    foreach ($lead['fields'] as $k => $v) {
        $lines[] = (LEAD_LABELS[$k] ?? $k) . ': ' . lead_field_text($v);
    }
    $source = implode(' / ', array_filter([$lead['utm_source'], $lead['utm_medium'], $lead['utm_campaign']]));
    $lines[] = '';
    $lines[] = 'Page: ' . ($lead['page_url'] ?: 'not known');
    $lines[] = 'Source: ' . ($source ?: ($lead['referrer'] ?: 'direct'));
    $lines[] = 'Received: ' . date('d/m/y, g:i A T', (int) strtotime((string) $lead['created_at']));
    $lines[] = '';
    $lines[] = 'Open it in the admin: ' . rtrim($baseUrl, '/') . '/admin/leads/' . (int) $lead['id'];
    return implode("\n", $lines);
}
