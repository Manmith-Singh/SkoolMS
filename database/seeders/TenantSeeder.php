<?php

namespace Database\Seeders;

use App\Models\Tenant\FeeCategory;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['name' => 'Tuition Fee',     'default_amount' => 1500, 'frequency' => 'monthly',    'description' => 'Regular tuition fee'],
            ['name' => 'Transport Fee',   'default_amount' => 500,  'frequency' => 'monthly',    'description' => 'School bus / transport'],
            ['name' => 'Library Fee',     'default_amount' => 200,  'frequency' => 'annually',   'description' => 'Library & books'],
            ['name' => 'Examination Fee', 'default_amount' => 300,  'frequency' => 'quarterly',  'description' => 'Mid-term and final exams'],
            ['name' => 'Lab Fee',         'default_amount' => 250,  'frequency' => 'half_yearly','description' => 'Science / computer lab'],
            ['name' => 'Sports Fee',      'default_amount' => 150,  'frequency' => 'annually',   'description' => 'Sports & extracurriculars'],
            ['name' => 'Admission Fee',   'default_amount' => 2000, 'frequency' => 'one_time',   'description' => 'One-time admission charge'],
        ];

        foreach ($defaults as $cat) {
            FeeCategory::firstOrCreate(
                ['name' => $cat['name']],
                $cat + ['is_active' => true]
            );
        }
    }
}
