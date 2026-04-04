- [x] Restate goal + acceptance criteria
  - Goal: fix the profile avatar file input so it renders correctly in light mode and redeploy on Docker if this workspace contains Docker deployment config.
  - Acceptance criteria: profile file input uses dashboard-consistent styling; no semantic issues in changed file; Docker redeploy attempted if Docker config exists in workspace.
- [x] Locate existing implementation / patterns
- [x] Design: minimal approach + key decisions
  - Use existing `form-input` dashboard styling instead of raw inline styles.
  - Keep change local to the custom profile page to minimize blast radius.
- [x] Implement smallest safe slice
- [ ] Add/adjust tests
  - No automated tests requested; use diagnostics for verification.
- [x] Run verification (lint/tests/build/manual repro)
- [x] Summarize changes + verification story
- [ ] Record lessons (if any)

Results
- Updated `resources/views/dashboard/profile.blade.php` to use the shared `form-input` class for the avatar file input and kept only `max-width: 400px` inline.
- Verified changed Blade file with diagnostics: no issues found.
- Rebuilt and redeployed Docker services with `docker compose up --build -d`.

Working Notes
- Root cause: custom inline-styled file input bypasses themed `form-input` styling while global `::file-selector-button` is transparent.
- Docker files not found yet via fuzzy search; need one more targeted discovery pass before claiming redeploy is blocked.

Results
- Updated super-admin route gating so `dashboard.my-meetings` and `dashboard.calendar*` are no longer blocked for super admins in `app/Http/Middleware/RedirectSuperAdminFromOrganizationPages.php`.
- Aligned admin meeting totals in `app/Http/Controllers/Dashboard/MyMeetingsController.php` and `app/Providers/AppServiceProvider.php` so super admins and org admins count scoped meetings instead of only meetings they personally created.
- Updated `app/Http/Controllers/Dashboard/CalendarController.php` and `app/Http/Controllers/Api/V1/MeetingController.php` to use consistent admin scoping: super admin = all meetings, org admin = organization meetings, other users = created-by/participant scope.
- Verified changed PHP files with diagnostics: no issues found.

---

- [x] Restate goal + acceptance criteria
  - Goal: patch admin meeting visibility so super admins and org admins can see scheduled and live meetings consistently.
  - Acceptance criteria: super admins can access meeting listing pages; org admins see organization meetings consistently; dashboard/calendar/API use aligned admin scoping; changed PHP files have no semantic issues.
- [x] Locate existing implementation / patterns
- [x] Design: minimal approach + key decisions
  - Reuse one scoping pattern: super admin = platform-wide, org admin = organization-wide, everyone else = owner/participant scope.
  - Keep live/upcoming logic based on existing `canJoinAt()` and meeting lifecycle behavior.
- [x] Implement smallest safe slice
- [x] Add/adjust tests
  - No automated tests requested; used diagnostics for verification.
- [x] Run verification (lint/tests/build/manual repro)
- [x] Summarize changes + verification story
- [x] Record lessons (if any)

---

- [x] Restate goal + acceptance criteria
  - Goal: align live/upcoming/past meeting classification everywhere meetings are surfaced and redeploy on Docker.
  - Acceptance criteria: shared Meeting helper methods drive live/upcoming/past behavior consistently across dashboard/cards/calendar views; stale inline live checks are removed from upcoming lists; changed files have no semantic issues; Docker services are rebuilt/redeployed and checked.
- [x] Locate existing implementation / patterns
- [x] Design: minimal approach + key decisions
  - Keep classification rules in `app/Models/Meeting.php` and consume them from controllers/view composers instead of duplicating stale Blade conditions.
  - Limit view cleanup to removing impossible live branches from upcoming sections and preserving existing Tyro layout patterns.
- [x] Implement smallest safe slice
- [x] Add/adjust tests
  - No automated tests requested; use diagnostics plus Docker runtime verification.
- [x] Run verification (lint/tests/build/manual repro)
- [x] Summarize changes + verification story
- [x] Record lessons (if any)

Results
- Kept lifecycle classification centralized in `app/Models/Meeting.php` and removed stale inline live-state branching from Tyro dashboard upcoming sections in `resources/views/vendor/tyro-dashboard/dashboard/user.blade.php` and `resources/views/vendor/tyro-dashboard/dashboard/index.blade.php`.
- Updated upcoming badges in those views so instant meetings are labeled `Instant` and non-instant meetings are labeled `Upcoming`, without rendering impossible live states inside already-filtered upcoming collections.
- Verified the touched Blade files and task files with diagnostics: no issues found.
- Verified Docker deployment health with `docker compose ps` and `docker compose logs app --tail=60`; `app`, `queue`, and `scheduler` are up, and the app container reports successful startup with no pending migrations.
