# Models

- Use CLAUDE.md domain names: Tenant, Building, Outlet, Folio, Charge, Payment, etc.
- Tenant-owned tables include `tenant_id` (indexed).
- Money rows are immutable after create except void metadata pattern (separate void record or void flags with reason/user/timestamp — never silent overwrite of amount).
- Prefer `created_at` / `updated_at`; audit charge, payment, void events.
- Hut dormitories sell beds, not whole rooms — model capacity accordingly.
- Avoid generic `json` blobs for core money fields.
