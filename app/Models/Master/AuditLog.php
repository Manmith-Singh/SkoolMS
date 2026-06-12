<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $connection = 'mysql';

    public $timestamps = false;

    protected $fillable = [
        'user_id', 'tenant_id', 'action', 'entity_type', 'entity_id',
        'metadata', 'ip_address', 'user_agent', 'created_at',
    ];

    protected $casts = [
        'metadata'   => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public static function record(string $action, $entity = null, array $metadata = []): void
    {
        $request = request();
        static::create([
            'user_id'     => auth()->id(),
            'tenant_id'   => $request?->attributes->get('currentTenant')?->id,
            'action'      => $action,
            'entity_type' => $entity ? get_class($entity) : null,
            'entity_id'   => $entity?->getKey(),
            'metadata'    => $metadata ?: null,
            'ip_address'  => $request?->ip(),
            'user_agent'  => substr($request?->userAgent() ?? '', 0, 512),
            'created_at'  => now(),
        ]);
    }
}
