<?php

namespace Modules\Core\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\Paginates;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\App\Http\Requests\StoreSeasonRequest;
use Modules\Core\App\Http\Requests\UpdateSeasonRequest;
use Modules\Core\App\Models\AuditEvent;
use Modules\Core\App\Models\Season;
use Modules\Core\App\Services\SeasonService;
use Modules\Core\App\Support\CoreAudit;
use Modules\Core\App\Support\StaffAuthz;

class SeasonController extends Controller
{
    use Paginates;

    public function __construct(private SeasonService $seasons) {}

    public function index(Request $request): JsonResponse
    {
        if ($d = StaffAuthz::denyUnlessOwner()) {
            return $d;
        }
        $q = Season::on(config('tenancy.connection_name'))->orderBy('starts_on');

        return $this->paginateQuery($q, $request, fn (Season $s) => $this->serialize($s));
    }

    public function store(StoreSeasonRequest $request): JsonResponse
    {
        if ($d = StaffAuthz::denyUnlessOwner()) {
            return $d;
        }
        $s = $this->seasons->create($request->validated());
        CoreAudit::record(AuditEvent::ACTION_CREATE, 'season', (int) $s->getKey(), newValues: $this->serialize($s));

        return response()->json($this->serialize($s), 201);
    }

    public function show(int $season): JsonResponse
    {
        if ($d = StaffAuthz::denyUnlessOwner()) {
            return $d;
        }
        $s = Season::on(config('tenancy.connection_name'))->findOrFail($season);

        return response()->json($this->serialize($s));
    }

    public function update(UpdateSeasonRequest $request, int $season): JsonResponse
    {
        if ($d = StaffAuthz::denyUnlessOwner()) {
            return $d;
        }
        $s = Season::on(config('tenancy.connection_name'))->findOrFail($season);
        $old = $this->serialize($s);
        $s = $this->seasons->update($s, $request->validated());
        CoreAudit::record(
            AuditEvent::ACTION_UPDATE,
            'season',
            (int) $s->getKey(),
            oldValues: $old,
            newValues: $this->serialize($s),
        );

        return response()->json($this->serialize($s));
    }

    public function destroy(int $season): JsonResponse
    {
        if ($d = StaffAuthz::denyUnlessOwner()) {
            return $d;
        }
        $s = Season::on(config('tenancy.connection_name'))->findOrFail($season);
        $old = $this->serialize($s);
        $id = (int) $s->getKey();
        $s->delete();
        CoreAudit::record(AuditEvent::ACTION_UPDATE, 'season', $id, oldValues: $old, reason: 'deleted');

        return response()->json(null, 204);
    }

    private function serialize(Season $s): array
    {
        return [
            'id' => $s->getKey(),
            'kind' => $s->kind,
            'starts_on' => optional($s->starts_on)->format('Y-m-d'),
            'ends_on' => optional($s->ends_on)->format('Y-m-d'),
            'is_active' => (bool) $s->is_active,
        ];
    }
}
