<?php

namespace Database\Seeders;

use App\Models\Master\Tenant;
use App\Models\Master\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(MasterSeeder::class);
    }
}
