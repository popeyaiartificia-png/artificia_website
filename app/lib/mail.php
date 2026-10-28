<?php
declare(strict_types=1);

/** Plain-text mail via PHP mail(), From = config mail_from. In local env it is appended to storage/mail.log instead. */
function send_mail(string $to, string $subject, string $text): bool
{
    $from = config()['mail_from'] ?? 'info@artificiaai.com';
    $oneLine = fn(string $s): string => trim(preg_replace('/[\r\n]+/', ' ', $s)); // no header injection
    $to = $oneLine($to);
    $subject = $oneLine($subject);
    if (config()['env'] === 'local') {
        $entry = "--- " . date('c') . "\nFrom: $from\nTo: $to\nSubject: $subject\n\n$text\n\n";
        return file_put_contents(dirname(__DIR__, 2) . '/storage/mail.log', $entry, FILE_APPEND | LOCK_EX) !== false;
    }
    return mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $text,
        "From: $from\r\nContent-Type: text/plain; charset=UTF-8\r\nMIME-Version: 1.0");
}
