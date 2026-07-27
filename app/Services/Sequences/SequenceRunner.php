<?php

declare(strict_types=1);

namespace App\Services\Sequences;

use App\Enums\EnrollmentStatus;
use App\Models\SequenceEnrollment;
use App\Services\Messaging\SmsSender;
use App\Services\Templates\TemplateRenderer;

class SequenceRunner
{
    public function __construct(
        private TemplateRenderer $renderer,
        private SmsSender $sms,
    ) {}

    /**
     * Send every due sequence step and advance the enrollment. Returns the
     * number of steps sent.
     */
    public function runDue(int $limit = 200): int
    {
        $due = SequenceEnrollment::query()
            ->with(['sequence.steps', 'account', 'lead'])
            ->where('status', EnrollmentStatus::Active->value)
            ->whereNotNull('next_run_at')
            ->where('next_run_at', '<=', now())
            ->limit($limit)
            ->get();

        $sent = 0;

        foreach ($due as $enrollment) {
            if ($this->process($enrollment)) {
                $sent++;
            }
        }

        return $sent;
    }

    public function process(SequenceEnrollment $enrollment): bool
    {
        $steps = $enrollment->sequence->steps;
        $step = $steps->firstWhere('position', $enrollment->current_step);

        if ($step === null) {
            $enrollment->update(['status' => EnrollmentStatus::Completed, 'next_run_at' => null]);

            return false;
        }

        $didSend = false;

        if ($step->is_active) {
            $body = $this->renderer->render($step->body, $enrollment->account, $enrollment->context ?? []);
            $this->sms->send($enrollment->account, $enrollment->phone, $body, $enrollment->lead, $step->media_url);
            $didSend = true;
        }

        $nextPosition = $enrollment->current_step + 1;
        $nextStep = $steps->firstWhere('position', $nextPosition);

        if ($nextStep !== null) {
            $enrollment->update([
                'current_step' => $nextPosition,
                'last_sent_at' => now(),
                'next_run_at' => $enrollment->base_at->copy()->addMinutes($nextStep->delay_minutes),
            ]);
        } else {
            $enrollment->update([
                'current_step' => $nextPosition,
                'last_sent_at' => now(),
                'status' => EnrollmentStatus::Completed,
                'next_run_at' => null,
            ]);
        }

        return $didSend;
    }
}
