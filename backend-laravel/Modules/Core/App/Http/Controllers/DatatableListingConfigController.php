<?php

namespace Modules\Core\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\App\Enums\StaffRole;
use Modules\Core\App\Models\Building;
use Modules\Core\App\Models\Outlet;
use Modules\Core\App\Support\StaffAuthz;

/**
 * EQMS POST /api/datatableListingConfig — dropdown options for DataTable column filters.
 *
 * eQMS: CoreController::datatableListingConfig (holders → submodule options).
 * Mountain: holders.value is the listing resource / column family key.
 */
class DatatableListingConfigController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        if ($denied = StaffAuthz::denyUnlessOwner()) {
            return $denied;
        }

        $holders = $request->input('holders', []);
        if (! is_array($holders)) {
            $holders = [];
        }

        $data = [];
        $cache = [];

        foreach ($holders as $holderData) {
            if (! is_array($holderData)) {
                continue;
            }
            $holder = (string) ($holderData['key'] ?? '');
            $submodule = (string) ($holderData['value'] ?? '');
            if ($holder === '' || $submodule === '') {
                continue;
            }

            if (isset($cache[$submodule])) {
                $data[$holder] = $cache[$submodule];

                continue;
            }

            $options = $this->optionsFor($submodule);
            $cache[$submodule] = $options;
            $data[$holder] = $options;
        }

        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }

    /**
     * @return list<array{value: mixed, title: string}>
     */
    private function optionsFor(string $submodule): array
    {
        return match ($submodule) {
            'users', 'role' => $this->roleOptions(),
            'status' => [
                ['value' => 1, 'title' => 'Enabled'],
                ['value' => 0, 'title' => 'Disabled'],
            ],
            'buildings', 'building_type' => $this->mapTypes(Building::TYPES),
            'outlets', 'outlet_type' => $this->mapTypes(Outlet::TYPES),
            'seasons', 'kind' => [
                ['value' => 'winter', 'title' => 'Winter'],
                ['value' => 'summer', 'title' => 'Summer'],
                ['value' => 'closed', 'title' => 'Closed'],
            ],
            default => [],
        };
    }

    /**
     * @return list<array{value: string, title: string}>
     */
    private function roleOptions(): array
    {
        $roles = [];
        foreach (StaffRole::LABELS as $code => $label) {
            $roles[] = ['value' => $code, 'title' => $label];
        }

        return $roles;
    }

    /**
     * @param  array<string, string>  $types
     * @return list<array{value: string, title: string}>
     */
    private function mapTypes(array $types): array
    {
        $out = [];
        foreach ($types as $code => $label) {
            $out[] = ['value' => $code, 'title' => $label];
        }

        return $out;
    }
}
