- [x] Restate goal + acceptance criteria
  - Goal: Ship the highest-risk v1 fixes for tenant security, guest join reliability, and server-to-server auth without redesigning the product.
  - Acceptance: Team management requires org-admin/super-admin access; meeting-bound dashboard/API endpoints enforce meeting policy and tenant scoping; demo seeders no longer run by default; production startup fails fast if APP_KEY is missing; public join supports guest display name + meeting password; app-level guest admission gate is removed so Jitsi lobby remains the waiting-room source of truth; recording ingest and Jitsi webhook secrets are env-configured and consistent.
- [x] Locate existing implementation / patterns
- [x] Design: minimal approach + key decisions
- [x] Implement authorization and tenant-scoping fixes
- [x] Implement guest join and secret-handling fixes
- [x] Run verification (diagnostics)
- [x] Summarize changes + verification story

Working Notes:
- Prioritizing P0 and fast-launch blockers only.
- Current docker entrypoint already no longer regenerates APP_KEY; only production hardening is needed there.
- Meeting analytics/diagnostics/exports should require manage-level access, not generic auth.
- Public guest flow should collect display name and optional password before calling join-guest.
- App-level admission flow was removed from web join so Jitsi lobby controls the waiting room.

Results:
- Added `app/Policies/MeetingPolicy.php` and registered it in `app/Providers/AppServiceProvider.php`.
- Added `app/Http/Middleware/EnsureOrgAdminOrSuperAdmin.php`, aliased it in `bootstrap/app.php`, and applied it to all dashboard team routes in `routes/web.php`.
- Tightened `TeamController`, `MeetingSummaryController`, `MeetingDiagnosticsController`, and `Api/V1/MeetingController` with explicit authorization.
- Changed `database/seeders/DatabaseSeeder.php` so demo seeders only run outside production when `ENABLE_DEMO_SEEDERS=true`.
- Hardened `docker/entrypoint.sh` to fail fast in production when `APP_KEY` is missing.
- Added `services.jitsi.recording_ingest_secret` config and updated `Api/V1/RecordingController` to use env-based secret validation.
- Removed the app-layer guest admission gate from `Api/MeetingJoinController`.
- Updated `resources/views/meeting/show.blade.php` to collect guest display name and optional meeting password before public join, pass them to the API, and stop showing app-level admission controls.
- Added minimal subscription enforcement in `Dashboard/CreateMeetingController` for expired org subscriptions or missing personal plans.
- Verified with diagnostics on all touched files: no issues found.
