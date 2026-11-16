# Change Report: Core menu parity + fullstack delivery rule

- **Date**: 2026-11-16
- **By**: Agent
- **Change ID**: 98

## 1. Summary

Removed backend-only / frontend-deferred agent rule (fullstack by default). Tightened Core Staff/Buildings/Seasons/Settings/Outlets UI↔API wiring.

## 2. Before

- CLAUDE/AGENTS: backend first; Nuxt deferred unless asked
- Staff password reset hit missing `/user/passwordReset`
- Listing filter preference called missing `/saveUserPreference` (404 noise)
- DELETE 204 treated as failure in resource client
- Owner menu codes incomplete for staff form meta

## 3. After

- Delivery = Laravel + Nuxt together; setup still off by default
- Password reset → `POST /api/auth/reset-password` + UI on staff view
- Preference save is a local no-op
- DELETE 204 returns success token; resource remove reliable
- Owner ROLE_PERMISSIONS include `users.*`

## 4. Reason

User: check all Core menus with backend; remove backend-only rule; work both layers together.

## 5. Files Affected

- `CLAUDE.md`, `AGENTS.md`, `docs/DECISIONS.md`, `work/PLAN.md`, `work/MEMORY.md`
- `frontend/composables/auth/useAuthService.ts`
- `frontend/composables/api-clients/useUserApiClient.ts`
- `frontend/composables/api-clients/useSharedApiClient.ts`
- `frontend/composables/api-clients/useResourceApiClient.ts`
- `frontend/composables/core/useApi.ts`
- `frontend/pages/users/view/[id].vue`
- work kanban / change report

## 6. Verification / Status

- [ ] Code-only (no run)
- [ ] Core menu map: Staff list/form/view, Buildings, Outlets, Seasons, Settings → matching Laravel routes
