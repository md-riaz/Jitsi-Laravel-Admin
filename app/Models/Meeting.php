<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class Meeting extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'organization_id',
        'created_by',
        'title',
        'description',
        'start_at',
        'end_at',
        'timezone',
        'join_early_minutes',
        'join_late_minutes',
        'visibility',
        'status',
        'password',
        'lobby_enabled',
        'allow_guests',
        'max_participants',
        'allowed_ips',
        'ip_restriction_enabled',
    ];

    protected $casts = [
        'start_at' => 'immutable_datetime',
        'end_at' => 'immutable_datetime',
        'lobby_enabled' => 'boolean',
        'allow_guests' => 'boolean',
        'ip_restriction_enabled' => 'boolean',
    ];

    protected $attributes = [
        'timezone' => 'UTC',
        'join_early_minutes' => 10,
        'join_late_minutes' => 60,
        'visibility' => 'invite_only',
        'status' => 'scheduled',
        'lobby_enabled' => true,
        'allow_guests' => false,
        'ip_restriction_enabled' => false,
    ];

    protected static function booted(): void
    {
        static::creating(function (Meeting $meeting): void {
            $meeting->applyVisibilityGuestPolicy();

            if (empty($meeting->room_name)) {
                $meeting->room_name = sprintf('mtg_%s', Str::lower(Str::random(12)));
            }

            // Hash password if provided
            if (!empty($meeting->password)) {
                $meeting->password = Hash::make($meeting->password);
            }
        });

        static::updating(function (Meeting $meeting): void {
            $meeting->applyVisibilityGuestPolicy();

            if ($meeting->isDirty('room_name')) {
                $meeting->room_name = $meeting->getOriginal('room_name');
            }

            // Hash password if changed and not already hashed
            if ($meeting->isDirty('password') && !empty($meeting->password)) {
                // Only hash if it doesn't look like a bcrypt hash already
                if (!str_starts_with($meeting->password, '$2y$')) {
                    $meeting->password = Hash::make($meeting->password);
                }
            }
        });
    }

    public static function normalizeVisibility(?string $visibility): string
    {
        return match ($visibility) {
            'organization' => 'org_only',
            'org' => 'org_only',
            'public_link' => 'link_anyone',
            null, '' => 'invite_only',
            default => $visibility,
        };
    }

    public function applyVisibilityGuestPolicy(): void
    {
        $this->visibility = self::normalizeVisibility($this->visibility);
        $this->allow_guests = $this->visibility === 'link_anyone';
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class)->withDefault();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(MeetingParticipant::class);
    }

    public function invites(): HasMany
    {
        return $this->hasMany(MeetingInvite::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(MeetingEvent::class);
    }

    public function recurrenceRule(): HasOne
    {
        return $this->hasOne(RecurrenceRule::class);
    }

    public function canJoinAt(CarbonInterface $now): bool
    {
        // Instant meetings (no start/end time) can always be joined if status is live
        if ($this->start_at === null || $this->end_at === null) {
            return $this->status === 'live';
        }

        $opensAt = $this->start_at->subMinutes($this->join_early_minutes);
        $closesAt = $this->end_at->addMinutes($this->join_late_minutes);

        return $now->betweenIncluded($opensAt, $closesAt);
    }

    public function isInstantMeeting(): bool
    {
        return $this->start_at === null || $this->end_at === null;
    }

    public function verifyPassword(?string $password): bool
    {
        if (empty($this->password)) {
            return true; // No password required
        }

        if (empty($password)) {
            return false; // Password required but not provided
        }

        return Hash::check($password, $this->password);
    }

    public function getAllowedIps(): array
    {
        if (empty($this->allowed_ips)) {
            return [];
        }

        return array_filter(array_map('trim', explode("\n", $this->allowed_ips)));
    }

    public function isIpAllowed(string $ip): bool
    {
        if (!$this->ip_restriction_enabled) {
            return true;
        }

        $allowedIps = $this->getAllowedIps();
        if (empty($allowedIps)) {
            return true;
        }

        foreach ($allowedIps as $allowedIp) {
            if ($this->ipMatchesCidr($ip, $allowedIp)) {
                return true;
            }
        }

        return false;
    }

    private function ipMatchesCidr(string $ip, string $cidr): bool
    {
        // If no CIDR notation, do exact match
        if (!str_contains($cidr, '/')) {
            return $ip === $cidr;
        }

        [$subnet, $bits] = explode('/', $cidr);
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - $bits);
        $subnet &= $mask;

        return ($ip & $mask) === $subnet;
    }
}
