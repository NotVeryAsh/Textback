<?php

declare(strict_types=1);

namespace App\Livewire\Onboarding;

use App\Enums\TemplateKind;
use App\Enums\Vertical;
use App\Models\Account;
use App\Services\Accounts\AccountSetup;
use App\Services\Twilio\NumberProvisioner;
use App\Support\Phone;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Slim 3-step onboarding (business -> number -> go live). Everything else
 * (verify phone, review link, templates, test, billing) is deferred to the
 * dashboard setup checklist. Research: forcing verification/extra steps up
 * front drives heavy drop-off; get to value fast, finish the rest later.
 */
#[Layout('layouts.app')]
class Wizard extends Component
{
    private const STEPS = ['business', 'number', 'activate'];

    public ?Account $account = null;

    public string $step = 'business';

    public string $business_name = '';

    public string $vertical = 'realtor';

    public string $area_code = '';

    public string $manual_number = '';

    public string $operator_cell = '';

    public function mount(): void
    {
        $this->account = auth()->user()->account;

        if ($this->account === null) {
            return;
        }

        if ($this->account->isOnboarded()) {
            $this->redirectRoute('dashboard', navigate: true);

            return;
        }

        $this->business_name = $this->account->business_name;
        $this->vertical = $this->account->vertical->value;
        // Prefill from the account, falling back to the mobile captured at signup.
        $this->operator_cell = (string) ($this->account->operator_cell ?? auth()->user()->phone);

        // Map any legacy step onto the new 3-step flow.
        $this->step = in_array($this->account->onboarding_step, self::STEPS, true)
            ? $this->account->onboarding_step
            : ($this->account->twilio_number ? 'activate' : 'number');
    }

    public function saveBusiness(AccountSetup $setup): void
    {
        $this->validate([
            'business_name' => ['required', 'string', 'max:120'],
            'vertical' => ['required', 'in:realtor,contractor,freelancer'],
        ]);

        if ($this->account === null) {
            $this->account = $setup->create(auth()->user(), $this->business_name, Vertical::from($this->vertical));
        } else {
            $this->account->update([
                'business_name' => $this->business_name,
                'vertical' => $this->vertical,
            ]);
            $setup->seedTemplates($this->account);
            $setup->seedSequences($this->account);
        }

        $this->goto('number');
    }

    public function provisionNumber(NumberProvisioner $provisioner): void
    {
        try {
            $provisioner->provision($this->account, $this->area_code ?: null);
            // Number + voice are live instantly; SMS waits on A2P registration,
            // which happens in the background and does not block onboarding.
            $this->account->update(['a2p_status' => 'pending']);
            $this->account->refresh();
            $this->goto('activate');
        } catch (\Throwable $e) {
            $this->addError('area_code', 'Could not provision a number: '.$e->getMessage());
        }
    }

    public function saveManualNumber(NumberProvisioner $provisioner): void
    {
        $e164 = Phone::normalize($this->manual_number);

        if ($e164 === null) {
            $this->addError('manual_number', 'Enter a valid phone number.');

            return;
        }

        $provisioner->attachManual($this->account, $e164);
        $this->account->refresh();
        $this->goto('activate');
    }

    public function finishSetup(): void
    {
        $cell = Phone::normalize($this->operator_cell);

        if ($cell === null) {
            $this->addError('operator_cell', 'Enter a valid mobile number so calls can forward to you.');

            return;
        }

        $this->account->update([
            'operator_cell' => $cell,
            'onboarding_step' => 'done',
            'is_live' => true,
        ]);

        $this->redirectRoute('dashboard', navigate: true);
    }

    public function goto(string $step): void
    {
        $this->step = $step;

        if ($this->account !== null) {
            $this->account->update(['onboarding_step' => $step]);
        }
    }

    public function render(): View
    {
        return view('livewire.onboarding.wizard', [
            'verticals' => Vertical::options(),
            'twilioConfigured' => filled(config('services.twilio.sid')),
            'reviewTemplate' => $this->account?->templateBody(TemplateKind::MissedCall),
        ]);
    }
}
