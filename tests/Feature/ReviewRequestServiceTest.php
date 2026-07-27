<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\SendReviewRequest;
use App\Models\Account;
use App\Services\Reviews\ReviewRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ReviewRequestServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_queues_a_single_review_request(): void
    {
        Queue::fake();
        $account = Account::factory()->create();

        $request = app(ReviewRequestService::class)->queue($account, '+14155551234', 'Jane');

        $this->assertNotNull($request);
        $this->assertDatabaseHas('review_requests', [
            'account_id' => $account->id,
            'phone' => '+14155551234',
            'client_name' => 'Jane',
            'status' => 'queued',
        ]);
        Queue::assertPushed(SendReviewRequest::class);
    }

    public function test_it_rejects_an_unusable_number(): void
    {
        Queue::fake();
        $account = Account::factory()->create();

        $this->assertNull(app(ReviewRequestService::class)->queue($account, 'nope'));
    }

    public function test_it_queues_many(): void
    {
        Queue::fake();
        $account = Account::factory()->create();

        $count = app(ReviewRequestService::class)->queueMany($account, [
            ['name' => 'A', 'phone' => '+14155551111'],
            ['name' => 'B', 'phone' => '+14155552222'],
            ['phone' => 'garbage'],
        ]);

        $this->assertSame(2, $count);
        Queue::assertPushed(SendReviewRequest::class, 2);
    }
}
