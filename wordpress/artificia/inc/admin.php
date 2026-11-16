<?php
// Admin: the Leads inbox (list, detail, spam flag, resend to n8n, CSV export) and one Settings screen, both from the PHP site.
declare(strict_types=1);

require ARTIFICIA_ROOT . '/app/admin/leads.php'; // LEADS_TABS, leads_where(), lead_forward_state(), lead_csv_cell(), lead_phone_display()

/** Settings → Artificia: group => key => [label, type, hint]. The PHP site's settings screen plus its robots.txt / llms.txt desk. */
const ARTIFICIA_SETTINGS = [
    'Tracking codes' => [
        'ga4_id' => ['Google Analytics 4 measurement ID', 'track', 'Looks like G-XXXXXXXXXX.'],
        'gsc_verification' => ['Search Console verification code', 'track', 'Only the code, not the whole meta tag.'],
        'meta_pixel_id' => ['Meta Pixel ID', 'digits', 'Digits only.'],
        'meta_capi_token' => ['Meta Conversions API token', 'secret', ''],
        'meta_test_event_code' => ['Meta test event code', 'track', 'Only while testing in Events Manager; clear it afterwards.'],
    ],
    'Lead routing' => [
        'n8n_webhook_url' => ['n8n webhook URL', 'url', 'Every new lead is sent here.'],
        'n8n_webhook_token' => ['n8n webhook token', 'secret', 'Sent as the X-Artificia-Token header.'],
        'lead_alert_email' => ['Lead alert email', 'email', 'New leads are mailed here.'],
    ],
    'Business details' => [
        'gbp_url' => ['Google Business Profile URL', 'url', ''],
        'org_map_url' => ['Google Maps URL', 'url', 'Destination for the office map link.'],
        'org_map_query' => ['Map location', 'text', 'Google Maps place name or coordinates. Blank uses the address.'],
        'whatsapp_number' => ['WhatsApp number', 'digits', 'With country code. Blank uses 919104488859.'],
        'org_phone' => ['Phone', 'text', ''],
        'org_email' => ['Public email', 'email', ''],
        'org_address' => ['Address', 'textarea', 'Shown in the site footer.'],
    ],
    'robots.txt and llms.txt' => [
        'robots_mode' => ['robots.txt', 'mode', 'Automatic allows everything except /admin. Staging always blocks everything.'],
        'robots_block_ai' => ['Block AI crawlers (GPTBot, ClaudeBot, PerplexityBot, ...)', 'check', 'Automatic mode only.'],
        'robots_custom' => ['Custom robots.txt', 'textarea', 'Used when robots.txt is Custom.'],
        'llms_mode' => ['llms.txt', 'mode', 'Automatic lists every indexable page with its description.'],
        'llms_intro' => ['llms.txt introduction', 'textarea', 'Automatic mode: shown under the title.'],
        'llms_extra' => ['llms.txt closing text', 'textarea', 'Automatic mode: added at the end.'],
        'llms_custom' => ['Custom llms.txt', 'textarea', 'Used when llms.txt is Custom.'],
    ],
];

add_action('admin_menu', function (): void {
    $new = artificia_ist(fn() => leads_recent_count(artificia_leads_db()));
    add_menu_page('Leads', 'Leads' . ($new ? " <span class=\"awaiting-mod\">$new</span>" : ''), 'edit_pages', 'artificia-leads', 'artificia_leads_screen', 'dashicons-email-alt', 3);
    add_options_page('Artificia settings', 'Artificia', 'manage_options', 'artificia-settings', 'artificia_settings_screen');
    remove_menu_page('edit-comments.php');
});

function artificia_leads_db(): PDO
{
    artificia_tables(db());
    return db();
}

/** The inbox view from the query string: tab and form (leads_where() also takes page, source and dates). */
function artificia_leads_view(): array
{
    $tab = (string) ($_GET['tab'] ?? '');
    $form = (string) ($_GET['form'] ?? '');
    return ['tab' => isset(LEADS_TABS[$tab]) ? $tab : '', 'form' => in_array($form, LEAD_TYPES, true) ? $form : '', 'page' => '', 'source' => '', 'from' => '', 'to' => ''];
}

function artificia_leads_screen(): void
{
    artificia_ist(function (): void {
        $db = artificia_leads_db();
        $id = absint($_GET['lead'] ?? 0);
        echo '<div class="wrap">';
        $id ? artificia_lead_detail($db, $id) : artificia_leads_list($db);
        echo '</div>';
    });
}

function artificia_leads_list(PDO $db): void
{
    $v = artificia_leads_view();
    [$where, $args] = leads_where($v);
    $st = $db->prepare("SELECT COUNT(*) FROM leads WHERE $where");
    $st->execute($args);
    $total = (int) $st->fetchColumn();
    $pages = max(1, (int) ceil($total / LEADS_PER_PAGE));
    $p = min($pages, max(1, absint($_GET['p'] ?? 1)));
    $st = $db->prepare("SELECT * FROM leads WHERE $where ORDER BY created_at DESC, id DESC LIMIT " . LEADS_PER_PAGE . ' OFFSET ' . (($p - 1) * LEADS_PER_PAGE));
    $st->execute($args);
    $url = fn(array $q) => esc_url(add_query_arg($q + ['page' => 'artificia-leads'], admin_url('admin.php')));
    echo '<h1 class="wp-heading-inline">Leads</h1>';
    if (current_user_can('manage_options')) {
        echo ' <a class="page-title-action" href="' . esc_url(wp_nonce_url(add_query_arg(['action' => 'artificia_leads_csv', 'tab' => $v['tab'], 'form' => $v['form']], admin_url('admin-post.php')), 'artificia_leads_csv')) . '">Export CSV</a>';
    }
    echo '<ul class="subsubsub">';
    foreach (LEADS_TABS as $tab => $label) {
        echo '<li><a href="' . $url(['tab' => $tab, 'form' => $v['form']]) . '"' . ($tab === $v['tab'] ? ' class="current"' : '') . '>' . esc_html($label) . '</a> | </li>';
    }
    echo '</ul><form method="get" style="clear:both;margin:8px 0"><input type="hidden" name="page" value="artificia-leads"><input type="hidden" name="tab" value="' . esc_attr($v['tab']) . '">';
    echo '<select name="form"><option value="">All forms</option>';
    foreach (LEAD_TYPES as $t) {
        echo '<option' . selected($t, $v['form'], false) . '>' . esc_html($t) . '</option>';
    }
    echo '</select> <button class="button">Filter</button> <span class="description">' . $total . ' lead' . ($total === 1 ? '' : 's') . '</span></form>';
    echo '<table class="widefat striped"><thead><tr><th>Received</th><th>Name</th><th>Phone</th><th>Email</th><th>Form</th><th>Page</th><th>Source</th><th>n8n</th></tr></thead><tbody>';
    foreach ($st->fetchAll() as $r) {
        [, $state] = lead_forward_state($r);
        echo '<tr><td><a href="' . $url(['lead' => $r['id']]) . '">' . esc_html(date('d M Y, g:i A', strtotime($r['created_at']))) . '</a></td><td>' . esc_html($r['name'])
            . '</td><td>' . esc_html(lead_phone_display($r['phone'])) . '</td><td>' . esc_html($r['email']) . '</td><td>' . esc_html($r['form_type'])
            . '</td><td>' . esc_html(lead_page_path($r['page_url'])) . '</td><td>' . esc_html(implode(' / ', array_filter([$r['utm_source'], $r['utm_medium']])))
            . '</td><td>' . esc_html($r['spam'] ? 'Spam' : $state) . '</td></tr>';
    }
    echo '</tbody></table>';
    if ($pages > 1) {
        echo '<p>' . ($p > 1 ? '<a class="button" href="' . $url(['tab' => $v['tab'], 'form' => $v['form'], 'p' => $p - 1]) . '">Newer</a> ' : '')
            . "Page $p of $pages" . ($p < $pages ? ' <a class="button" href="' . $url(['tab' => $v['tab'], 'form' => $v['form'], 'p' => $p + 1]) . '">Older</a>' : '') . '</p>';
    }
}

function artificia_lead_detail(PDO $db, int $id): void
{
    $lead = lead_find($db, $id);
    echo '<h1>Lead ' . $id . '</h1><p><a href="' . esc_url(admin_url('admin.php?page=artificia-leads')) . '">&larr; All leads</a></p>';
    if (!$lead) {
        echo '<p>That lead was not found.</p>';
        return;
    }
    if (isset($_GET['done'])) {
        echo '<div class="notice notice-success"><p>' . esc_html((string) $_GET['done']) . '</p></div>';
    }
    $rows = ['Received' => date('d M Y, g:i A', strtotime($lead['created_at'])), 'Form' => $lead['form_type'], 'Name' => $lead['name'],
        'Phone' => lead_phone_display($lead['phone']), 'Email' => $lead['email'], 'Company' => $lead['company']];
    foreach ($lead['fields'] as $k => $val) {
        $rows[LEAD_LABELS[$k] ?? $k] = lead_field_text($val);
    }
    $rows += ['Page' => $lead['page_url'], 'Referrer' => $lead['referrer'], 'UTM' => implode(' / ', array_filter([$lead['utm_source'], $lead['utm_medium'], $lead['utm_campaign'], $lead['utm_term'], $lead['utm_content']])),
        'gclid' => $lead['gclid'], 'fbclid' => $lead['fbclid'], 'Spam' => $lead['spam'] ? 'Yes: ' . lead_spam_reason_text($lead['spam_reason']) : 'No',
        'n8n' => lead_forward_state($lead)[1] . ($lead['forward_error'] ? ' (' . $lead['forward_error'] . ')' : ''), 'Alert email' => $lead['emailed_at'] ? "Sent {$lead['emailed_at']}" : 'Not sent',
        'Meta CAPI' => $lead['capi_sent_at'] ? "Sent {$lead['capi_sent_at']}" : ($lead['capi_error'] ?: 'Not sent'), 'Event ID' => $lead['event_id']];
    echo '<table class="widefat striped" style="max-width:900px"><tbody>';
    foreach ($rows as $label => $value) {
        $value = (string) $value;
        $cell = preg_match('#^https?://#', $value) ? '<a href="' . esc_url($value) . '" target="_blank" rel="noopener noreferrer">' . esc_html($value) . '</a>' : nl2br(esc_html($value));
        echo '<tr><th style="width:200px">' . esc_html($label) . "</th><td>$cell</td></tr>";
    }
    echo '</tbody></table><form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="margin-top:16px">';
    wp_nonce_field("artificia_lead_$id");
    echo '<input type="hidden" name="action" value="artificia_lead"><input type="hidden" name="lead" value="' . $id . '">';
    echo '<button class="button" name="do" value="' . ($lead['spam'] ? 'not_spam">Not spam' : 'spam">Mark as spam') . '</button> ';
    if (current_user_can('manage_options') && !$lead['spam']) {
        echo '<button class="button" name="do" value="forward">Send to n8n again</button>';
    }
    echo '</form>';
}

add_action('admin_post_artificia_lead', function (): void {
    $id = absint($_POST['lead'] ?? 0);
    check_admin_referer("artificia_lead_$id");
    if (!current_user_can('edit_pages')) {
        wp_die('Not allowed.');
    }
    $done = artificia_ist(function () use ($id): string {
        $db = artificia_leads_db();
        $lead = lead_find($db, $id);
        return match ((string) ($_POST['do'] ?? '')) {
            'spam' => $db->prepare("UPDATE leads SET spam = 1, spam_reason = 'marked by hand' WHERE id = ?")->execute([$id]) ? 'Marked as spam.' : '',
            'not_spam' => $db->prepare('UPDATE leads SET spam = 0, spam_reason = NULL WHERE id = ?')->execute([$id]) ? 'Marked as not spam.' : '',
            'forward' => $lead && current_user_can('manage_options')
                ? ((lead_forward($db, $lead, settings(), config()['base_url']) ?? '') === '' ? 'Sent to n8n.' : 'n8n did not take it; see the n8n row.') : '',
            default => '',
        };
    });
    wp_safe_redirect(add_query_arg(['page' => 'artificia-leads', 'lead' => $id, 'done' => $done], admin_url('admin.php')));
    exit;
});

// The PHP site's CSV export, column for column.
add_action('admin_post_artificia_leads_csv', function (): void {
    check_admin_referer('artificia_leads_csv');
    if (!current_user_can('manage_options')) {
        wp_die('Not allowed.');
    }
    artificia_ist(function (): void {
        [$where, $args] = leads_where(artificia_leads_view());
        $st = artificia_leads_db()->prepare("SELECT * FROM leads WHERE $where ORDER BY created_at DESC, id DESC");
        $st->execute($args);
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="artificia-leads-' . date('Y-m-d') . '.csv"');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['Lead ID', 'Received', 'Form', 'Name', 'Phone', 'Email', 'Company', 'Details', 'Page', 'Referrer', 'UTM source', 'UTM medium',
            'UTM campaign', 'UTM term', 'UTM content', 'gclid', 'fbclid', 'Spam', 'Forwarded at', 'Forward error', 'Emailed at', 'Event ID'], ',', '"', '');
        while ($r = $st->fetch()) {
            $details = [];
            foreach (json_decode((string) $r['fields'], true) ?: [] as $k => $val) {
                $details[] = (LEAD_LABELS[$k] ?? $k) . ': ' . lead_field_text($val);
            }
            fputcsv($out, array_map('lead_csv_cell', [$r['id'], $r['created_at'], $r['form_type'], $r['name'], lead_phone_display($r['phone']), $r['email'], $r['company'],
                implode("\n", $details), $r['page_url'], $r['referrer'], $r['utm_source'], $r['utm_medium'], $r['utm_campaign'], $r['utm_term'],
                $r['utm_content'], $r['gclid'], $r['fbclid'], $r['spam'] ? 'yes' : 'no', $r['forwarded_at'], $r['forward_error'], $r['emailed_at'], $r['event_id']]), ',', '"', '');
        }
    });
    exit;
});

/** Checks and saves the settings form; nothing is saved while any field is invalid. @return array<string,string> errors by key */
function artificia_settings_save(array $in): array
{
    $current = settings();
    $next = $current;
    $errors = [];
    foreach (ARTIFICIA_SETTINGS as $group) {
        foreach ($group as $key => [$label, $type]) {
            $v = trim(wp_unslash((string) ($in[$key] ?? '')));
            if ($type === 'check') {
                $v = isset($in[$key]) ? '1' : '';
            } elseif ($type === 'secret' && $v === '') {
                $v = isset($in["clear_$key"]) ? '' : ($current[$key] ?? ''); // blank keeps the saved secret
            }
            $bad = $v !== '' && match ($type) {
                'track' => !preg_match('/^[A-Za-z0-9_-]+$/', $v),
                'digits' => !preg_match('/^\d+$/', $v),
                'email' => !is_email($v),
                'url' => !preg_match('#^https?://\S+$#i', $v),
                'mode' => !in_array($v, ['', 'custom'], true),
                default => false,
            };
            if ($bad) {
                $errors[$key] = "$label: check the format.";
            }
            $next[$key] = str_replace("\r", '', $v);
        }
    }
    if (!$errors) {
        update_option('artificia_settings', $next);
    }
    return $errors;
}

function artificia_settings_screen(): void
{
    $errors = [];
    if (isset($_POST['artificia_settings'])) {
        check_admin_referer('artificia_settings');
        $errors = artificia_settings_save($_POST);
        echo $errors ? '<div class="notice notice-error"><p>' . esc_html(implode(' ', $errors)) . ' Nothing was saved.</p></div>' : '<div class="notice notice-success"><p>Saved.</p></div>';
    }
    $values = $errors ? array_map(fn($v) => wp_unslash((string) $v), $_POST) + settings() : settings();
    echo '<div class="wrap"><h1>Artificia settings</h1><form method="post">';
    wp_nonce_field('artificia_settings');
    echo '<input type="hidden" name="artificia_settings" value="1">';
    foreach (ARTIFICIA_SETTINGS as $group => $fields) {
        echo '<h2>' . esc_html($group) . '</h2><table class="form-table"><tbody>';
        foreach ($fields as $key => [$label, $type, $hint]) {
            $v = (string) ($values[$key] ?? '');
            $name = esc_attr($key);
            $input = match ($type) {
                'textarea' => "<textarea class=\"large-text\" rows=\"4\" id=\"$name\" name=\"$name\">" . esc_textarea($v) . '</textarea>',
                'check' => "<input type=\"checkbox\" id=\"$name\" name=\"$name\" value=\"1\"" . checked($v, '1', false) . '>',
                'mode' => "<select id=\"$name\" name=\"$name\"><option value=\"\">Automatic</option><option value=\"custom\"" . selected($v, 'custom', false) . '>Custom</option></select>',
                'secret' => "<input class=\"regular-text\" type=\"password\" autocomplete=\"new-password\" id=\"$name\" name=\"$name\" value=\"\" placeholder=\"" . ($v !== '' ? 'Saved. Type a new one to replace it.' : '') . '">'
                    . ($v !== '' ? " <label><input type=\"checkbox\" name=\"clear_$name\"> Clear</label>" : ''),
                default => "<input class=\"regular-text\" type=\"text\" id=\"$name\" name=\"$name\" value=\"" . esc_attr($v) . '">',
            };
            echo "<tr><th><label for=\"$name\">" . esc_html($label) . "</label></th><td>$input" . ($hint !== '' ? '<p class="description">' . esc_html($hint) . '</p>' : '') . '</td></tr>';
        }
        echo '</tbody></table>';
    }
    submit_button();
    echo '</form></div>';
}
