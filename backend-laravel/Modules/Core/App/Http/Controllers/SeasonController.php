<?php

namespace Modules\Core\App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Support\Http\Envelope;
use Modules\Core\App\Support\StaffAuthz;
use App\Support\Http\Paginates;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\App\Models\Season;
use Modules\Core\App\Services\SeasonService;

class SeasonController extends Controller
{
    use Paginates;

    public function __construct(private SeasonService $seasons) {}

    public function index(Request $request): JsonResponse
    {
        if ($d = StaffAuthz::denyUnlessOwner()) return $d;
        $q = Season::on(config('tenancy.connection_name'))->orderBy('starts_on');
        return $this->paginateQuery($q, $request, fn (Season $s) => $this->serialize($s));
    }

    public function store(Request $request): JsonResponse
    {
        if ($d = StaffAuthz::denyUnlessOwner()) return $d;
        $data = $request->validate([
            'kind' => ['required', 'in:winter,summer,closed'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after_or_equal:starts_on'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $s = $this->seasons->create($data);
        return response()->json($this->serialize($s), 201);
    }

    public function show(int $season): JsonResponse
    {
        if ($d = StaffAuthz::denyUnlessOwner()) return $d;
        $s = Season::on(config('tenancy.connection_name'))->findOrFail($season);
        return response()->json($this->serialize($s));
    }

    public function update(Request $request, int $season): JsonResponse
    {
        if ($d = StaffAuthz::denyUnlessOwner()) return $d;
        $s = Season::on(config('tenancy.connection_name'))->findOrFail($season);
        $data = $request->validate([
            'kind' => ['sometimes', 'in:winter,summer,closed'],
            'starts_on' => ['sometimes', 'date'],
            'ends_on' => ['sometimes', 'date'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $s = $this->seasons->update($s, $data);
        return response()->json($this->serialize($s));
    }

    public function destroy(int $season): JsonResponse
    {
        if ($d = StaffAuthz::denyUnlessOwner()) return $d;
        Season::on(config('tenancy.connection_name'))->findOrFail($season)->delete();
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
