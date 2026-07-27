<?php

declare(strict_types=1);

namespace App\Services\Leads;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Enums\TemplateKind;
use App\Models\Account;
use App\Models\Lead;
use App\Services\Messaging\SmsSender;
use App\Services\Templates\TemplateRenderer;
use App\Support\Phone;

class MissedCallHandler
{
    public function __construct(
        private TemplateRenderer $renderer,
        private SmsSender $sms,
    ) {}

    /**
     * Handle a missed call: capture/refresh the lead and text them back.
     * Returns the Lead, or null when the caller number is unusable or the
     * caller is the operator's own phone.
     */
    public function handle(Account $account, ?string $rawCaller): ?Lead
    {
        $caller = Phone::normalize($rawCaller);

        if ($caller === null) {
            return null;
        }

        // Never text the operator back if they call their own Textback number.
        if ($caller === Phone::normalize($account->operator_cell)) {
            return null;
        }

        $existing = $account->leads()->where('phone', $caller)->first();

        // Dedupe: if we already texted this caller very recently, do nothing.
        $dedupeMinutes = (int) config('textback.dedupe_minutes');
        if ($existing?->last_contacted_at?->gt(now()->subMinutes($dedupeMinutes))) {
            return $existing;
        }

        $isNew = $existing === null;

        $lead = $existing ?? new Lead([
            'account_id' => $account->id,
            'phone' => $caller,
            'source' => LeadSource::MissedCall,
        ]);

        $lead->status = LeadStatus::TextedBack;
        $lead->last_contacted_at = now();
        $lead->save();

        if ($isNew) {
            $account->increment('leads_recovered_count');
            $account->currentUsage()->increment('leads_recovered');
        }

        $body = $this->renderer->render(
            $account->templateBody(TemplateKind::MissedCall),
            $account,
        );

        $this->sms->send($account, $caller, $body, $lead);

        return $lead->refresh();
    }
}
