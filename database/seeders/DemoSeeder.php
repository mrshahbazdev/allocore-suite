<?php

namespace Database\Seeders;

use App\Models\AllocoreScore;
use App\Models\CaseStudy;
use App\Models\GlossaryTerm;
use App\Models\Plan;
use App\Models\Team;
use App\Models\ToolSubscription;
use App\Models\User;
use App\Services\MaturityDataSnapshotService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Modules\AuditPro\Models\Audit;
use Modules\InvoiceMaker\Models\Client;
use Modules\InvoiceMaker\Models\Invoice;
use Modules\InvoiceMaker\Models\InvoiceItem;
use Modules\LeadQuality\Models\Contact as LeadContact;
use Modules\PlanHive\Models\CalendarEvent;
use Modules\PlanHive\Models\Contact as PlanHiveContact;
use Modules\PlanHive\Models\Document;
use Modules\PlanHive\Models\Goal;
use Modules\PlanHive\Models\Note;
use Modules\PlanHive\Models\Project;
use Modules\PlanHive\Models\Reminder;
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
                'industry_sub' => 'SaaS',
                'company_name' => 'Demo GmbH',
                'size' => '11–50',
                'company_age' => 5,
                'country' => 'Germany',
                'revenue_range' => '€1M – €5M',
            ]
        );

        $team->update([
            'industry' => 'Software',
            'industry_sub' => 'SaaS',
            'company_name' => 'Demo GmbH',
            'size' => '11–50',
            'company_age' => 5,
            'country' => 'Germany',
            'revenue_range' => '€1M – €5M',
        ]);

        $admin->update(['current_team_id' => $team->id, 'onboarding_completed_at' => now()]);
        $demoUser->update(['current_team_id' => $team->id, 'onboarding_completed_at' => now()]);

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
                'payment_method' => 'bank',
                'billing_interval' => 'yearly',
                'starts_at' => now(),
                'ends_at' => now()->addYear(),
                'subtotal' => $bundle->price_yearly,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total' => $bundle->price_yearly,
            ]);
        }

        $this->seedAllocoreScore($team, $demoUser);
        $this->seedInvoiceMaker($team, $admin);
        $this->seedPlanHive($team, $admin);
        $this->seedLeadOs($team, $admin);
        $this->seedCaseStudies();
    }

    protected function seedAllocoreScore(Team $team, User $user): void
    {
        GlossaryTerm::firstOrCreate(
            ['slug' => 'revenue-run-rate'],
            [
                'term' => 'Revenue Run Rate',
                'definition' => 'Projected annual revenue based on recent monthly recurring revenue.',
                'simple_definition' => 'How much money your business is on track to make in a year.',
                'related_modules' => ['financial-platform'],
                'is_published' => true,
                'is_beginner_friendly' => true,
            ]
        );

        $audit = Audit::firstOrCreate(
            ['team_id' => $team->id, 'company_name' => $team->name],
            [
                'created_by' => $user->id,
                'status' => 'completed',
                'industry' => $team->industry,
                'size' => $team->size,
                'completed_at' => now(),
            ]
        );

        $score = AllocoreScore::firstOrCreate(
            ['audit_id' => $audit->id, 'team_id' => $team->id],
            [
                'company_name' => $team->name,
                'industry' => $team->industry,
                'size' => $team->size,
                'company_age' => $team->company_age,
                'score' => 62.50,
                'maturity_level' => 'Strong',
                'pillars' => [
                    ['name' => 'Revenue', 'score' => 70, 'maturity' => 'Strong'],
                    ['name' => 'Profit', 'score' => 55, 'maturity' => 'Solid'],
                    ['name' => 'Order', 'score' => 80, 'maturity' => 'Excellent'],
                    ['name' => 'Influence', 'score' => 45, 'maturity' => 'Weak'],
                    ['name' => 'Legacy', 'score' => 62, 'maturity' => 'Strong'],
                ],
                'calculated_at' => now(),
            ]
        );

        MaturityDataSnapshotService::fromScore($score);
    }

    protected function seedInvoiceMaker(Team $team, User $user): void
    {
        $client = Client::withoutGlobalScope('current_team')->firstOrCreate(
            ['team_id' => $team->id, 'company_name' => 'Acme GmbH'],
            [
                'name' => 'Alice Müller',
                'email' => 'alice@acme.test',
                'phone' => '+49 123 456789',
                'address' => "Musterstraße 1\n12345 Berlin",
            ]
        );

        $invoice = Invoice::withoutGlobalScope('current_team')->firstOrCreate(
            ['team_id' => $team->id, 'invoice_number' => 'INV-DEMO-001'],
            [
                'client_id' => $client->id,
                'type' => 'invoice',
                'status' => 'sent',
                'invoice_date' => now()->subDays(7),
                'due_date' => now()->addDays(23),
                'currency' => 'EUR',
                'subtotal' => 1000,
                'tax_total' => 190,
                'grand_total' => 1190,
                'amount_due' => 1190,
            ]
        );

        InvoiceItem::withoutGlobalScope('current_team')->firstOrCreate(
            ['invoice_id' => $invoice->id, 'description' => 'Premium consulting'],
            [
                'team_id' => $team->id,
                'quantity' => 10,
                'unit_price' => 100,
                'total' => 1000,
            ]
        );
    }

    protected function seedPlanHive(Team $team, User $user): void
    {
        $project = Project::withoutGlobalScope('current_team')->firstOrCreate(
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

        if ($project->wasRecentlyCreated) {
            $project->members()->attach($user->id, ['role' => 'owner']);
        }

        $tasks = [
            ['title' => 'Prepare landing page', 'description' => 'Draft copy and hero section for the launch page.', 'status' => 'in_progress', 'priority' => 'high', 'due_date' => now()->addWeek()],
            ['title' => 'Design mockups', 'description' => 'Create high-fidelity UI mockups.', 'status' => 'todo', 'priority' => 'medium', 'due_date' => now()->addDays(10)],
            ['title' => 'Set up analytics', 'description' => 'Connect tracking and reporting tools.', 'status' => 'todo', 'priority' => 'low', 'due_date' => now()->addDays(14)],
            ['title' => 'Launch campaign', 'description' => 'Press send on the announcement email.', 'status' => 'done', 'priority' => 'high', 'due_date' => now()->subDay()],
        ];

        foreach ($tasks as $task) {
            Task::withoutGlobalScope('current_team')->firstOrCreate(
                ['project_id' => $project->id, 'title' => $task['title']],
                $task + ['team_id' => $team->id, 'user_id' => $user->id]
            );
        }

        Goal::withoutGlobalScope('current_team')->firstOrCreate(
            ['project_id' => $project->id, 'title' => 'Hit 1,000 signups'],
            [
                'team_id' => $team->id,
                'user_id' => $user->id,
                'description' => 'Reach 1,000 beta signups by launch day.',
                'target_date' => now()->addMonth(),
                'progress' => 35,
                'status' => 'active',
            ]
        );

        Note::withoutGlobalScope('current_team')->firstOrCreate(
            ['project_id' => $project->id, 'title' => 'Kickoff notes'],
            [
                'team_id' => $team->id,
                'user_id' => $user->id,
                'content' => 'Target audience: SMBs in DACH. Key message: simplify project management with Allocore.',
            ]
        );

        PlanHiveContact::withoutGlobalScope('current_team')->firstOrCreate(
            ['team_id' => $team->id, 'email' => 'partner@example.com'],
            [
                'user_id' => $user->id,
                'project_id' => $project->id,
                'name' => 'Partner Contact',
                'company' => 'Example Co.',
                'phone' => '+1 555 1234',
            ]
        );

        CalendarEvent::withoutGlobalScope('current_team')->firstOrCreate(
            ['project_id' => $project->id, 'title' => 'Launch review'],
            [
                'team_id' => $team->id,
                'user_id' => $user->id,
                'description' => 'Review launch results with the team.',
                'start_at' => now()->addDays(3)->setTime(10, 0),
                'end_at' => now()->addDays(3)->setTime(11, 0),
                'all_day' => false,
            ]
        );

        Reminder::withoutGlobalScope('current_team')->firstOrCreate(
            ['project_id' => $project->id, 'title' => 'Follow up with partner'],
            [
                'team_id' => $team->id,
                'user_id' => $user->id,
                'remindable_type' => Project::class,
                'remindable_id' => $project->id,
                'remind_at' => now()->addDays(2)->setTime(9, 0),
                'is_done' => false,
            ]
        );

        $path = 'planhive/documents/'.$team->id.'/demo-project-brief.txt';
        if (! Storage::disk('public')->exists($path)) {
            Storage::disk('public')->put($path, "Project Brief\n\nThis is a demo document for PlanHive.");
        }

        Document::withoutGlobalScope('current_team')->firstOrCreate(
            ['project_id' => $project->id, 'title' => 'Demo project brief'],
            [
                'team_id' => $team->id,
                'user_id' => $user->id,
                'path' => $path,
                'mime_type' => 'text/plain',
                'size' => (int) Storage::disk('public')->size($path),
            ]
        );
    }

    protected function seedLeadOs(Team $team, User $user): void
    {
        LeadContact::withoutGlobalScope('current_team')->firstOrCreate(
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

    protected function seedCaseStudies(): void
    {
        $studies = [
            [
                'title' => 'Mittelständischer Dienstleister steigert Profitabilität',
                'slug' => 'profitabilitaet-dienstleister',
                'industry' => 'Dienstleistung',
                'company' => 'MusterConsult GmbH',
                'challenge' => 'Ungenaue Projektrentabilität, fehlende Transparenz über die profitabelsten Aufträge.',
                'solution' => 'Einführung von CashCore und InvoiceMaker zur Deckungsbeitragsrechnung pro Projekt.',
                'result' => 'Innerhalb von 6 Monaten konnte der operative Gewinn um 18% gesteigert werden.',
                'metrics' => ['Gewinnmarge' => '+18%', 'Cash-Runway' => '+3 Monate', 'Zeitersparnis' => '8h/Woche'],
            ],
            [
                'title' => 'Agentur skaliert mit klarer Vision und Prozessen',
                'slug' => 'vision-prozesse-agentur',
                'industry' => 'Agentur',
                'company' => 'WachstumAgentur',
                'challenge' => 'Schnelles Wachstum führte zu unklaren Verantwortlichkeiten und Reibungsverlusten.',
                'solution' => 'VisionFlow und PlanHive zur Ziel- und Projektabstimmung über alle Teams.',
                'result' => 'Onboarding neuer Mitarbeiter halbiert und Projektlieferung pünktlicher.',
                'metrics' => ['Onboarding-Zeit' => '-50%', 'Termintreue' => '+35%', 'Mitarbeiterzufriedenheit' => '+22%'],
            ],
            [
                'title' => 'Handwerksbetrieb gewinnt qualifizierte Leads',
                'slug' => 'leads-handwerk',
                'industry' => 'Handwerk',
                'company' => 'BauPlus Schmidt',
                'challenge' => 'Wenig Sichtbarkeit online und unstrukturierte Anfragenverwaltung.',
                'solution' => 'LeadQuality und KeywordClustering zur systematischen Lead-Generierung und -Bewertung.',
                'result' => 'Mehr qualifizierte Anfragen bei gleichem Marketing-Budget.',
                'metrics' => ['Leads/Monat' => '+60%', 'Conversion' => '+12%', 'Akquise-Zeit' => '-25%'],
            ],
        ];

        foreach ($studies as $study) {
            CaseStudy::firstOrCreate(['slug' => $study['slug']], $study + ['is_published' => true, 'sort_order' => 0]);
        }
    }
}
