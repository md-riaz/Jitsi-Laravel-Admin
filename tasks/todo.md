# TODO - Docker Deployment

## Goal
Deploy this Laravel Jitsi admin project with Docker using the smallest production-ready setup that matches the app's requirements.

## Acceptance Criteria
- [x] Root `Dockerfile` exists and builds the Laravel app with Composer and Vite assets.
- [x] `docker-compose.yml` provisions the app and required supporting services.
- [x] Runtime supports web, queue worker, and scheduler responsibilities.
- [x] Docker docs reflect how to start the stack on host port `18090` mapped to container port `8090`.
- [x] Local Docker login and auth page URLs render over `http://localhost:18090` without forced HTTPS.

## Working Notes
- Existing `docker/entrypoint.sh` starts Laravel on `${PORT:-8090}` when no explicit command is passed.
- App defaults expect database-backed queue, cache, and session drivers.
- User requested SQLite instead of a separate database service.
- Docker deployment should stay minimal and avoid custom dashboard changes.
- Windows blocked lower published ports during testing, so the stack now uses host port `18090`.

## Plan
- [x] Restate goal + acceptance criteria
- [x] Locate existing implementation / patterns
- [x] Design: minimal approach + key decisions
- [x] Implement smallest safe slice
- [x] Add/adjust tests
- [x] Run verification (lint/tests/build/manual repro)
- [x] Summarize changes + verification story
- [x] Record lessons (if any)

## Results
- Switched Docker deployment from PostgreSQL to SQLite-backed volumes.
- Kept separate app, queue, and scheduler services using the same image.
- Updated `docker/entrypoint.sh` to create the SQLite database file automatically, stop copying `.env.example` into `.env`, honor service-specific commands so queue and scheduler do not start the web server, and clear Laravel caches on startup.
- Removed remaining global URL forcing from `app/Providers/AppServiceProvider.php` so local Docker URL generation follows the real incoming HTTP request instead of a forced app root.
- Made the Docker/docs boundary explicit: this repo deploys only the Laravel admin/orchestration side, not Jitsi Meet itself.
- Changed the published host port to `18090` while keeping the container on `8090`.
- Verified `docker compose config` and editor diagnostics for Docker/runtime files.
- Verified live deployment with `docker compose up --build -d`, `docker compose ps`, `docker compose logs --tail=40 app queue scheduler`, and `curl.exe -I http://localhost:18090` returning `HTTP/1.1 200 OK`.
