<?php

namespace App\Models\Master;

use App\Services\TenantDatabaseManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Tenant extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'name',
        'subdomain',
        'db_name',
        'db_username',
        'db_password',
        'status',
        'contact_email',
        'contact_phone',
        'address',
        'plan_id',
        'subscription_ends_at',
        'trial_ends_at',
    ];

    protected $hidden = [
        'db_password',
    ];

    protected $casts = [
        'plan_id'             => 'integer',
        'subscription_ends_at' => 'datetime',
        'trial_ends_at'       => 'datetime',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function plan(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function subscriptions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isExpired(): bool
    {
        return $this->subscription_ends_at && $this->subscription_ends_at->isPast();
    }

    /**
     * Re-activate a suspended tenant and extend subscription by the plan's billing period.
     * Returns true if reactivated, false if no plan is assigned.
     */
    public function reactivateAfterPayment(): bool
    {
        if (! $this->plan) {
            return false;
        }

        $extension = match ($this->plan->billing_period) {
            'monthly'     => now()->addMonth(),
            'quarterly'   => now()->addMonths(3),
            'half_yearly'=> now()->addMonths(6),
            'yearly'      => now()->addYear(),
            'one_time'    => now()->addYear(5),
            default       => now()->addMonth(),
        };

        $this->update([
            'status'              => 'active',
            'subscription_ends_at' => $extension,
        ]);

        Cache::forget('tenant:subdomain:' . $this->subdomain);

        return true;
    }

    public function url(): string
    {
        // Build the URL from the current request so the port is preserved
        // automatically.  Falls back to the static config if no request
        // is bound (e.g. in CLI / seeder contexts).
        $request = app()->bound('request') ? request() : null;

        $scheme = $request?->getScheme()
            ?? (parse_url(config('app.url'), PHP_URL_SCHEME) ?: 'http');

        $base = config('tenancy.app_domain');

        $url = "{$scheme}://{$this->subdomain}.{$base}";

        if ($request) {
            $port = $request->getPort();
            $isDefault = ($scheme === 'https' && $port === 443)
                      || ($scheme === 'http'  && $port === 80);

            if (! $isDefault) {
                $url .= ":{$port}";
            }
        }

        return $url;
    }
}
