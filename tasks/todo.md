- [x] Restate goal + acceptance criteria
  - Goal: On the create meeting page, default timezone to the user's browser timezone when supported, and default visibility to Anyone with Link.
  - Acceptance: New create-meeting form loads with visibility preselected to `link_anyone`; timezone auto-selects from `Intl.DateTimeFormat().resolvedOptions().timeZone` when that timezone exists in the dropdown; existing old input still wins after validation errors.
- [x] Locate existing implementation / patterns
- [x] Design: minimal approach + key decisions
- [x] Implement smallest safe slice
- [x] Run verification (diagnostics)
- [x] Summarize changes + verification story

Working Notes:
- Relevant files: `app/Http/Controllers/Dashboard/CreateMeetingController.php`, `resources/views/dashboard/create-meeting.blade.php`
- Keep change scoped to the create meeting page only.
- Preserve server-rendered old input precedence after validation failures.

Results:
- Changed `app/Http/Controllers/Dashboard/CreateMeetingController.php` to pass `defaultTimezone` and `defaultVisibility` into the create-meeting view.
- Changed `resources/views/dashboard/create-meeting.blade.php` so visibility defaults to `link_anyone` and browser timezone auto-select runs only when there is no old timezone input.
- Verified with diagnostics: no issues in either touched file.
