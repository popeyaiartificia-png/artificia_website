#!/bin/bash
# Only this domain: code, core/uploads, private resumes, database and config remain together.
set -eu
BASE=/home/u447231494/domains/artificiaai.com
umask 077
OUT="$BASE/backups/wordpress-daily/$(date -u +%Y%m%dT%H%M%SZ)"
mkdir -p "$OUT"
php "$BASE/artificia-wp/bin/wordpress-backup.php" "$BASE/public_html/wp" "$OUT/database.sql.gz"
tar --exclude='artificia-wp/.git' -czf "$OUT/site.tar.gz" -C "$BASE" public_html/.htaccess public_html/wp artificia-wp
cp "$BASE/public_html/wp/wp-config.php" "$OUT/wp-config.php"
gzip -t "$OUT/database.sql.gz" "$OUT/site.tar.gz"
echo "WordPress backup complete: $OUT"
