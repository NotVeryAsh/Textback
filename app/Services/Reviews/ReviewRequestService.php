<?php

declare(strict_types=1);

namespace App\Services\Reviews;

use App\Jobs\SendReviewRequest;
use App\Models\Account;
use App\Models\ReviewRequest;
use App\Support\Phone;
use Illuminate\Support\Carbon;

class ReviewRequestService
{
    /**
     * Queue a single review request. Optionally delayed (best practice is to
     * send 1-2 days after the deal closes, when goodwill is highest).
     * Returns null when the phone number is unusable.
     */
    public function queue(Account $account, string $rawPhone, ?string $clientName = null, ?Carbon $scheduledAt = null): ?ReviewRequest
    {
        $phone = Phone::normalize($rawPhone);

        if ($phone === null) {
            return null;
        }

        $reviewRequest = $account->reviewRequests()->create([
            'client_name' => $clientName,
            'phone' => $phone,
            'status' => 'queued',
            'scheduled_at' => $scheduledAt,
        ]);

        $job = SendReviewRequest::dispatch($reviewRequest->id);

        if ($scheduledAt !== null) {
            $job->delay($scheduledAt);
        }

        return $reviewRequest;
    }

    /**
     * Queue review requests for many rows at once (bulk import).
     *
     * @param  array<int, array{phone: string, name?: string|null}>  $rows
     * @return int number of requests queued
     */
    public function queueMany(Account $account, array $rows, ?Carbon $scheduledAt = null): int
    {
        $queued = 0;

        foreach ($rows as $row) {
            $result = $this->queue($account, $row['phone'], $row['name'] ?? null, $scheduledAt);

            if ($result !== null) {
                $queued++;
            }
        }

        return $queued;
    }
}
