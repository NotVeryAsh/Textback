<?php

declare(strict_types=1);

namespace App\Livewire\Leads;

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Services\Messaging\SmsSender;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class LeadsIndex extends Component
{
    public ?int $selectedLeadId = null;

    public string $reply = '';

    public string $filter = 'all';

    public function selectLead(int $leadId): void
    {
        $this->selectedLeadId = $leadId;
        $this->reply = '';
    }

    public function sendReply(SmsSender $sms): void
    {
        $this->validate(['reply' => ['required', 'string', 'max:480']]);

        $lead = $this->currentLead();

        if ($lead === null) {
            return;
        }

        $sms->send($lead->account, $lead->phone, $this->reply, $lead);
        $lead->update(['last_contacted_at' => now()]);

        $this->reply = '';
    }

    public function setStatus(string $status): void
    {
        $lead = $this->currentLead();
        $lead?->update(['status' => LeadStatus::from($status)]);
    }

    public function currentLead(): ?Lead
    {
        if ($this->selectedLeadId === null) {
            return null;
        }

        return auth()->user()->account
            ->leads()
            ->with('messages')
            ->find($this->selectedLeadId);
    }

    public function render(): View
    {
        $account = auth()->user()->account;

        $query = $account->leads()->latest('last_contacted_at');

        if ($this->filter !== 'all') {
            $query->where('status', $this->filter);
        }

        return view('livewire.leads.leads-index', [
            'leads' => $query->limit(100)->get(),
            'lead' => $this->currentLead(),
            'statuses' => LeadStatus::cases(),
        ]);
    }
}
