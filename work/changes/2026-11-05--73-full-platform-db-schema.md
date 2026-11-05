# Change Report: Full platform DB schema design (Modules A to I)

- **Date**: 2026-11-05
- **By**: Agent
- **Change ID**: 73

## 1. Summary

Added a single schema contract for the whole platform: central SaaS database plus the tenant
database tables for Modules A to I. It records what is already implemented and proposes the
remaining tables with columns, keys, indexes and invariants.

## 2. Before

- No single database schema document existed.
- Table design was spread across module migration files, `work/phases/Module_*_Deep_Analysis.md`
  and `docs/DECISIONS.md`, so later modules could drift from Module A to C decisions.
- `docs/GLOSSARY.md` still says every business table carries `tenant_id`, which contradicts the
  accepted DB-per-tenant decision.
- Module D to I had entity names but no column level design.

## 3. After

- New `docs/DATABASE_SCHEMA.md`:
  - conventions: naming, money, tax basis points, dates, timestamps, enum storage, FK actions
  - central database: `saas_tenant`, `saas_domain`, `saas_user` (implemented) and
    `saas_audit_event` (proposed)
  - tenant Module A Core: 7 implemented tables plus `updated_at` proposals
  - tenant Module B Hotel: 10 implemented tables, chalets as rooms, price decisions restated
  - tenant Module C Folio: 4 implemented tables plus proposed `folio_invoice` and `tax_breakdown`
  - tenant Module D POS: 10 proposed tables including `pos_board_claim`
  - tenant Module E Rental: 5 proposed tables with the open rental partial unique index
  - tenant Module F Services: 9 proposed tables
  - tenant Module G Transport: 10 proposed tables
  - tenant Module H Weather: 10 proposed tables
  - tenant Module I Reports: optional `report_saved_view`
  - cross module money flow, global invariants, index plan, migration order
  - client open questions with current defaults
  - menu note: menu is code config, not a database table, matching the InventorySystem pattern
  - implementation status table
- `docs/README.md` now links the schema doc.

## 4. Reason

User asked to design the database schema for the full platform (Modules A to I) before continuing
the menu and UI work. One contract avoids incompatible table choices when Modules D to I land.

## 5. Files Affected

- `docs/DATABASE_SCHEMA.md` (new)
- `docs/README.md` (index row)
- `work/changes/2026-11-05--73-full-platform-db-schema.md` (this file)

## 6. Verification / Status

- [x] Existing migration files inspected for Core, Hotel, Folio, Saas
- [x] Phase docs and deep analysis data model sections reconciled
- [x] Table prefixes and FK actions consistent across modules
- [ ] No migrations created or run (design only)
- [ ] Client answers pending for the 7 open questions in the doc
