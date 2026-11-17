# artificiaai.com

WordPress 7.1.4 serves https://artificiaai.com on Hostinger shared hosting, using the custom Artificia theme and this repository's existing PHP templates, CSS, JavaScript and assets. The previous plain-PHP CMS remains available for rollback. No build step, Composer or page builder.

- Spec: `docs/superpowers/specs/2026-10-27-artificiaai-site-design.md`
- Plan: `docs/superpowers/plans/`
- `legacy/` is the old static site (reference only), plus mirrors of the two ad funnels,
  `legacy/landing-page/` and `legacy/co-orporate/`. Tag `legacy-live-2025-09-17` is the original backup.

## Production WordPress

- Public site: https://artificiaai.com; administrator: https://artificiaai.com/wp-admin/.
- WordPress core and durable uploads: `/home/u447231494/domains/artificiaai.com/public_html/wp`.
- Shared Git checkout/theme/assets: `/home/u447231494/domains/artificiaai.com/artificia-wp`.
- Database: `u447231494_artificiawp`; original PHP database retained separately.
- Private new resume PDFs: `artificia-wp/storage/resumes`, outside the web root.
- Hostinger root replacement auto-deployment is **disabled**. Never deploy a static archive or replace `public_html`.
- After pushing `main`, update only the private source checkout with `git -C /home/u447231494/domains/artificiaai.com/artificia-wp pull --ff-only origin main`. Shell SSH is currently disabled; use a temporary Hostinger cron command and remove that job after its result is verified.
- Automatic backups run at **05:00 IST daily** (`30 23 * * *` on the UTC hosting server). Manual backup: `bash /home/u447231494/domains/artificiaai.com/artificia-wp/bin/wordpress-backup.sh`. Archives stay private in `backups/wordpress-daily/`; storage/retention needs periodic review.
- Immediate routing rollback: `bash /home/u447231494/domains/artificiaai.com/rollback-wordpress.sh`. Keep the old PHP files/database for at least 30 days. WordPress leads remain in its separate database after rollback; reconcile newer records before re-importing.
- [Editing guide](docs/wordpress-editor-guide.md) and [deployment/import/rollback runbook](docs/wordpress-migration.md).

The owner's private login is saved on this laptop in `D:\artificia-wp-local\HOSTINGER-LOGIN.txt`; credentials never belong in Git.

## Layout

`public/` is the only web-served folder. `app/` is the code, `db/migrations/` the schema,
`bin/` the dev scripts, `tests/` the tests. Migrations run on boot.

## Local PHP development (rollback source)

1. `brew install php mariadb && brew services start mariadb`, then create the `artificia` and `artificia_test`
   databases and a user that can use both (the test runner drops and recreates `artificia_test`).
2. Create `~/.config/artificia/config.php` (outside the repo, never committed). It returns an array with
   `env` (`local`, `staging` or `production`), `base_url`, `db` (`dsn`, `user`, `pass`), `app_secret`
   (64 hex characters, `openssl rand -hex 32`), `staging_auth` (`user`, `pass`), `setup_token` and `mail_from`.
3. `php bin/seed.php` loads the pages, templates and images. `php bin/create-admin.php you@example.com`
   creates your admin login (it asks for the password).
4. `bin/serve` starts http://127.0.0.1:8000 (admin at `/admin`). `php tests/run.php` runs the tests.

On the server the config path is `$ARTIFICIA_CONFIG`, or `artificia-config.php` three folders above `app/`.

## Git workflow

Work, commit and push directly on `main`; the owner does not use feature branches
or pull requests for this repository. Before starting with a clean working tree:

```sh
git switch main
git fetch origin
git merge --ff-only origin/main
```

After reviewing and committing the intended files, use `git push origin main`.
If the remote has moved, integrate those changes locally and resolve any conflicts
before retrying. Never force-push. Concurrent edits can still need resolution.

Enable the main-only guard and safe defaults once per new checkout:

```sh
git config --local core.hooksPath .githooks
git config --local remote.pushDefault origin
git config --local remote.origin.push refs/heads/main:refs/heads/main
git config --local push.default simple
git config --local pull.ff only
```
