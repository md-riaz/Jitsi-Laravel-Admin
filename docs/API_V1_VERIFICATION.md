# API v1 Verification Report

Date (UTC): 2026-02-26
Environment: Production deployment behind subpath
Base: `{APP_BASE_URL}{APP_BASE_PATH}/api/v1`

## Scope
Validated end-to-end behavior for:
- Auth login
- Invite resolve/accept
- Guest join (invite-only)
- Admission-required lobby flow
- Admission status polling
- Authenticated join (JWT payload)

---

## Test Matrix

| Test | Endpoint | Expected | Result |
|---|---|---|---|
| Auth login | `POST /auth/login` | `ok=true`, token returned | ✅ Pass |
| Invite resolve | `POST /invites/resolve` | valid meeting context | ✅ Pass |
| Invite accept | `POST /invites/{token}/accept` | accepted response | ✅ Pass |
| Guest join (invite-only) | `POST /meetings/{id}/join-guest` + invite token | `ok=true`, join payload returned | ✅ Pass |
| Guest join (lobby enabled) | `POST /meetings/{id}/join-guest` | `ERR_ADMISSION_REQUIRED` + `pending_participant_id` | ✅ Pass |
| Admission status | `GET /meetings/{id}/admission-status?participant_id=...` | `pending` for new request | ✅ Pass |
| Auth join | `POST /meetings/{id}/join` (Bearer) | `ok=true`, Jitsi payload + JWT | ✅ Pass |

---

## Fixes applied during verification

1. **Invite-only + guest gate conflict**
   - Issue: valid invite token still denied by generic guest gate.
   - Fix: in `MeetingAccessPolicyService`, allow invite-only guest join when invite is validated.

2. **Admission status compatibility mapping**
   - DB uses `invited/accepted/declined`; client expects `pending/admitted/rejected`.
   - Fix: map status in API response:
     - `invited -> pending`
     - `accepted -> admitted`
     - `declined/bounced -> rejected`

3. **API session coupling removed from invite accept**
   - Issue: API client should not rely on web session writes.
   - Fix: `InviteController::accept` no longer depends on session storage in v1 flow.

4. **Guest polling endpoint exposure**
   - Added `GET /meetings/{meeting}/admission-status` in v1 root group for guest polling.

---

## Notes

- Legacy `/api/meetings/*` routes were removed in favor of `/api/v1/*`.
- Web meeting page was migrated to v1 endpoints.
- Postman collection is available at:
  - `docs/API_V1.postman_collection.json`

---

## Recommended next checks

1. Add PHPUnit feature tests mirroring this matrix.
2. Add CI job to run API smoke against staging.
3. Capture screenshots listed in `docs/UPDATED_SCREENSHOTS.md`.
