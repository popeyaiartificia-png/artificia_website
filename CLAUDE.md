# artificiaai.com

Read [CONTEXT.md](CONTEXT.md) (the project handover: business, design direction, status, next plans) and the latest day in [PROGRESS.md](PROGRESS.md) before starting.

**Git policy (owner, 16 Nov 2026):** work, commit and push directly on `main`.
Do not create feature branches or pull requests unless the owner changes this
instruction. Fetch and sync before editing; preserve other work, resolve any
divergence locally and never force-push. See [AGENTS.md](AGENTS.md) for setup.

**Checkpoint after every change, however small, before ending the turn:**
1. CONTEXT.md: correct any status, decision or plan it now gets wrong, and add one dated line to its Changelog.
2. PROGRESS.md: add to today's entry (create it if this is the day's first change): Done, Current lead, Next, Blocked.

A Stop hook (`.claude/hooks/checkpoint.sh`) blocks the end of a turn while any repo file is newer than either of the two.
