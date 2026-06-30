<?php

namespace App\Models\Tenant\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class AcademicYearScope implements Scope
{
    protected static bool $enabled = true;

    public static function disable(): void
    {
        static::$enabled = false;
    }

    public static function enable(): void
    {
        static::$enabled = true;
    }

    public function apply(Builder $builder, Model $model): void
    {
        if (! static::$enabled) return;

        $yearId = session('current_academic_year_id');

        if (! $yearId) {
            $year = \App\Models\Tenant\AcademicYear::withoutGlobalScopes()
                ->where('is_active', true)->first();
            if ($year) {
                $yearId = $year->id;
                session(['current_academic_year_id' => $yearId]);
            }
        }

        if ($yearId) {
            $builder->where('academic_year_id', $yearId);
        }
    }
}
