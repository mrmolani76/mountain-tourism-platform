# Mountain common platform layer

Shared Laravel pieces used by tenant and SaaS modules.

| Path | Role |
|------|------|
| `app/Models/BaseModel.php` | Eloquent base + optional actor stamps + auditing hook |
| `app/Models/BasePivot.php` | Pivot actor stamps |
| `app/Models/BaseAuditTrail.php` | Immutable audit trail rows |
| `app/Traits/CustomAuditable.php` | Auditable + relation events |
| `app/Services/DataTableService.php` | SPA listing search / sort / paginate |
| `app/Services/DateTimeZoneMutator.php` | Date display / filter TZ helpers |
| `app/Http/Controllers/Controller.php` | `handleAction`, `ok()` |
| `app/Helpers/helpers.php` | `actor_id`, `setDateFormat`, `logException` |
| `config/listing.php` | DataTable field maps (merged with module listing configs) |
| `config/audit.php` | Auditing package config; disabled by default for MVP |

## Intentionally deferred

SSO, digital signature, S3 uploads, escalation codes, and bulk-delete SPA contracts stay out of MVP unless scope adds them.

## Usage sketch

```php
class Building extends \App\Models\BaseModel
{
    // ...
}

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
