# Change Report: Split Pos migrations one table per file

- **Date**: 2026-11-13
- **By**: Agent
- **Change ID**: 78

## 1. Summary

Replaced the combined `create_pos_tables` migration with ten single-table
tenant migrations (Hotel module pattern).

## 2. Before

- `Modules/Pos/Database/migrations/tenant/2026_11_13_000001_create_pos_tables.php`
  created all `pos_*` tables in one file.

## 3. After

- Removed the combined migration (not yet shipped / migrated).
- Added:
  - `000001_create_pos_category_table`
  - `000002_create_pos_product_table`
  - `000003_create_pos_outlet_product_table`
  - `000004_create_pos_product_season_table`
  - `000005_create_pos_cashier_shift_table`
  - `000006_create_pos_order_table`
  - `000007_create_pos_order_item_table`
  - `000008_create_pos_board_claim_table`
  - `000009_create_pos_packed_lunch_table`
  - `000010_create_pos_packed_lunch_item_table`

## 4. Reason

User: one migration file must not hold all tables; keep them separate.

## 5. Files Affected

- Deleted: `.../2026_11_13_000001_create_pos_tables.php`
- Added: ten `2026_11_13_00000N_create_pos_*_table.php` files under Pos tenant migrations

## 6. Verification / Status

- [x] One table per migration file
- [ ] Migrate not run (code-only)
