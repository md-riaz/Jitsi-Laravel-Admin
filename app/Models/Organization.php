<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'name',
        'slug',
        'require_jwt',
        'jwt_expiry_minutes',
    ];

    protected $casts = [
        'require_jwt' => 'boolean',
    ];

    protected $attributes = [
        'require_jwt' => false,
        'jwt_expiry_minutes' => 120,
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('role')->withTimestamps();
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class);
    }
}
