# Lessons Learned

## 2026-02-10 — Planning granularity must match requested scope
- **Failure mode:** Delivered a short operational checklist instead of a full end-to-end project completion roadmap.
- **Detection signal:** User feedback explicitly requested that `task.md` cover the full project from start to finish for iterative feature completion.
- **Prevention rule:** When asked for a "plan" file, default to phased, completion-oriented scope with acceptance criteria, dependencies, verification checkpoints, and iteration tracking fields.

## 2026-02-10 — Browser screenshot `Not Found` can be a forwarding issue
- **Failure mode:** Interpreted screenshot artifact `Not Found` as potential app routing problem.
- **Detection signal:** Playwright content was `<pre>Not Found</pre>` while `curl -I http://127.0.0.1:8000/` returned `HTTP/1.1 200 OK`, and `php artisan serve` showed no matching request log during screenshot capture.
- **Prevention rule:** When screenshot output conflicts with local HTTP checks, verify server-side request logs before changing routes/views; treat as tooling/network path issue if requests do not reach Laravel.
