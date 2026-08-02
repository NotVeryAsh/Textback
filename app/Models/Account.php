<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CallerIdMode;
use App\Enums\SequenceKind;
use App\Enums\TemplateKind;
use App\Enums\Vertical;
use Database\Factories\AccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    /** @use HasFactory<AccountFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'vertical' => Vertical::class,
            'caller_id_mode' => CallerIdMode::class,
            'operator_cell_verified_at' => 'datetime',
            'is_live' => 'boolean',
            'setup_dismissed' => 'boolean',
            'leads_recovered_count' => 'integer',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<PhoneNumber, $this> */
    public function phoneNumbers(): HasMany
    {
        return $this->hasMany(PhoneNumber::class);
    }

    /** @return HasMany<Lead, $this> */
    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    /** @return HasMany<Message, $this> */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /** @return HasMany<ReviewRequest, $this> */
    public function reviewRequests(): HasMany
    {
        return $this->hasMany(ReviewRequest::class);
    }

    /** @return HasMany<Template, $this> */
    public function templates(): HasMany
    {
        return $this->hasMany(Template::class);
    }

    /** @return HasMany<UsageCounter, $this> */
    public function usageCounters(): HasMany
    {
        return $this->hasMany(UsageCounter::class);
    }

    /** @return HasMany<Sequence, $this> */
    public function sequences(): HasMany
    {
        return $this->hasMany(Sequence::class);
    }

    /** @return HasMany<SequenceEnrollment, $this> */
    public function sequenceEnrollments(): HasMany
    {
        return $this->hasMany(SequenceEnrollment::class);
    }

    public function sequenceFor(SequenceKind $kind): ?Sequence
    {
        return $this->sequences()->where('kind', $kind->value)->where('is_active', true)->first();
    }

    /**
     * The pillar-3 sequence kind this vertical uses.
     */
    public function primarySequenceKind(): SequenceKind
    {
        return $this->vertical === Vertical::Realtor
            ? SequenceKind::Nurture
            : SequenceKind::InvoiceReminder;
    }

    public function template(TemplateKind $kind): ?Template
    {
        return $this->templates->firstWhere('kind', $kind);
    }

    public function templateBody(TemplateKind $kind): string
    {
        $template = $this->template($kind);

        if ($template !== null) {
            return $template->body;
        }

        return $this->vertical->defaultTemplates()[$kind->value] ?? '';
    }

    /**
     * Name used for the {{agent}} merge tag (the account owner).
     */
    public function agentName(): string
    {
        return $this->user?->name ?? $this->business_name;
    }

    public function isOnboarded(): bool
    {
        return $this->onboarding_step === 'done';
    }

    public function operatorCellVerified(): bool
    {
        return $this->operator_cell_verified_at !== null;
    }

    public function hasReviewLink(): bool
    {
        return filled($this->google_review_link);
    }

    public function hasCapturedLead(): bool
    {
        return $this->leads_recovered_count > 0;
    }

    public function smsPending(): bool
    {
        return $this->a2p_status === 'pending';
    }

    /**
     * Has the account hit the "free until it works" trial lead cap?
     */
    public function leadCapReached(): bool
    {
        return $this->leads_recovered_count >= (int) config('textback.trial_lead_cap');
    }

    public function currentUsage(): UsageCounter
    {
        return $this->usageCounters()->firstOrCreate([
            'period' => now()->format('Y-m'),
        ]);
    }
}
