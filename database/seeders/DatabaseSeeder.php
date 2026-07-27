<?php

namespace Database\Seeders;

use App\Enums\LeadStatus;
use App\Enums\MessageDirection;
use App\Enums\Vertical;
use App\Models\Account;
use App\Models\User;
use App\Services\Accounts\AccountSetup;
use App\Services\Sequences\SequenceEnroller;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(AccountSetup $setup): void
    {
        $user = User::firstOrCreate(
            ['email' => 'demo@textback.test'],
            [
                'name' => 'Sarah Chen',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        if ($user->account()->exists()) {
            return;
        }

        $account = $setup->create($user, 'Sarah Chen Realty', Vertical::Realtor);
        $account->update([
            'operator_cell' => '+15550000001',
            'operator_cell_verified_at' => now(),
            'twilio_number' => '+15557654321',
            'google_review_link' => 'https://g.page/r/sarahchenrealty',
            'onboarding_step' => 'done',
            'is_live' => true,
            'leads_recovered_count' => 3,
        ]);

        $this->seedLeads($account);

        $account->reviewRequests()->createMany([
            ['client_name' => 'The Hendersons', 'phone' => '+15551230001', 'status' => 'sent', 'sent_at' => now()->subDays(2)],
            ['client_name' => 'Marcus Lee', 'phone' => '+15551230002', 'status' => 'sent', 'sent_at' => now()->subDay()],
        ]);

        $account->currentUsage()->update([
            'sms_out' => 8,
            'sms_in' => 3,
            'leads_recovered' => 3,
        ]);

        // Demo pillar-3 enrollment (nurture) for the Follow-ups screen.
        $jordan = $account->leads()->where('phone', '+15559990001')->first();
        app(SequenceEnroller::class)
            ->enrollNurture($account, 'Jordan Blake', '+15559990001', $jordan?->id);
    }

    private function seedLeads(Account $account): void
    {
        $samples = [
            ['name' => 'Jordan Blake', 'phone' => '+15559990001', 'status' => LeadStatus::Replied],
            ['name' => null, 'phone' => '+15559990002', 'status' => LeadStatus::TextedBack],
            ['name' => 'Priya Patel', 'phone' => '+15559990003', 'status' => LeadStatus::Converted],
        ];

        foreach ($samples as $sample) {
            $lead = $account->leads()->create([
                'phone' => $sample['phone'],
                'name' => $sample['name'],
                'status' => $sample['status'],
                'source' => 'missed_call',
                'last_contacted_at' => now()->subHours(random_int(1, 40)),
            ]);

            $account->messages()->create([
                'lead_id' => $lead->id,
                'direction' => MessageDirection::Outbound,
                'from' => $account->twilio_number,
                'to' => $lead->phone,
                'body' => "Hi, it's Sarah with Sarah Chen Realty. Sorry I missed your call! How can I help?",
                'status' => 'sent',
                'sent_at' => $lead->last_contacted_at,
            ]);

            if ($sample['status'] === LeadStatus::Replied) {
                $account->messages()->create([
                    'lead_id' => $lead->id,
                    'direction' => MessageDirection::Inbound,
                    'from' => $lead->phone,
                    'to' => $account->twilio_number,
                    'body' => 'Hi Sarah, yes I wanted to ask about the listing on Oak Street.',
                    'status' => 'received',
                    'sent_at' => $lead->last_contacted_at?->addMinutes(4),
                ]);
            }
        }
    }
}
