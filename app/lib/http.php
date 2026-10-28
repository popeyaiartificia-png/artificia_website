<?php
declare(strict_types=1);

function e(?string $s): string
{
    return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $to, int $code = 302): never
{
    header('Location: ' . $to, true, $code);
    exit;
}

function json_out(array $data, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/** The uncaught-exception handler outside env=local: logs the error, answers 500 with a static page (JSON for the lead form's fetch). Needs no DB. */
function error_page(Throwable $e): void
{
    error_log('Uncaught ' . $e);
    if (headers_sent()) {
        return;
    }
    $wa = whatsapp_url([]); // the business default: the saved number would need the DB
    http_response_code(500);
    header('Cache-Control: no-store');
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && request_path() === '/lead' && lead_wants_json($_SERVER)) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'whatsapp' => $wa], JSON_UNESCAPED_SLASHES);
        return;
    }
    header('Content-Type: text/html; charset=utf-8');
    echo render_view('500', ['wa' => $wa]);
}

function request_path(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    return is_string($path) && $path !== '' ? rawurldecode($path) : '/';
}
