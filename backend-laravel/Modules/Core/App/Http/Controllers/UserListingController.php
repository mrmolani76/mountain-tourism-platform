<?php

namespace Modules\Core\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Modules\Core\App\Enums\StaffRole;
use Modules\Core\App\Http\Requests\StoreStaffRequest;
use Modules\Core\App\Http\Requests\UpdateStaffRequest;
use Modules\Core\App\Models\AuditEvent;
use Modules\Core\App\Models\Staff;
use Modules\Core\App\Support\CoreAudit;
use Modules\Core\App\Support\StaffAuthz;

/**
 * eQMS Complere UserController listing surface, mapped onto tenant Staff.
 *
 * Routes mirror qualitycenter-spa expectations:
 *   GET  /api/users/list/config
 *   POST /api/users/list
 *   POST /api/users/create
 *   GET  /api/user/fetch?id=
 *   POST /api/user/update
 *   POST /api/users/destroy
 *   POST /api/users/usersActivate
 *   POST /api/users/usersDeactivate
 *
 * Row cells follow the eQMS contract: `{ label, type }` / status color / actions flags.
 */
class UserListingController extends Controller
{
    public function listingConfig(): JsonResponse
    {
        if ($d = StaffAuthz::denyUnlessOwner()) {
            return $d;
        }

        $table = config('listing.users');
        $roles = [];
        foreach (StaffRole::LABELS as $code => $label) {
            $roles[] = ['value' => $code, 'title' => $label];
        }

        return response()->json([
            'status' => true,
            'data' => [
                'headers' => $table,
                'bulkActions' => ['delete', 'enable', 'disable'],
                'columnSearchable' => [
                    'role' => $roles,
                    'status' => [
                        ['value' => 1, 'title' => 'Enabled'],
                        ['value' => 0, 'title' => 'Disabled'],
                    ],
                ],
                'showCheckbox' => true,
                'selectedColumns' => [],
                'showAddButton' => true,
                'advanceFilters' => new \stdClass(),
                'showExportButton' => false,
                'exportColumns' => $table['fields'],
                'externalFilters' => new \stdClass(),
            ],
        ]);
    }

    public function list(Request $request): JsonResponse
    {
        if ($d = StaffAuthz::denyUnlessOwner()) {
            return $d;
        }

        $page = max(1, (int) $request->input('page', 1));
        $size = (int) $request->input('itemsPerPage', $request->input('page_size', 10));
        if ($size < 1) {
            $size = 10;
        }
        if ($size > 100) {
            $size = 100;
        }

        $q = Staff::on(config('tenancy.connection_name'));

        $columnSearch = $request->input('columnSearch', []);
        if (is_array($columnSearch)) {
            if (! empty($columnSearch['email'])) {
                $q->where('email', 'ilike', '%'.$columnSearch['email'].'%');
            }
            if (! empty($columnSearch['role'])) {
                $roles = is_array($columnSearch['role']) ? $columnSearch['role'] : [$columnSearch['role']];
                $q->whereIn('role', $roles);
            }
            if (array_key_exists('status', $columnSearch) && $columnSearch['status'] !== '' && $columnSearch['status'] !== null) {
                $status = $columnSearch['status'];
                if (is_array($status)) {
                    $status = $status[0] ?? null;
                }
                if ($status !== null && $status !== '') {
                    $q->where('is_active', (bool) (int) $status);
                }
            }
        }

        $sortKey = (string) data_get($request->input('sortBy'), '0.key', 'email');
        $sortOrder = strtolower((string) data_get($request->input('sortBy'), '0.order', 'asc')) === 'desc' ? 'desc' : 'asc';
        $sortMap = [
            'email' => 'email',
            'role' => 'role',
            'status' => 'is_active',
        ];
        $q->orderBy($sortMap[$sortKey] ?? 'email', $sortOrder);

        $total = (clone $q)->count();
        $items = (clone $q)->skip(($page - 1) * $size)->take($size)->get();

        $rows = $items->map(fn (Staff $s) => $this->listRow($s))->values()->all();

        return response()->json([
            'status' => true,
            'data' => $rows,
            'pagination' => [
                'total' => $total,
            ],
        ]);
    }

    public function create(StoreStaffRequest $request): JsonResponse
    {
        if ($d = StaffAuthz::denyUnlessOwner()) {
            return $d;
        }

        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        if (! array_key_exists('is_active', $data)) {
            $data['is_active'] = true;
        }

        $s = Staff::on(config('tenancy.connection_name'))->create($data);
        CoreAudit::record(AuditEvent::ACTION_CREATE, 'staff', (int) $s->getKey(), newValues: $this->detail($s));

        return response()->json([
            'status' => true,
            'message' => 'Staff created.',
            'uuid' => (string) $s->getKey(),
            'data' => $this->detail($s),
        ], 201);
    }

    public function fetch(Request $request): JsonResponse
    {
        if ($d = StaffAuthz::denyUnlessOwner()) {
            return $d;
        }

        $id = (int) $request->query('id', $request->query('uuid', 0));
        $s = Staff::on(config('tenancy.connection_name'))->find($id);
        if ($s === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'user' => $this->detail($s),
            ],
        ]);
    }

    public function show(Request $request): JsonResponse
    {
        if ($d = StaffAuthz::denyUnlessOwner()) {
            return $d;
        }

        $id = (int) $request->query('uuid', $request->query('id', 0));
        $s = Staff::on(config('tenancy.connection_name'))->find($id);
        if ($s === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $this->detail($s),
        ]);
    }

    public function update(UpdateStaffRequest $request): JsonResponse
    {
        if ($d = StaffAuthz::denyUnlessOwner()) {
            return $d;
        }

        $id = (int) ($request->input('uuid') ?? $request->input('id') ?? 0);
        $s = Staff::on(config('tenancy.connection_name'))->find($id);
        if ($s === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        $old = $this->detail($s);
        $data = $request->validated();
        if (isset($data['password']) && $data['password'] !== '') {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // Map SPA `active` boolean if sent.
        if ($request->has('active') && ! array_key_exists('is_active', $data)) {
            $data['is_active'] = (bool) $request->input('active');
        }

        $s->fill($data)->save();
        CoreAudit::record(
            AuditEvent::ACTION_UPDATE,
            'staff',
            (int) $s->getKey(),
            oldValues: $old,
            newValues: $this->detail($s->fresh()),
        );

        return response()->json([
            'status' => true,
            'message' => 'Staff updated.',
            'uuid' => (string) $s->getKey(),
            'data' => $this->detail($s->fresh()),
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        if ($d = StaffAuthz::denyUnlessOwner()) {
            return $d;
        }

        $ids = $this->resolveIds($request);
        foreach ($ids as $id) {
            $s = Staff::on(config('tenancy.connection_name'))->find($id);
            if ($s === null) {
                continue;
            }
            $old = $this->detail($s);
            $s->delete();
            CoreAudit::record(AuditEvent::ACTION_UPDATE, 'staff', $id, oldValues: $old, reason: (string) $request->input('reason', 'deleted'));
        }

        return response()->json(['status' => true, 'message' => 'Staff deleted.']);
    }

    public function activate(Request $request): JsonResponse
    {
        return $this->setActive($request, true);
    }

    public function deactivate(Request $request): JsonResponse
    {
        return $this->setActive($request, false);
    }

    private function setActive(Request $request, bool $active): JsonResponse
    {
        if ($d = StaffAuthz::denyUnlessOwner()) {
            return $d;
        }

        foreach ($this->resolveIds($request) as $id) {
            $s = Staff::on(config('tenancy.connection_name'))->find($id);
            if ($s === null) {
                continue;
            }
            $old = $this->detail($s);
            $s->setAttribute('is_active', $active);
            $s->save();
            CoreAudit::record(
                AuditEvent::ACTION_UPDATE,
                'staff',
                $id,
                oldValues: $old,
                newValues: $this->detail($s),
                reason: (string) $request->input('reason', $active ? 'enabled' : 'disabled'),
            );
        }

        return response()->json([
            'status' => true,
            'message' => $active ? 'Staff enabled.' : 'Staff disabled.',
        ]);
    }

    /**
     * @return list<int>
     */
    private function resolveIds(Request $request): array
    {
        $raw = $request->input('uuids', $request->input('ids', []));
        if (! is_array($raw)) {
            $raw = [$raw];
        }

        return array_values(array_filter(array_map('intval', $raw)));
    }

    /**
     * eQMS listing row cell contract.
     *
     * @return array<string, mixed>
     */
    private function listRow(Staff $s): array
    {
        $active = (bool) $s->is_active;

        return [
            'uuid' => (string) $s->getKey(),
            'id' => (int) $s->getKey(),
            'actions' => [
                'writePermission' => true,
                'viewPermission' => true,
                'selectable' => true,
                'isLocked' => false,
            ],
            'email' => [
                'label' => (string) $s->email,
                'type' => 'text',
                'url' => '/users/view/'.$s->getKey(),
            ],
            'role' => [
                'label' => $s->roleDisplay(),
                'type' => 'text',
            ],
            'status' => [
                'label' => $active ? 'Enabled' : 'Disabled',
                'type' => 'status',
                'color' => $active ? 'success' : 'error',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function detail(Staff $s): array
    {
        $email = (string) $s->email;
        $roleCode = (string) $s->role;

        return [
            'id' => (int) $s->getKey(),
            'uuid' => (string) $s->getKey(),
            'email' => $email,
            'fullName' => explode('@', $email)[0] ?? $email,
            'firstName' => explode('@', $email)[0] ?? '',
            'lastName' => '',
            'roleCode' => $roleCode,
            'role_display' => $s->roleDisplay(),
            'is_active' => (bool) $s->is_active,
            'active' => (bool) $s->is_active,
            'lastLogin' => $s->last_login?->toIso8601String(),
            'createdAt' => $s->created_at?->toIso8601String(),
            'updatedAt' => null,
            'loginCount' => null,
            'deletedAt' => null,
            'role' => [
                'id' => 0,
                'name' => $roleCode,
                'roleName' => $s->roleDisplay(),
            ],
            'creator' => [
                'fullName' => '',
                'label' => '',
                'url' => '',
                'type' => 'text',
            ],
        ];
    }

    public function formInitialData(): JsonResponse
    {
        if ($d = StaffAuthz::denyUnlessOwner()) {
            return $d;
        }

        $roles = [];
        foreach (StaffRole::LABELS as $code => $label) {
            $roles[] = [
                'id' => $code,
                'name' => $code,
                'roleName' => $label,
            ];
        }

        return response()->json([
            'status' => true,
            'data' => [
                'roles' => $roles,
            ],
        ]);
    }
}
