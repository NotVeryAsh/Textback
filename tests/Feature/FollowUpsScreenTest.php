<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Sequences\FollowUps;
use App\Models\Account;
use App\Models\User;
use App\Services\Accounts\AccountSetup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class FollowUpsScreenTest extends TestCase
{
    use RefreshDatabase;

    public function test_contractor_sees_invoice_form_and_can_schedule(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create(['vertical' => 'contractor']);
        app(AccountSetup::class)->seedSequences($account);

        Livewire::actingAs($user)
            ->test(FollowUps::class)
            ->assertOk()
            ->assertSee('Invoice reminders')
            ->set('phone', '+14155551234')
            ->set('amount', '$500')
            ->set('due_date', now()->addDays(3)->toDateString())
            ->set('pay_link', 'https://pay.example/1')
            ->call('addInvoice')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('sequence_enrollments', [
            'account_id' => $account->id,
            'phone' => '+14155551234',
            'status' => 'active',
        ]);
    }

    public function test_realtor_sees_followup_form_and_can_start(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create(['vertical' => 'realtor']);
        app(AccountSetup::class)->seedSequences($account);

        Livewire::actingAs($user)
            ->test(FollowUps::class)
            ->assertOk()
            ->assertSee('Follow-ups')
            ->set('phone', '+14155559999')
            ->call('startNurture')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('sequence_enrollments', [
            'account_id' => $account->id,
            'phone' => '+14155559999',
            'status' => 'active',
        ]);
    }
}
