<?php
declare(strict_types=1);

/** The redirect for $path: an exact row beats a prefix row, and the longest prefix wins. */
function redirect_match(PDO $db, string $path): ?array
{
    $stmt = $db->prepare("SELECT to_path, code FROM redirects
        WHERE (match_type = 'exact' AND from_path = :p1)
           OR (match_type = 'prefix' AND LEFT(:p2, CHAR_LENGTH(from_path)) = from_path)
        ORDER BY match_type = 'exact' DESC, CHAR_LENGTH(from_path) DESC LIMIT 1");
    $stmt->execute(['p1' => $path, 'p2' => $path]);
    $row = $stmt->fetch();
    // The target comes from the DB: only same-site paths and absolute http(s) URLs are ever emitted.
    if (!$row || !preg_match('#^(/(?![/\\\\])|https?://)#i', $row['to_path'])) {
        return null;
    }
    return ['to' => $row['to_path'], 'code' => in_array((int) $row['code'], [301, 302, 307, 308], true) ? (int) $row['code'] : 301];
}

/** Redirect target with the request's query string carried over (ad click ids, utm_*), unless the target has its own. */
function redirect_location(string $to, string $query): string
{
    if ($query === '' || str_contains($to, '?')) {
        return $to;
    }
    [$url, $hash] = explode('#', $to, 2) + [1 => null];
    return $url . '?' . $query . ($hash !== null ? '#' . $hash : '');
}
