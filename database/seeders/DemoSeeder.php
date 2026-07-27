<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\Team;
use App\Models\ToolSubscription;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\InvoiceMaker\Models\Client;
use Modules\InvoiceMaker\Models\Invoice;
use Modules\InvoiceMaker\Models\InvoiceItem;
use Modules\LeadQuality\Models\Contact as LeadContact;
use Modules\PlanHive\Models\Contact as PlanHiveContact;
use Modules\PlanHive\Models\Project;
use Modules\PlanHive\Models\Task;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // Make sure core modules and plans exist.
        $this->call(CoreSeeder::class);

        $admin = User::firstOrCreate(
            ['email' => 'admin@allocore.test'],
            ['name' => 'Admin', 'password' => Hash::make('password')]
        );

        $demoUser = User::firstOrCreate(
            ['email' => 'demo@allocore.test'],
            ['name' => 'Demo User', 'password' => Hash::make('password')]
        );

        $team = Team::firstOrCreate(
            ['name' => 'Demo Team'],
            [
                'owner_id' => $admin->id,
                'industry' => 'Software',
                'size' => '10–50',
            ]
        );

        $admin->update(['current_team_id' => $team->id]);
        $demoUser->update(['current_team_id' => $team->id]);

        if (! $team->members()->where('users.id', $demoUser->id)->exists()) {
            $team->members()->attach($demoUser->id, ['role' => 'member']);
        }

        $bundle = Plan::where('slug', 'all-tools')->first();

        if ($bundle && ! $team->activeSubscriptions()->where('plan_id', $bundle->id)->exists()) {
            ToolSubscription::create([
                'billable_type' => Team::class,
                'billable_id' => $team->id,
                'plan_id' => $bundle->id,
                'status' => 'active',
                'payment_method' => 'bank_transfer',
                'billing_interval' => 'yearly',
                'starts_at' => now(),
                'ends_at' => now()->addYear(),
                'subtotal' => $bundle->price_yearly,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total' => $bundle->price_yearly,
            ]);
        }

        $this->seedInvoiceMaker($team, $admin);
        $this->seedPlanHive($team, $admin);
        $this->seedLeadOs($team, $admin);
    }

    protected function seedInvoiceMaker(Team $team, User $user): void
    {
        $client = Client::firstOrCreate(
            ['team_id' => $team->id, 'company_name' => 'Acme GmbH'],
            [
                'name' => 'Alice Müller',
                'email' => 'alice@acme.test',
                'phone' => '+49 123 456789',
                'address' => "Musterstraße 1\n12345 Berlin",
            ]
        );

        $invoice = Invoice::create([
            'team_id' => $team->id,
            'client_id' => $client->id,
            'invoice_number' => 'INV-DEMO-001',
            'type' => 'invoice',
            'status' => 'sent',
            'invoice_date' => now()->subDays(7),
            'due_date' => now()->addDays(23),
            'currency' => 'EUR',
            'subtotal' => 1000,
            'tax_total' => 190,
            'grand_total' => 1190,
            'amount_due' => 1190,
        ]);

        InvoiceItem::create([
            'team_id' => $team->id,
            'invoice_id' => $invoice->id,
            'description' => 'Premium consulting',
            'quantity' => 10,
            'unit_price' => 100,
            'total' => 1000,
        ]);
    }

    protected function seedPlanHive(Team $team, User $user): void
    {
        $project = Project::firstOrCreate(
            ['team_id' => $team->id, 'name' => 'Demo Product Launch'],
            [
                'user_id' => $user->id,
                'description' => 'A sample project to demonstrate PlanHive.',
                'color' => '#14b8a6',
                'status' => 'active',
                'start_date' => now(),
                'end_date' => now()->addMonth(),
            ]
        );

        Task::firstOrCreate(
            ['project_id' => $project->id, 'title' => 'Prepare landing page'],
            [
                'team_id' => $team->id,
                'user_id' => $user->id,
                'description' => 'Draft copy and hero section for the launch page.',
                'status' => 'in_progress',
                'priority' => 'high',
                'due_date' => now()->addWeek(),
            ]
        );

        PlanHiveContact::firstOrCreate(
            ['team_id' => $team->id, 'email' => 'partner@example.com'],
            [
                'user_id' => $user->id,
                'project_id' => $project->id,
                'name' => 'Partner Contact',
                'company' => 'Example Co.',
                'phone' => '+1 555 1234',
            ]
        );
    }

    protected function seedLeadOs(Team $team, User $user): void
    {
        LeadContact::firstOrCreate(
            ['team_id' => $team->id, 'email' => 'lead@example.com'],
            [
                'user_id' => $user->id,
                'name' => 'Sample Lead',
                'company' => 'Growth Inc.',
                'position' => 'Head of Sales',
                'status' => 'new',
                'source' => 'demo',
                'score' => 78,
            ]
        );
    }
}
