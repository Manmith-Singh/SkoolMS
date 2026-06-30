<?php

namespace App\Models\Tenant\Traits;

use App\Models\Tenant\AcademicYear;

trait HasAcademicYear
{
    protected static function bootHasAcademicYear(): void
    {
        static::creating(function ($model) {
            if (is_null($model->academic_year_id)) {
                $yearId = session('current_academic_year_id');

                if (! $yearId) {
                    $year = AcademicYear::withoutGlobalScopes()
                        ->where('is_active', true)
                        ->first();
                    $yearId = $year?->id;
                }

                $model->academic_year_id = $yearId;
            }
        });
    }
}
