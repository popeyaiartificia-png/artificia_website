-- SEO desk (Package A, Orkay-style): focus keyword, canonical override, automatic-schema switch and custom JSON-LD per page.
ALTER TABLE pages ADD COLUMN IF NOT EXISTS focus_keyword VARCHAR(100) NOT NULL DEFAULT '' AFTER meta_description;
ALTER TABLE pages ADD COLUMN IF NOT EXISTS canonical VARCHAR(500) NOT NULL DEFAULT '' AFTER focus_keyword;
ALTER TABLE pages ADD COLUMN IF NOT EXISTS schema_auto TINYINT(1) NOT NULL DEFAULT 1 AFTER noindex;
ALTER TABLE pages ADD COLUMN IF NOT EXISTS schema_custom TEXT NULL AFTER schema_auto;
