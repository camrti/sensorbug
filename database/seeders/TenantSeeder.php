<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        Tenant::updateOrCreate(
            ['name' => 'Evolution Group'],
            [
                'is_enabled' => true,
                'is_system' => true,
            ]
        );

        Tenant::factory()->count(3)->create();
    }
}