<?php

declare(strict_types=1);

namespace App\Livewire\Sequences;

use App\Enums\SequenceKind;
use App\Services\Sequences\SequenceEnroller;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class FollowUps extends Component
{
    public string $name = '';

    public string $phone = '';

    // Invoice-only fields (contractors / freelancers)
    public string $amount = '';

    public string $due_date = '';

    public string $pay_link = '';

    public function addInvoice(SequenceEnroller $enroller): void
    {
        $this->validate([
            'phone' => ['required', 'string'],
            'amount' => ['required', 'string', 'max:40'],
            'due_date' => ['required', 'date'],
            'pay_link' => ['nullable', 'string', 'max:255'],
        ]);

        $enrollment = $enroller->enrollInvoiceReminder(
            auth()->user()->account,
            $this->name ?: null,
            $this->phone,
            $this->amount,
            Carbon::parse($this->due_date),
            $this->pay_link ?: '(payment link)',
        );

        if ($enrollment === null) {
            $this->addError('phone', 'Could not start reminders (check the phone number).');

            return;
        }

        $this->reset('name', 'phone', 'amount', 'due_date', 'pay_link');
        session()->flash('status', 'Invoice reminders scheduled.');
    }

    public function startNurture(SequenceEnroller $enroller): void
    {
        $this->validate(['phone' => ['required', 'string']]);

        $enrollment = $enroller->enrollNurture(
            auth()->user()->account,
            $this->name ?: null,
            $this->phone,
        );

        if ($enrollment === null) {
            $this->addError('phone', 'Could not start follow-up (check the phone number).');

            return;
        }

        $this->reset('name', 'phone');
        session()->flash('status', 'Follow-up sequence started.');
    }

    public function stop(int $id, SequenceEnroller $enroller): void
    {
        $enrollment = auth()->user()->account->sequenceEnrollments()->find($id);

        if ($enrollment !== null) {
            $enroller->stop($enrollment);
            session()->flash('status', 'Sequence stopped.');
        }
    }

    public function render(): View
    {
        $account = auth()->user()->account;
        $isInvoice = $account->primarySequenceKind() === SequenceKind::InvoiceReminder;

        return view('livewire.sequences.follow-ups', [
            'account' => $account,
            'isInvoice' => $isInvoice,
            'enrollments' => $account->sequenceEnrollments()
                ->with('sequence')
                ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
                ->latest()
                ->limit(50)
                ->get(),
        ]);
    }
}
