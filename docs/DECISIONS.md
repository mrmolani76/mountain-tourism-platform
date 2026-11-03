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
| 2026-11-03 | **Module 1 tenancy freeze (MVP): DB-per-tenant + Host/`Domain` routing is the settled isolation story** | Closes Module_1 §19 Q1–Q2: reuses the 2026-11-02 Accepted DB-per-tenant decision; MVP needs no purchased subdomain (host routing works on shared VPS hostname/sslip.io or IP:port; `Domain` registry maps host → tenant; staff login binds session to issuing tenant, BUG-TENANT-001). No `tenant_id` columns; isolation via active connection | Accepted |
| 2026-11-03 | **Module 1 MVP policy defaults (§19 Q3–Q18)** | Coding defaults so Module 1 can close without blocking on every client answer: Q3 complex create needs name+slug mandatory (rest optional); Q4 new complex active immediately (`is_active=True` default, Super Admin can deactivate later); Q5 deactivated tenant → login/API blocked with 404 `Tenant inactive.`; Q6 deactivation is temporary/reactivable, no data loss; Q7 staff fields = email/password/role/is_active; Q8 Managers may reset staff passwords (existing `CanResetStaffPassword`); Owner creates staff (existing `IsOwner` on StaffViewSet); Q9 Owner accesses all outlets implicitly; others via explicit `StaffOutletAccess`; Q10-Q11: yes, staff can hold multiple outlet accesses; Q12-Q13: building holds multiple outlets/types; building/outlet types are fixed enums in code (not tenant-configurable); Q14: seasons may NOT overlap (cross-kind, enforced); Q15: tax_rates = JSON list of percentages; Q16: new tenant defaults currency=`EUR`, units=`metric`, timezone=`UTC`; Q17: audit log viewable by Owner/Manager (UI deferred to Module C); Q18: audit retention = indefinite (append-only, no purge). Q19 → isolation source of truth = DB-per-tenant + active connection (resolved in Q1/Q2 row). True client-only questions (exact mandatory complex fields beyond name/slug, custom tax components, audit UI) deferred; defaults may be overridden by client later without migration. | Accepted |

When adding a decision: append a row, never rewrite history silently.
