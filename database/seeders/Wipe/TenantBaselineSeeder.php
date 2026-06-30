<?php

namespace Database\Seeders\Wipe;

use App\Models\Tenant\ExamType;
use App\Models\Tenant\ExpenditureType;
use App\Models\Tenant\FeeCategory;
use App\Models\Tenant\IncomeType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantBaselineSeeder extends Seeder
{
    public function run(): void
    {
        $legacyId = DB::connection('tenant')->table('academic_years')->insertGetId([
            'year_label' => 'Legacy',
            'start_date' => '2020-01-01',
            'end_date'   => '2025-12-31',
            'is_active'  => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Assign all baseline seed data to Legacy year
        $examTypes = [
            ['name' => 'Midterm Exam',        'description' => 'Mid-term examinations'],
            ['name' => 'Final Exam',          'description' => 'End-of-term/year final examinations'],
            ['name' => 'Quiz',                'description' => 'Short quizzes and class tests'],
            ['name' => 'Term Exam',           'description' => 'Term-based examinations'],
            ['name' => 'Monthly Test',        'description' => 'Monthly assessment tests'],
            ['name' => 'Weekly Test',         'description' => 'Weekly assessment tests'],
            ['name' => 'Pre-Board Exam',      'description' => 'Pre-board / mock examinations'],
            ['name' => 'Practical Exam',      'description' => 'Practical / lab examinations'],
        ];

        foreach ($examTypes as $type) {
            ExamType::create($type + ['academic_year_id' => $legacyId]);
        }

        $feeCategories = [
            ['name' => 'Tuition Fee',     'default_amount' => 1500, 'frequency' => 'monthly',    'description' => 'Regular tuition fee'],
            ['name' => 'Transport Fee',   'default_amount' => 500,  'frequency' => 'monthly',    'description' => 'School bus / transport'],
            ['name' => 'Library Fee',     'default_amount' => 200,  'frequency' => 'annually',   'description' => 'Library & books'],
            ['name' => 'Examination Fee', 'default_amount' => 300,  'frequency' => 'quarterly',  'description' => 'Mid-term and final exams'],
            ['name' => 'Lab Fee',         'default_amount' => 250,  'frequency' => 'half_yearly','description' => 'Science / computer lab'],
            ['name' => 'Sports Fee',      'default_amount' => 150,  'frequency' => 'annually',   'description' => 'Sports & extracurriculars'],
            ['name' => 'Admission Fee',   'default_amount' => 2000, 'frequency' => 'one_time',   'description' => 'One-time admission charge'],
        ];

        foreach ($feeCategories as $cat) {
            FeeCategory::create($cat + ['is_active' => true, 'academic_year_id' => $legacyId]);
        }

        $incomeTypes = [
            'Tuition Fees', 'Transport Fees', 'Library Fees', 'Admission Fees',
            'Lab Fees', 'Sports Fees', 'Examination Fees', 'Government Grant',
            'Donations', 'Interest Income', 'Miscellaneous Income',
        ];

        foreach ($incomeTypes as $name) {
            IncomeType::create([
                'name' => $name, 'is_active' => true, 'academic_year_id' => $legacyId,
            ]);
        }

        $expenditureTypes = [
            'Salaries', 'Electricity Bills', 'Water Bills', 'Internet & Phone',
            'Maintenance & Repairs', 'Office Supplies', 'Transportation',
            'Events & Activities', 'Library Books', 'Laboratory Equipment',
            'Sports Equipment', 'Insurance', 'Taxes & Fees', 'Miscellaneous',
        ];

        foreach ($expenditureTypes as $name) {
            ExpenditureType::create([
                'name' => $name, 'is_active' => true, 'academic_year_id' => $legacyId,
            ]);
        }
    }
}
