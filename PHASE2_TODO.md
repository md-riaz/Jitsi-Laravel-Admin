# Phase 2 TODO / Checkpoints

- [x] Create Phase 2 checklist and checkpoints file
- [x] Normalize visibility values and enforce canonical policy mapping
- [x] Centralize join policy checks in `MeetingAccessPolicyService`
- [x] Enforce invite-only token flow for guests
- [x] Enforce strict org-only membership checks
- [x] Add waiting-room style admission request flow (first pass)
- [x] Add moderator quick controls in meeting UI (lock/mute/lobby + admission actions)
- [x] Add access diagnostics page (admin)
- [x] Expand stable join denial error codes
- [x] Run cache clear + smoke checks
- [ ] User validation checkpoint

## Checkpoints

### Checkpoint A — Policy Core
- Added canonical visibility mapping in model (`organization/org/public_link` => canonical values)
- Added centralized join policy service (`MeetingAccessPolicyService`)

### Checkpoint B — Guest/Org Enforcement
- Invite-only guests now require a valid invite token/session
- Org-only now strictly checks authenticated organization membership (with owner/super-admin exception)

### Checkpoint C — Admission + Moderator Controls
- Added pending admission API endpoints for moderators
- Added admit/reject actions and basic moderator controls in meeting page

### Checkpoint D — Diagnostics
- Added dashboard diagnostics page with policy snapshot, pending admissions and recent denial/admission events
