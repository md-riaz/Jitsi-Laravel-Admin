# TODO - Docker Deployment

## Goal
Deploy this Laravel Jitsi admin project with Docker using the smallest production-ready setup that matches the app's requirements.

## Acceptance Criteria
- [ ] Root `Dockerfile` exists and builds the Laravel app with Composer and Vite assets.
- [ ] `docker-compose.yml` provisions the app and required supporting services.
- [ ] Runtime supports web, queue worker, and scheduler responsibilities.
- [ ] Docker docs reflect how to start the stack on port `8090`.

## Working Notes
- Existing `docker/entrypoint.sh` already starts Laravel on `${PORT:-8090}`.
- App defaults expect database-backed queue, cache, and session drivers.
- User requested SQLite instead of a separate database service.
- Docker deployment should stay minimal and avoid custom dashboard changes.

## Plan
- [x] Restate goal + acceptance criteria
- [x] Locate existing implementation / patterns
- [x] Design: minimal approach + key decisions
- [x] Implement smallest safe slice
- [x] Add/adjust tests
- [x] Run verification (lint/tests/build/manual repro)
- [x] Summarize changes + verification story
- [ ] Record lessons (if any)

## Results
- Switched Docker deployment from PostgreSQL to SQLite-backed volumes.
- Kept separate app, queue, and scheduler services using the same image.
- Updated `docker/entrypoint.sh` to create the SQLite database file automatically.
- Verified `docker compose config` and editor diagnostics for Docker/runtime files.
