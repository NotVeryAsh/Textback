<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\EnrollmentStatus;
use App\Enums\SequenceKind;
use App\Jobs\SendSms;
use App\Models\Account;
use App\Services\Accounts\AccountSetup;
use App\Services\Sequences\SequenceEnroller;
use App\Services\Sequences\SequenceRunner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SequenceEngineTest extends TestCase
{
    use RefreshDatabase;

    private function account(string $vertical = 'realtor'): Account
    {
        $account = Account::factory()->create(['vertical' => $vertical]);
        app(AccountSetup::class)->seedSequences($account);

        return $account->fresh();
    }

    public function test_seeding_creates_a_sequence_with_steps(): void
    {
        $account = $this->account('realtor');

        $this->assertDatabaseHas('sequences', ['account_id' => $account->id, 'kind' => 'nurture']);
        $this->assertSame(3, $account->sequenceFor(SequenceKind::Nurture)->steps()->count());
    }

    public function test_enroll_nurture_schedules_the_first_step(): void
    {
        Queue::fake();
        $account = $this->account('realtor');

        $enrollment = app(SequenceEnroller::class)->enrollNurture($account, 'Jane', '+14155551234');

        $this->assertNotNull($enrollment);
        $this->assertSame(EnrollmentStatus::Active, $enrollment->status);
        $this->assertSame(0, $enrollment->current_step);
        $this->assertTrue($enrollment->next_run_at->greaterThan(now()->addDays(2)));
    }

    public function test_enroll_invoice_sets_context_and_schedule_from_due_date(): void
    {
        Queue::fake();
        $account = $this->account('contractor');
        $due = now()->addDays(5)->startOfDay();

        $enrollment = app(SequenceEnroller::class)->enrollInvoiceReminder(
            $account, 'Bob', '+14155551234', '$500', $due, 'https://pay.example/abc'
        );

        $this->assertNotNull($enrollment);
        $this->assertSame('$500', $enrollment->context['amount']);
        $this->assertSame('https://pay.example/abc', $enrollment->context['pay_link']);
        // First reminder is 1 day after the due date.
        $this->assertEquals($due->copy()->addDay()->timestamp, $enrollment->next_run_at->timestamp);
    }

    public function test_runner_sends_the_due_step_and_advances(): void
    {
        Queue::fake();
        $account = $this->account('realtor');
        $enrollment = app(SequenceEnroller::class)->enrollNurture($account, 'Jane', '+14155551234');
        $enrollment->update(['next_run_at' => now()->subMinute()]);

        $sent = app(SequenceRunner::class)->runDue();

        $this->assertSame(1, $sent);
        $this->assertDatabaseHas('messages', [
            'account_id' => $account->id,
            'to' => '+14155551234',
            'direction' => 'out',
        ]);
        $enrollment->refresh();
        $this->assertSame(1, $enrollment->current_step);
        $this->assertSame(EnrollmentStatus::Active, $enrollment->status);
        Queue::assertPushed(SendSms::class);
    }

    public function test_runner_completes_after_the_last_step(): void
    {
        Queue::fake();
        $account = $this->account('realtor'); // 3 steps
        $enrollment = app(SequenceEnroller::class)->enrollNurture($account, 'Jane', '+14155551234');

        for ($i = 0; $i < 3; $i++) {
            $enrollment->update(['next_run_at' => now()->subMinute()]);
            app(SequenceRunner::class)->runDue();
            $enrollment->refresh();
        }

        $this->assertSame(EnrollmentStatus::Completed, $enrollment->status);
        $this->assertNull($enrollment->next_run_at);
    }

    public function test_stopping_an_enrollment_halts_it(): void
    {
        Queue::fake();
        $account = $this->account('realtor');
        $enrollment = app(SequenceEnroller::class)->enrollNurture($account, 'Jane', '+14155551234');

        app(SequenceEnroller::class)->stop($enrollment);
        $enrollment->refresh();

        $this->assertSame(EnrollmentStatus::Stopped, $enrollment->status);
        $this->assertNull($enrollment->next_run_at);

        $enrollment->update(['next_run_at' => now()->subMinute()]); // even if scheduled
        $this->assertSame(0, app(SequenceRunner::class)->runDue());
    }

    public function test_invoice_step_renders_context_tags(): void
    {
        Queue::fake();
        $account = $this->account('contractor');
        $due = now()->subDays(1)->startOfDay();
        $enrollment = app(SequenceEnroller::class)->enrollInvoiceReminder(
            $account, 'Bob', '+14155551234', '$750', $due, 'https://pay.example/xyz'
        );
        $enrollment->update(['next_run_at' => now()->subMinute()]);

        app(SequenceRunner::class)->runDue();

        $message = $account->messages()->latest()->first();
        $this->assertStringContainsString('$750', $message->body);
        $this->assertStringContainsString('https://pay.example/xyz', $message->body);
    }
}
