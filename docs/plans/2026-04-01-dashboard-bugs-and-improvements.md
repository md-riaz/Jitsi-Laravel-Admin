# Dashboard Bugs and UX Improvements Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Fix reported dashboard/meeting bugs and apply scoped UX updates without violating project domain rules or Tyro dashboard constraints.

**Architecture:** Keep Jitsi as external media system and Laravel as control plane. Apply minimal, role-aware, server-side fixes in existing controllers/services/policies/views. Reuse Tyro dashboard/view structure and avoid introducing custom admin systems.

**Tech Stack:** Laravel 12, Tyro Dashboard, Tyro RBAC, Blade, Eloquent, Jitsi external integration.

---

## Current state map

### Already implemented (verify and keep)
- Meeting invite/access policy logic exists for invite-only/org-only constraints.
  - `app/Services/MeetingAccessPolicyService.php`
- Meeting manage/view authorization policy exists.
  - `app/Policies/MeetingPolicy.php`
- Summary and diagnostics controllers exist with authorization checks.
  - `app/Http/Controllers/Dashboard/MeetingSummaryController.php`
  - `app/Http/Controllers/Dashboard/MeetingDiagnosticsController.php`
- Profile upload service/controller exist.
  - `app/Services/ProfilePictureService.php`
  - `app/Http/Controllers/Api/ProfilePictureController.php`
- Subscription page controller exists and resolves effective plan.
  - `app/Http/Controllers/Dashboard/SubscriptionController.php`
- Alert modal utilities exist in dashboard scripts.
  - `resources/views/vendor/tyro-dashboard/partials/scripts.blade.php`

### Needs implementation/fix
- Guest exit redirect currently routes guests back to meeting page.
  - `resources/views/meeting/show.blade.php`
- Org-admin dashboard stats are not org-scoped.
  - `app/Providers/AppServiceProvider.php`
  - `app/Http/Controllers/Dashboard/MyMeetingsController.php`
- Host/member `/dashboard` 500 requires root-cause fix.
  - Dashboard composer + role-aware view data chain
- Guest link behavior must align with settings and token flow in all entry paths.
  - Access/join endpoints + invite route integration
- Branding replacement is incomplete (`Alora Admin` standardization still needs to be completed everywhere it appears).
  - Views, emails, metadata/title fallbacks
- Sidebar/menu ordering must be aligned to UX request.
  - Tyro sidebar partials
- Org-admin visibility requirements for all org meetings + creator attribution need verification and likely query tightening.

---

### Task 1: Reproduce and pin each failing report

**Files:**
- Read only: `routes/web.php`, `routes/api.php`, `storage/logs/laravel.log`
- Inspect controllers/views listed in later tasks

**Step 1: Create reproducibility matrix**
- Define role + route + action + expected result for each report.

**Step 2: Capture deterministic failures**
- Reproduce each issue and record exact exception/message/stack location.

**Step 3: Freeze scope**
- Confirm only listed issues are in this patch.

**Step 4: Commit planning artifact**
- Commit notes-only changes (if tracked in repo).

### Task 2: Fix guest exit redirect after leaving meeting

**Files:**
- Modify: `resources/views/meeting/show.blade.php`

**Step 1: Write failing feature check**
- Add/adjust feature test or deterministic manual case for guest close behavior.

**Step 2: Implement minimal redirect logic**
- On `readyToClose`, route unauthenticated users to dashboard home landing, not meeting show route.

**Step 3: Verify authenticated behavior unchanged**
- Authenticated users still route to expected meetings/dashboard destination.

**Step 4: Commit**
- `fix: correct guest redirect after meeting exit`

### Task 3: Scope org-admin dashboard stats to organization

**Files:**
- Modify: `app/Providers/AppServiceProvider.php`
- Modify: `app/Http/Controllers/Dashboard/MyMeetingsController.php`
- (Optional) Modify shared query helper if extracted

**Step 1: Define role-aware query contract**
- super-admin: global
- org-admin: same organization
- host/member: own created/participating

**Step 2: Apply query changes in composer/controller**
- Ensure counts and collections are derived from the same scoped base query.

**Step 3: Verify counts and meeting lists**
- Validate with seeded data for mixed organizations.

**Step 4: Commit**
- `fix: scope dashboard meeting stats by role and organization`

### Task 4: Fix host/member `/dashboard` 500

**Files:**
- Modify: `app/Providers/AppServiceProvider.php`
- Modify: impacted dashboard view(s) under `resources/views/vendor/tyro-dashboard/dashboard/*.blade.php`

**Step 1: Reproduce with host and member accounts**
- Collect exact null/undefined/index error.

**Step 2: Patch data contract mismatch**
- Ensure composer always provides expected keys and collection types.

**Step 3: Validate routes and middleware assumptions**
- Confirm role redirects don’t push host/member into incompatible dashboard template.

**Step 4: Commit**
- `fix: prevent dashboard runtime errors for host and member`

### Task 5: Guest access via meeting link (settings-aligned)

**Files:**
- Modify if needed: `app/Services/MeetingAccessPolicyService.php`
- Modify if needed: `app/Http/Controllers/Api/V1/InviteController.php`
- Modify if needed: `app/Http/Controllers/Api/MeetingJoinController.php`
- Modify if needed: `resources/views/meeting/show.blade.php`

**Step 1: Verify intended matrix**
- `invite_only` requires valid invite token (unless owner/super-admin/participant).
- `link_anyone` + allow guests permits guest link joins.

**Step 2: Fix mismatch in link flow**
- Ensure shared link path carries/accepts required invite token where policy expects it.

**Step 3: Regression verify for all visibility modes**
- org_only, invite_only, link_anyone with/without guest allowance.

**Step 4: Commit**
- `fix: align guest join-link access with meeting visibility settings`

### Task 6: Profile image upload reliability hardening

**Files:**
- Verify: `app/Services/ProfilePictureService.php`
- Verify/modify: `app/Http/Controllers/Api/ProfilePictureController.php`
- Verify storage config: `config/filesystems.php`

**Step 1: Reproduce upload failure path**
- Inspect validation/storage/public disk linkage.

**Step 2: Apply minimal fix**
- Keep current MIME constraints; only adjust failing edge path (disk config, URL generation, max size handling, or response payload).

**Step 3: Verify upload + persistence + retrieval URL**
- Confirm avatar survives refresh and old avatar cleanup behavior.

**Step 4: Commit**
- `fix: restore reliable profile image upload flow`

### Task 7: Subscription defaults and My Subscription UX

**Files:**
- Verify/modify: registration/onboarding controllers
- Verify/modify: `app/Http/Controllers/Dashboard/SubscriptionController.php`
- Verify/modify: `resources/views/dashboard/subscription.blade.php`

**Step 1: Confirm new org self-sign flow assigns free plan**
- Enforce server-side default for free tier when no paid subscription exists.

**Step 2: Ensure My Subscription fallback rendering**
- Show paid package details if active else show free plan details.

**Step 3: Verify across account types**
- personal, org-admin, member views.

**Step 4: Commit**
- `feat: enforce free-plan default and subscription fallback display`

### Task 8: Menu placement updates

**Files:**
- Modify: `resources/views/vendor/tyro-dashboard/partials/user-sidebar.blade.php`
- Modify additional sidebar partials if used for host/member

**Step 1: Remove “My Profile” from left sidebar**

**Step 2: Place “My Subscription” directly below “Calendar”**

**Step 3: Verify per role sidebar variants**

**Step 4: Commit**
- `chore: update sidebar menu ordering for subscription UX`

### Task 9: Scheduled meeting edit permissions verification

**Files:**
- Verify/modify: `app/Policies/MeetingPolicy.php`
- Verify controllers calling `authorize('manage', $meeting)`

**Step 1: Add/update authorization tests**
- org-admin + host allowed, member denied, super-admin allowed.

**Step 2: Tighten policy only if failing**
- Keep minimal changes.

**Step 3: Verify endpoints + dashboard actions respect policy**

**Step 4: Commit**
- `fix: enforce scheduled meeting edit permissions by role`

### Task 10: Org-admin organization-wide meeting visibility + creator info

**Files:**
- Modify: `app/Http/Controllers/Dashboard/MyMeetingsController.php`
- Modify relevant views: meeting list/index cards/tables

**Step 1: Ensure org-admin query includes all org meetings**
- both scheduled and instant meetings.

**Step 2: Render creator identity in meeting listings**
- Avoid N+1 by eager loading `creator` relation.

**Step 3: Verify with mixed creators in same org**

**Step 4: Commit**
- `feat: org-admin org-wide meeting visibility with creator attribution`

### Task 11: Branding standardization (`Alora Admin` rollout)

**Files:**
- Search/modify all views and mail templates under `resources/views/**`
- Verify metadata/title references and app name fallback in config-driven usage

**Step 1: Replace hardcoded labels**
- UI labels, page titles, emails, metadata.

**Step 2: Prefer centralized config source where possible**
- reduce future string drift.

**Step 3: Verify rendered outputs**
- dashboard, meeting pages, emails (preview if available).

**Step 4: Commit**
- `chore: standardize platform branding to Alora Admin`

### Task 12: Alert behavior normalization

**Files:**
- Modify: `resources/views/vendor/tyro-dashboard/partials/scripts.blade.php`
- Modify affected alert call sites if needed

**Step 1: Enforce popup notifications behavior**
- dismissible manually
- auto-dismiss at 10s

**Step 2: Keep backwards compatibility of existing alert invocations**

**Step 3: Verify alert types**
- success, error, warning, info.

**Step 4: Commit**
- `feat: normalize dashboard alerts as dismissible timed popups`

---

## Verification protocol

Run after each task where relevant:
- PHP diagnostics on touched files
- Targeted feature tests for authorization/join flows
- Manual role-based smoke checks:
  - super-admin, org-admin, host, member, guest

Final regression pass:
- `/dashboard`
- meeting join/leave
- summary and diagnostics pages
- profile upload
- subscription page
- sidebar/menu layout
- branding text snapshots

## Risk controls

- Keep changes behind existing role/visibility logic; no new policy model unless necessary.
- Avoid Jitsi-side scheduling/control changes.
- Enforce all decisions server-side.
- Preserve Tyro-generated dashboard structure (no custom admin system).
