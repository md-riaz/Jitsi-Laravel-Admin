<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
    ];

    protected $casts = [
        'start_at' => 'immutable_datetime',
        'end_at' => 'immutable_datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Meeting $meeting): void {
            if (empty($meeting->room_name)) {
                $meeting->room_name = sprintf('mtg_%s', Str::lower(Str::random(12)));
            }
        });

        static::updating(function (Meeting $meeting): void {
            if ($meeting->isDirty('room_name')) {
                $meeting->room_name = $meeting->getOriginal('room_name');
            }
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
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

    public function canJoinAt(CarbonInterface $now): bool
    {
        $opensAt = $this->start_at->subMinutes($this->join_early_minutes);
        $closesAt = $this->end_at->addMinutes($this->join_late_minutes);

        return $now->betweenIncluded($opensAt, $closesAt);
    }
}
