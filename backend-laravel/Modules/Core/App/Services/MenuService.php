<?php

namespace Modules\Core\App\Services;

use Modules\Core\App\Enums\StaffRole;
use Modules\Core\App\Models\Staff;

/**
 * Builds the vertical nav from `config('menu.items')` (eQMS Complere pattern).
 * Filters by staff role. Owner sees every entry that names `owner` or has no role gate.
 */
class MenuService
{
    /**
     * @return array{menu: list<array<string, mixed>>, pinned_item_arr: list<string>}
     */
    public function forStaff(Staff $staff): array
    {
        $role = (string) $staff->getAttribute('role');
        $items = config('menu.items', []);
        $menu = [];

        foreach ($items as $item) {
            if (! $this->roleAllowed($item['allowed_roles'] ?? false, $role)) {
                continue;
            }

            $node = $this->createMenuItem($item);
            $children = [];

            foreach ($item['submenu_items'] ?? [] as $sub) {
                if (! $this->roleAllowed($sub['allowed_roles'] ?? false, $role)) {
                    continue;
                }
                $children[] = $this->createMenuItem($sub);
            }

            if ($children !== []) {
                $node['children'] = $children;
            }

            // Skip empty parent groups (no leaf and no page).
            if (($node['to'] === '' || $node['to'] === null) && empty($node['children'])) {
                continue;
            }

            $menu[] = $node;
        }

        return [
            'menu' => $menu,
            'pinned_item_arr' => [],
        ];
    }

    /**
     * @param  false|list<string>  $allowed
     */
    private function roleAllowed(false|array $allowed, string $role): bool
    {
        if ($allowed === false || $allowed === []) {
            return true;
        }

        if ($role === StaffRole::OWNER) {
            return true;
        }

        return in_array($role, $allowed, true);
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function createMenuItem(array $item): array
    {
        $page = $item['page'] ?? '';

        return [
            'title' => (string) ($item['label'] ?? ''),
            'icon' => ['icon' => (string) ($item['iconClass'] ?? 'tabler-circle')],
            'to' => $page !== '' ? ['path' => $page] : null,
            'disabled' => $page === '',
            'submodule' => (string) ($item['submodule'] ?? ''),
            'uniqueKey' => (string) ($item['uniqueKey'] ?? ''),
            'showPinned' => false,
            'show_pinned_icon' => false,
            'activeto' => '',
        ];
    }
}
