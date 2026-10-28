-- R27: a page can ask search engines not to index it (robots meta + left out of sitemap.xml). MariaDB's IF NOT EXISTS keeps a re-run safe.
ALTER TABLE pages ADD COLUMN IF NOT EXISTS noindex TINYINT(1) NOT NULL DEFAULT 0 AFTER status;
