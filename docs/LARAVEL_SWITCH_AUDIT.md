# Laravel Switch — Phase 0 Audit (Django/DRF)

> Master plan: Laravel Backend Switch (work item 68, 2026-11-03).
> Migration principle 1: "Audit before coding. Do not infer functionality from folder names."
> This audit documents what EXISTS today. It claims nothing about Laravel implementation status.

## 1. Existing module list

| Layer | Component | Path | Status |
|---|---|---|---|
| Central registry | Tenant, Domain, SaasUser | `backend/modules/saas/` | Implemented (models + APIs) |
| Tenancy shim | Host→DB resolution, router, context | `backend/tenancy/` | Implemented (runtime DB switching) |
| Core config | Building, Outlet, Season, TenantSettings, Staff, StaffRole, StaffOutletAccess, AuditEvent | `backend/modules/core/` | Implemented (models + APIs + services) |
| Hotel (B) | RoomType, Room, BoardPlan, Stay | `backend/modules/hotel/` | Partial vs docs (missing HutBed/Reservation/RoomSeasonPrice/nightly job) |
| Folio (C) | Folio, Charge, Payment | `backend/modules/folio/` | Implemented (append-only money domain) |
| POS (D) | ProductCategory, Product, Order, OrderLine | `backend/modules/pos/` | Partial (place order + add line only) |
| Rental (E) | Rental | `backend/modules/rental/` | Model + CRUD only (no service flow) |
| Services (F) | Service, Slot | `backend/modules/services/` | Model + CRUD only (no Booking) |
| Transport (G) | Vehicle, Driver, Trip | `backend/modules/transport/` | Model + CRUD only |
| Weather (H) | WeatherLimit, WeatherWarning | `backend/modules/weather/` | Model + CRUD only (no MET integration) |
| Reports (I) | Report | `backend/modules/reports/` | Model + CRUD only (no generation) |

Django apps (`config/settings/base.py`): `tenancy`, `modules.saas` (central), `modules.core/hotel/folio/pos/rental/services/transport/weather/reports` (tenant).

## 2. API inventory

Envelope contract (must be preserved by Laravel): `{success, data, error:{code,message,details}, meta}`.
Pagination: page-number, `page_size=20`, `?page_size` ≤ 100; meta = `{page, page_size, total_pages, total_count, has_next, has_previous}`.

### Central SaaS — `/api/saas/`
| # | Endpoint | Method | Auth | Permission | Side effects | Laravel target | Status |
|---|---|---|---|---|---|---|---|
| 1 | api/saas/auth/login/ | POST | public | AllowAny | session `saas_user_id` + tenant rows | TBD | Not started |
| 2 | api/saas/auth/logout/ | POST | session | AllowAny + SaasSessionAuthentication | session flush | TBD | Not started |
| 3 | api/saas/auth/me/ | GET | session | IsSaasUser | — | TBD | Not started |
| 4 | api/saas/tenants/ | GET/POST | session | IsSaasUser | register_tenant (registry + alias only) | TBD | Not started |
| 5 | api/saas/tenants/{pk}/ | GET/PATCH | session | IsSaasUser | — | TBD | Not started |
| 6 | api/saas/tenants/{pk}/activate/ | POST | session | IsSaasUser | is_active=True | TBD | Not started |
| 7 | api/saas/tenants/{pk}/deactivate/ | POST | session | IsSaasUser | is_active=False | TBD | Not started |

### Core — `/api/`
| # | Endpoint | Method | Auth | Permission | Side effects | Laravel target | Status |
|---|---|---|---|---|---|---|---|
| 8 | api/health/ | GET | public | AllowAny | — | Verified (item 70) | Verified (item 70) |
| 9 | api/ | GET | public (root links) | AllowAny | — | TBD | Not started |
| 10 | api/auth/login/ | POST | public | AllowAny | session staff_id + tenant binding + CSRF token | Verified (item 70) | Verified (item 70) |
| 11 | api/auth/logout/ | POST | public | AllowAny | session flush | Verified (item 70) | Verified (item 70) |
| 12 | api/auth/me/ | GET | session | IsTenantStaff | — | Verified (item 70) | Verified (item 70) |
| 13 | api/auth/reset-password/ | POST | session | CanResetStaffPassword (owner/manager) | password policy write | Verified (item 70) | Verified (item 70) |
| 14 | api/settings/ | GET/PATCH | session | IsOwner | tenant config write | TBD | Not started |
| 15 | api/staff/ | GET/POST/PATCH/DELETE | session | IsOwner | staff CRUD | TBD | Not started |
| 16 | api/outlet-access/ | GET/POST/DELETE | session | IsOwner | access matrix | TBD | Not started |
| 17 | api/buildings/ | GET/POST/PATCH/DELETE | session | IsOwner | config CRUD | Verified (item 70) | Verified (item 70) |
| 18 | api/outlets/ | GET/POST/PATCH/DELETE | session | IsOwner | config CRUD | TBD | Not started |
| 19 | api/seasons/ | GET/POST/PATCH/DELETE | session | IsOwner | overlap validation | TBD | Not started |

### Hotel — `/api/hotel/`
| # | Endpoint | Method | Auth | Permission | Side effects | Laravel target | Status |
|---|---|---|---|---|---|---|---|
| 20 | api/hotel/board-plans/ | full CRUD | session | read: staff; write: HotelManager | — | TBD | Not started |
| 21 | api/hotel/room-types/ | full CRUD | session | read: staff; write: HotelManager | — | TBD | Not started |
| 22 | api/hotel/rooms/ | full CRUD | session | read: staff; write: HotelManager | — | TBD | Not started |
| 23 | api/hotel/stays/ | full CRUD | session | read: staff; write: HotelManager | — | TBD | Not started |
| 24 | api/hotel/stays/{pk}/check-in/ | POST | session | HotelManager | RESERVED→CHECKED_IN only | TBD | Not started |
| 25 | api/hotel/stays/{pk}/check-out/ | POST | session | HotelManager | CHECKED_IN→CHECKED_OUT only | TBD | Not started |

### Folio (money, append-only) — `/api/folio/`
| # | Endpoint | Method | Auth | Permission | Side effects | Laravel target | Status |
|---|---|---|---|---|---|---|---|
| 26 | api/folio/folios/ | full CRUD | session | read: staff; write: FolioManager | total recompute on charge/payment ops | TBD | Not started |
| 27 | api/folio/folios/{pk}/close/ | POST | session | FolioManager | zero-balance check (or confirm_zero_balance=True) → CLOSED | TBD | Not started |
| 28 | api/folio/charges/ | create only (PUT/PATCH/DELETE → 405) | session | read: staff; create+void: FolioManager | folio total recompute | TBD | Not started |
| 29 | api/folio/charges/{pk}/void/ | POST | session | FolioManager | void + recompute; money audit requires reason | TBD | Not started |
| 30 | api/folio/payments/ | create only (PUT/PATCH/DELETE → 405) | session | read: staff; create+void: FolioManager | folio total recompute | TBD | Not started |
| 31 | api/folio/payments/{pk}/void/ | POST | session | FolioManager | void + recompute | TBD | Not started |

### POS — `/api/pos/`
| # | Endpoint | Method | Auth | Permission | Side effects | Laravel target | Status |
|---|---|---|---|---|---|---|---|
| 32 | api/pos/product-categories/ | full CRUD | session | read: staff; write: PosManager | — | TBD | Not started |
| 33 | api/pos/products/ | full CRUD | session | read: staff; write: PosManager | — | TBD | Not started |
| 34 | api/pos/orders/ | full CRUD | session | read: staff; write: PosCashier (owner/manager/front_desk/cashier) | — | TBD | Not started |
| 35 | api/pos/order-lines/ | full CRUD | session | read: staff; write: PosCashier | order total recompute (non-voided lines) | TBD | Not started |

### Rental / Services / Transport / Weather / Reports
| # | Endpoint | Method | Auth | Permission | Notes | Laravel target | Status |
|---|---|---|---|---|---|---|---|
| 36 | api/rental/rentals/ | full CRUD | session | IsRentalStaff (any staff) | no return/damage flow | TBD | Not started |
| 37 | api/services/services/ | full CRUD | session | IsServicesStaff (any staff) | — | TBD | Not started |
| 38 | api/services/slots/ | full CRUD | session | IsServicesStaff | no booking/capacity enforcement | TBD | Not started |
| 39 | api/transport/trips/ | full CRUD | session | IsTransportStaff | — | TBD | Not started |
| 40 | api/transport/vehicles/ | full CRUD | session | IsTransportStaff | — | TBD | Not started |
| 41 | api/transport/drivers/ | full CRUD | session | IsTransportStaff | — | TBD | Not started |
| 42 | api/weather/weather-limits/ | full CRUD | session | IsWeatherStaff | — | TBD | Not started |
| 43 | api/weather/weather-warnings/ | full CRUD | session | IsWeatherStaff | manual CRUD, no MET fetch | TBD | Not started |
| 44 | api/reports/reports/ | full CRUD | session | IsReportsStaff | no computation logic | TBD | Not started |
| 45 | api/schema/ | GET | public | — | DRF OpenAPI | TBD | Not started |

## 3. Database schema inventory

**Tenancy model: DB-per-tenant.** Central DB (default) + one physical DB per tenant, cloned from template `tenant_template`. Isolation = active connection, not row filters. Laravel equivalent must implement runtime per-tenant DB switching per request (host → central lookup → connection override) — this is the highest-risk port.

### Central DB
| Table | Columns | Keys/Constraints | Notes |
|---|---|---|---|
| saas_tenant | name C(200), slug S(64) uq, db_name C(128) uq, is_active B d True, created_at, updated_at | pk id | registry |
| saas_domain | tenant_id FK→saas_tenant CASCADE, hostname C(255) uq, is_primary B d True, created_at | pk, fk | host→tenant mapping |
| saas_user | email E(254) uq, password C(128), is_active B d True, created_at | pk | not AUTH_USER_MODEL; custom props is_authenticated/is_anonymous |

### Tenant DBs (all)
| Table | Columns | Keys/Constraints | Notes |
|---|---|---|---|
| core_staff | email E(254) uq, password C(128), role C(32) choices d front_desk, is_active B, last_login DT null, created_at | pk | roles: owner/manager/front_desk/cashier/rental_desk/service_staff/transport_desk/lift_operator |
| core_staff_outlet_access | staff_id FK CASCADE, outlet_id FK CASCADE, created_at | unique (staff,outlet) | exists but NEVER checked in any permission |
| core_building | name C(200), building_type C(32) choices d other, altitude_m I null, lat/lng D(9,6) null, is_active B | pk | types: valley_station/main_hotel/chalets/mid_station/mountain_hut/summit_station/other |
| core_outlet | building_id FK PROTECT, name C(200), outlet_type C(32) choices d other, is_active B | pk | 16 outlet types |
| core_season | kind C(16) d closed winter/summer/closed, starts_on D, ends_on D, is_active B | pk | overlap validation in service layer (cross-kind) |
| core_tenant_settings | currency C(8) d EUR, tax_rates JSON d [], timezone C(64) d UTC, units C(16) d metric | pk | singleton per tenant |
| core_audit_event | actor_staff_id I null (loose pk, no FK), action C(16) [create/update/void/login], entity_type C(128), entity_id I, old_values JSON null, new_values JSON null, reason C(255) blank, request_id C(128) blank, ip INET null, created_at | idx (entity_type,entity_id), actor, created_at | append-only enforced in save()/delete() |
| hotel_room_type | name C(100), code C(32) uq, description C(255) blank, capacity +int d 1, is_active B | pk | |
| hotel_room | building_id FK PROTECT, room_type_id FK PROTECT, number C(20), floor +int null, is_active B, is_available B | unique (building,number) | doubles as HutBed stand-in |
| hotel_board_plan | name C(100), code C(16) uq, description blank, is_active B | pk | BoardPlanKind enum dead code |
| hotel_stay | room_id FK PROTECT, guest_name C(200), check_in D, check_out D, status C(16) d reserved, board_plan_id FK PROTECT, folio_id FK SET_NULL null | pk | nights property; no date-overlap validation |
| folio_folio | guest_name C(200), stay_reference C(100) blank, status C(16) d open, currency C(8) d EUR, total_amount D(12,2) d 0, voided_at null, voided_reason blank, voided_by null | pk | computed via service recompute |
| folio_charge | folio_id FK PROTECT, outlet_id FK PROTECT, description C(255), amount D(12,2), tax_amount D(12,2) d 0, tax_rate D(6,4) d 0, source C(50), void fields | pk | append-only (updates/deletes 405) |
| folio_payment | folio_id FK PROTECT, method C(16) cash/card, amount D(12,2), reference C(100) blank, void fields | pk | append-only |
| pos_product_category | name C(100), code C(32) uq, description, is_active | pk | |
| pos_product | outlet_id PROTECT, category_id PROTECT, name C(200), sku C(64) blank, price D(10,2), tax_rate D(6,4) d 0, is_active | pk | |
| pos_order | outlet_id PROTECT, staff_id PROTECT, folio_id SET_NULL null, status C(16) d open, total_amount D(12,2) d 0 | pk | |
| pos_order_line | order_id FK CASCADE, product_id PROTECT, quantity +int d 1, unit_price D(10,2), tax_amount D(10,2), void fields | pk | line_total = qty*price + tax |
| rental_rental | outlet_id, staff_id, folio_id SET_NULL null, item_name C(200), item_sku blank, checked_out_at auto, returned_at null, status [out/returned/damaged], damage_fee D(10,2), deposit D(10,2) | pk | |
| services_service | outlet_id PROTECT, name C(200), description, duration_minutes +int d 60, price D(10,2), is_active | pk | |
| services_slot | service_id CASCADE, starts_at DT, ends_at DT, capacity +int d 1, booked_count +int d 0, staff_id PROTECT, is_active | pk | available_capacity/is_full props |
| transport_vehicle | vehicle_type C(16) d shuttle, license_plate C(20) uq, capacity +int d 4, is_active | pk | |
| transport_driver | name C(200), phone C(50) blank, license_number C(50) blank, is_active | pk | no login |
| transport_trip | vehicle_id, driver_id (PROTECT), from/to building_id PROTECT, starts_at DT, ends_at null, status C(16) d scheduled [scheduled/in_progress/completed/cancelled], passenger_count, notes | pk | |
| weather_weatherlimit | limit_type C(16) [wind_speed/temperature/snowfall/visibility], threshold_value D(10,2), threshold_unit C(16) blank, is_active | pk | |
| weather_weatherwarning | weather_limit_id PROTECT, affected_area C(200), description C(255), current_value D(10,2), raised_at auto, resolved_at null, is_active | pk | |
| reports_report | report_type C(32) [folio_summary/occupancy/revenue/rental/transport], title C(200), parameters JSON d {}, result JSON d {}, generated_by FK PROTECT, published generated_at auto, is_active | pk | result never computed |

Decision conflict to resolve before schema port: master-plan money = integer minor units, but implemented schema = Decimal(2 dp). Laravel target: keep DECIMAL(12,2)/(10,2) to preserve data unless client approves conversion.

## 4. Authentication/permission matrix

**Auth flows (both session-based; no tokens; no SSO):**
- **Tenant staff auth** (`modules/core/auth.py::StaffSessionAuthentication`): session `staff_id` loaded from current tenant DB; session ALSO carries `staff_tenant_id` + `staff_tenant_slug` written at login; mismatch → AuthenticationFailed (cross-host replay guard). Inactive staff/tenant → fail. `authenticate_header` → 401 not 403.
- **Central SaaS auth** (`modules/saas/auth.py::SaasSessionAuthentication`): session `saas_user_id` → central SaasUser. mutually exclusive with staff sessions (class-name checks + is_central guard).
- Login: POST email+password → `check_password` (hashers used directly; Staff is NOT AUTH_USER_MODEL; `last_login` never updated). Password reset: owner/manager only via service.

**Matrix (extract exact behavior; do NOT invent):**
| Resource | Read | Create | Update | Delete | Special |
|---|---|---|---|---|---|
| SaaS tenants | SaasUser | SaasUser | SaasUser (PATCH only) | n/a (405) | activate/deactivate SaasUser |
| Tenant settings | owner | n/a | owner (PATCH) | n/a | — |
| Staff / outlet-access / buildings / outlets / seasons | owner | owner | owner | owner | password reset owner/manager |
| Hotel (board plans, room types, rooms, stays) | any staff | manager+ | manager+ | manager+ | check-in/out manager+ |
| Folio read (folios/charges/payments) | any staff | — | — | — | — |
| Charge/Payment create + void | — | manager+ | 405 append-only | 405 append-only | void needs reason; close manager+; zero-balance gate |
| POS (categories, products) | any staff | manager+ | manager+ | manager+ | — |
| POS (orders, order lines) | any staff | cashier-quartet (owner/manager/front_desk/cashier) | cashier-quartet | cashier-quartet | line add blocked on non-open order |
| Rental / Services / Transport / Weather / Reports | any staff | any staff | any staff | any staff | (no role restriction today) |

## 5. External integration list

| Integration | Status | Notes |
|---|---|---|
| MET Norway (api.met.no) | Decision only, NOT coded | docs mandate server-side cache + User-Agent + attribution |
| Maps (Leaflet/MapLibre) | Frontend concern | backend provides location data only |
| Email/SMS | None in backend | no send_mail/celery anywhere |
| Scheduled jobs (nightly posting) | None | no celery/crontab in code |

## 6. File-storage inventory

No media handling exists: no MEDIA_ROOT/MEDIA_URL, no FileField/ImageField, no upload endpoints. Static only. Laravel may launch without storage configuration but must plan disks if uploads enter scope.

## 7. Known issues and technical debt (port decisions)

| # | Issue | File | Impact on Laravel port |
|---|---|---|---|
| 1 | Money units: DECISIONS says integer minor units; code uses Decimal | docs/DECISIONS.md vs folio/pos models | Pick Decimal for port (preserve behavior) unless client approves |
| 2 | `voided_at = models.DateTimeField.auto_now` placeholder bug (dead code) | folio/models/charge.py, payment.py | Laravel void path = service logic (correct), skip dead code |
| 3 | AuditService exists, zero call sites | core/services/audit.py | Laravel: wire audit at Houston ports per master plan module work |
| 4 | StaffOutletAccess never checked | core models | Keep table + API, decide enforcement with client |
| 5 | Role checks string-compare 'owner','manager' | modules/*/permissions/*.py | Laravel Policies should centralize role logic |
| 6 | `can_reset_password` reused as manager proxy in folio close | folio services | Dedicated manager check in Laravel |
| 7 | Common exceptions defined, never raised | common/exceptions | Map to Laravel domain exceptions where useful |
| 8 | last_login never updated, no Django user integration | core/auth | Laravel: use native users or custom guard with last_login update |
| 9 | `apps/core/` duplicate legacy dir | backend/apps/core | Do not port |
| 10 | Missing vs docs: HutBed, RoomSeasonPrice, Reservation, nightly job, Booking, MET fetch, report computation, rental return flow | docs/modules/*.md | These are feature gaps; port ONLY what exists, flag rest to scope |

## 8. Migration risks and dependencies

| Risk | Severity | Mitigation |
|---|---|---|
| DB-per-tenant runtime connection switching has no Laravel native equivalent | Critical | Custom middleware: resolve host→tenant (central DB) → override pgsql connection/database before auth; port tests for isolation |
| Session binding (staff_tenant_id/slug) cross-host guard | High | Port exact guard logic in middleware/guard |
| API envelope + pagination contract | High | Custom Laravel Response macros + custom paginator preserving meta keys |
| Appended-only 405 on charges/payments | High | Laravel returns 405/409 preserving detail message |
| Deployment currently Django-specific (release dirs w/ symlink, `manage.py migrate`) | Medium | New deploy workflow PHP-FPM/artisan per master plan §17; keep Django release path until cutover |
| CI currently Python tooling | Medium | Replace per master plan §16 (no test suites in CI) |
| Test suite requires manually provisioned tenant DBs | Medium | Laravel necessity: seed/init per-tenant DBs documentation |
| Frontend proxies /api/ via nginx Host passthrough (tenant resolution depends on Host) | High | Laravel must respect Host-based tenant resolution; do not drop Host header |

## 9. Laravel endpoint mapping status

All 45 endpoints → Not started. Mapping will be filled row-by-row in work item(s) following Stage C (module vertical slices). This inventory is the source of truth for the port.

---

**Audit by:** OpenCode agent (deep code audit, file-verified)
**Date:** 2026-11-03
**Work item:** 68
