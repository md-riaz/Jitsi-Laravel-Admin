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
