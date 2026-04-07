# Changelog

All notable changes to this project will be documented in this file.

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

### Notes
- Jitsi Docker JWT settings were already aligned with Laravel. The main issue was Laravel organization JWT policy being disabled for org records.
- A separate dashboard bug caused org-owned link-based meetings to lose `organization_id`, which hid valid upcoming meetings from org-admin views and blocked org-based edit access.
