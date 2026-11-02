---
id: BUG-ENV-001
title: ensure_tenant_connection clones DB template without OPTIONS key
status: VERIFIED
type: ENV
severity: high
created: 2026-11-02
---

# BUG-ENV-001: Tenant DB template clone missing OPTIONS key

## 1. Summary
`tenancy.connections.ensure_tenant_connection()` deep-copies `TENANT_DB_TEMPLATE`
and replaces NAME only. The Django 5.2 PostgreSQL backend reads
`settings_dict["OPTIONS"]` when opening a connection (pool init) and crashes with
`KeyError: 'OPTIONS'` for any runtime-registered tenant alias.

## 2. Environment
Docker dev stack (`mtmtp_web`, Django 5.2.18, Postgres 16), main after f274914.
Found during BUG-TENANT-001 runtime smoke (tenant alias migrate).

## 3. Steps to Reproduce
1. Bring up docker stack (Postgres template has no `OPTIONS` key in `TENANT_DB_TEMPLATE`).
2. Provision a tenant (`provision_tenant`) and apply migrations on its alias.
3. First connection to `tenant_N` raises `KeyError: 'OPTIONS'`.

## 4. Expected
Cloned tenant config always contains `OPTIONS` (copied from the template or empty).

## 5. Actual
`KeyError: 'OPTIONS'` inside `django/db/backends/postgresql/base.py` on connect.

## 6. Evidence
`docker exec mtmtp_web ... migrate ...` traceback ends at
`self.settings_dict["OPTIONS"].get("pool")` (redacted runtime log).

## 7. Root Cause
`deepcopy(template)` copies whatever keys exist; the sqlite-default template in
`config/settings/base.py` only defines ENGINE/NAME/USER/PASSWORD/HOST/PORT.
Postgres backend requires `OPTIONS` since the pooling change.

## 8. Workaround
None (every tenant DB connection fails).

## 9. Suggested Fix
RESOLVED IN PROGRESS: `ensure_tenant_connection()` now merges Django's standard defaulted keys (OPTIONS, TIME_ZONE, CONN_MAX_AGE, CONN_HEALTH_CHECKS, AUTOCOMMIT, ATOMIC_REQUESTS, TEST) into late-registered aliases (root cause widened: handler defaults skipped for runtime aliases).
In `ensure_tenant_connection()`: after deepcopy, set
`cfg['OPTIONS'] = deepcopy(template.get('OPTIONS') or {})`. Also mirror into the
registered `connections.databases[alias]` entry.

## 10. Impact
All tenant DB connections on Postgres break (isolation layer unusable in runtime).
Central/default DB unaffected (defined in settings directly).

## 11. Related
- Work item: 17 (BUG-TENANT-001 fix verification)
- Other bugs: BUG-TENANT-001
