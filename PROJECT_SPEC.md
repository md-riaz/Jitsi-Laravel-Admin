# PROJECT_SPEC.md
## Jitsi Meeting Scheduling & Orchestration Platform

### Goal
Provide a matrx.io-like scheduling experience on top of an existing
Jitsi Meet instance.

Jitsi is a media engine.
Laravel is the control plane.

---

## Core Capabilities

- Scheduled meetings
- Invite-only or link-based access
- Join-time enforcement
- Embedded Jitsi UI
- Organization-based multi-tenancy
- Admin dashboards via Tyro Dashboard

---

## Required Packages

- Laravel 12
- hasinhayder/tyro
- hasinhayder/tyro-login
- hasinhayder/tyro-dashboard

---

## User Roles

- super-admin
- org-admin
- host
- member
- guest (implicit)

---

## Meeting Lifecycle

1. Create meeting
2. Invite participants
3. Send calendar invite
4. Enforce join window
5. Join embedded Jitsi
6. Record audit events
7. End meeting

---

## Join Rules

Join allowed only if:
- User is authenticated OR valid invite
- Current time is within join window
- Backend explicitly allows access

Frontend cannot override this.

---

## Dashboard

All admin and user dashboards are implemented
using Tyro Dashboard scaffolding.

No custom admin systems are allowed.
