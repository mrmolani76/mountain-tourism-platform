# Database schema (full platform, Modules A to I)

- **Status**: design draft for approval (2026-11-05)
- **Database**: PostgreSQL
- **Tenancy**: database per tenant
- **Backend**: Laravel modules (`backend-laravel/Modules/*`)
- **Money**: integer minor units (cents)

This file is the schema contract for all modules. Any table change must update this file in the
same change. Implemented tables are marked **implemented** with their migration path.

## 1. Conventions

| Topic | Rule |
|---|---|
| Naming | `snake_case`, module prefix: `core_`, `hotel_`, `folio_`, `pos_`, `rental_`, `service_`, `transport_`, `weather_`, `report_`, `saas_` |
| Primary key | `id bigint` auto increment |
| Foreign key | `<name>_id bigint`, real FK constraint |
| Money | `bigint` cents, never float or decimal |
| Tax | `tax_rate_bp` smallint basis points (1250 = 12.50%) |
| Business date | `date` (check-in, posted_on, due_on) |
| Event time | `timestamp` stored in UTC, rendered in tenant timezone |
| Enum | `varchar(16 or 32)` plus a PHP constant list. No database enum type |
| Tenancy | No `tenant_id` column. The active tenant connection owns the rows |
| Soft delete | Not used for money or history. Inventory uses `is_active`. Hard delete only when no history depends on the row |
| Audit | `core_audit_event` for money and configuration changes |
| JSON | `json` column (Laravel `json`), used for snapshots and payloads |

Foreign key actions:

| Case | Action |
|---|---|
| Master row used by money or history | `restrictOnDelete` |
| Pure child rows (order item, booking participant) | `cascadeOnDelete` |
| Optional link where history must survive | `nullOnDelete` |

## 2. Databases

```text
Central database (landlord)
  saas_tenant, saas_domain, saas_user, saas_audit_event

Tenant database (one per complex, created by the provisioner)
  core_*        Module A foundation
  hotel_*       Module B hotel, chalets, huts
  folio_*       Module C guest folio and checkout
  pos_*         Module D restaurants, huts, shop
  rental_*      Module E equipment rental
  service_*     Module F hotel services and activities
  transport_*   Module G transport and lifts
  weather_*     Module H weather
  report_*      Module I saved report views (optional)
```

Framework tables (`users`, `cache`, `jobs`, `personal_access_tokens`) exist only in the central
database and are not business data.

## 3. Central database

### 3.1 `saas_tenant` (implemented)

`Modules/Saas/Database/migrations/2026_11_03_000001_create_saas_registry_tables.php`

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| name | varchar(200) | required |
| slug | varchar(64) | required, unique |
| db_name | varchar(128) | required, unique, tenant database name |
| is_active | boolean | default true |
| created_at | timestamp | |
| updated_at | timestamp | |

### 3.2 `saas_domain` (implemented)

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| tenant_id | bigint | FK `saas_tenant`, cascade delete |
| hostname | varchar(255) | required, unique, for example `lab.local.mtmtp.local` |
| is_primary | boolean | default true |
| created_at | timestamp | |
| updated_at | timestamp | |

### 3.3 `saas_user` (implemented)

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| email | varchar(254) | required, unique |
| password | varchar(128) | hash |
| is_active | boolean | default true |
| created_at | timestamp | |

### 3.4 `saas_audit_event` (proposed)

Super admin actions: tenant created, activated, deactivated, owner reset.

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| actor_saas_user_id | bigint | nullable FK `saas_user`, null on delete |
| action | varchar(24) | `create`, `update`, `activate`, `deactivate` |
| tenant_id | bigint | nullable FK `saas_tenant`, null on delete |
| old_values | json | nullable |
| new_values | json | nullable |
| ip | varchar(45) | nullable |
| created_at | timestamp | index |

Index: `(tenant_id, created_at)`.

## 4. Module A: Core (tenant)

All tables below are implemented in:

- `Modules/Core/Database/migrations/tenant/2026_11_03_000001_create_core_staff_and_building_tables.php`
- `Modules/Core/Database/migrations/tenant/2026_11_03_000002_create_core_config_tables.php`

### 4.1 `core_staff` (implemented)

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| email | varchar(254) | required, unique |
| password | varchar(128) | hash |
| role | varchar(32) | `owner`, `manager`, `front_desk`, `cashier`, `rental_desk`, `service_staff`, `transport_desk`, `lift_operator` |
| is_active | boolean | default true |
| last_login | timestamp | nullable |
| created_at | timestamp | |

Proposed: add `updated_at timestamp`. Reason: password reset and role changes need a change time.

### 4.2 `core_building` (implemented)

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| name | varchar(200) | required |
| building_type | varchar(32) | `hotel`, `chalet`, `hut`, `restaurant`, `shop`, `rental`, `spa`, `sports`, `transport`, `lift_station`, `other` |
| altitude_m | integer | nullable |
| latitude | numeric(9,6) | nullable |
| longitude | numeric(9,6) | nullable |
| is_active | boolean | default true |
| created_at | timestamp | |
| updated_at | timestamp | |

Every building is also a weather point candidate for Module H.

### 4.3 `core_outlet` (implemented)

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| building_id | bigint | nullable FK `core_building`, null on delete |
| name | varchar(200) | required |
| outlet_type | varchar(32) | `restaurant`, `hut_kitchen`, `shop`, `rental`, `spa`, `sports`, `transport`, `lifts`, `other` |
| is_active | boolean | default true |
| created_at | timestamp | |

Proposed: add `updated_at timestamp`.

### 4.4 `core_season` (implemented)

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| kind | varchar(16) | `winter`, `summer`, `closed` |
| starts_on | date | required |
| ends_on | date | required, `ends_on >= starts_on` |
| is_active | boolean | default true |
| created_at | timestamp | |
| updated_at | timestamp | |

Rule: seasons may not overlap across kinds. Enforced in the service layer (decision 2026-11-03,
Q14). Optional PostgreSQL hardening: exclusion constraint on `daterange(starts_on, ends_on, '[]')`.

### 4.5 `core_tenant_settings` (implemented)

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| currency | varchar(8) | default `EUR` |
| tax_rates | json | list of `{name, rate_bp}` |
| timezone | varchar(64) | default `UTC` |
| units | varchar(16) | `metric` or `imperial` |
| created_at | timestamp | |
| updated_at | timestamp | |

Single row per tenant database. Created by the provisioner.

### 4.6 `core_staff_outlet_access` (implemented)

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| staff_id | bigint | FK `core_staff`, cascade delete |
| outlet_id | bigint | FK `core_outlet`, cascade delete |
| created_at | timestamp | |

Unique: `(staff_id, outlet_id)`. Owner has implicit access to all outlets.

### 4.7 `core_audit_event` (implemented)

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| actor_staff_id | bigint | nullable |
| action | varchar(16) | `create`, `update`, `void`, `delete`, `login` |
| entity_type | varchar(128) | for example `folio_charge` |
| entity_id | bigint | |
| old_values | json | nullable |
| new_values | json | nullable |
| reason | varchar(255) | default empty |
| request_id | varchar(128) | default empty |
| ip | varchar(45) | nullable |
| created_at | timestamp | |

Indexes: `(entity_type, entity_id)`, `(actor_staff_id)`, `(created_at)`.
Append-only.

## 5. Module B: Hotel, chalets and huts (tenant)

Implemented in
`Modules/Hotel/Database/migrations/tenant/2026_11_04_000001_create_hotel_tables.php`.

Design choices already recorded in `docs/DECISIONS.md` (2026-11-04):

- Room pricing is per room type per season.
- Hut beds inherit the dormitory `room_type_id` price.
- Board price is per person per night, `board_price_per_person_cents x (adults + children)`.
- Chalets are `hotel_room` rows with `accommodation_type = 'chalet'`, not a separate table.
- Reservation has one board plan and 1 to n allocation rows.

### 5.1 `hotel_room_type` (implemented)

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| name | varchar(100) | required |
| description | text | nullable |
| max_adults | smallint | default 2 |
| max_children | smallint | default 0 |
| is_active | boolean | default true |
| created_at | timestamp | |
| updated_at | timestamp | |

### 5.2 `hotel_room` (implemented)

Rooms and chalets.

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| building_id | bigint | FK `core_building`, restrict delete |
| room_type_id | bigint | FK `hotel_room_type`, restrict delete |
| room_number | varchar(32) | required |
| name | varchar(120) | nullable |
| accommodation_type | varchar(16) | `room` or `chalet`, default `room` |
| is_active | boolean | default true |
| created_at | timestamp | |
| updated_at | timestamp | |

Unique: `(building_id, room_number)`. Index: `room_type_id`.

### 5.3 `hotel_hut_dormitory` (implemented)

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| building_id | bigint | FK `core_building`, restrict delete |
| room_type_id | bigint | nullable FK `hotel_room_type`, null on delete |
| name | varchar(120) | required |
| is_active | boolean | default true |
| created_at | timestamp | |
| updated_at | timestamp | |

Index: `building_id`.

### 5.4 `hotel_hut_bed` (implemented)

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| hut_dormitory_id | bigint | FK `hotel_hut_dormitory`, cascade delete |
| bed_number | varchar(32) | required |
| is_active | boolean | default true |
| created_at | timestamp | |
| updated_at | timestamp | |

Unique: `(hut_dormitory_id, bed_number)`.

### 5.5 `hotel_room_season_price` (implemented)

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| room_type_id | bigint | FK `hotel_room_type`, cascade delete |
| season_id | bigint | FK `core_season`, cascade delete |
| amount_cents | bigint | required, per night |
| created_at | timestamp | |
| updated_at | timestamp | |

Unique: `(room_type_id, season_id)`.

### 5.6 `hotel_board_plan` (implemented)

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| code | varchar(24) | unique: `room_only`, `bed_breakfast`, `half_board`, `full_board` |
| name | varchar(100) | required |
| price_per_person_cents | bigint | default 0, per night |
| inclusions | json | list of `breakfast`, `lunch`, `dinner` |
| is_active | boolean | default true |
| created_at | timestamp | |
| updated_at | timestamp | |

### 5.7 `hotel_reservation` (implemented)

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| reference | varchar(32) | unique |
| guest_name | varchar(160) | required |
| guest_email | varchar(160) | nullable |
| guest_phone | varchar(40) | nullable |
| check_in_on | date | required |
| check_out_on | date | required, after check in |
| adults | smallint | default 1 |
| children | smallint | default 0 |
| board_plan_id | bigint | nullable FK `hotel_board_plan`, null on delete |
| deposit_cents | bigint | default 0, intended deposit |
| arrival_mode | varchar(16) | nullable: `car`, `train`, `plane`, `bus` |
| arrival_time | varchar(8) | nullable `H:i` |
| arrival_station | varchar(160) | nullable |
| status | varchar(16) | `pending`, `confirmed`, `checked_in`, `checked_out`, `cancelled`, `no_show` |
| notes | text | nullable |
| created_by | bigint | nullable staff id |
| created_at | timestamp | |
| updated_at | timestamp | |

Indexes: `(check_in_on, check_out_on)`, `status`.

`deposit_cents` is the agreed deposit, not a money row. Actual money is `folio_payment`.

### 5.8 `hotel_reservation_accommodation` (implemented)

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| reservation_id | bigint | FK `hotel_reservation`, cascade delete |
| accommodation_type | varchar(16) | `room` or `bed` |
| room_id | bigint | nullable FK `hotel_room`, restrict delete |
| hut_bed_id | bigint | nullable FK `hotel_hut_bed`, restrict delete |
| nightly_price_cents | bigint | default 0, snapshot at booking |
| board_price_per_person_cents | bigint | default 0, snapshot at booking |
| created_at | timestamp | |
| updated_at | timestamp | |

Rule: exactly one of `room_id` and `hut_bed_id` is set, matching `accommodation_type`.
Indexes: `reservation_id`, `room_id`, `hut_bed_id`.

### 5.9 `hotel_stay` (implemented)

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| reservation_id | bigint | nullable FK `hotel_reservation`, null on delete |
| folio_id | bigint | nullable FK `folio_folio`, null on delete |
| room_id | bigint | nullable FK `hotel_room`, null on delete |
| hut_bed_id | bigint | nullable FK `hotel_hut_bed`, null on delete |
| checked_in_at | timestamp | required |
| checked_out_at | timestamp | nullable |
| status | varchar(16) | `in_house`, `checked_out` |
| created_at | timestamp | |
| updated_at | timestamp | |

Indexes: `status`, `reservation_id`.

### 5.10 `hotel_nightly_posting` (implemented)

Idempotency log for the nightly job.

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| stay_id | bigint | FK `hotel_stay`, cascade delete |
| posted_on | date | required |
| charges_count | smallint | default 0 |
| room_charge_cents | bigint | default 0 |
| board_charge_cents | bigint | default 0 |
| status | varchar(16) | `posted`, `skipped`, `failed` |
| message | varchar(255) | nullable |
| created_at | timestamp | |

Unique: `(stay_id, posted_on)`.

### 5.11 Proposed additions for Module B

None required for MVP. Two client questions stay open:

1. Child board rate. Current default: full rate for children until the client answers.
2. Seasonal board price. Current default: one board price per person. Add
   `hotel_board_plan_season_price` only if the client asks for seasonal board pricing.

## 6. Module C: Guest folio and checkout (tenant)

Implemented in `Modules/Folio/Database/migrations/tenant/2026_11_04_000001_create_folio_tables.php`
(Module 2 slice). Money rows are append-only: no `updated_at`, void only.

### 6.1 `folio_folio` (implemented)

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| reference | varchar(64) | nullable, unique per tenant recommended |
| status | varchar(16) | `open`, `closed`, `void` |
| currency | varchar(8) | default `EUR` |
| opened_at | timestamp | required |
| closed_at | timestamp | nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

Indexes: `status`, `reference`.

### 6.2 `folio_charge` (implemented)

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| folio_id | bigint | FK `folio_folio`, cascade delete |
| outlet_id | bigint | nullable FK `core_outlet`, null on delete |
| posted_on | date | required |
| description | varchar(255) | required |
| amount_cents | bigint | required, non negative |
| tax_cents | bigint | default 0 |
| source_type | varchar(32) | `room`, `board`, `pos`, `rental`, `service`, `transport`, `other` |
| source_id | varchar(64) | nullable, source record id |
| stay_id | bigint | nullable |
| is_voided | boolean | default false |
| created_by | bigint | nullable staff id |
| created_at | timestamp | |

Indexes: `(folio_id, is_voided)`, `(source_type, source_id)`, `posted_on`.
Append-only: update allowed only for `is_voided`. No delete.

### 6.3 `folio_payment` (implemented)

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| folio_id | bigint | FK `folio_folio`, cascade delete |
| method | varchar(16) | `cash`, `card` |
| amount_cents | bigint | required |
| reference | varchar(128) | nullable staff reference |
| is_voided | boolean | default false |
| created_by | bigint | nullable staff id |
| created_at | timestamp | |

Index: `(folio_id, is_voided)`. Append-only: update allowed only for `is_voided`. No delete.

### 6.4 `folio_void` (implemented)

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| folio_id | bigint | FK `folio_folio`, cascade delete |
| charge_id | bigint | nullable, charge being voided |
| payment_id | bigint | nullable, payment being voided |
| reason | varchar(255) | required |
| reason_code | varchar(32) | default `other`, for example `weather`, `mistake` |
| voided_by | bigint | nullable staff id |
| created_at | timestamp | |

Indexes: `folio_id`, `charge_id`, `payment_id`. Append-only.

### 6.5 `folio_invoice` (proposed, Module 3)

Printable invoice snapshot so later price changes cannot rewrite history.

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| folio_id | bigint | FK `folio_folio`, restrict delete |
| reference | varchar(64) | required, unique |
| issued_at | timestamp | required |
| currency | varchar(8) | required |
| subtotal_cents | bigint | required |
| tax_cents | bigint | required |
| total_cents | bigint | required |
| payload | json | guest, stay, lines, tax breakdown |
| created_by | bigint | nullable staff id |
| created_at | timestamp | |

### 6.6 Proposed addition to `folio_charge`

`tax_breakdown json` nullable, list of `{name, rate_bp, amount_cents}`, so multiple tax rates can be
reported. Keep `tax_cents` as the total for simple queries.

## 7. Module D: Restaurants, huts and shop (tenant, proposed)

Source of truth for sales money stays Module C. Every POS sale posts a `folio_charge` and payments
go to `folio_payment`. Walk-in sales use a one-off folio so reports stay single-sourced.

### 7.1 `pos_category`

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| name | varchar(100) | required |
| sort_order | smallint | default 0 |
| is_active | boolean | default true |
| created_at / updated_at | timestamp | |

### 7.2 `pos_product`

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| category_id | bigint | nullable FK `pos_category`, null on delete |
| code | varchar(32) | nullable, unique |
| name | varchar(120) | required |
| description | text | nullable |
| price_cents | bigint | required |
| tax_rate_bp | smallint | default 0 |
| is_active | boolean | default true |
| created_at / updated_at | timestamp | |

Index: `(category_id, is_active)`.

### 7.3 `pos_outlet_product`

Product availability per outlet.

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| outlet_id | bigint | FK `core_outlet`, cascade delete |
| product_id | bigint | FK `pos_product`, cascade delete |
| is_available | boolean | default true |
| sort_order | smallint | default 0 |
| created_at | timestamp | |

Unique: `(outlet_id, product_id)`.

### 7.4 `pos_product_season`

Seasonal visibility and optional price override.

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| product_id | bigint | FK `pos_product`, cascade delete |
| season_id | bigint | FK `core_season`, cascade delete |
| is_available | boolean | default true |
| price_cents | bigint | nullable, override |
| created_at | timestamp | |

Unique: `(product_id, season_id)`.

### 7.5 `pos_order`

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| outlet_id | bigint | FK `core_outlet`, restrict delete |
| shift_id | bigint | nullable FK `pos_cashier_shift`, null on delete |
| stay_id | bigint | nullable, guest context |
| folio_id | bigint | nullable FK `folio_folio`, restrict delete |
| table_label | varchar(16) | nullable |
| status | varchar(16) | `open`, `sent`, `billed`, `paid`, `void` |
| subtotal_cents | bigint | default 0 |
| tax_cents | bigint | default 0 |
| total_cents | bigint | default 0 |
| charge_id | bigint | nullable FK `folio_charge`, null on delete |
| payment_method | varchar(16) | nullable `cash`, `card`, `room` |
| opened_by | bigint | nullable staff id |
| opened_at | timestamp | required |
| closed_at | timestamp | nullable |
| created_at / updated_at | timestamp | |

Indexes: `(outlet_id, status)`, `opened_at`, `stay_id`.

### 7.6 `pos_order_item`

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| order_id | bigint | FK `pos_order`, cascade delete |
| product_id | bigint | nullable FK `pos_product`, null on delete |
| description | varchar(120) | snapshot of product name |
| quantity | smallint | required, positive |
| unit_price_cents | bigint | snapshot |
| tax_rate_bp | smallint | snapshot |
| tax_cents | bigint | computed |
| line_total_cents | bigint | `quantity x unit_price_cents` |
| note | varchar(255) | nullable |
| is_board_included | boolean | default false, no charge for included meal |
| created_at | timestamp | |

Index: `order_id`.

### 7.7 `pos_board_claim`

Prevents claiming the same board meal twice for one stay and date.

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| stay_id | bigint | FK `hotel_stay`, cascade delete |
| order_id | bigint | nullable FK `pos_order`, null on delete |
| board_plan_id | bigint | FK `hotel_board_plan`, restrict delete |
| service_date | date | required |
| meal | varchar(16) | `breakfast`, `lunch`, `dinner` |
| status | varchar(16) | `claimed`, `void` |
| claimed_by | bigint | nullable staff id |
| created_at | timestamp | |

Unique: `(stay_id, service_date, meal)` where `status = 'claimed'` (partial unique index).

### 7.8 `pos_packed_lunch`

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| outlet_id | bigint | FK `core_outlet`, restrict delete |
| stay_id | bigint | nullable |
| guest_name | varchar(160) | required |
| for_date | date | required |
| status | varchar(16) | `requested`, `prepared`, `collected`, `cancelled` |
| notes | text | nullable |
| created_by | bigint | nullable staff id |
| created_at / updated_at | timestamp | |

Index: `(for_date, status)`.

### 7.9 `pos_packed_lunch_item`

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| packed_lunch_id | bigint | FK `pos_packed_lunch`, cascade delete |
| product_id | bigint | nullable FK `pos_product`, null on delete |
| description | varchar(120) | snapshot |
| quantity | smallint | required |

### 7.10 `pos_cashier_shift`

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| outlet_id | bigint | FK `core_outlet`, restrict delete |
| opened_by | bigint | staff id |
| opened_at | timestamp | required |
| closed_by | bigint | nullable staff id |
| closed_at | timestamp | nullable |
| opening_float_cents | bigint | default 0 |
| expected_cash_cents | bigint | default 0 |
| counted_cash_cents | bigint | nullable |
| difference_cents | bigint | nullable |
| status | varchar(16) | `open`, `closed` |
| notes | text | nullable |

Index: `(outlet_id, status)`.

## 8. Module E: Equipment rental (tenant, proposed)

Money stays in Module C. `rental_rental.charge_id` links the rental charge, and late or damage
fees post additional `folio_charge` rows with `source_type = 'rental'`.

### 8.1 `rental_equipment_type`

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| name | varchar(100) | required |
| description | text | nullable |
| is_active | boolean | default true |
| created_at / updated_at | timestamp | |

### 8.2 `rental_equipment_item`

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| equipment_type_id | bigint | FK `rental_equipment_type`, restrict delete |
| item_code | varchar(32) | required, unique |
| size | varchar(24) | nullable |
| condition | varchar(16) | `ok`, `damaged`, `retired` |
| is_active | boolean | default true |
| created_at / updated_at | timestamp | |

Index: `(equipment_type_id, is_active)`.

### 8.3 `rental_season_price`

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| equipment_type_id | bigint | FK `rental_equipment_type`, cascade delete |
| season_id | bigint | FK `core_season`, cascade delete |
| day_rate_cents | bigint | required |
| created_at / updated_at | timestamp | |

Unique: `(equipment_type_id, season_id)`.

### 8.4 `rental_rental`

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| reference | varchar(32) | unique |
| customer_type | varchar(16) | `room`, `walk_in` |
| stay_id | bigint | nullable |
| folio_id | bigint | nullable FK `folio_folio`, restrict delete |
| guest_name | varchar(160) | required |
| rented_by | bigint | staff id |
| rented_at | timestamp | required |
| due_on | date | required |
| status | varchar(16) | `open`, `returned`, `late`, `void` |
| deposit_cents | bigint | default 0, expected deposit |
| notes | text | nullable |
| created_at / updated_at | timestamp | |

Indexes: `status`, `due_on`, `stay_id`.

### 8.5 `rental_rental_item`

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| rental_id | bigint | FK `rental_rental`, cascade delete |
| equipment_item_id | bigint | FK `rental_equipment_item`, restrict delete |
| day_rate_cents | bigint | snapshot |
| days | smallint | required |
| line_total_cents | bigint | snapshot |
| status | varchar(16) | `out`, `returned`, `damaged` |
| returned_at | timestamp | nullable |
| condition_on_return | varchar(16) | nullable |
| late_days | smallint | default 0 |
| late_fee_cents | bigint | default 0 |
| damage_fee_cents | bigint | default 0 |
| created_at / updated_at | timestamp | |

Partial unique index: `(equipment_item_id)` where `status = 'out'`. One open rental per item.

## 9. Module F: Hotel services and activities (tenant, proposed)

One configurable booking engine. No per-service-type tables.

### 9.1 `service_service`

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| name | varchar(120) | required |
| service_type | varchar(24) | `sauna`, `spa`, `ski_lesson`, `guided_hike`, `activity`, `other` |
| description | text | nullable |
| outlet_id | bigint | nullable FK `core_outlet`, null on delete |
| building_id | bigint | nullable FK `core_building`, null on delete |
| duration_minutes | smallint | required |
| default_capacity | smallint | default 1 |
| price_cents | bigint | required |
| price_mode | varchar(16) | `per_person`, `per_booking`, `included` |
| outdoor | boolean | default false, drives weather warnings |
| is_active | boolean | default true |
| created_at / updated_at | timestamp | |

### 9.2 `service_staff_requirement`

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| service_id | bigint | FK `service_service`, cascade delete |
| staff_role | varchar(32) | for example `instructor`, `guide`, `therapist` |
| required_count | smallint | default 1 |

Unique: `(service_id, staff_role)`.

### 9.3 `service_schedule_rule`

Recurring template used to generate slots.

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| service_id | bigint | FK `service_service`, cascade delete |
| weekday | smallint | 0 to 6 |
| starts_at | time | required |
| ends_at | time | required |
| capacity | smallint | required |
| valid_from | date | required |
| valid_to | date | nullable |
| is_active | boolean | default true |

Index: `(service_id, weekday)`.

### 9.4 `service_slot`

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| service_id | bigint | FK `service_service`, restrict delete |
| starts_at | timestamp | required |
| ends_at | timestamp | required |
| capacity | smallint | required |
| booked_count | smallint | default 0, maintained by the booking service |
| status | varchar(16) | `scheduled`, `cancelled`, `completed`, `weather_hold` |
| weather_warning_id | bigint | nullable FK `weather_warning`, null on delete |
| created_at / updated_at | timestamp | |

Unique: `(service_id, starts_at)`. Index: `(starts_at, status)`.

### 9.5 `service_slot_staff`

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| slot_id | bigint | FK `service_slot`, cascade delete |
| staff_id | bigint | FK `core_staff`, restrict delete |
| role | varchar(32) | for example `instructor`, `guide` |

Unique: `(slot_id, staff_id)`. Conflict check on overlapping slots is done in the service layer.

### 9.6 `service_booking`

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| slot_id | bigint | FK `service_slot`, restrict delete |
| stay_id | bigint | nullable |
| folio_id | bigint | nullable FK `folio_folio`, restrict delete |
| guest_name | varchar(160) | required |
| participants_count | smallint | required, positive |
| status | varchar(16) | `booked`, `cancelled`, `no_show`, `completed` |
| charge_id | bigint | nullable FK `folio_charge`, null on delete |
| booked_by | bigint | staff id |
| created_at / updated_at | timestamp | |

Index: `(slot_id, status)`.

### 9.7 `service_booking_participant`

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| booking_id | bigint | FK `service_booking`, cascade delete |
| name | varchar(120) | required |
| is_lead | boolean | default false |
| checked_in | boolean | default false |

### 9.8 `service_outing`

Operational lifecycle for guided outings.

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| slot_id | bigint | FK `service_slot`, cascade delete, unique |
| status | varchar(16) | `open`, `out`, `closed` |
| departed_at | timestamp | nullable |
| returned_at | timestamp | nullable |
| notes | text | nullable |

### 9.9 `service_outing_participant`

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| outing_id | bigint | FK `service_outing`, cascade delete |
| booking_participant_id | bigint | FK `service_booking_participant`, cascade delete |
| out_at | timestamp | nullable |
| back_at | timestamp | nullable |
| status | varchar(16) | `pending`, `out`, `back`, `missing` |

Unique: `(outing_id, booking_participant_id)`. Closing the outing is blocked while any participant
is not back (missing or pending), unless a manager overrides with a reason.

## 10. Module G: Transport and lifts (tenant, proposed)

### 10.1 `transport_vehicle`

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| name | varchar(100) | required |
| plate | varchar(32) | required, unique |
| vehicle_type | varchar(24) | `minibus`, `van`, `car`, `snowmobile`, `other` |
| seats | smallint | required |
| is_active | boolean | default true |
| created_at / updated_at | timestamp | |

### 10.2 `transport_driver`

Drivers do not log in for MVP. The optional staff link is for future use.

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| staff_id | bigint | nullable FK `core_staff`, null on delete |
| name | varchar(120) | required |
| phone | varchar(40) | nullable |
| licence_no | varchar(64) | nullable |
| is_active | boolean | default true |

### 10.3 `transport_route`

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| name | varchar(120) | required |
| route_type | varchar(16) | `shuttle`, `taxi`, `pickup`, `dropoff` |
| geometry | json | nullable, staff drawn path (list of lat/lng points) |
| is_active | boolean | default true |
| created_at / updated_at | timestamp | |

### 10.4 `transport_route_stop`

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| route_id | bigint | FK `transport_route`, cascade delete |
| sequence | smallint | required, 1 based |
| name | varchar(120) | required |
| building_id | bigint | nullable FK `core_building`, null on delete |
| latitude | numeric(9,6) | nullable |
| longitude | numeric(9,6) | nullable |

Unique: `(route_id, sequence)`.

### 10.5 `transport_trip`

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| route_id | bigint | nullable FK `transport_route`, null on delete |
| vehicle_id | bigint | nullable FK `transport_vehicle`, restrict delete |
| driver_id | bigint | nullable FK `transport_driver`, restrict delete |
| trip_type | varchar(16) | `shuttle`, `taxi`, `pickup`, `dropoff` |
| starts_at | timestamp | required |
| ends_at | timestamp | required |
| seats | smallint | required, snapshot of vehicle capacity |
| status | varchar(16) | `scheduled`, `running`, `done`, `cancelled`, `weather_cancelled` |
| reservation_id | bigint | nullable FK `hotel_reservation`, null on delete |
| notes | text | nullable |
| created_at / updated_at | timestamp | |

Indexes: `(starts_at)`, `(vehicle_id, starts_at)`, `(driver_id, starts_at)`.
Driver and vehicle overlap checks are done in the service layer using these indexes.

### 10.6 `transport_trip_passenger`

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| trip_id | bigint | FK `transport_trip`, cascade delete |
| stay_id | bigint | nullable |
| reservation_id | bigint | nullable |
| guest_name | varchar(160) | required |
| seats | smallint | default 1 |
| pickup_stop_id | bigint | nullable FK `transport_route_stop`, null on delete |
| dropoff_stop_id | bigint | nullable FK `transport_route_stop`, null on delete |
| status | varchar(16) | `booked`, `boarded`, `no_show` |
| charge_id | bigint | nullable FK `folio_charge`, null on delete |

Rule: sum of `seats` per trip must not exceed `transport_trip.seats`.

### 10.7 `transport_lift`

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| name | varchar(120) | required |
| origin_building_id | bigint | nullable FK `core_building`, null on delete |
| destination_building_id | bigint | nullable FK `core_building`, null on delete |
| lift_type | varchar(24) | `cable_car`, `chairlift`, `drag_lift`, `gondola`, `other` |
| capacity | smallint | nullable |
| opens_at | time | nullable |
| closes_at | time | nullable |
| is_active | boolean | default true |
| created_at / updated_at | timestamp | |

### 10.8 `transport_lift_status_history`

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| lift_id | bigint | FK `transport_lift`, cascade delete |
| status | varchar(16) | `open`, `closed`, `limited`, `weather_hold` |
| reason | varchar(255) | nullable |
| changed_by | bigint | nullable staff id |
| created_at | timestamp | |

Index: `(lift_id, created_at)`. Append-only. Current status is the latest row.

### 10.9 `transport_lift_pass_product`

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| product_id | bigint | FK `pos_product`, cascade delete |
| lift_id | bigint | nullable FK `transport_lift`, null on delete |
| valid_days | smallint | required |
| is_active | boolean | default true |

Unique: `(product_id, lift_id)`.

### 10.10 `transport_issued_lift_pass`

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| lift_pass_product_id | bigint | FK `transport_lift_pass_product`, restrict delete |
| stay_id | bigint | nullable |
| guest_name | varchar(160) | required |
| valid_from | date | required |
| valid_to | date | required |
| charge_id | bigint | nullable FK `folio_charge`, null on delete |
| issued_by | bigint | nullable staff id |
| created_at | timestamp | |

## 11. Module H: Weather (tenant, proposed)

Forecast storage keeps the last successful snapshot when a fetch fails.

### 11.1 `weather_point`

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| building_id | bigint | nullable FK `core_building`, null on delete |
| lift_id | bigint | nullable FK `transport_lift`, null on delete |
| name | varchar(120) | required |
| latitude | numeric(9,6) | required |
| longitude | numeric(9,6) | required |
| altitude_m | integer | nullable |
| is_active | boolean | default true |
| created_at / updated_at | timestamp | |

Rule: a point references a building, a lift station, or is standalone with coordinates.

### 11.2 `weather_fetch_run`

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| weather_point_id | bigint | FK `weather_point`, cascade delete |
| fetched_at | timestamp | required, index |
| status | varchar(16) | `success`, `failed` |
| http_status | smallint | nullable |
| error_message | varchar(255) | nullable |
| duration_ms | integer | nullable |

Index: `(weather_point_id, fetched_at)`.

### 11.3 `weather_forecast_snapshot`

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| weather_point_id | bigint | FK `weather_point`, cascade delete |
| fetch_run_id | bigint | nullable FK `weather_fetch_run`, null on delete |
| fetched_at | timestamp | required |
| period_start | timestamp | required |
| period_end | timestamp | required |
| raw | json | nullable, provider payload for troubleshooting |
| is_current | boolean | default false |

Index: `(weather_point_id, fetched_at)`. Partial unique index: one `is_current = true` per point.

### 11.4 `weather_forecast_value`

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| snapshot_id | bigint | FK `weather_forecast_snapshot`, cascade delete |
| valid_at | timestamp | required |
| variable | varchar(24) | `temperature_c`, `wind_ms`, `gust_ms`, `precipitation_mm`, `symbol` |
| unit | varchar(16) | `c`, `m/s`, `mm`, `code` |
| value | numeric(10,2) | required |

Unique: `(snapshot_id, valid_at, variable)`. Index: `(variable, valid_at)`.

### 11.5 `weather_limit`

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| weather_point_id | bigint | FK `weather_point`, cascade delete |
| variable | varchar(24) | required |
| operator | varchar(4) | `gt`, `gte`, `lt`, `lte` |
| threshold | numeric(10,2) | required |
| severity | varchar(16) | `info`, `warning`, `danger` |
| is_active | boolean | default true |
| created_by | bigint | nullable staff id |
| created_at / updated_at | timestamp | |

Unique: `(weather_point_id, variable, operator, threshold)`.

### 11.6 `weather_warning`

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| weather_point_id | bigint | FK `weather_point`, cascade delete |
| limit_id | bigint | nullable FK `weather_limit`, null on delete |
| forecast_value_id | bigint | nullable FK `weather_forecast_value`, null on delete |
| variable | varchar(24) | snapshot |
| threshold | numeric(10,2) | snapshot |
| forecast_value | numeric(10,2) | snapshot |
| starts_at | timestamp | required |
| ends_at | timestamp | required |
| status | varchar(16) | `active`, `resolved`, `expired` |
| raised_at | timestamp | required |
| resolved_at | timestamp | nullable |
| resolved_by | bigint | nullable staff id |

Index: `(status, starts_at)`.

### 11.7 `weather_warning_target`

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| warning_id | bigint | FK `weather_warning`, cascade delete |
| target_type | varchar(16) | `lift`, `trip`, `service_slot` |
| target_id | bigint | required |
| acknowledged | boolean | default false |
| acknowledged_by | bigint | nullable staff id |
| acknowledged_at | timestamp | nullable |

Unique: `(warning_id, target_type, target_id)`.

### 11.8 `weather_cancellation`

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| warning_id | bigint | nullable FK `weather_warning`, null on delete |
| target_type | varchar(16) | `lift`, `trip`, `service_slot` |
| target_id | bigint | required |
| cancelled_at | timestamp | required |
| cancelled_by | bigint | nullable staff id |
| reason | varchar(255) | required |
| notes | text | nullable |

Money impact is recorded by voiding the related `folio_charge` rows with `reason_code = 'weather'`.
Never auto cancel: this row is created by staff action only.

### 11.9 `weather_avalanche_daily`

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| valid_on | date | required, unique |
| level | smallint | 1 to 5 |
| source | varchar(120) | nullable, official bulletin reference |
| notes | text | nullable |
| entered_by | bigint | nullable staff id |
| created_at | timestamp | |

### 11.10 `weather_lobby_screen`

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| name | varchar(120) | required |
| public_token | varchar(64) | required, unique, private read only link |
| building_id | bigint | nullable FK `core_building`, null on delete |
| is_active | boolean | default true |
| created_at / updated_at | timestamp | |

## 12. Module I: Owner dashboard and reports (tenant, optional)

Reports read Modules B to H directly. No money table is duplicated. Cached aggregates are not part
of the MVP schema.

### 12.1 `report_saved_view` (optional)

| Column | Type | Rules |
|---|---|---|
| id | bigint | PK |
| name | varchar(120) | required |
| report_key | varchar(64) | for example `revenue_by_outlet` |
| filters | json | date range, outlet, method |
| is_shared | boolean | default false |
| created_by | bigint | nullable staff id |
| created_at / updated_at | timestamp | |

CSV exports are generated on demand and not stored.

## 13. Cross module money flow

```text
Hotel nightly job      -> folio_charge (source_type = room, board)
POS order              -> folio_charge (source_type = pos)
Rental                 -> folio_charge (source_type = rental)
Service booking        -> folio_charge (source_type = service)
Trip or lift pass      -> folio_charge (source_type = transport)
Payment (cash or card) -> folio_payment
Correction             -> folio_void + is_voided on the original row
Checkout               -> blocked while balance_cents != 0
```

Rules:

1. `balance_cents = sum(charges where is_voided = false) - sum(payments where is_voided = false)`.
2. No module writes money rows outside the folio service.
3. `source_type` plus `source_id` lets a module find its own charge without a second money table.
4. Direct sales still create a folio so reports only read folio tables.

## 14. Global invariants

| Invariant | Enforcement |
|---|---|
| Charge and payment are append-only | Model guard plus service layer. Update only `is_voided`, delete throws |
| Void needs reason, actor, time | `folio_void` columns plus role gate (Manager or Owner) |
| Nightly posting runs once per stay and night | Unique `(stay_id, posted_on)` |
| No double booking of a room or bed | Overlap query on reservation allocations |
| No double rental of an item | Partial unique index on open rental item |
| No driver or vehicle double booking | Service check on overlapping trips |
| No over capacity slot | `booked_count <= capacity` checked in transaction |
| No over capacity trip | Sum of passenger seats against trip seats |
| Checkout only at zero balance | Checkout service |
| Season ranges do not overlap | Service check, optional exclusion constraint |
| Weather never auto cancels | Cancellation rows created by staff action only |
| Tenant isolation | Separate database per tenant, no `tenant_id` columns |

## 15. Index plan

| Table | Index | Why |
|---|---|---|
| `hotel_reservation` | `(check_in_on, check_out_on)`, `status` | Availability and arrivals |
| `hotel_reservation_accommodation` | `room_id`, `hut_bed_id` | Overlap checks |
| `hotel_stay` | `status` | In house list |
| `folio_charge` | `(folio_id, is_voided)`, `posted_on` | Balance and reports |
| `folio_payment` | `(folio_id, is_voided)` | Balance |
| `pos_order` | `(outlet_id, status)`, `opened_at` | Open orders and shift |
| `pos_order_item` | `order_id` | Order lines |
| `rental_rental` | `status`, `due_on` | Out list and overdue |
| `rental_rental_item` | partial unique on open item | Double rent guard |
| `service_slot` | `(service_id, starts_at)`, `(starts_at, status)` | Schedule and capacity |
| `transport_trip` | `(driver_id, starts_at)`, `(vehicle_id, starts_at)` | Conflict checks |
| `weather_forecast_value` | `(variable, valid_at)` | Warning evaluation |
| `core_audit_event` | `(entity_type, entity_id)`, `created_at` | Audit trail |

## 16. Migration order

1. Central: `saas_*` (already).
2. Tenant: `core_*` (already).
3. Tenant: `hotel_*` (already).
4. Tenant: `folio_*` (already, Module 2 slice). Add `folio_invoice` and `tax_breakdown` in Module 3.
5. Tenant: `pos_*`.
6. Tenant: `rental_*`.
7. Tenant: `service_*`.
8. Tenant: `transport_*`.
9. Tenant: `weather_*`.
10. Tenant: `report_saved_view` (optional).

Each module keeps its migration under `Modules/<Name>/Database/migrations/tenant/`, created with
`php artisan module:make`, never hand made folders.

## 17. Open questions for the client

| # | Question | Current default |
|---|---|---|
| 1 | Child board rate | Full rate until answered |
| 2 | Seasonal board price | One board price per person |
| 3 | Deposit before check in | Stored as reservation intent until folio exists, then posted as payment |
| 4 | Chalet inventory | Modeled as a room with `accommodation_type = 'chalet'` |
| 5 | Lift pass sale source | POS product mapped by `transport_lift_pass_product` |
| 6 | Multi currency | One currency per tenant |
| 7 | Tax components | `tax_rate_bp` per product, `tax_breakdown` snapshot on invoice |

## 18. Menu data (not a schema table)

The staff sidebar is not stored in the database for MVP. It follows the InventorySystem pattern:

- menu items live in code (`frontend/utils/app-modules.ts` today, optional backend config later),
- the API filters by role and permission when a backend menu endpoint is added,
- no `menu` table is needed until the client asks for per-tenant custom menus.

If per-tenant custom menus are approved later, add:

```text
core_menu_item(id, key unique, title, icon, route_path, parent_id, sort_order, permission_key, is_active)
core_menu_role(menu_item_id, role)  unique(menu_item_id, role)
```

## 19. Implementation status

| Module | Tables | Status |
|---|---|---|
| Central SaaS | 3 | implemented |
| A Core | 7 | implemented |
| B Hotel | 10 | implemented |
| C Folio | 4 | implemented (slice), invoice proposed |
| D POS | 10 | proposed |
| E Rental | 5 | proposed |
| F Services | 9 | proposed |
| G Transport | 10 | proposed |
| H Weather | 10 | proposed |
| I Reports | 1 optional | proposed |

Existing migrations that must be applied before dependent modules:

- `Modules/Core/Database/migrations/tenant/2026_11_03_000001_create_core_staff_and_building_tables.php`
- `Modules/Core/Database/migrations/tenant/2026_11_03_000002_create_core_config_tables.php`
- `Modules/Hotel/Database/migrations/tenant/2026_11_04_000001_create_hotel_tables.php`
- `Modules/Folio/Database/migrations/tenant/2026_11_04_000001_create_folio_tables.php`
