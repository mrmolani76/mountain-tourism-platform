<?php

namespace Tests\Feature\Modules\Reports;

use Modules\Core\App\Models\TenantSettings;
use Tests\Support\TenancyTestCase;

class DashboardTest extends TenancyTestCase
{
    public function test_owner_can_load_today_dashboard_using_tenant_currency(): void
    {
        TenantSettings::on('tenant')->create([
            'currency' => 'CHF',
        ]);

        $this->actingAsStaff($this->owner)
            ->getJson('/api/reports/today')
            ->assertOk()
            ->assertJsonPath('currency', 'CHF')
            ->assertJsonPath('rentals_out.count', 0)
            ->assertJsonPath('open_folios.count', 0);
    }
}
