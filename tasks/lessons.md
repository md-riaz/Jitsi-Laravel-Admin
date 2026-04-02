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

## 2026-03-30 — Match infrastructure scope to current deployment stage
- **Failure mode:** Started with a heavier PostgreSQL-based Docker stack when the current project stage only needed a simpler local deployment.
- **Detection signal:** User explicitly requested SQLite instead of a full database service.
- **Prevention rule:** Default to the lightest viable infrastructure for first-pass deployments, and only add separate stateful services when the user asks for them or the app clearly requires them.

## 2026-03-30 — Do not force URLs in local Docker runtime
- **Failure mode:** Forced global URL generation in application bootstrapping, which made local Docker auth pages render absolute secure URLs even though the app was served over plain HTTP.
- **Detection signal:** User hit `Unsupported SSL request`, the rendered login form used `https://localhost:18090/...`, and the app still had global URL forcing enabled in `AppServiceProvider`.
- **Prevention rule:** In local Docker, do not force scheme or root URL globally unless the runtime actually terminates TLS there; let URL generation follow the real incoming request and clear Laravel caches after URL-generation changes.

## 2026-03-30 — Migrations do not imply seeding in Docker startup
- **Failure mode:** Assumed seeded demo data would exist after container deployment because migrations were running.
- **Detection signal:** Docker startup only executed `php artisan migrate --force --no-interaction`, while `DatabaseSeeder` and demo seeders existed but were never invoked.
- **Prevention rule:** When seeded data is expected in a containerized Laravel environment, wire seeding explicitly into startup with an opt-in env flag such as `RUN_SEEDERS=true`.

## 2026-03-31 — Production docs must distinguish sample env from real deployment env
- **Failure mode:** Deployment documentation suggested a sample Docker env file as a production starting point, creating risk around reused secrets, demo defaults, and misleading production behavior.
- **Detection signal:** Deployment review showed `stack.env` was documented too close to a real production config and still defaulted to seeding behavior.
- **Prevention rule:** For deployment docs, always make `.env.example` the canonical production template, mark sample Docker env files as local/demo-only, and explicitly document production-safe values for migrations and seeding.

## 2026-03-31 — Public landing pages must not hard-fail on optional auth route names
- **Failure mode:** The homepage directly called named auth routes from the Tyro login package, so if those routes were not registered or differed by environment, guests hit a 500 before seeing the app.
- **Detection signal:** `resources/views/welcome.blade.php` called `route('tyro-login.login')` directly while route discovery did not confirm any application-side definition, and the root route only rendered that view.
- **Prevention rule:** For public landing pages, avoid unconditional `route()` calls to package-provided auth names; guard with `Route::has(...)` and provide safe URL fallbacks so the page still renders during partial auth setup or route drift.

## 2026-04-02 — Use full-file reads after repeated exact-replace failures
- **Failure mode:** Tried multiple exact string replacements against a Blade file after prior edits without first re-reading the full current file.
- **Detection signal:** `strReplace` failed repeatedly because the target text no longer matched the updated file exactly.
- **Prevention rule:** After one or two exact-replace misses on the same file, re-read the full file and patch against the current contents instead of retrying stale text.

## 2026-04-02 — Guest exit redirects must respect authentication state
- **Failure mode:** Changed the embedded meeting exit redirect to a dashboard-only destination, which breaks the intended guest flow because guests do not have dashboard access.
- **Detection signal:** User clarified that unauthenticated guests should return to the main URL after completing or exiting a meeting.
- **Prevention rule:** When changing post-meeting redirects, always separate authenticated and guest destinations and verify the route is reachable for both audiences.

## 2026-04-02 — Instant meeting displays need lifecycle fields, not instant heuristics
- **Failure mode:** Instant meeting UI and calendar displays treated live meetings as timeless and used `now()` or historical participant relations, producing incorrect times and zero live counts.
- **Detection signal:** `MeetingLifecycleService` already maintained `actual_started_at` and `active_participant_count`, while dashboard/calendar views still relied on `isInstantMeeting()`, `now()`, and `participants->count()`.
- **Prevention rule:** For live instant-meeting displays, use lifecycle-backed fields like `actual_started_at` and `active_participant_count`; reserve relationship counts for historical attendance and avoid `now()` as a display fallback when persisted lifecycle data exists.

## 2026-04-02 — Dashboard diagnostics pages must tolerate sparse runtime data
- **Failure mode:** Admin/debug pages assumed meeting event payloads and derived user avatar values were always fully populated, creating avoidable 500s on pages meant for inspection.
- **Detection signal:** `MeetingSummaryController` directly indexed into `payload['user_name']`, and diagnostics JWT generation depended on a strict string-return avatar helper.
- **Prevention rule:** For summary/diagnostic pages, treat event payloads and derived profile fields as optional; normalize to safe arrays/strings before rendering or generating debug artifacts.

## 2026-04-02 — Server-rendered dashboard pages should not depend on Sanctum API auth for same-page mutations
- **Failure mode:** A normal `auth`-protected dashboard page posted avatar uploads to `auth:sanctum` API routes, creating an avoidable auth boundary mismatch for a simple session-backed profile action.
- **Detection signal:** `resources/views/dashboard/profile.blade.php` was rendered from `routes/web.php` under `auth`, while its JS `fetch()` calls targeted `/api/profile/avatar` in `routes/api.php` under `auth:sanctum`.
- **Prevention rule:** For server-rendered dashboard pages performing same-session mutations, prefer matching `web` routes with `auth` middleware unless the feature genuinely needs a cross-client API surface.
