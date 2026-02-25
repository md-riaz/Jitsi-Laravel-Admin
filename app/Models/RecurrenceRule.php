<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurrenceRule extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'meeting_id',
        'frequency',
        'interval',
        'count',
        'until_date',
        'by_day',
        'by_month_day',
        'exceptions',
    ];

    protected $casts = [
        'until_date' => 'immutable_datetime',
        'exceptions' => 'array',
    ];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }
}
