# ARCHITECTURE.md
## System Architecture

### High-Level

Client
 → Laravel App
   → Database
   → Queue
   → Mail/SMS
   → Jitsi (external)

---

## Key Principles

- Stateless frontend
- Backend is source of truth
- Jitsi has no authority over scheduling
- All join logic is centralized

---

## Integration Boundary

### Jitsi
Inputs:
- roomName
- jwt (optional)
- user displayName

Outputs:
- media only

Jitsi must not be queried for meeting state.

---

## Dashboard Architecture

- Blade-based
- Generated via Tyro Dashboard
- Uses Tyro RBAC
- Sidebar auto-managed

---

## Failure Isolation

- Jitsi failure does not corrupt meetings
- Notification failure does not block joining
- Dashboard failure does not block meeting join
