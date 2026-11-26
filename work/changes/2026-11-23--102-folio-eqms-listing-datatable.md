# Change Report: Folio EQMS list/config + DataTable

- **Date**: 2026-11-23
- **By**: Agent
- **Change ID**: 102

## 1. Summary

After all-module deep check, started Module C Folio: EQMS DataTable listing (`list/config` + `list`) + Nuxt DataTable. Money charge/pay/void unchanged.

## 2. Module audit (deep check)

| Module | Backend | Frontend | EQMS list/config | Tests | Gap severity |
|--------|---------|----------|------------------|-------|--------------|
| Saas | 3 models, central | — | N/A | SaasRegistry | ok |
| Core | 7 models, listing | users/buildings/… | aligned | Core+tenant | ok |
| Hotel | 10 models, listing | hotel/* | aligned | HotelModule | medium (72 verify) |
| Folio | 4 models, money APIs | folios | **aligned in 102** | none | was high → fixed listing |
| Pos | 10 models | pos/* custom | no | none | medium |
| Rental | 5 models | rental/* custom | no | none | medium |
| Services | 8 models | services/* custom | no | none | medium |
| Transport | 11 models | transport/* custom | no | none | medium |
| Weather | 10 models | weather/* custom | no | none | medium (avalanche stub) |
| Reports | 0 models, services | reports/* | no | none | low |

## 3. Before / After (Folio)

- Before: `GET /folios` + VTable
- After: `GET folios/list/config`, `POST folios/list`, DataTable; legacy GET kept

## 4. Reason

User: deeply check all modules and start work.

## 5. Files Affected

- `Modules/Folio/config/listing.php` (new)
- `Modules/Folio/App/Http/Controllers/FolioListingController.php` (new)
- `Modules/Folio/App/Providers/FolioServiceProvider.php`
- `Modules/Folio/routes/api.php`
- `frontend/composables/api-clients/useFolioApiClient.ts`
- `frontend/pages/folios/index.vue`
- work kanban / change report

## 6. Verification / Status

- [ ] Code-only
- [ ] Next: Pos/Rental/Services/Transport/Weather EQMS listings (optional series)
