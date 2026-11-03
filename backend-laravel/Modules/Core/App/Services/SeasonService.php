<?php

namespace Modules\Core\App\Services;

use App\Support\Http\Envelope;
use Illuminate\Http\Exceptions\HttpResponseException;
use Modules\Core\App\Models\Season;

class SeasonService
{
    public function create(array $data): Season
    {
        $this->assertNoOverlap($data['kind'], $data['starts_on'], $data['ends_on'], null);
        return Season::on(config('tenancy.connection_name'))->create($data);
    }

    public function update(Season $season, array $data): Season
    {
        $kind = $data['kind'] ?? $season->kind;
        $start = $data['starts_on'] ?? $season->starts_on;
        $end = $data['ends_on'] ?? $season->ends_on;
        $this->assertNoOverlap($kind, $start, $end, $season->getKey());
        $season->fill($data)->save();
        return $season;
    }

    private function assertNoOverlap(string $kind, $start, $end, ?int $ignoreId): void
    {
        $q = Season::on(config('tenancy.connection_name'))
            ->where('kind', $kind)
            ->where('starts_on', '<=', $end)
            ->where('ends_on', '>=', $start);
        if ($ignoreId) {
            $q->where('id', '!=', $ignoreId);
        }
        if ($q->exists()) {
            throw new HttpResponseException(Envelope::errorJson(
                'error',
                'Season dates overlap an existing season of the same kind.',
                [],
                400
            ));
        }
    }
}
