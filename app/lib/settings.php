<?php
declare(strict_types=1);

/** @return array<string,string> */
function settings(PDO $db): array
{
    return $db->query('SELECT k, v FROM settings')->fetchAll(PDO::FETCH_KEY_PAIR);
}

function setting_set(PDO $db, string $key, string $value): void
{
    $db->prepare('INSERT INTO settings (k, v) VALUES (?, ?) ON DUPLICATE KEY UPDATE v = VALUES(v)')
       ->execute([$key, $value]);
}
