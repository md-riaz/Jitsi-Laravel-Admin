# Lessons Learned

## 2026-02-10 — Planning granularity must match requested scope
- **Failure mode:** Delivered a short operational checklist instead of a full end-to-end project completion roadmap.
- **Detection signal:** User feedback explicitly requested that `task.md` cover the full project from start to finish for iterative feature completion.
- **Prevention rule:** When asked for a "plan" file, default to phased, completion-oriented scope with acceptance criteria, dependencies, verification checkpoints, and iteration tracking fields.

## 2026-02-10 — Browser screenshot `Not Found` can be a forwarding issue
- **Failure mode:** Interpreted screenshot artifact `Not Found` as potential app routing problem.
- **Detection signal:** Playwright content was `<pre>Not Found</pre>` while `curl -I http://127.0.0.1:8000/` returned `HTTP/1.1 200 OK`, and `php artisan serve` showed no matching request log during screenshot capture.
- **Prevention rule:** When screenshot output conflicts with local HTTP checks, verify server-side request logs before changing routes/views; treat as tooling/network path issue if requests do not reach Laravel.

## 2026-02-10 — Use dedicated patch tool when editing files
- **Failure mode:** Applied a patch through shell execution instead of using the dedicated patch tool.
- **Detection signal:** Tooling warning indicated `apply_patch` was requested via `exec_command`.
- **Prevention rule:** Always use the dedicated patch mechanism for patch-style edits; reserve shell commands for file writes, reads, and command execution.

## 2026-02-10 — Preserve requested deployment defaults exactly
- **Failure mode:** Docker setup used a common default port (`8080`) instead of the requested project-specific deployment port.
- **Detection signal:** User follow-up explicitly requested port `8090`.
- **Prevention rule:** When user specifies operational defaults (ports, tags, env names), encode them consistently across runtime files and documentation before finalizing.
