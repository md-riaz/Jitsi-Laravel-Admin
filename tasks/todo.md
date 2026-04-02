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
- Updated the embedded meeting `readyToClose` redirect to always return to `route('tyro-dashboard.index')` instead of falling back to external or mixed destinations.
- Verified `app/Http/Controllers/Api/MeetingJoinController.php` and `resources/views/meeting/show.blade.php` with diagnostics; no issues were reported.
