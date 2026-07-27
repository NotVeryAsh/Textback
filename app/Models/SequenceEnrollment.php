<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EnrollmentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SequenceEnrollment extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'context' => 'array',
            'base_at' => 'datetime',
            'current_step' => 'integer',
            'status' => EnrollmentStatus::class,
            'next_run_at' => 'datetime',
            'last_sent_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Account, $this> */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /** @return BelongsTo<Sequence, $this> */
    public function sequence(): BelongsTo
    {
        return $this->belongsTo(Sequence::class);
    }

    /** @return BelongsTo<Lead, $this> */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function isActive(): bool
    {
        return $this->status === EnrollmentStatus::Active;
    }

    public function displayName(): string
    {
        return $this->name ?: $this->phone;
    }
}
