<?php
declare(strict_types=1);

/** The redirect for $path: an exact row beats a prefix row, and the longest prefix wins. A prefix row "/old/" also covers "/old". */
function redirect_match(PDO $db, string $path): ?array
{
    $stmt = $db->prepare("SELECT to_path, code FROM redirects
        WHERE (match_type = 'exact' AND from_path = :p1)
           OR (match_type = 'prefix' AND (LEFT(:p2, CHAR_LENGTH(from_path)) = from_path OR from_path = :p3))
        ORDER BY match_type = 'exact' DESC, CHAR_LENGTH(from_path) DESC LIMIT 1");
    $stmt->execute(['p1' => $path, 'p2' => $path, 'p3' => "$path/"]);
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

/**
 * Insert a redirect, or update it when $r['id'] is set; returns its id.
 * Throws InvalidArgumentException with a message for the SEO editor on any rule break.
 * WordPress supplies its published-page lookup; the original CMS uses redirect_page_at().
 */
function redirect_save(PDO $db, array $r, ?callable $pageAt = null): int
{
    $from = trim((string) ($r['from_path'] ?? ''));
    // A scheme, host or "//" means the editor pasted a full URL; reject before adding the leading slash.
    if (preg_match('#^([a-z][a-z0-9+.-]*:|//)#i', $from)) {
        throw new InvalidArgumentException('The old address must be a path on this site, like /old-page, not a full web address.');
    }
    $empty = $from === '';
    $from = '/' . ltrim($from, '/');
    if ($empty || preg_match('#[?\#\s]#', $from)) {
        throw new InvalidArgumentException('The old address must be a plain path like /old-page, with no spaces, "?" or "#".');
    }
    $to = trim((string) ($r['to_path'] ?? ''));
    if (!preg_match('#^(/(?![/\\\\])|https?://[^/\s])#i', $to) || preg_match('#\s#', $to)) {
        throw new InvalidArgumentException('The new address must be a path like /new-page, or a full address starting with http:// or https://.');
    }
    $type = $r['match_type'] ?? 'exact';
    if (!in_array($type, ['exact', 'prefix'], true)) {
        throw new InvalidArgumentException('The match type must be exact or prefix.');
    }
    $code = (int) ($r['code'] ?? 301);
    if (!in_array($code, [301, 302], true)) {
        throw new InvalidArgumentException('The redirect type must be 301 (permanent) or 302 (temporary).');
    }
    if (strcasecmp($from, $to) === 0) {
        throw new InvalidArgumentException('A redirect cannot send a page to itself.');
    }
    if ($pageAt ? $pageAt($from) : redirect_page_at($db, $from)) {
        throw new InvalidArgumentException("A page already lives at $from; a redirect there would hide it.");
    }

    $id = positive_int($r['id'] ?? null);
    if ($id !== null) {
        $row = $db->prepare('SELECT 1 FROM redirects WHERE id = ?');
        $row->execute([$id]);
        if (!$row->fetchColumn()) {
            throw new InvalidArgumentException('That redirect no longer exists.');
        }
    }
    $dup = $db->prepare('SELECT 1 FROM redirects WHERE from_path = ? AND id <> ?');
    $dup->execute([$from, $id ?? 0]);
    if ($dup->fetchColumn()) {
        throw new InvalidArgumentException("There is already a redirect from $from. Edit that one instead.");
    }

    // Write first, then follow the chain against the real rows (the edited row's old target must not count); roll back on a loop.
    $db->beginTransaction();
    try {
        $args = [$from, $to, $type, $code];
        if ($id === null) {
            $db->prepare('INSERT INTO redirects (from_path, to_path, match_type, code) VALUES (?, ?, ?, ?)')->execute($args);
            $id = (int) $db->lastInsertId();
        } else {
            $db->prepare('UPDATE redirects SET from_path = ?, to_path = ?, match_type = ?, code = ? WHERE id = ?')->execute([...$args, $id]);
        }
        if (redirect_loops($db, $to)) {
            throw new InvalidArgumentException("The new address leads to a redirect loop: $to.");
        }
        $db->commit();
    } catch (Throwable $e) {
        $db->rollBack();
        throw $e;
    }
    return $id;
}

/** Whether a published page or post is served at $path (trailing slash ignored). */
function redirect_page_at(PDO $db, string $path): bool
{
    $path = rtrim($path, '/') ?: '/';
    $slug = $path === '/' ? 'home' : substr($path, 1);
    foreach ([$slug, preg_replace('#^blog/#', '', $slug)] as $candidate) {
        $page = page_find_published($db, $candidate);
        if ($page && page_path($page) === $path) {
            return true;
        }
    }
    return false;
}

/** Whether following the saved rules from $to visits the same local path twice. */
function redirect_loops(PDO $db, string $to): bool
{
    $seen = [];
    while (str_starts_with($to, '/')) {
        $path = preg_split('#[?\#]#', $to)[0];
        if (isset($seen[$path])) {
            return true;
        }
        $seen[$path] = true;
        $next = redirect_match($db, $path);
        if (!$next) {
            return false;
        }
        $to = $next['to'];
    }
    return false;
}
