# Backend API Reference (Current Project State)

This project exposes web and API endpoints under the same app root path.

- Base web: `{APP_BASE_PATH}`
- Base API: `{APP_BASE_PATH}/api`

`APP_BASE_PATH` is deployment-specific (can be `/`, `/jitsiadmin`, etc.).

> No separate `/mobile` prefix is required. Any client (web, Flutter, desktop) can consume the same API contract.

---

## Authentication Model

Current production flow is session/cookie-based (`web` middleware on critical join routes).

- Dashboard login creates a Laravel session.
- Meeting join/leave/admission endpoints are under `web` middleware to recognize authenticated users.

If token auth is needed later (Sanctum/Passport), add it as an additional auth mode; keep current web-session endpoints for dashboard compatibility.

---

## Meeting Join Flow Endpoints

### Health check
- `GET /api/meetings/{meeting}/health`
- Purpose: verify Jitsi service availability before join.

### Join
- `POST /api/meetings/{meeting}/join`
- Purpose: policy validation + JWT generation (if required) + join payload.

### Leave
- `POST /api/meetings/{meeting}/leave`
- Purpose: track participant leave event.

### Pending admissions (host/moderator)
- `GET /api/meetings/{meeting}/pending-admissions`

### Admit / reject waiting participant (host/moderator)
- `POST /api/meetings/{meeting}/admissions/{participant}`
- Body: `{ "action": "admit" | "reject" }`

---

## Join Denial Error Codes (stable)

- `ERR_IP_NOT_ALLOWED`
- `ERR_MEETING_FULL`
- `ERR_INVALID_PASSWORD`
- `ERR_GUEST_NOT_ALLOWED`
- `ERR_OUTSIDE_JOIN_WINDOW`
- `ERR_JWT_REQUIRED_NOT_CONFIGURED`
- `ERR_INVITE_REQUIRED`
- `ERR_INVITE_EXPIRED`
- `ERR_ORG_MEMBERS_ONLY`
- `ERR_ADMISSION_REQUIRED`
- `ERR_ADMISSION_REJECTED`

Use `error_code` as primary handling key in clients.

---

## Access/Policy Rules (Canonical)

Visibility values are normalized to:

- `invite_only`
- `link_anyone`
- `org_only`

Computed guest policy:

- `link_anyone` => `allow_guests = true`
- `invite_only` => `allow_guests = false` (unless valid invite token flow)
- `org_only` => `allow_guests = false`

---

## Dashboard Analytics & Reporting Endpoints

### Diagnostics page (dashboard)
- `GET /dashboard/meetings/{meeting}/diagnostics`

### Summary page (dashboard)
- `GET /dashboard/meetings/{meeting}/summary`

### Export attendance CSV
- `GET /dashboard/meetings/{meeting}/summary/export/participants`

### Export events CSV
- `GET /dashboard/meetings/{meeting}/summary/export/events`

---

## Integration Note for Flutter

Use app base URL + `/api` route set directly:

- `{APP_BASE_URL}{APP_BASE_PATH}/api/...`

Example patterns:
- `https://example.com/api/...` (root deployment)
- `https://example.com/<subpath>/api/...` (subpath deployment)

Do not hardcode room/JWT on client. Always call `join` endpoint and use returned payload.
