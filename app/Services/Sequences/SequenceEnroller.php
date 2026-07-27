<?php

declare(strict_types=1);

namespace App\Services\Sequences;

use App\Enums\EnrollmentStatus;
use App\Enums\SequenceKind;
use App\Models\Account;
use App\Models\SequenceEnrollment;
use App\Support\Phone;
use Illuminate\Support\Carbon;

class SequenceEnroller
{
    /**
     * Enroll a phone number into a sequence and schedule its first step.
     * Returns null when the number is unusable or no active sequence exists.
     *
     * @param  array<string, string|null>  $context
     */
    public function enroll(
        Account $account,
        SequenceKind $kind,
        string $rawPhone,
        ?string $name,
        Carbon $baseAt,
        array $context = [],
        ?int $leadId = null,
    ): ?SequenceEnrollment {
        $phone = Phone::normalize($rawPhone);

        if ($phone === null) {
            return null;
        }

        $sequence = $account->sequenceFor($kind);
        $firstStep = $sequence?->steps()->orderBy('position')->first();

        if ($sequence === null || $firstStep === null) {
            return null;
        }

        return $account->sequenceEnrollments()->create([
            'sequence_id' => $sequence->id,
            'lead_id' => $leadId,
            'name' => $name,
            'phone' => $phone,
            'context' => $context ?: null,
            'base_at' => $baseAt,
            'current_step' => 0,
            'status' => EnrollmentStatus::Active,
            'next_run_at' => $baseAt->copy()->addMinutes($firstStep->delay_minutes),
        ]);
    }

    /**
     * Chase an invoice (contractors / freelancers). Reminders are scheduled
     * relative to the due date.
     */
    public function enrollInvoiceReminder(
        Account $account,
        ?string $name,
        string $phone,
        string $amount,
        Carbon $dueDate,
        string $payLink,
    ): ?SequenceEnrollment {
        return $this->enroll($account, SequenceKind::InvoiceReminder, $phone, $name, $dueDate, [
            'amount' => $amount,
            'pay_link' => $payLink,
            'due_date' => $dueDate->toFormattedDayDateString(),
        ]);
    }

    /**
     * Nurture a lead or past client (realtors). Scheduled from now.
     */
    public function enrollNurture(Account $account, ?string $name, string $phone, ?int $leadId = null): ?SequenceEnrollment
    {
        return $this->enroll($account, SequenceKind::Nurture, $phone, $name, now(), [], $leadId);
    }

    public function stop(SequenceEnrollment $enrollment): void
    {
        $enrollment->update([
            'status' => EnrollmentStatus::Stopped,
            'next_run_at' => null,
        ]);
    }
}
