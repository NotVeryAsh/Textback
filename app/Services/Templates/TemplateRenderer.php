<?php

declare(strict_types=1);

namespace App\Services\Templates;

use App\Models\Account;

class TemplateRenderer
{
    /**
     * Replace merge tags in a template body with the account's values plus any
     * extra per-message context (e.g. invoice {{amount}}, {{pay_link}},
     * {{due_date}}). Standard tags: {{business}}, {{agent}}, {{review_link}}.
     *
     * @param  array<string, string|null>  $context
     */
    public function render(string $body, Account $account, array $context = []): string
    {
        $replacements = [
            '{{business}}' => $account->business_name,
            '{{agent}}' => $account->agentName(),
            '{{review_link}}' => (string) $account->google_review_link,
        ];

        foreach ($context as $key => $value) {
            $replacements['{{'.$key.'}}'] = (string) $value;
        }

        return strtr($body, $replacements);
    }
}
