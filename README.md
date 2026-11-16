# artificiaai.com

Plain PHP 8 + MySQL site for https://artificiaai.com (Hostinger shared hosting). No framework, no Composer.

- Spec: `docs/superpowers/specs/2026-10-27-artificiaai-site-design.md`
- Plan: `docs/superpowers/plans/`
- `legacy/` is the old static site (reference only), plus mirrors of the two ad funnels,
  `legacy/landing-page/` and `legacy/co-orporate/`. Tag `legacy-live-2025-09-17` is the original backup.

## Layout

`public/` is the only web-served folder. `app/` is the code, `db/migrations/` the schema,
`bin/` the dev scripts, `tests/` the tests. Migrations run on boot.

## Local development

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
