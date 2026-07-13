<?php

namespace Modules\InvoiceMaker\Services;

use App\Models\Team;
use Illuminate\Support\Facades\DB;
use Modules\InvoiceMaker\Models\AccountingCategory;
use Modules\InvoiceMaker\Models\EmailTemplate;
use Modules\InvoiceMaker\Models\Profile;
use Modules\InvoiceMaker\Models\Template;

class InvoiceMakerContext
{
    public function team(): Team
    {
        return auth()->user()?->currentTeam
            ?? throw new \RuntimeException('A current team is required.');
    }

    public function profile(): Profile
    {
        $team = $this->team();

        $profile = Profile::withoutGlobalScopes()->firstOrCreate([
            'team_id' => $team->id,
        ], [
            'name' => $team->name,
            'email' => $team->owner?->email,
        ]);

        $this->provisionDefaults($profile);

        return $profile;
    }

    private function provisionDefaults(Profile $profile): void
    {
        DB::transaction(function () use ($profile): void {
            if (! Template::withoutGlobalScopes()->where('team_id', $profile->team_id)->exists()) {
                Template::withoutGlobalScopes()->create([
                    'team_id' => $profile->team_id,
                    'name' => 'Allocore Professional',
                    'is_default' => true,
                    'primary_color' => '#4f46e5',
                    'font_family' => 'DejaVu Sans',
                    'header_style' => 'simple',
                    'payment_terms' => 'Please pay by the due date.',
                    'footer_message' => 'Thank you for your business.',
                ]);
            }

            foreach ([
                ['Sales', 'income'],
                ['Services', 'income'],
                ['Office', 'expense'],
                ['Software', 'expense'],
                ['Travel', 'expense'],
            ] as [$name, $type]) {
                AccountingCategory::withoutGlobalScopes()->firstOrCreate([
                    'team_id' => $profile->team_id,
                    'name' => $name,
                    'type' => $type,
                ]);
            }

            foreach ($this->emailTemplates() as $template) {
                EmailTemplate::withoutGlobalScopes()->firstOrCreate([
                    'team_id' => $profile->team_id,
                    'type' => $template['type'],
                    'is_default' => true,
                ], [
                    ...$template,
                    'team_id' => $profile->team_id,
                ]);
            }
        });
    }

    private function emailTemplates(): array
    {
        return [
            [
                'name' => 'Default invoice email',
                'type' => 'invoice',
                'subject' => 'Invoice [invoice_number] from [business_name]',
                'body' => "Hello [client_name],\n\nYour invoice [invoice_number] for [amount_due] is available at [invoice_link].",
                'is_default' => true,
            ],
            [
                'name' => 'Default payment reminder',
                'type' => 'reminder',
                'subject' => 'Payment reminder for [invoice_number]',
                'body' => "Hello [client_name],\n\nInvoice [invoice_number] was due on [due_date]. The outstanding amount is [amount_due].",
                'is_default' => true,
            ],
        ];
    }
}
