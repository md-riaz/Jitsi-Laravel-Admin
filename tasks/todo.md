- [x] Restate goal + acceptance criteria
  - Goal: fix meeting exit redirect and moderator JWT behavior on the meeting join flow.
  - Acceptance criteria: leaving the embedded meeting returns users to the app-owned dashboard home route, not the external Jitsi host.
  - Acceptance criteria: only organization admins for the meeting organization and participants with the `host` role receive moderator JWT context.
  - Acceptance criteria: guests continue to join as non-moderators.
- [x] Locate existing implementation / patterns
- [x] Design: minimal approach + key decisions
- [x] Implement smallest safe slice
- [ ] Add/adjust tests
- [x] Run verification (diagnostics/manual review)
- [x] Summarize changes + verification story
- [x] Record lessons (if any)

Working Notes:
- `resources/views/meeting/show.blade.php` currently redirects authenticated users to `dashboard.my-meetings` and guests to `/` from the `readyToClose` handler.
- `app/Http/Controllers/Api/MeetingJoinController.php` currently treats creator and `cohost` as moderators, which conflicts with the latest requirement.
- Keep the fix server-side by changing only moderator resolution and the app-side close redirect target.
- Use the existing `tyro-dashboard.index` route as the app-owned post-exit destination.

Results:
- Updated meeting join moderator resolution so only same-organization `org-admin` users and `host` participants receive moderator JWT context.
- Removed creator-only and `cohost`-based moderator elevation from the server-side join flow.
- Updated the embedded meeting `readyToClose` redirect to return authenticated users to `route('tyro-dashboard.index')` and unauthenticated guests to `url('/')`.
- Verified `app/Http/Controllers/Api/MeetingJoinController.php` and `resources/views/meeting/show.blade.php` with diagnostics; no issues were reported.

## 2026-04-02 — Instant meeting display and participant count bugfix
- [x] Restate goal + acceptance criteria
  - Goal: fix instant meeting time display and real-time participant counts.
  - Acceptance criteria: instant meetings show their actual started time where the dashboard presents live timing.
  - Acceptance criteria: calendar event generation for instant meetings uses actual meeting lifecycle timestamps when available.
  - Acceptance criteria: live participant counts come from the server-maintained real-time counter, not historical participant rows.
- [x] Locate existing implementation / patterns
- [x] Design: minimal approach + key decisions
- [ ] Implement smallest safe slice
- [ ] Run verification (diagnostics/manual review)
- [ ] Summarize changes + verification story

Working Notes:
- `MeetingLifecycleService` is the source of truth for `actual_started_at` and `active_participant_count`.
- `Meeting::isInstantMeeting()` remains true for instant meetings after they start because `end_at` stays null, so display fixes should use lifecycle fields directly instead of changing model semantics.
- Keep historical participant analytics untouched unless the screen is explicitly about live presence.
- [x] Implement smallest safe slice
- [x] Run verification (diagnostics/manual review)
- [x] Summarize changes + verification story

Results:
- Updated instant meeting calendar generation in `app/Services/CalendarService.php` to use persisted lifecycle timestamps (`actual_started_at` / `actual_ended_at`) with safe fallbacks instead of always using `now()`.
- Updated dashboard calendar event generation in `app/Http/Controllers/Dashboard/CalendarController.php` to render instant meetings from lifecycle timestamps instead of synthetic current-time values.
- Updated live meeting displays in `resources/views/vendor/tyro-dashboard/dashboard/index.blade.php` and `resources/views/vendor/tyro-dashboard/dashboard/user.blade.php` to show `actual_started_at` when present and use `active_participant_count` for the room count.
- Updated `resources/views/dashboard/my-meetings.blade.php` to use `active_participant_count` instead of relationship counts for participant display.
- Updated summary metrics in `app/Providers/AppServiceProvider.php` and `app/Http/Controllers/Dashboard/MyMeetingsController.php` to use the real-time counter where the UI is reporting current room presence.
- Verified all edited files with diagnostics; no issues were reported.

## 2026-04-02 — Summary/Diagnostics 500 bugfix
- [x] Restate goal + acceptance criteria
  - Goal: eliminate 500 errors when opening meeting Summary or Diagnostics pages.
  - Acceptance criteria: Summary page renders even when meeting event payloads are sparse or null.
  - Acceptance criteria: Diagnostics page renders even when user avatar/email data is incomplete.
  - Acceptance criteria: changes stay narrow to these pages' runtime assumptions.
- [x] Locate existing implementation / patterns
- [x] Design: minimal approach + key decisions
- [ ] Implement smallest safe slice
- [ ] Run verification (diagnostics/manual review)
- [ ] Summarize changes + verification story

Working Notes:
- `MeetingSummaryController::buildAttendanceRows()` directly indexes into event payload data and should tolerate null payloads.
- `MeetingDiagnosticsController` wraps JWT generation in a try/catch, but hardening `User::getJitsiAvatarUrl()` removes an avoidable runtime edge.
- Fix the unsafe assumptions instead of changing routes or policy behavior.
- [x] Implement smallest safe slice
- [x] Run verification (diagnostics/manual review)
- [x] Summarize changes + verification story

Results:
- Hardened `app/Http/Controllers/Dashboard/MeetingSummaryController.php` so guest/event attendance fallback tolerates null or malformed payloads instead of directly indexing into an optional payload array.
- Hardened `app/Models/User.php` so `getJitsiAvatarUrl()` always returns a string, which keeps diagnostics JWT test-token generation safe even when derived avatar data is missing.
- Verified `app/Http/Controllers/Dashboard/MeetingSummaryController.php`, `app/Http/Controllers/Dashboard/MeetingDiagnosticsController.php`, `app/Models/User.php`, `resources/views/dashboard/meeting-summary.blade.php`, and `resources/views/dashboard/meeting-diagnostics.blade.php` with diagnostics; no issues were reported.

## 2026-04-02 — Profile avatar upload bugfix
- [x] Restate goal + acceptance criteria
  - Goal: fix profile avatar upload failure from the dashboard profile page.
  - Acceptance criteria: authenticated dashboard users can upload and delete avatars without hitting auth failures.
  - Acceptance criteria: profile page uses app-owned authenticated routes that match the page's session auth model.
  - Acceptance criteria: changes stay narrow and do not disturb existing API behavior beyond this page.
- [x] Locate existing implementation / patterns
- [x] Design: minimal approach + key decisions
- [x] Implement smallest safe slice
- [x] Run verification (diagnostics/manual review)
- [x] Summarize changes + verification story

Working Notes:
- `resources/views/dashboard/profile.blade.php` is rendered behind `auth` on the web stack, but it submitted avatar mutations to `/api/profile/avatar` behind `auth:sanctum`.
- `bootstrap/app.php` enables `statefulApi()`, but using Sanctum API auth for a normal server-rendered dashboard page is still a fragile mismatch and the likeliest request-failure point.
- The environment also lacks `public/storage`, so even successful uploads will not render until the storage symlink exists.
- Keep the fix minimal by reusing the existing JSON controller and moving this page's upload/delete endpoints onto authenticated web routes.

Results:
- Added authenticated web routes in `routes/web.php` for avatar upload and delete, reusing `App\Http\Controllers\Api\ProfilePictureController` so the response contract stays unchanged.
- Updated `resources/views/dashboard/profile.blade.php` to submit upload/delete requests to the new web routes instead of the Sanctum API endpoints.
- Verified `routes/web.php`, `resources/views/dashboard/profile.blade.php`, and `app/Http/Controllers/Api/ProfilePictureController.php` with diagnostics; no issues were reported.
- Confirmed a separate environment issue remains: `public/storage` is missing, so avatar images still require `php artisan storage:link` to be publicly reachable.
