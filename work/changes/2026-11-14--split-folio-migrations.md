# Change Report: Split Folio migrations one table per file

- **Date**: 2026-11-14
- **By**: Agent
- **Change ID**: folio-migrations

## 1. Summary

Replaced combined `create_folio_tables` with four single-table tenant migrations
(Pos/Hotel pattern).

## 2. Before

- `Modules/Folio/Database/migrations/tenant/2026_11_04_000001_create_folio_tables.php`
  created `folio_folio`, `folio_charge`, `folio_payment`, `folio_void` in one file.

## 3. After

- Removed the combined migration (not treated as immutable ship for this split;
  same approach as item 78 Pos).
- Added:
  - `000001_create_folio_folio_table`
  - `000002_create_folio_charge_table`
  - `000003_create_folio_payment_table`
  - `000004_create_folio_void_table`

## 4. Reason

User: ek migration mein saari tables mat likho; alag rakho.

## 5. Files Affected

- Deleted: `.../2026_11_04_000001_create_folio_tables.php`
- Added: four `2026_11_04_00000N_create_folio_*_table.php` files

## 6. Verification / Status

- [x] One table per file
- [ ] migrate deferred (code-only)
