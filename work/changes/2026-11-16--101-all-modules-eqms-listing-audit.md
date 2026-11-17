# Change Report: All-modules EQMS listing audit + Hotel align

- **Date**: 2026-11-16
- **By**: Agent
- **Change ID**: 101

## 1. Summary

Audited every MVP module for EQMS `list/config` pattern. Aligned Hotel (only remaining nested listing.php) to flat EQMS shape + `ListingConfigService`.

## 2. Module audit

| Module | list/config? | Status |
|--------|--------------|--------|
| A Core | Yes (`listing`) | Aligned in **100** |
| B Hotel | Yes (`hotel_listing`) | Aligned in **101** (flat + ListingConfigService) |
| C Folio | No | Custom `client.list` pages — not EQMS DataTable yet |
| D POS | No | Custom composable lists |
| E Rental | No | Custom lists |
| F Services | No | Custom lists |
| G Transport | No | Custom lists |
| H Weather | No | Custom lists |
| I Reports | No | Custom dashboard/report pages |

## 3. Before / After (Hotel)

- Before: nested `headers` + `model` in `hotel_listing`; controller duplicated envelope; `ilike`
- After: flat fields; RESOURCES map; `ListingConfigService::build/table(..., 'hotel_listing')`; `like`

## 4. Reason

User: check all modules for the same EQMS listing pattern.

## 5. Files Affected

- `Modules/Hotel/config/listing.php`
- `Modules/Hotel/App/Http/Controllers/HotelListingController.php`
- `Modules/Hotel/App/Http/Controllers/AvailabilityController.php`
- `Modules/Core/App/Services/ListingConfigService.php`
- work kanban / change report

## 6. Verification / Status

- [ ] Code-only
- [ ] Follow-up: migrate Folio+ domain modules to EQMS list/config when UI work starts
