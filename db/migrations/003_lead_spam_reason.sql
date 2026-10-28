-- R35: why a lead was saved as spam (comma-separated codes: honeypot, bad_token, too_fast, rate_limited); NULL for real leads.
ALTER TABLE leads ADD COLUMN IF NOT EXISTS spam_reason VARCHAR(100) NULL AFTER spam;
