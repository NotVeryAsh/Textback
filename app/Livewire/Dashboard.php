<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Services\Billing\BillingService;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public function render(BillingService $billing): View
    {
        $account = auth()->user()->account;

        return view('livewire.dashboard', [
            'account' => $account,
            'leadsRecovered' => $account->leads_recovered_count,
            'reviewsSent' => $account->reviewRequests()->where('status', 'sent')->count(),
            'repliesCount' => $account->leads()->where('status', 'replied')->count(),
            'messagesSent' => $account->messages()->where('direction', 'out')->count(),
            'recentLeads' => $account->leads()->latest()->limit(6)->get(),
            'onTrial' => $billing->onTrial($account),
            'trialDaysLeft' => $billing->trialDaysLeft($account),
            'leadsRemaining' => $billing->leadsRemainingInTrial($account),
            'subscribed' => $billing->subscribed($account),
            'promptUpgrade' => $billing->shouldPromptUpgrade($account),
        ]);
    }
}
