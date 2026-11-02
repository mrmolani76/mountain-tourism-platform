# Audit module (domain trail) + where business logic lives

**Not** the same as `work/audits/` (those are project/process audit reports).  
This doc = **data change audit trail** for the SaaS product — like Complere’s `owen-it/laravel-auditing` + domain audit events.

**Status:** Design only. No package install. No setup. Implementation later (Module A stub → folio money path).

---

## 1. Laravel / EQMS reference

| Complere (Laravel) | Mountain (Django + DRF) |
|--------------------|-------------------------|
| `owen-it/laravel-auditing` | Custom audit models + writers in `services/` (default). Third-party (`django-auditlog`, etc.) only with **explicit package approval** (Rule 4). |
| `Auditable` on models | Mixin / signals / explicit `AuditService.record(...)` from services |
| Audits table per tenant | Rows in **tenant DB** (DB-per-tenant isolation) |
| Who / what / when / old / new | Same fields (see §3) |
| Money / regulated records always audited | **Must** audit Charge, Payment, Void (CLAUDE Rule 5 / 12) |

SSO / e-sign from EQMS are **out of scope** for MVP.

---

## 2. Where it lives in our layout

Suggested home (Complere-style module):

```text
backend/modules/core/          # foundation for MVP
  models/audit.py              # AuditEvent (or AuditLog)
  services/audit.py            # AuditService.record(...)
  serializers/audit.py         # read APIs later
  views/                       # list/filter audit for owner/manager later
```

Optional later split: `backend/modules/audit/` if the trail grows large.  
Until then keep writers under **`modules.core.services.audit`** so folio/hotel/pos call one place.

**Naming clash:**

| Path | Meaning |
|------|---------|
| `work/audits/*.md` | Human/agent project audits |
| `modules.core` audit models | Product audit trail (this doc) |

---

## 3. Intended `AuditEvent` shape (tenant DB)

| Field | Purpose |
|-------|---------|
| `id` | PK |
| `actor_staff_id` | Who (nullable for system jobs) |
| `action` | `create` / `update` / `void` / `login` / … |
| `entity_type` | e.g. `folio.Charge`, `core.Staff` |
| `entity_id` | Target row id |
| `old_values` | JSON (nullable) |
| `new_values` | JSON (nullable) |
| `reason` | Required for voids (`weather`, manager reason, …) |
| `request_id` / `ip` | Optional ops metadata |
| `created_at` | Timestamp (append-only; never update/delete audit rows) |

Rules:

- Audit rows are **append-only** (like Charge/Payment).
- Prefer writing audits from **services**, not from fat views.
- Money path: every charge create, payment create, and void **must** write an audit (or be the void record itself with full reason/user/time — still keep a unified trail).

---

## 4. How to write audits (pattern)

```text
DRF view / ViewSet
    → modules.<x>.services.*     # business rules
        → models save / void
        → AuditService.record(...)   # side effect
```

Do **not** put audit + money rules only in serializers or only in the client.

Pseudo:

```python
# modules/folio/services/charges.py  (future)
def post_charge(...):
    charge = Charge.objects.create(...)
    AuditService.record(
        actor=staff,
        action='create',
        entity_type='folio.Charge',
        entity_id=charge.pk,
        new_values={...},
    )
    return charge
```

---

## 5. Where business logic is written

**Single rule:** thin DRF layer → **`modules/<name>/services/`** holds business logic.

| Layer | Path | Allowed |
|-------|------|---------|
| HTTP / API | `modules/<name>/views/` | Auth, status codes, call service, return serializer data |
| DTO / validation | `modules/<name>/serializers/` | Shape + field validation (Complere `App/Data`) |
| **Business rules** | **`modules/<name>/services/`** | Folio post, void, capacity, rental conflict, provision registry, password reset rules, audit writes |
| Persistence | `modules/<name>/models/` | Fields, constraints, simple helpers — not multi-step workflows |
| Permissions | `modules/<name>/permissions/` | Who may call the endpoint |
| Platform | `tenancy/` | Host → DB only; no domain money rules |
| Central SaaS | `modules/saas/services/` | Register/activate tenant registry (not physical createdb until setup) |

### Examples already in repo

| Logic | Location |
|-------|----------|
| Staff login / password reset rules | `modules/core/services/auth.py` |
| Tenant register / activate (registry) | `modules/saas/services/tenant.py` |
| Future charge / void | `modules/folio/services/` |
| Future rental double-book check | `modules/rental/services/` |

### Anti-patterns

- Fat `views/` with pricing, capacity, void rules
- Serializers that create Charge + Payment + side effects without a service
- Copying `tenant_id` filters ad hoc (isolation is the **tenant DB connection**)
- Cross-module concrete model imports for workflows — prefer service API / events later

---

## 6. MVP priority for audit

1. Stub `AuditService` + model when Module A settings land (or with first folio work).
2. Wire **Charge / Payment / Void** first (non-negotiable).
3. Then staff password reset, product price changes, weather cancel voids.
4. Read API for owner/manager audit list — later.

---

## 7. Packages

Declared in [`../backend/DEPENDENCIES.md`](../backend/DEPENDENCIES.md): no audit package yet.  
If someone wants `django-auditlog` / similar → **ask first** (Rule 4). Default = custom service + model.

---

## 8. Related docs

- [`ARCHITECTURE.md`](ARCHITECTURE.md)
- [`backend/modules/README.md`](../backend/modules/README.md)
- [`modules/C-guest-folio-and-checkout.md`](modules/C-guest-folio-and-checkout.md) (money)
- `CLAUDE.md` Rules 5, 9, 12
