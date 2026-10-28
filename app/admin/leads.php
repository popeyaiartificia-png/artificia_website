<?php
declare(strict_types=1);

// Admin leads inbox: list (tabs, filters, 50 a screen), detail, CSV export, delete and retry forward.
// Both roles read; only an admin exports, deletes or retries.

const LEADS_PER_PAGE = 50;
const LEADS_TABS = ['' => 'All (not spam)', 'unforwarded' => 'Not forwarded', 'spam' => 'Spam'];

/** Non-spam leads from the last 24 hours: the Leads nav badge (ruling R34). */
function leads_recent_count(PDO $db): int
{
    return (int) $db->query('SELECT COUNT(*) FROM leads WHERE spam = 0 AND created_at >= NOW() - INTERVAL 1 DAY')->fetchColumn();
}

/** The inbox view from the query string, cleaned: tab, form, page, source, from, to (Y-m-d). */
function leads_view_from_get(): array
{
    $date = fn(string $k) => preg_match('/^(\d{4})-(\d\d)-(\d\d)$/D', admin_get_str($k), $m) && checkdate((int) $m[2], (int) $m[3], (int) $m[1]) ? $m[0] : '';
    return [
        'tab' => isset(LEADS_TABS[admin_get_str('tab')]) ? admin_get_str('tab') : '',
        'form' => in_array(admin_get_str('form'), LEAD_TYPES, true) ? admin_get_str('form') : '',
        'page' => admin_get_str('page'),
        'source' => admin_get_str('source'),
        'from' => $date('from'),
        'to' => $date('to'),
    ];
}

/** WHERE clause and args for a view; $withTab = false leaves the tab out (for the tab counts). */
function leads_where(array $v, bool $withTab = true): array
{
    $where = ['1 = 1'];
    $args = [];
    if ($withTab) {
        $where[] = ['' => 'spam = 0', 'unforwarded' => 'spam = 0 AND forwarded_at IS NULL', 'spam' => 'spam = 1'][$v['tab']];
    }
    foreach (['form' => 'form_type = ?', 'page' => 'page_url = ?', 'source' => 'utm_source = ?'] as $k => $sql) {
        if ($v[$k] !== '') {
            $where[] = $sql;
            $args[] = $v[$k];
        }
    }
    if ($v['from'] !== '') {
        $where[] = 'created_at >= ?';
        $args[] = $v['from'] . ' 00:00:00';
    }
    if ($v['to'] !== '') {
        $where[] = 'created_at < ? + INTERVAL 1 DAY';
        $args[] = $v['to'] . ' 00:00:00';
    }
    return [implode(' AND ', $where), $args];
}

/** [pill class, label] for a lead's delivery to n8n. */
function lead_forward_state(array $lead): array
{
    return match (true) {
        $lead['forwarded_at'] !== null => ['pill--published', 'Forwarded'],
        $lead['forward_error'] === 'no webhook configured' => ['pill--warn', 'Not forwarded'],
        $lead['forward_error'] !== null => ['pill--error', 'Not forwarded'],
        (bool) $lead['spam'] => ['pill--draft', 'Spam, not sent'],
        default => ['pill--draft', 'Sending'],
    };
}

function lead_page_path(string $url): string
{
    return $url === '' ? '' : (parse_url($url, PHP_URL_PATH) ?: '/');
}

function admin_leads(PDO $db, string $method): HttpResponse
{
    $user = auth_require('seo', $db);
    $v = leads_view_from_get();
    [$where, $args] = leads_where($v);
    $st = $db->prepare("SELECT COUNT(*) FROM leads WHERE $where");
    $st->execute($args);
    $total = (int) $st->fetchColumn();
    $pages = max(1, (int) ceil($total / LEADS_PER_PAGE));
    $p = min($pages, max(1, (int) admin_get_str('p')));
    $st = $db->prepare("SELECT id, created_at, name, phone, email, form_type, page_url, utm_source, utm_medium, spam, spam_reason, forwarded_at, forward_error
        FROM leads WHERE $where ORDER BY created_at DESC, id DESC LIMIT " . LEADS_PER_PAGE . ' OFFSET ' . (($p - 1) * LEADS_PER_PAGE));
    $st->execute($args);
    $rows = $st->fetchAll();
    [$cw, $cargs] = leads_where($v, false);
    $st = $db->prepare("SELECT COALESCE(SUM(spam = 0), 0), COALESCE(SUM(spam = 0 AND forwarded_at IS NULL), 0), COALESCE(SUM(spam = 1), 0) FROM leads WHERE $cw");
    $st->execute($cargs);
    $counts = array_combine(array_keys(LEADS_TABS), array_map('intval', $st->fetch(PDO::FETCH_NUM)));
    $options = [
        'page' => $db->query("SELECT DISTINCT page_url FROM leads WHERE page_url <> '' ORDER BY page_url")->fetchAll(PDO::FETCH_COLUMN),
        'source' => $db->query("SELECT DISTINCT utm_source FROM leads WHERE utm_source <> '' ORDER BY utm_source")->fetchAll(PDO::FETCH_COLUMN),
    ];
    return new HttpResponse(200, admin_view('leads', ['title' => 'Leads', 'rows' => $rows, 'total' => $total, 'p' => $p, 'pages' => $pages,
        'v' => $v, 'counts' => $counts, 'options' => $options, 'isAdmin' => admin_role_allows($user, 'admin')], $user));
}

/**
 * A spreadsheet cell: anything that starts like a formula (= + - @ tab CR) gets a leading apostrophe, so it stays text.
 * That includes every phone number: Excel reads a leading + as a formula, and bare digits as a number (9.19877E+11).
 */
function lead_csv_cell(mixed $v): string
{
    $s = (string) $v;
    return preg_match('/^[=+@\t\r-]/', $s) ? "'" . $s : $s;
}

/** +919876543210 as "+91 98765 43210"; other numbers as stored. */
function lead_phone_display(string $phone): string
{
    return preg_match('/^\+91([0-9]{5})([0-9]{5})$/', $phone, $m) ? "+91 $m[1] $m[2]" : $phone;
}

function admin_leads_export(PDO $db, string $method): HttpResponse
{
    auth_require('admin', $db);
    $v = leads_view_from_get();
    [$where, $args] = leads_where($v);
    $st = $db->prepare("SELECT * FROM leads WHERE $where ORDER BY created_at DESC, id DESC");
    $st->execute($args);
    $cols = ['Lead ID', 'Received', 'Form', 'Name', 'Phone', 'Email', 'Company', 'Details', 'Page', 'Referrer', 'UTM source', 'UTM medium',
        'UTM campaign', 'UTM term', 'UTM content', 'gclid', 'fbclid', 'Spam', 'Forwarded at', 'Forward error', 'Emailed at', 'Event ID'];
    $out = fopen('php://temp', 'r+');
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, $cols, ',', '"', '');
    while ($r = $st->fetch()) {
        $details = [];
        foreach (json_decode((string) $r['fields'], true) ?: [] as $k => $val) {
            $details[] = (LEAD_LABELS[$k] ?? $k) . ': ' . lead_field_text($val);
        }
        fputcsv($out, array_map('lead_csv_cell', [$r['id'], $r['created_at'], $r['form_type'], $r['name'], lead_phone_display($r['phone']), $r['email'], $r['company'],
            implode("\n", $details), $r['page_url'], $r['referrer'], $r['utm_source'], $r['utm_medium'], $r['utm_campaign'], $r['utm_term'],
            $r['utm_content'], $r['gclid'], $r['fbclid'], $r['spam'] ? 'yes' : 'no', $r['forwarded_at'], $r['forward_error'], $r['emailed_at'], $r['event_id']]), ',', '"', '');
    }
    rewind($out);
    if (PHP_SAPI !== 'cli') {
        header('Content-Disposition: attachment; filename="artificia-leads-' . date('Y-m-d') . '.csv"');
    }
    return new HttpResponse(200, (string) stream_get_contents($out), null, 'text/csv; charset=utf-8');
}

/** GET: the lead. POST (admin only): action=delete or action=retry. */
function admin_lead(PDO $db, string $method, int $id): HttpResponse
{
    $user = auth_require($method === 'POST' ? 'admin' : 'seo', $db);
    $lead = lead_find($db, $id);
    if (!$lead) {
        return admin_not_found($user);
    }
    if ($method === 'POST') {
        $action = admin_post_str('action');
        if ($action === 'delete') {
            $db->prepare('DELETE FROM leads WHERE id = ?')->execute([$id]);
            return admin_redirect('/admin/leads', 'Lead deleted. It is gone from the inbox and the export; remove it from the Google Sheet too if it was forwarded.');
        }
        if ($action === 'retry') {
            $error = lead_forward($db, $lead, settings($db), (string) config()['base_url']);
            return $error === null
                ? admin_redirect("/admin/leads/$id", 'Forwarded. n8n has the lead now.')
                : admin_redirect("/admin/leads/$id", "Not forwarded: $error", 'error');
        }
    }
    return new HttpResponse(200, admin_view('lead', ['title' => $lead['name'] ?: 'Lead', 'lead' => $lead, 'isAdmin' => admin_role_allows($user, 'admin')], $user));
}
