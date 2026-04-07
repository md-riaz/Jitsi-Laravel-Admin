# Changelog

All notable changes to this project will be documented in this file.

## [2026-04-07]

### Fixed
- Fixed Jitsi login prompts for organization admins and hosts by enabling JWT by default for organizations created through registration and team flows.
- Updated the organization model defaults so organization-linked meetings generate Jitsi JWTs consistently.
- Added a database migration to backfill existing organizations with `require_jwt = true` and `jwt_expiry_minutes = 120`.
- Fixed `Summary` and `Diagnostics` 500 errors by restoring Laravel base controller inheritance and authorization helpers (`authorize()` / validation traits).

### Notes
- Jitsi Docker JWT settings were already aligned with Laravel. The main issue was Laravel organization JWT policy being disabled for org records.
