# Architecture decisions

Record choices that differ from or extend the MVP PDF preferences.

| Date | Decision | Why | Status |
|------|----------|-----|--------|
| 2026-11-02 | Backend = **Python Django + DRF** | Team choice (PDF preferred Nest/Laravel/Next; stack is open for discussion) | Accepted |
| 2026-11-02 | Frontend = **Nuxt + Vue + Vuetify** | Team choice; PDF allows clean component libraries; staff UX > polish | Accepted |
| 2026-11-02 | DB = **PostgreSQL** | Matches VPS and MVP technical rules (RLS option) | Accepted |
| 2026-11-02 | Tenancy = shared DB + `tenant_id`, isolation via **single scoped layer** (manager/RLS) | MVP §4 + §7 | **Superseded** by DB-per-tenant (same day) |
| 2026-11-02 | Money = **integer minor units**, append-only charges/payments | MVP §7 + §4 hard rule | Accepted |
| 2026-11-02 | MVP deploy = **VPS direct (no Docker required)**; optional local Docker only for dx | MVP §6 | Accepted |
| 2026-11-02 | Weather = MET Norway server-side cache only | MVP §7 / §5H | Accepted |
| 2026-11-02 | Maps = Leaflet or MapLibre + free OSM tiles | MVP §7 | Accepted |
| 2026-11-02 | **Delivery order = API/backend first**; Nuxt UI deferred | User: work API-base pehle; phases/todos backend-only for now | Accepted |
| 2026-11-02 | Tenant isolation = **scoped manager** (custom Django manager + base model), not Postgres RLS | Simpler, Django-idiomatic, testable without DB setup; single layer per TECHNICAL_RULES §2 | **Superseded** by connection-based isolation |
| 2026-11-02 | API auth = **session-based** (DRF SessionAuthentication), email/password | MVP simplest; Nuxt deferred; token can be added later if cross-origin | Accepted |
| 2026-11-02 | Tenancy = **DB-per-tenant** (one Postgres DB per complex); Host/`Domain` resolve; central `modules.saas` registry | User: EQMS-style; supersedes shared-DB + `tenant_id` row filter for MVP | Accepted |
| 2026-11-02 | Isolation = **active DB connection** (`tenancy/` + `DATABASE_ROUTERS`), not scoped manager | Follows DB-per-tenant | Accepted |
| 2026-11-02 | API surface = **DRF ViewSets + DefaultRouter**; auth = APIView; thin views → services | User: DRF-aligned | Accepted |
| 2026-11-02 | **SSO out of scope** (no SAML/ADFS/OAuth2/MFA) | User: leave SSO | Accepted |
| 2026-11-02 | Domain code under **`backend/modules/`** (Complere `Modules/{Name}` mirror), not flat `apps/` | User: module-wise like eQMS | Accepted |

When adding a decision: append a row, never rewrite history silently.
