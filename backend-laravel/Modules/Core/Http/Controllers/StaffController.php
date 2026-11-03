<?php

namespace Modules\Core\Http\Controllers;

use App\Support\Http\Envelope;
use App\Support\Http\Paginates;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Models\Staff;

class StaffController
{
    use Paginates;

    private function ensureOwner(): ?JsonResponse
    {
        if (! app('staff')->isOwner()) {
            return Envelope::errorJson('error', 'You do not have permission to perform this action.', [], 403);
        }
        return null;
    }

    public function index(Request $request): JsonResponse
    {
        if ($d = $this->ensureOwner()) return $d;
        $q = Staff::on(config('tenancy.connection_name'))->orderBy('email');
        return $this->paginateQuery($q, $request, fn (Staff $s) => $this->serialize($s));
    }

    public function store(Request $request): JsonResponse
    {
        if ($d = $this->ensureOwner()) return $d;
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $data['password'] = Hash::make($data['password']);
        $s = Staff::on(config('tenancy.connection_name'))->create($data);
        return response()->json($this->serialize($s), 201);
    }

    public function show(int $staff): JsonResponse
    {
        if ($d = $this->ensureOwner()) return $d;
        $s = Staff::on(config('tenancy.connection_name'))->findOrFail($staff);
        return response()->json($this->serialize($s));
    }

    public function update(Request $request, int $staff): JsonResponse
    {
        if ($d = $this->ensureOwner()) return $d;
        $s = Staff::on(config('tenancy.connection_name'))->findOrFail($staff);
        $data = $request->validate([
            'email' => ['sometimes', 'email'],
            'password' => ['sometimes', 'string', 'min:8'],
            'role' => ['sometimes', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        $s->fill($data)->save();
        return response()->json($this->serialize($s));
    }

    public function destroy(int $staff): JsonResponse
    {
        if ($d = $this->ensureOwner()) return $d;
        Staff::on(config('tenancy.connection_name'))->findOrFail($staff)->delete();
        return response()->json(null, 204);
    }

    private function serialize(Staff $s): array
    {
        return [
            'id' => $s->getKey(),
            'email' => $s->email,
            'role' => $s->role,
            'role_display' => $s->roleDisplay(),
            'is_active' => (bool) $s->is_active,
        ];
    }
}
