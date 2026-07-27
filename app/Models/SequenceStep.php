<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MessageChannel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SequenceStep extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'delay_minutes' => 'integer',
            'channel' => MessageChannel::class,
            'is_active' => 'boolean',
        ];
    }

    /** @return BelongsTo<Sequence, $this> */
    public function sequence(): BelongsTo
    {
        return $this->belongsTo(Sequence::class);
    }
}
