# BUILD_ORDER.md
## Required Build Sequence

1. Bootstrap Laravel
2. Install Tyro + Tyro Login
3. Install Tyro Dashboard
4. Define roles & privileges
5. Create core database schema
6. Scaffold dashboard pages (Artisan)
7. Implement meeting domain logic
8. Implement join gating
9. Integrate Jitsi embed
10. Add notifications + calendar
11. Final security audit

---

## Stop Conditions

If any of the following occur, STOP:
- Manual dashboard UI wiring
- Client-side join logic
- Jitsi state queried as authority
