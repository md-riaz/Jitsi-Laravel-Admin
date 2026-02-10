# DOMAIN_RULES.md
## Domain Invariants

---

## Meeting Rules

- Every meeting has exactly one room_name
- room_name is generated server-side
- room_name is immutable after creation

---

## Time Rules

Join allowed only if:
now >= start_at - join_early_minutes
AND
now <= end_at + join_late_minutes

---

## Invite Rules

- Invites are signed
- Invites are revocable
- Expired invites are invalid

---

## Security Rules

- JWTs are short-lived
- JWTs are per-meeting
- JWTs must match room_name

---

## Audit Rules

- All lifecycle events are logged
- Audit logs are immutable
- Audit logs are read-only in dashboard
