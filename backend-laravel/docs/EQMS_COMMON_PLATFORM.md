# eQMS Complere → Mountain common platform

Shared Laravel layer ported from `/var/www/html/eQMS/complere` for Module 1+.

| eQMS path | Mountain path | Role |
|-----------|---------------|------|
| `app/Models/BaseModel.php` | `app/Models/BaseModel.php` | Eloquent base + optional actor stamps + auditing hook |
| `app/Models/BasePivot.php` | `app/Models/BasePivot.php` | Pivot actor stamps |
| `app/Models/BaseAuditTrail.php` | `app/Models/BaseAuditTrail.php` | Immutable owen-it audit rows |
| `app/Traits/CustomAuditable.php` | `app/Traits/CustomAuditable.php` | owen-it Auditable + relation events |
| `app/Services/DataTableService.php` | `app/Services/DataTableService.php` | SPA listing search/sort/paginate |
| `app/Services/DateTimeZoneMutator.php` | `app/Services/DateTimeZoneMutator.php` | Date display / filter TZ helpers |
| `app/Http/Controllers/Controller.php` | `app/Http/Controllers/Controller.php` | `handleAction`, `ok()` |
| Helpers (`setDateFormat`, …) | `app/Helpers/helpers.php` | `actor_id`, `setDateFormat`, `logException` |
| listing configs | `config/listing.php` | DataTable field maps |
| audit config | `config/audit.php` | owen-it; **disabled by default** |

## Intentionally not ported yet

SSO, digital signature, S3/FileUpload, escalation codes, bulk-delete SPA contracts, demo packs — QMS-specific or later modules.

## Usage

```php
// Model
class Building extends \App\Models\BaseModel { ... }

// Controller
class BuildingController extends \App\Http\Controllers\Controller
{
    public function list(Request $request)
    {
        return $this->handleAction(function () use ($request) {
            $svc = new \App\Services\DataTableService(config('listing.buildings'));
            $res = $svc->execute($request, DB::connection('tenant')->table('core_building'));

            return $this->ok($res);
        });
    }
}
```

After `composer install`, run `composer dump-autoload` so `helpers.php` loads.

Domain money rules stay append-only (`AuditEvent` / Charge / Payment) — separate from owen-it `hist`.
