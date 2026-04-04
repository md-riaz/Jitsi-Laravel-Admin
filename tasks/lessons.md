# Lessons

- Failure mode: custom page markup bypassed shared dashboard form styling, causing light-mode inconsistencies for file inputs.
  - Detection signal: file input looked wrong only on a custom profile page while dashboard-generated forms rendered correctly.
  - Prevention rule: for dashboard pages, prefer existing shared form classes like `form-input` before adding inline control styling.
- Failure mode: admin meeting visibility logic diverged across middleware, dashboard counters, calendar, and API scopes, causing super admins to be blocked and org admins to see inconsistent meeting sets.
  - Detection signal: super admin could not access meeting pages, while org admin visibility differed between My Meetings and Calendar.
  - Prevention rule: when adding role-scoped visibility for meetings, keep middleware access, dashboard queries, calendar queries, and API listing rules aligned under the same role model.
- Failure mode: Blade upcoming-meeting lists kept stale inline live checks after classification moved into shared model helpers, creating duplicated and misleading UI branches.
  - Detection signal: upcoming collections were already helper-filtered, but templates still called `canJoinAt(now())` and rendered live/join states inside upcoming sections.
  - Prevention rule: once lifecycle classification is centralized in model helpers or controller/view-composer collections, remove duplicate status branching from Blade templates and keep views dumb.
