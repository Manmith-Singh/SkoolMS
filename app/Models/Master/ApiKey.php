<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'name', 'key', 'secret_hash', 'scopes', 'is_active',
        'last_used_at', 'expires_at', 'created_by',
    ];

    protected $hidden = ['secret_hash'];

    protected $casts = [
        'scopes'       => 'array',
        'is_active'    => 'boolean',
        'last_used_at' => 'datetime',
        'expires_at'   => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generate(string $name, array $scopes = [], int $ttlDays = 365): array
    {
        $key    = 'skm_' . Str::random(32);
        $secret = Str::random(48);

        $apiKey = static::create([
            'name'        => $name,
            'key'         => $key,
            'secret_hash' => password_hash($secret, PASSWORD_BCRYPT),
            'scopes'      => $scopes,
            'is_active'   => true,
            'expires_at'  => now()->addDays($ttlDays),
            'created_by'  => auth()->id(),
        ]);

        // The plain-text secret is only returned once
        return [
            'id'     => $apiKey->id,
            'key'    => $key,
            'secret' => $secret,
        ];
    }
}
