<?php

namespace Database\Factories;

use App\Models\Master\Tenant;
use App\Models\Master\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'name'      => fake()->name(),
            'email'     => fake()->unique()->safeEmail(),
            'password'  => Hash::make('password'),
            'role'      => 'admin',
        ];
    }
}
