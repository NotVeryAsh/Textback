<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Account;
use App\Services\Templates\TemplateRenderer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TemplateRendererTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_replaces_merge_tags(): void
    {
        $account = Account::factory()->create([
            'business_name' => 'Acme Realty',
            'google_review_link' => 'https://g.page/r/acme',
        ]);
        $account->user->update(['name' => 'Dana']);
        $account->refresh();

        $rendered = app(TemplateRenderer::class)->render(
            'Hi from {{business}}, this is {{agent}}. Review: {{review_link}}',
            $account,
        );

        $this->assertSame('Hi from Acme Realty, this is Dana. Review: https://g.page/r/acme', $rendered);
    }
}
