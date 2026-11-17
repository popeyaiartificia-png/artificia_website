<?php
// The old site's redirect lookup and canonical-host rules, without changing their matching or query-string behaviour.
declare(strict_types=1);

require_once ARTIFICIA_ROOT . '/app/lib/redirects.php';
require_once ARTIFICIA_ROOT . '/app/lib/router.php';

/** The imported redirect table deliberately has the old schema so redirect_match() runs unchanged. */
function artificia_redirects_table(PDO $db): void
{
    $db->exec("CREATE TABLE IF NOT EXISTS redirects (
      id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
      from_path VARCHAR(190) NOT NULL UNIQUE, to_path VARCHAR(500) NOT NULL,
      match_type ENUM('exact','prefix') NOT NULL DEFAULT 'exact', code SMALLINT NOT NULL DEFAULT 301
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

/** Redirects run after published-page lookup, just as dispatch() did, and never replace a real page. */
function artificia_legacy_redirect(string $path, bool $publishedPage): ?array
{
    if ($publishedPage || preg_match('#^/blog/([^/]+)/?$#', $path) || !get_option('artificia_redirects_imported')) {
        return null;
    }
    return redirect_match(db(), $path);
}
