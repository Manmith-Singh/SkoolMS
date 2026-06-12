<?php

namespace Database\Seeders;

use App\Models\Master\Tenant;
use App\Models\Master\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MasterSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Super-admin
        User::firstOrCreate(
            ['email' => 'superadmin@school.test'],
            [
                'tenant_id' => null,
                'name'      => 'Super Admin',
                'password'  => Hash::make('superadmin123'),
                'role'      => 'super_admin',
            ]
        );

        $this->command?->info('✓ Super-admin seeded: superadmin@school.test / superadmin123');
    }
}
