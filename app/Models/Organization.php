<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class Organization extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'name',
        'slug',
        'is_active',
        'require_jwt',
        'jwt_expiry_minutes',
        'subscription_plan_id',
        'subscription_starts_at',
        'subscription_ends_at',
        'subscription_status',
        'billing_notification_days',
        'logo_path',
        'primary_color',
        'secondary_color',
        'owner_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'require_jwt' => 'boolean',
        'subscription_starts_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
    ];

    protected $attributes = [
        'is_active' => true,
        'require_jwt' => true,
        'jwt_expiry_minutes' => 120,
        'subscription_status' => 'active',
        'billing_notification_days' => 5,
    ];

    /**
     * Check whether the subscription is expiring within the notification window.
     */
    public function isSubscriptionExpiringSoon(): bool
    {
        if (!$this->subscription_ends_at) {
            return false;
        }

        $days = (int) ($this->billing_notification_days ?? 5);

        return $this->subscription_ends_at->isFuture()
            && now()->diffInDays($this->subscription_ends_at) <= $days;
    }

    /**
     * Check whether the subscription has already expired.
     */
    public function isSubscriptionExpired(): bool
    {
        return $this->subscription_ends_at !== null && $this->subscription_ends_at->isPast();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('role')->withTimestamps();
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class);
    }

    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->owner_id !== null && (int) $this->owner_id === (int) $user->id;
    }

    /**
     * Get the organization's logo URL.
     */
    public function getLogoUrlAttribute(): ?string
    {
        if ($this->logo_path) {
            return asset('storage/' . $this->logo_path);
        }
        return null;
    }
}
