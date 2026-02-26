<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingEvent extends Model
{
    use HasFactory;
    use HasUuids;

    protected $appends = [
        'payload_preview',
    ];

    protected $fillable = [
        'meeting_id',
        'type',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function getPayloadPreviewAttribute(): string
    {
        $payload = $this->payload;

        if (is_array($payload) || is_object($payload)) {
            $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return $json ?: '';
        }

        return (string) ($payload ?? '');
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }
}
