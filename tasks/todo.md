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
- Tightened `README.md` and `DEPLOYMENT.md` so Docker-on-VPS and manual Ubuntu deployment paths are clearly separated, production env guidance points to `.env.example`, and production seeding is explicitly disabled.
- Sanitized `stack.env` into a safe sample file with placeholder secrets and `RUN_SEEDERS=false`.

- [x] Investigate and fix homepage 500 for guest landing page
  - Root cause: `resources/views/welcome.blade.php` depended on named auth routes that can be absent or drift by environment, which can throw during Blade rendering for `/`.
  - Fix: guard auth route generation with `Route::has(...)` and safe URL fallbacks; harden container startup permissions so fresh Laravel exceptions can be logged.
  - Verification: diagnostics requested for `resources/views/welcome.blade.php` and `docker/entrypoint.sh` after the change.

- [x] Restate goal + acceptance criteria
  - Goal: Fix the create meeting page so the timezone select defaults to the user's browser timezone on first load.
  - Acceptance: When there is no old submitted timezone value, the create meeting page preselects the browser timezone even if it is not in the current curated timezone list.
- [x] Locate existing implementation / patterns
- [x] Design: minimal approach + key decisions
- [x] Implement smallest safe slice
- [x] Run verification (diagnostics)
- [x] Summarize changes + verification story

Working Notes:
- Current view JS only applies the browser timezone if it exactly matches an existing option.
- The curated timezone list is intentionally limited, so many valid browser timezones will never be selected unless injected client-side.

Results:
- Updated `resources/views/dashboard/create-meeting.blade.php` so the browser timezone is still selected when it is not part of the server-side curated timezone list.
- The page now injects a temporary `${browserTimezone} (Browser)` option on first load when needed, instead of silently falling back to `UTC`.
- Verified with diagnostics on `resources/views/dashboard/create-meeting.blade.php`: no issues found.

- [x] Restate goal + acceptance criteria
  - Goal: Remove any remaining app-level lobby/admission controls from the public meeting page so Jitsi remains the only waiting-room source.
  - Acceptance: No moderator lobby/admission buttons are rendered on the web meeting page, and no page JavaScript exposes app-layer lobby commands.
- [x] Locate existing implementation / patterns
- [x] Design: minimal approach + key decisions
- [x] Implement smallest safe slice
- [ ] Run verification (diagnostics)
- [ ] Summarize changes + verification story

Working Notes:
- The create meeting page does not contain lobby controls.
- Remaining controls were still present on `resources/views/meeting/show.blade.php` as moderator-only client-side buttons (`toggleLobby`, `muteEveryoneNow`).
- These controls conflict with the rule that Jitsi is the source of truth for waiting-room behavior.

- [x] Restate goal + acceptance criteria
  - Goal: Make the native `datetime-local` picker trigger visible on the create meeting page without removing the existing themed input background.
  - Acceptance: The browser date/time picker affordance remains visible for the start and end fields.
- [x] Locate existing implementation / patterns
- [x] Design: minimal approach + key decisions
- [x] Implement smallest safe slice
- [ ] Run verification (diagnostics)
- [ ] Summarize changes + verification story

Working Notes:
- The page styles all inputs with themed background and foreground colors.
- Native `datetime-local` picker icons can disappear when the browser renders a dark-themed control surface against custom input styling.
- Minimal fix is CSS-only: keep the existing theme, but force a light color scheme for `datetime-local` controls so the native picker affordance stays visible.

- [x] Restate goal + acceptance criteria
  - Goal: Add only the task 1 bug-condition exploration test for organization-account onboarding.
  - Acceptance: The exploration test covers public registration and super-admin provisioning for non-super-admin onboarding and encodes the invariant that onboarding must create an organization plus initial user.
- [x] Locate existing implementation / patterns
- [x] Design: minimal approach + key decisions
- [x] Implement smallest safe slice
- [ ] Run verification (skipped by user request)
- [ ] Summarize changes + verification story

Working Notes:
- `.kiro/specs/organization-account-onboarding/.config.kiro` marks this as a bugfix spec.
- User explicitly requested coding only: no PHPUnit, Pest, artisan test, or other verification commands.
- Existing exploration coverage already lived in `tests/Feature/OrganizationAccountOnboardingBugConditionTest.php`, so the safest change is to tighten that file rather than introduce parallel test scaffolding.

- [ ] Restate goal + acceptance criteria
  - Goal: Update onboarding tests to avoid hanging and ensure dependencies and role assignments are seeded correctly.
  - Acceptance: Debug output reduced, data providers minimized, org-admin role seeded for RegisterController flows, assignRole/removeRole use Role models; targeted phpunit run completes or instrumentation identifies stall step.
- [ ] Locate existing implementation / patterns
- [ ] Design: minimal approach + key decisions
- [ ] Implement smallest safe slice
- [ ] Run verification (phpunit for onboarding bug condition test)
- [ ] Summarize changes + verification story

- [x] Restate goal + acceptance criteria
  - Goal: Ensure super-admin provisioning creates a new organization + initial org admin (like public registration), without relying on client-provided creator flags.
  - Acceptance: When a super-admin adds a user, organization_name is required server-side and only admin role is accepted; the flow creates organization + initial user with org-admin role.
- [x] Locate existing implementation / patterns
- [x] Design: minimal approach + key decisions
- [x] Implement smallest safe slice
- [x] Run verification (diagnostics)
- [ ] Summarize changes + verification story

Working Notes:
- Enforce super-admin onboarding requirements server-side (no creator_type dependency).
- Keep org-admin assignment aligned with public registration.

Results:
- Updated TeamController validation to require organization_name for super-admin provisioning server-side, without relying on creator_type.
- Enforced super-admin provisioning to always create a new organization with an initial org admin, mirroring public registration.
- Diagnostics: app/Http/Controllers/Dashboard/TeamController.php clean.

- [ ] Restate goal + acceptance criteria
  - Goal: Align super-admin and org-admin dashboard/user-provisioning behavior with organization-based model.
  - Acceptance: Super-admin no longer sees org-user menu items (subscription, my meetings, calendar); super-admin is redirected away from those org-user pages if accessed directly; add-user form clearly shows org context for org-admin and lets super-admin either create a new organization+first admin or provision into an existing organization.
- [ ] Locate existing implementation / patterns
- [ ] Design: minimal approach + key decisions
- [ ] Implement smallest safe slice
- [ ] Run verification (diagnostics)
- [ ] Summarize changes + verification story

Working Notes:
- Keep platform admin (super-admin) separated from org-user experience.
- Preserve existing super-admin path for creating a brand-new organization + initial admin.
- Add optional existing-organization provisioning path for super-admin to create profiles directly into an org.

Results:
- Hid organization-user menu items from super-admin in `resources/views/vendor/tyro-dashboard/partials/admin-sidebar.blade.php` (My Subscription, Create Meeting, My Meetings, Calendar).
- Added `app/Http/Middleware/RedirectSuperAdminFromOrganizationPages.php` and registered it in `bootstrap/app.php` to block direct super-admin access to organization-user pages.
- Extended `TeamController@create` to load organizations for super-admin provisioning.
- Extended `TeamController@store` with provisioning modes:
  - `new`: create new organization + initial admin.
  - `existing`: create user inside selected existing organization.
- Updated `resources/views/dashboard/team/create.blade.php`:
  - Super-admin now gets provisioning mode toggle + existing organization selector.
  - Org-admin now sees clear readonly organization context.
- Diagnostics clean for all touched files.

- [x] Restate goal + acceptance criteria
  - Goal: Remove invite user page flow entry points without touching meeting invite mechanics.
  - Acceptance: No admin sidebar link to invitation pages, no invitation/referral cards on admin dashboard, and invitation feature flag disabled.
- [x] Locate existing implementation / patterns
- [x] Design: minimal approach + key decisions
- [x] Implement smallest safe slice
- [x] Run verification (diagnostics)
- [x] Summarize changes + verification story

Working Notes:
- Kept scope strictly to invite-user page flow entry points only.
- Did not modify meeting invite domain behavior/routes/services.

Results:
- Removed the “Invitation Links” menu entry from `resources/views/vendor/tyro-dashboard/partials/admin-sidebar.blade.php`.
- Removed invitation/referral stat cards from `resources/views/vendor/tyro-dashboard/dashboard/admin.blade.php`.
- Disabled dashboard invitation feature flag in `config/tyro-dashboard.php` by setting `features.invitation_system` to `false`.
