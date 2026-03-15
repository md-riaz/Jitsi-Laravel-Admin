<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'billing_cycle',
        'max_users',
        'max_meeting_duration',
        'recording_storage_gb',
        'concurrent_meetings',
        'is_active',
        'trial_days',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'max_users' => 'integer',
        'max_meeting_duration' => 'integer',
        'recording_storage_gb' => 'integer',
        'concurrent_meetings' => 'integer',
        'trial_days' => 'integer',
    ];

    public function organizations(): HasMany
    {
        return $this->hasMany(Organization::class, 'subscription_plan_id');
    }
}
