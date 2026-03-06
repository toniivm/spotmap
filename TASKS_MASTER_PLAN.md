# SpotMap Master Task Plan

Updated: 2026-02-16

## Goal
Ship a secure, testable, and production-ready SpotMap stack (backend + frontend + CI + E2E) with clear P1/P2 execution.

## Completed (P0/P1)
- [x] Harden backend auth fallback with JWT signature verification (HS256).
- [x] Add explicit env controls for fallback policy:
  - `SUPABASE_JWT_SECRET`
  - `ALLOW_INSECURE_JWT_FALLBACK` (default false)
- [x] Fix boolean parsing for `DEBUG` in backend config.
- [x] Replace insecure production example admin email defaults.
- [x] Update env templates for safer production onboarding.
- [x] Repair backend test harness issues and stabilize unit tests.
- [x] Add CI workflow for frontend lint/test/build.
- [x] Add CI workflow for backend PHPUnit.
- [x] Add E2E smoke + recovery flow in CI.
- [x] Add business-flow E2E (user -> moderation -> notification).
- [x] Add modal accessibility behavior in app shell (Escape, focus trap, focus restore, inert background).
- [x] Extract modal accessibility logic to reusable composable (`useModalA11y`).
- [x] Add dedicated modal accessibility E2E spec.
- [x] Enforce business-flow credentials on `staging` and `release/*` CI contexts.

## In Progress (P1)
- [ ] Add branch protections to require all CI checks before merge (`main`, `staging`, `release/*`).
- [ ] Configure repository secrets for mandatory business-flow contexts:
  - `E2E_USER_EMAIL`
  - `E2E_USER_PASSWORD`
  - `E2E_MOD_EMAIL`
  - `E2E_MOD_PASSWORD`
- [ ] Run one full CI pass on target branches after secrets are set.

## Pending (P2)
- [ ] Add role cache TTL + invalidation strategy in backend auth layer.
- [ ] Add image optimization/conversion pipeline (WebP/size caps) in upload path.
- [ ] Complete remaining legacy parity items in Vue migration plan.
- [ ] Extend E2E coverage for moderation edge cases (reject, stale pending state, duplicate actions).
- [ ] Add observability metrics dashboard for auth/moderation/notifications latency.

## Definition of Done
- [ ] CI green on `main`.
- [ ] CI green on `staging` with required business-flow.
- [ ] CI green on one `release/*` branch with required business-flow.
- [ ] Security and deployment docs aligned with final env policy.

## Notes
- Business-flow E2E can skip only when `REQUIRE_E2E_BUSINESS=false`.
- CI sets `REQUIRE_E2E_BUSINESS=true` automatically for `staging` and `release/*`.
