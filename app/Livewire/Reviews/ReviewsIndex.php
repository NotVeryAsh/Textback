<?php

declare(strict_types=1);

namespace App\Livewire\Reviews;

use App\Services\Reviews\ReviewRequestService;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ReviewsIndex extends Component
{
    public string $single_name = '';

    public string $single_phone = '';

    public string $bulk = '';

    public int $delay_days = 0;

    public function sendSingle(ReviewRequestService $service): void
    {
        $this->validate(['single_phone' => ['required', 'string']]);

        $scheduledAt = $this->delay_days > 0 ? now()->addDays($this->delay_days) : null;

        $result = $service->queue(
            auth()->user()->account,
            $this->single_phone,
            $this->single_name ?: null,
            $scheduledAt,
        );

        if ($result === null) {
            $this->addError('single_phone', 'That phone number could not be read.');

            return;
        }

        $this->reset('single_name', 'single_phone');
        session()->flash('status', 'Review request queued.');
    }

    public function sendBulk(ReviewRequestService $service): void
    {
        $rows = [];

        foreach (preg_split('/\r\n|\r|\n/', trim($this->bulk)) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $parts = array_map('trim', explode(',', $line));

            if (count($parts) >= 2) {
                $rows[] = ['name' => $parts[0], 'phone' => $parts[1]];
            } else {
                $rows[] = ['phone' => $parts[0]];
            }
        }

        if ($rows === []) {
            $this->addError('bulk', 'Add at least one row.');

            return;
        }

        $scheduledAt = $this->delay_days > 0 ? now()->addDays($this->delay_days) : null;
        $count = $service->queueMany(auth()->user()->account, $rows, $scheduledAt);

        $this->reset('bulk');
        session()->flash('status', "{$count} review requests queued.");
    }

    public function render(): View
    {
        return view('livewire.reviews.reviews-index', [
            'account' => auth()->user()->account,
            'requests' => auth()->user()->account->reviewRequests()->latest()->limit(50)->get(),
        ]);
    }
}
