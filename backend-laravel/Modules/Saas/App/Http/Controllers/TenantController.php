<?php

namespace Modules\Saas\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\Paginates;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Saas\App\Exceptions\TenantDatabaseAlreadyExistsException;
use Modules\Saas\App\Exceptions\TenantDatabaseProvisioningException;
use Modules\Saas\App\Models\SaasTenantActivity;
use Modules\Saas\App\Models\Tenant;
use Modules\Saas\App\Services\OwnerCredentialsDeliveryService;
use Modules\Saas\App\Services\TenantLifecycleService;
use Modules\Saas\App\Services\TenantReadinessService;
use Modules\Saas\App\Services\TenantService;

class TenantController extends Controller
{
    use Paginates;

    public function __construct(
        private TenantService $tenants,
        private TenantLifecycleService $lifecycle,
        private TenantReadinessService $readiness,
        private OwnerCredentialsDeliveryService $credentialsDelivery
    ) {}

    public function dashboard(): JsonResponse
    {
        $query = Tenant::query();
        $events = SaasTenantActivity::query()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(6)
            ->get(['id', 'tenant_id', 'actor_email', 'event_type', 'created_at']);
        $tenants = Tenant::query()
            ->whereIn('id', $events->pluck('tenant_id')->unique())
            ->get(['id', 'uuid', 'name'])
            ->keyBy('id');

        return response()->json([
            'total' => (clone $query)->count(),
            'active' => (clone $query)->where('is_active', true)->count(),
            'inactive' => (clone $query)->where('is_active', false)->count(),
            'recent_activity' => $events->map(fn (SaasTenantActivity $event): array => [
                'id' => $event->getKey(),
                'tenant_uuid' => $tenants->get($event->tenant_id)?->uuid,
                'tenant_name' => $tenants->get($event->tenant_id)?->name,
                'actor_email' => $event->actor_email,
                'event_type' => $event->event_type,
                'created_at' => $event->created_at,
            ])->values()->all(),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'search' => ['nullable', 'string', 'max:200'],
            'status' => ['nullable', Rule::in(['all', 'active', 'inactive'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'page_size' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Tenant::query()->with('domains')->orderBy('name');

        if (! empty($data['search'])) {
            $search = trim($data['search']);
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', '%'.$search.'%')
                    ->orWhere('slug', 'like', '%'.$search.'%');
            });
        }

        if (($data['status'] ?? null) === 'active') {
            $query->where('is_active', true);
        } elseif (($data['status'] ?? null) === 'inactive') {
            $query->where('is_active', false);
        }

        return $this->paginateQuery($query, $request, fn (Tenant $tenant) => $this->serialize($tenant));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'slug' => [
                'required',
                'string',
                'max:57',
                'regex:/\A[a-zA-Z0-9-]+\z/',
                Rule::unique(config('tenancy.central_connection').'.saas_tenant', 'slug'),
            ],
            'base_domain' => [
                'nullable',
                'string',
                'max:253',
                'regex:/^(?=.{1,253}$)[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?(?:\\.[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)*$/i',
            ],
            'owner_email' => ['nullable', 'email'],
        ]);

        try {
            $result = $this->tenants->register($data);
        } catch (TenantDatabaseAlreadyExistsException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 409);
        } catch (TenantDatabaseProvisioningException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 503);
        }

        $tenant = $result['tenant']->load('domains');
        $payload = $this->serialize($tenant);
        $deliveryStatus = empty($data['owner_email']) ? 'not_requested' : 'not_sent';
        if ($result['owner_temporary_password'] !== null) {
            $deliveryStatus = $this->credentialsDelivery->send(
                $tenant,
                strtolower(trim((string) $data['owner_email'])),
                $result['owner_temporary_password']
            );
        }
        $payload['tenant_database_ready'] = $result['tenant_database_ready'];
        $payload['tenant_migrations_ready'] = $result['tenant_migrations_ready'];
        $payload['owner_created'] = $result['owner_created'];
        $payload['owner_temporary_password'] = $result['owner_temporary_password'];
        $payload['owner_email_delivery_status'] = $deliveryStatus;
        $payload['tenant_domain_sync_status'] = $result['tenant_domain_sync_status'];
        $this->lifecycle->record($tenant, 'tenant_created', [
            'tenant_database_ready' => $result['tenant_database_ready'],
            'tenant_migrations_ready' => $result['tenant_migrations_ready'],
            'owner_created' => $result['owner_created'],
            'temporary_password_generated' => $result['owner_temporary_password'] !== null,
            'force_reset_password' => $result['owner_created'],
            'owner_email_delivery_status' => $deliveryStatus,
            'tenant_domain_sync_status' => $result['tenant_domain_sync_status'],
        ]);

        return response()->json($payload, 201);
    }

    public function show(string $tenantUuid): JsonResponse
    {
        $t = $this->findTenant($tenantUuid, true);

        return response()->json($this->serialize($t));
    }

    public function createOwner(Request $request, string $tenantUuid): JsonResponse
    {
        $record = $this->findTenant($tenantUuid);
        $data = $request->validate([
            'owner_email' => ['required', 'email'],
        ]);

        $temporaryPassword = $this->tenants->createOwnerStaff($record, $data['owner_email']);
        if ($temporaryPassword === null) {
            return response()->json([
                'owner_created' => false,
                'message' => 'Owner account could not be created. Confirm the tenant Core schema is ready and the email is not already assigned.',
            ], 409);
        }

        $deliveryStatus = $this->credentialsDelivery->send(
            $record,
            strtolower(trim($data['owner_email'])),
            $temporaryPassword
        );
        $this->lifecycle->record($record, 'owner_created', [
            'temporary_password_generated' => true,
            'force_reset_password' => true,
            'owner_email_delivery_status' => $deliveryStatus,
        ]);

        return response()->json([
            'owner_created' => true,
            'owner_temporary_password' => $temporaryPassword,
            'owner_email_delivery_status' => $deliveryStatus,
        ], 201);
    }

    public function readiness(string $tenantUuid): JsonResponse
    {
        $record = $this->findTenant($tenantUuid, true);

        return response()->json($this->readiness->forTenant($record));
    }

    public function activity(string $tenantUuid): JsonResponse
    {
        $tenant = $this->findTenant($tenantUuid);

        $events = SaasTenantActivity::query()
            ->where('tenant_id', $tenant->getKey())
            ->orderByDesc('created_at')
            ->limit(50)
            ->get(['id', 'actor_email', 'event_type', 'metadata', 'created_at']);

        return response()->json($events);
    }

    public function update(Request $request, string $tenantUuid): JsonResponse
    {
        $t = $this->findTenant($tenantUuid);
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:200'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $previousName = $t->name;
        $wasActive = (bool) $t->is_active;
        $t->fill($data)->save();
        $t->load('domains');

        if (array_key_exists('name', $data) && $previousName !== $t->name) {
            $this->lifecycle->record($t, 'tenant_updated', [
                'updated_fields' => ['name'],
            ]);
        }

        if (array_key_exists('is_active', $data) && $wasActive !== (bool) $t->is_active) {
            $this->lifecycle->record($t, $t->is_active ? 'tenant_activated' : 'tenant_deactivated');
        }

        return response()->json($this->serialize($t));
    }

    public function activate(string $tenantUuid): JsonResponse
    {
        $tenant = $this->findTenant($tenantUuid);
        $wasActive = (bool) $tenant->is_active;
        $t = $this->tenants->setActive((int) $tenant->getKey(), true);
        $t->load('domains');
        if (! $wasActive) {
            $this->lifecycle->record($t, 'tenant_activated');
        }

        return response()->json($this->serialize($t));
    }

    public function deactivate(string $tenantUuid): JsonResponse
    {
        $tenant = $this->findTenant($tenantUuid);
        $wasActive = (bool) $tenant->is_active;
        $t = $this->tenants->setActive((int) $tenant->getKey(), false);
        $t->load('domains');
        if ($wasActive) {
            $this->lifecycle->record($t, 'tenant_deactivated');
        }

        return response()->json($this->serialize($t));
    }

    private function serialize(Tenant $t): array
    {
        return [
            'uuid' => (string) $t->uuid,
            'name' => $t->name,
            'slug' => $t->slug,
            'db_name' => $t->db_name,
            'is_active' => (bool) $t->is_active,
            'domains' => $t->domains->pluck('hostname')->all(),
            'primary_domain' => $t->domains->firstWhere('is_primary', true)?->hostname,
            'created_at' => $t->created_at,
        ];
    }

    private function findTenant(string $uuid, bool $withDomains = false): Tenant
    {
        $query = Tenant::query();

        if ($withDomains) {
            $query->with('domains');
        }

        return $query->where('uuid', $uuid)->firstOrFail();
    }
}
