---
name: django-best-practices
description: >-
  Django and DRF best practices for the Mountain Tourism backend. Use when
  writing or reviewing Python under backend/, models, serializers, viewsets,
  services, permissions, migrations, or pytest API tests.
---

# Django Best Practices

Source of truth: `CLAUDE.md`. Detailed notes in `rules/`.

## Defaults

- Business logic in services, not fat views
- DRF serializers/viewsets at the API boundary
- Every tenant-owned model has `tenant_id` (or FK to Tenant) and queries are scoped
- Permissions checked on the server (role + outlet access)
- Migrations: append-only; never edit shipped files
- Charge/Payment: create + void only; no update/delete of money rows
- Prefer explicit domain model names from CLAUDE.md vocabulary
- Tests for tenant isolation and money flows when touching those areas

## Load rule files as needed

- `rules/architecture.md`
- `rules/models.md`
- `rules/api.md`
- `rules/security.md`
- `rules/testing.md`
