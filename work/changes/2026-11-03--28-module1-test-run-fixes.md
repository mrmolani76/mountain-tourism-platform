# Change Report: Module 1 verification — first full test run, fixes (item 28)

- **Date**: 2026-11-03
- **By**: OpenCode agent
- **Change ID**: 28

## 1. Summary
Ran the backend test suite for the first time since the DB-per-tenant rewrite. Result: **38 passed**. Getting there required: creating the physical scratch tenant DBs (`lab_test`, `other_test`) and migrating them; fixing three app bugs (pagination crash, season cross-kind overlap, login 401 semantics) and correcting test infrastructure (conftest, hosts, envelope reads, tenant context).

## 2. Before
- `pytest` never run post-rewrite (SUMMARY §3). First run: **0 passed, 38 failed + 4 errors**
- Root causes found (in order of discovery):
  1. Missing physical tenant DBs `lab_test` / `other_test` (tests connect to them explicitly) — created + migrated
  2. Test hosts (`lab.test`, `other.test`) not in `ALLOWED_HOSTS` — Django raised DisallowedHost → tests set their own `ALLOWED_HOSTS` override
  3. `CoreConfigTests.databases = {'default'}` missing tenant aliases; missing/incorrect alias setup — replaced with explicit `tenant_1`/`tenant_2` pre-registered in conftest + explicit `id=1/2` Tenants (Postgres sequences do not roll back, so pks were not deterministic)
  4. conftest pointed `tenant_1`/`tenant_2` at the **central** DB (UndefinedTable) and lacked Django backend defaults (`ATOMIC_REQUESTS` → KeyError) — rewritten; pre-registers correct scratch DBs with full backend defaults
  5. App bugs: pagination `self.paginator` (500 on all list endpoints, BUG-APP-002), season overlap same-kind-only (BUG-APP-003), staff login bad credentials 403→401 (BUG-APP-004)
  6. Test bugs: list responses read old shape (now envelope `['data']`), direct-ORM calls without tenant context (season create, `force_login`) wrapped in `context.set_current_tenant`, `unknown.test` host added to isolation test's ALLOWED_HOSTS
- `databases = '__all__'` tried and **rejected**: Django snapshots allowed aliases at class setup; dynamically created aliases are blocked and teardown crashes — replaced with explicit aliases.

## 3. After
- `pytest -q` → **38 passed** (full suite: core/saas/auth/isolation)
- Isolation acceptance test passes (`test_tenant_db_isolation.py`): staff in tenant A not visible in tenant B; front desk denied; unknown host → 404; tenant host health → 200
- App fixes: `backend/common/pagination/__init__.py`, `backend/modules/core/services/config.py`, `backend/modules/core/auth.py`, `backend/modules/core/views/api.py`
- Test/infra fixes: `tests/conftest.py`, `tests/api/test_auth.py`, `tests/api/test_core_config.py`, `tests/api/test_saas_registry.py` (unchanged; passes), `tests/api/test_core_config.py`, `tests/integration/test_tenant_db_isolation.py`
- Physical changes on this machine (outside repo): DBs `lab_test`, `other_test` created + fully migrated (scratch, data rolled back per test)

## 4. Reason
Module 1 (Core & Super Admin) spec: verify, don't rebuild. Client acceptance hinges on automated tenant-isolation proof. Suite was never executed after the rewrite.

## 5. Files Affected
- `backend/common/pagination/__init__.py` (BUG-APP-002)
- `backend/modules/core/services/config.py` (BUG-APP-003)
- `backend/modules/core/auth.py` (BUG-APP-004)
- `backend/modules/core/views/api.py` (BUG-APP-004)
- `backend/tests/conftest.py`
- `backend/tests/api/test_auth.py`, `backend/tests/api/test_core_config.py`, `backend/tests/integration/test_tenant_db_isolation.py`
- `work/bugs/BUG-APP-002..004-*.md`, `work/bugs/INDEX.md`
- `work/inprogress/28-module1-verification-tests.md`

## 6. Verification / Status
- [x] `pytest` → 38 passed (full suite)
- [x] Triage documented: every failure classified (infra vs app vs test) before fixing; no assertions weakened — test edits only read the envelope, admit both hosts/DBs legitimately in scope, and run DB writes inside the tenant context
- [x] Bugs recorded: BUG-APP-002 (high), BUG-APP-003 (medium), BUG-APP-004 (low) — all VERIFIED
- Notes: scratch DBs `a_test`/`b_test` created during diagnosis and dropped (unused). `lab_test`/`other_test` are required for the suite; CI must provision them the same way (sqlite path was the original design intent — conftest no longer assumes it).

## 7. Usage
```bash
docker compose -f docker-compose.dev.yml exec web python -m pytest -q
```
