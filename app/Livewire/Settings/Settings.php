<?php

declare(strict_types=1);

namespace App\Livewire\Settings;

use App\Enums\CallerIdMode;
use App\Enums\TemplateKind;
use App\Support\Phone;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Settings extends Component
{
    public string $business_name = '';

    public string $google_review_link = '';

    public string $operator_cell = '';

    public string $caller_id_mode = 'lead';

    public string $timezone = '';

    public string $quiet_hours_start = '';

    public string $quiet_hours_end = '';

    public string $tpl_missed = '';

    public string $tpl_review = '';

    public function mount(): void
    {
        $account = auth()->user()->account;

        $this->business_name = $account->business_name;
        $this->google_review_link = (string) $account->google_review_link;
        $this->operator_cell = (string) $account->operator_cell;
        $this->caller_id_mode = $account->caller_id_mode->value;
        $this->timezone = $account->timezone;
        $this->quiet_hours_start = $account->quiet_hours_start;
        $this->quiet_hours_end = $account->quiet_hours_end;
        $this->tpl_missed = $account->templateBody(TemplateKind::MissedCall);
        $this->tpl_review = $account->templateBody(TemplateKind::Review);
    }

    public function save(): void
    {
        $this->validate([
            'business_name' => ['required', 'string', 'max:120'],
            'caller_id_mode' => ['required', 'in:lead,textback,whisper'],
            'tpl_missed' => ['required', 'string', 'max:480'],
            'tpl_review' => ['required', 'string', 'max:480'],
        ]);

        $account = auth()->user()->account;

        $account->update([
            'business_name' => $this->business_name,
            'google_review_link' => $this->google_review_link ?: null,
            'operator_cell' => Phone::normalize($this->operator_cell) ?? $account->operator_cell,
            'caller_id_mode' => $this->caller_id_mode,
            'timezone' => $this->timezone ?: $account->timezone,
            'quiet_hours_start' => $this->quiet_hours_start ?: $account->quiet_hours_start,
            'quiet_hours_end' => $this->quiet_hours_end ?: $account->quiet_hours_end,
        ]);

        $account->templates()->updateOrCreate(
            ['kind' => TemplateKind::MissedCall->value],
            ['body' => $this->tpl_missed, 'is_active' => true],
        );
        $account->templates()->updateOrCreate(
            ['kind' => TemplateKind::Review->value],
            ['body' => $this->tpl_review, 'is_active' => true],
        );

        session()->flash('status', 'Settings saved.');
    }

    public function render(): View
    {
        return view('livewire.settings.settings', [
            'account' => auth()->user()->account,
            'callerModes' => CallerIdMode::cases(),
            'verticalLabel' => auth()->user()->account->vertical->label(),
        ]);
    }
}
