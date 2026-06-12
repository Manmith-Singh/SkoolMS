<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'group', 'key', 'value', 'type', 'description', 'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    public static function get(string $key, $default = null)
    {
        $row = static::where('key', $key)->first();

        if (! $row) {
            return $default;
        }

        return match ($row->type) {
            'boolean' => (bool) $row->value,
            'integer' => (int) $row->value,
            'json'    => json_decode($row->value ?? '[]', true),
            default   => $row->value,
        };
    }

    public static function put(string $key, $value, string $type = 'string', string $group = 'general'): void
    {
        $stored = match ($type) {
            'boolean' => $value ? '1' : '0',
            'json'    => json_encode($value),
            default   => (string) $value,
        };

        static::updateOrCreate(
            ['key' => $key],
            ['value' => $stored, 'type' => $type, 'group' => $group]
        );
    }
}
