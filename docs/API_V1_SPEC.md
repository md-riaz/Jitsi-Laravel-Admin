# API v1 Spec (Client-Ready)

Base:
- `{APP_BASE_URL}{APP_BASE_PATH}/api/v1`

Auth:
- `POST /auth/login`
- `GET /auth/me` (Bearer)
- `POST /auth/logout` (Bearer)

Invites / Guests:
- `POST /invites/resolve`
- `POST /invites/{token}/accept`
- `POST /meetings/{meeting}/join-guest`

Meetings (Bearer):
- `GET /meetings`
- `GET /meetings/{meeting}`
- `GET /meetings/{meeting}/health`
- `POST /meetings/{meeting}/join`
- `POST /meetings/{meeting}/leave`

Admissions (Bearer):
- `GET /meetings/{meeting}/pending-admissions`
- `POST /meetings/{meeting}/admissions/{participant}`
- `GET /meetings/{meeting}/admission-status?participant_id=...`

Analytics (Bearer):
- `GET /meetings/{meeting}/summary`
- `GET /meetings/{meeting}/timeline`
- `GET /meetings/{meeting}/attendance`
- `GET /meetings/{meeting}/diagnostics`

## Envelope

Success:
```json
{ "ok": true, "data": {} }
```

Error:
```json
{ "ok": false, "error_code": "ERR_CODE", "message": "...", "details": {} }
```

## Join denial codes
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
- `ERR_HEALTH_UNREACHABLE`
- `ERR_HEALTH_DOMAIN_MISSING`
