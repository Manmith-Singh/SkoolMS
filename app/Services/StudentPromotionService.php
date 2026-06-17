<?php

namespace App\Services;

use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\Student;
use App\Models\Tenant\StudentEnrollment;
use Illuminate\Support\Facades\DB;

class StudentPromotionService
{
    public function promote(int $fromYearId, int $toYearId): array
    {
        return DB::connection('tenant')->transaction(function () use ($fromYearId, $toYearId) {
            $stats = [
                'promoted' => 0,
                'graduated' => 0,
                'no_next_class' => 0,
                'skipped' => 0,
            ];

            $enrollments = StudentEnrollment::where('academic_year_id', $fromYearId)
                ->where('status', 'active')
                ->with(['student', 'schoolClass'])
                ->get();

            foreach ($enrollments as $enrollment) {
                $student = $enrollment->student;
                $currentClass = $enrollment->schoolClass;

                if (! $currentClass) {
                    $stats['skipped']++;
                    continue;
                }

                if ($currentClass->is_final_year) {
                    // Mark as graduated — create enrollment for new year with graduated status
                    StudentEnrollment::create([
                        'student_id'        => $student->id,
                        'class_id'          => null,
                        'academic_year_id'  => $toYearId,
                        'roll_no'           => null,
                        'status'            => 'graduated',
                    ]);
                    $stats['graduated']++;
                    continue;
                }

                // Find next class (same name+1, same section)
                $nextClass = $this->findNextClass($currentClass, $toYearId);

                if (! $nextClass) {
                    $stats['no_next_class']++;
                    continue;
                }

                // Create enrollment for new year
                StudentEnrollment::create([
                    'student_id'        => $student->id,
                    'class_id'          => $nextClass->id,
                    'academic_year_id'  => $toYearId,
                    'roll_no'           => $student->roll_no,
                    'status'            => 'active',
                ]);

                // Update student's current class
                $student->update(['class_id' => $nextClass->id]);

                $stats['promoted']++;
            }

            return $stats;
        });
    }

    protected function findNextClass(SchoolClass $currentClass, int $toYearId): ?SchoolClass
    {
        $nextName = $this->incrementClassName($currentClass->name);
        if (! $nextName) return null;

        return SchoolClass::withoutGlobalScopes()
            ->where('academic_year_id', $toYearId)
            ->where('name', $nextName)
            ->where('section', $currentClass->section)
            ->first();
    }

    protected function incrementClassName(string $name): ?string
    {
        // Try numeric classes: "Class 5", "Grade 10", "5", etc.
        if (preg_match('/^(\D*?)(\d+)$/', $name, $m)) {
            $prefix = $m[1];
            $num = (int) $m[2] + 1;
            return $prefix . $num;
        }

        // Try roman numerals: "I", "II", "III", "IV", "V", etc.
        $romanMap = ['I' => 1, 'II' => 2, 'III' => 3, 'IV' => 4, 'V' => 5,
                     'VI' => 6, 'VII' => 7, 'VIII' => 8, 'IX' => 9, 'X' => 10,
                     'XI' => 11, 'XII' => 12];
        $romanRev = array_flip($romanMap);

        $trimmed = trim($name);
        if (isset($romanMap[$trimmed])) {
            $nextVal = $romanMap[$trimmed] + 1;
            return $romanRev[$nextVal] ?? null;
        }

        // If class name has a roman suffix like "Class XII"
        if (preg_match('/^(.+?)\s+(' . implode('|', array_keys($romanMap)) . ')$/', $name, $m)) {
            $prefix = trim($m[1]);
            $val = $romanMap[$m[2]];
            $nextVal = $val + 1;
            $nextRoman = $romanRev[$nextVal] ?? null;
            return $nextRoman ? "$prefix $nextRoman" : null;
        }

        // Can't parse — skip
        return null;
    }
}
