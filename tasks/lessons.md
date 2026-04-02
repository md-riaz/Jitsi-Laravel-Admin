# Lessons

- Failure mode: custom page markup bypassed shared dashboard form styling, causing light-mode inconsistencies for file inputs.
  - Detection signal: file input looked wrong only on a custom profile page while dashboard-generated forms rendered correctly.
  - Prevention rule: for dashboard pages, prefer existing shared form classes like `form-input` before adding inline control styling.
