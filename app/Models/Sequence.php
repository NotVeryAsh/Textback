<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SequenceKind;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sequence extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'kind' => SequenceKind::class,
            'is_active' => 'boolean',
            'is_editable' => 'boolean',
        ];
    }

    /** @return BelongsTo<Account, $this> */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /** @return HasMany<SequenceStep, $this> */
    public function steps(): HasMany
    {
        return $this->hasMany(SequenceStep::class)->orderBy('position');
    }

    /** @return HasMany<SequenceEnrollment, $this> */
    public function enrollments(): HasMany
    {
        return $this->hasMany(SequenceEnrollment::class);
    }
}
