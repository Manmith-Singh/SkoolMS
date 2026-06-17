<?php

namespace App\Services;

use App\Models\Tenant\AcademicYear;
use App\Models\Tenant\ExamType;
use App\Models\Tenant\FeeCategory;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\Subject;
use Illuminate\Support\Facades\DB;

class AcademicYearDuplicationService
{
    public function duplicate(int $fromYearId, int $toYearId): array
    {
        return DB::connection('tenant')->transaction(function () use ($fromYearId, $toYearId) {
            $toYear = AcademicYear::findOrFail($toYearId);
            $stats = [];

            // 1. Duplicate classes (map old id → new id)
            $classIdMap = $this->duplicateClasses($fromYearId, $toYear);
            $stats['classes'] = count($classIdMap);

            // 2. Duplicate subjects + sync to new classes via subject_class pivot
            $stats['subjects'] = $this->duplicateSubjects($fromYearId, $toYear, $classIdMap);

            // 3. Duplicate fee categories
            $stats['fee_categories'] = $this->duplicateFeeCategories($fromYearId, $toYear);

            // 4. Duplicate exam types
            $stats['exam_types'] = $this->duplicateExamTypes($fromYearId, $toYear);

            return $stats;
        });
    }

    protected function duplicateClasses(int $fromYearId, AcademicYear $toYear): array
    {
        $map = [];
        $oldClasses = SchoolClass::withoutGlobalScopes()
            ->where('academic_year_id', $fromYearId)
            ->get();

        foreach ($oldClasses as $class) {
            $new = $class->replicate(['id', 'created_at', 'updated_at']);
            $new->academic_year_id = $toYear->id;
            $new->save();
            $map[$class->id] = $new->id;
        }

        return $map;
    }

    protected function duplicateSubjects(int $fromYearId, AcademicYear $toYear, array $classIdMap): int
    {
        $count = 0;
        $oldSubjects = Subject::withoutGlobalScopes()
            ->where('academic_year_id', $fromYearId)
            ->get();

        foreach ($oldSubjects as $subject) {
            $new = $subject->replicate(['id', 'created_at', 'updated_at']);
            $new->academic_year_id = $toYear->id;
            $new->save();

            // Sync to new classes
            $oldClassIds = $subject->classes()->pluck('classes.id')->toArray();
            $newClassIds = array_values(array_intersect_key($classIdMap, array_flip($oldClassIds)));
            if (! empty($newClassIds)) {
                $new->classes()->sync($newClassIds);
            }

            $count++;
        }

        return $count;
    }

    protected function duplicateFeeCategories(int $fromYearId, AcademicYear $toYear): int
    {
        $count = 0;
        $oldCategories = FeeCategory::withoutGlobalScopes()
            ->where('academic_year_id', $fromYearId)
            ->get();

        foreach ($oldCategories as $cat) {
            $new = $cat->replicate(['id', 'created_at', 'updated_at']);
            $new->academic_year_id = $toYear->id;
            $new->save();
            $count++;
        }

        return $count;
    }

    protected function duplicateExamTypes(int $fromYearId, AcademicYear $toYear): int
    {
        $count = 0;
        $oldTypes = ExamType::withoutGlobalScopes()
            ->where('academic_year_id', $fromYearId)
            ->get();

        foreach ($oldTypes as $type) {
            $new = $type->replicate(['id', 'created_at', 'updated_at']);
            $new->academic_year_id = $toYear->id;
            $new->save();
            $count++;
        }

        return $count;
    }
}
