<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UsageCounterFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsageCounter extends Model
{
    /** @use HasFactory<UsageCounterFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'sms_out' => 'integer',
            'sms_in' => 'integer',
            'call_minutes' => 'integer',
            'leads_recovered' => 'integer',
        ];
    }

    /** @return BelongsTo<Account, $this> */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
