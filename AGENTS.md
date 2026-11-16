# Repository instructions

Read `CONTEXT.md` and the latest entry in `PROGRESS.md` before making changes.
Follow the checkpoint instructions in `CLAUDE.md`: update both handover files
after each change and record validation results.

## Git workflow

The owner requires all work to be committed and pushed directly to `main`.
Do not create feature branches, pull requests, or PR merges unless the owner
explicitly changes this policy. Do not force-push or discard someone else's work.

- Before editing, check `git status`, ensure the branch is `main`, fetch `origin`,
  and update with `git merge --ff-only origin/main` when the working tree is clean.
- Preserve existing uncommitted changes. If branches have diverged, integrate
  upstream changes deliberately and resolve conflicts before pushing. Do not
  choose an entire side merely to remove conflict markers.
- Push with `git push origin main`. If another writer updated `main`, fetch and
  integrate their work before retrying; never bypass the non-fast-forward check.
- For a new checkout, run `git config --local core.hooksPath .githooks`,
  `git config --local remote.pushDefault origin`,
  `git config --local remote.origin.push refs/heads/main:refs/heads/main`,
  `git config --local push.default simple`, and
  `git config --local pull.ff only`.

The tracked pre-push hook requires both the checked-out branch and pushed branch
to be `main`, and only permits updates to remote `refs/heads/main`.
