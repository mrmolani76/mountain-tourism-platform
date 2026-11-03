<?php

namespace Modules\Saas\Http\Controllers;

use App\Support\Http\Envelope;
use App\Support\Http\Paginates;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Saas\Models\Domain;
use Modules\Saas\Models\Tenant;
use Modules\Saas\Services\TenantService;

class TenantController
{
    use Paginates;

    public function __construct(private TenantService $tenants)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $q = Tenant::query()->orderBy('name');

        return $this->paginateQuery($q, $request, fn (Tenant $t) => $this->serialize($t));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'slug' => ['required', 'string', 'max:64', 'alpha_dash'],
            'base_domain' => ['nullable', 'string', 'max:200'],
            'owner_email' => ['nullable', 'email'],
            'owner_password' => ['nullable', 'string', 'min:8'],
        ]);

        $tenant = $this->tenants->register($data);

        return response()->json($this->serialize($tenant), 201);
    }

    public function show(int $tenant): JsonResponse
    {
        $t = Tenant::query()->findOrFail($tenant);

        return response()->json($this->serialize($t));
    }

    public function update(Request $request, int $tenant): JsonResponse
    {
        $t = Tenant::query()->findOrFail($tenant);
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:200'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $t->fill($data)->save();

        return response()->json($this->serialize($t));
    }

    public function activate(int $tenant): JsonResponse
    {
        $t = $this->tenants->setActive($tenant, true);

        return response()->json($this->serialize($t));
    }

    public function deactivate(int $tenant): JsonResponse
    {
        $t = $this->tenants->setActive($tenant, false);

        return response()->json($this->serialize($t));
    }

    private function serialize(Tenant $t): array
    {
        return [
            'id' => $t->getKey(),
            'name' => $t->name,
            'slug' => $t->slug,
            'db_name' => $t->db_name,
            'is_active' => (bool) $t->is_active,
            'domains' => $t->domains()->pluck('hostname')->all(),
        ];
    }
}
