<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use HasinHayder\Tyro\Concerns\HasTyroRoles;
use HasinHayder\TyroLogin\Traits\HasTwoFactorAuth;



class User extends Authenticatable
{
    use HasApiTokens, HasTyroRoles, HasTwoFactorAuth;


    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'account_type',
        'status',
        'organization_id',
        'avatar_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's organization
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Check if user account is pending approval
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if user account is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if user has a single account type
     */
    public function isSingleUser(): bool
    {
        return $this->account_type === 'single';
    }

    /**
     * Check if user has an organization account type
     */
    public function isOrganizationUser(): bool
    {
        return $this->account_type === 'organization';
    }

    /**
     * Get the URL for the user's avatar
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if ($this->avatar_path) {
            return asset('storage/' . $this->avatar_path);
        }

        // Fallback to Gravatar
        return 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($this->email))) . '?d=mp&s=200';
    }

    /**
     * Get avatar URL for Jitsi integration
     */
    public function getJitsiAvatarUrl(): string
    {
        return $this->avatar_url;
    }
}
