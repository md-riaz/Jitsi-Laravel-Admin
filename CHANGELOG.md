# Changelog

All notable changes to this project will be documented in this file.

## [2026-04-09]

### Changed
- Reworked `/dashboard/my-meetings` to better separate instant, upcoming, and past meeting actions and layouts.
- Converted the live meetings area into an **Instant Meetings** table with organization, meeting name, created date, and streamlined actions.
- Simplified instant meeting actions to prioritize **Join** and support forced delete with dashboard-native confirmation.
- Removed redundant actions and descriptions from meeting listings to reduce clutter and improve hierarchy.
- Made past meetings table horizontally scrollable on smaller devices.
- Replaced browser delete confirms in meeting dashboard flows with the dashboard's built-in confirmation modal.
- Converted dashboard flash messages into floating top-right toast alerts that are dismissible and auto-dismiss after 10 seconds.
- Adjusted toast positioning so alerts do not cover the profile area in the top bar.
- Updated the application default timezone to `Asia/Dhaka`.

### Fixed
- Fixed `/dashboard/my-meetings` 500 errors caused by broken Blade structure during iterative UI changes.
- Fixed meeting dashboard delete flow so instant meetings can be force-deleted after an explicit warning.
- Fixed toast rendering on layouts using `tyro-dashboard::layouts.app` and `tyro-dashboard::layouts.user` by adding the required toast stack wrapper.
- Fixed dashboard live/instant meeting classification logic to rely on local meeting status instead of the removed `isLiveNow()` time-window behavior.

## [2026-04-07]

### Fixed
- Fixed Jitsi login prompts for organization admins and hosts by enabling JWT by default for organizations created through registration and team flows.
- Updated the organization model defaults so organization-linked meetings generate Jitsi JWTs consistently.
- Added a database migration to backfill existing organizations with `require_jwt = true` and `jwt_expiry_minutes = 120`.
- Fixed `Summary` and `Diagnostics` 500 errors by restoring Laravel base controller inheritance and authorization helpers (`authorize()` / validation traits).
- Fixed org-admin meeting visibility in the dashboard by ensuring meetings created by organization users keep their `organization_id`, even when visibility is `Anyone with Link`.
- Added a database migration to backfill missing `organization_id` values on meetings created by organization users.
- Added edit support for upcoming meetings with role-based authorization: org-admins can edit any upcoming meeting in their organization, while hosts can edit only the upcoming meetings they created.
- Added dedicated dashboard edit/update routes, controller, and Blade view for upcoming meetings.
- Expanded the create-meeting advanced options UI so it now exposes password, max participants, allowed IPs, lobby, and IP restriction settings consistently with the edit flow.
- Added production-safe meeting deletion with soft deletes, role-based authorization, backend validation, audit logging, and dashboard delete actions across live, upcoming, and past meeting sections.
- Blocked deletion of ongoing meetings by enforcing live-state and active-participant checks before delete operations are allowed.

### Notes
- Jitsi Docker JWT settings were already aligned with Laravel. The main issue was Laravel organization JWT policy being disabled for org records.
- A separate dashboard bug caused org-owned link-based meetings to lose `organization_id`, which hid valid upcoming meetings from org-admin views and blocked org-based edit access.
- Meeting deletion now uses soft deletes so removed meetings disappear from normal queries and route model binding without hard-deleting related historical records.
