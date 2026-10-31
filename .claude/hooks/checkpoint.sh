#!/usr/bin/env bash
# Stop hook: a turn can't end while a repo file is newer than CONTEXT.md or PROGRESS.md.
# Exit 2 hands the message to Claude, which then updates both files and stops again.
input=$(cat)
case "$input" in *'"stop_hook_active":true'*|*'"stop_hook_active": true'*) exit 0 ;; esac  # second pass: don't loop
cd "${CLAUDE_PROJECT_DIR:-.}" || exit 0

stale="" example=""
for f in CONTEXT.md PROGRESS.md; do
  if [ ! -f "$f" ]; then stale="$stale $f"; continue; fi
  hit=$(find . -type f -newer "$f" \
    -not -path './.git/*' -not -path './.claude/*' -not -path './storage/*' -not -path './public/uploads/*' \
    -not -name CONTEXT.md -not -name PROGRESS.md -print -quit)
  if [ -n "$hit" ]; then stale="$stale $f"; example=${example:-$hit}; fi
done
[ -z "$stale" ] && exit 0

echo "Checkpoint:${stale} is behind the repo (changed since: ${example:-new file}). Before finishing: update CONTEXT.md (status, decisions, next plans, one dated Changelog line) and add to today's entry in PROGRESS.md (Done / Current lead / Next / Blocked)." >&2
exit 2
