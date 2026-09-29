<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Modules\InvoiceMaker\Models\Client;
use Modules\InvoiceMaker\Models\Expense;
use Modules\InvoiceMaker\Models\Invoice;
use Modules\InvoiceMaker\Models\Payment;
use Tests\TestCase;

class InvoiceMakerAllocoreMetricsTest extends TestCase
{
    use RefreshDatabase;

    private const WEBHOOK = 'https://allocore-manager.example/api/v1/webhooks/test-token';

    private function teamUser(): User
    {
        $user = User::factory()->create();
        $team = Team::create([
            'name' => 'Acme GmbH',
            'owner_id' => $user->id,
            'industry' => 'software',
            'size' => '11-50',
            'country' => 'DE',
            'revenue_range' => '1m-5m',
        ]);
        $team->members()->attach($user->id, ['role' => 'owner']);
        $user->update(['current_team_id' => $team->id]);

        return $user;
    }

    private function client(): Client
    {
        return Client::create(['name' => 'Kunde AG']);
    }

    public function test_invoice_and_payment_events_are_pushed(): void
    {
        config(['services.allocore.webhook_url' => self::WEBHOOK]);

        $this->actingAs($this->teamUser());
        $client = $this->client();
        Http::fake();

        $invoice = Invoice::create([
            'invoice_number' => 'INV-1',
            'client_id' => $client->id,
            'status' => Invoice::STATUS_DRAFT,
            'type' => Invoice::TYPE_INVOICE,
            'invoice_date' => '2026-09-01',
            'due_date' => '2026-10-01',
            'grand_total' => 1250.5,
            'currency' => 'EUR',
        ]);

        $invoice->update(['status' => Invoice::STATUS_PAID]);

        Payment::create([
            'invoice_id' => $invoice->id,
            'amount' => 1250.5,
            'date' => '2026-09-20',
            'method' => Payment::METHOD_BANK_TRANSFER,
        ]);

        Http::assertSentCount(3);
        Http::assertSent(fn ($request) => $request->url() === self::WEBHOOK
            && $request['type'] === 'invoice_created'
            && $request['amount'] == 1250.5);
        Http::assertSent(fn ($request) => $request['type'] === 'invoice_paid'
            && $request['amount'] == 1250.5);
        Http::assertSent(fn ($request) => $request['type'] === 'payment_received'
            && $request['amount'] == 1250.5);
    }

    public function test_expense_and_client_events_are_pushed(): void
    {
        config(['services.allocore.webhook_url' => self::WEBHOOK]);
        Http::fake();

        $this->actingAs($this->teamUser());

        Expense::create([
            'amount' => 499.9,
            'date' => '2026-09-15',
            'category' => 'Marketing',
        ]);

        Client::create(['name' => 'Neue GmbH']);

        Http::assertSentCount(2);
        Http::assertSent(fn ($request) => $request['type'] === 'expense_created'
            && $request['amount'] == 499.9
            && $request['category'] === 'marketing');
        Http::assertSent(fn ($request) => $request['type'] === 'customer_created');
    }

    public function test_estimates_and_non_paid_updates_push_nothing(): void
    {
        config(['services.allocore.webhook_url' => self::WEBHOOK]);

        $this->actingAs($this->teamUser());
        $client = $this->client();
        Http::fake();

        Invoice::create([
            'invoice_number' => 'EST-1',
            'client_id' => $client->id,
            'status' => Invoice::STATUS_DRAFT,
            'type' => Invoice::TYPE_ESTIMATE,
            'invoice_date' => '2026-09-01',
            'due_date' => '2026-10-01',
            'grand_total' => 800,
        ]);

        $invoice = Invoice::create([
            'invoice_number' => 'INV-2',
            'client_id' => $client->id,
            'status' => Invoice::STATUS_SENT,
            'type' => Invoice::TYPE_INVOICE,
            'invoice_date' => '2026-09-01',
            'due_date' => '2026-10-01',
        ]);
        $invoice->update(['notes' => 'memo']);

        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request['type'] === 'invoice_created');
    }

    public function test_push_is_disabled_without_webhook_url(): void
    {
        config(['services.allocore.webhook_url' => null]);
        Http::fake();

        $this->actingAs($this->teamUser());

        Client::create(['name' => 'Silent GmbH']);

        Http::assertNothingSent();
    }
}
