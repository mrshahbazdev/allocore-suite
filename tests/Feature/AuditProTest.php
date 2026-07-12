<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\Plan;
use App\Models\Team;
use App\Models\ToolSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\AuditPro\Livewire\Assessment;
use Modules\AuditPro\Models\Audit;
use Modules\AuditPro\Models\AuditPillar;
use Modules\AuditPro\Models\AuditQuestion;
use Modules\AuditPro\Models\AuditTemplate;
use Tests\TestCase;

class AuditProTest extends TestCase
{
    use RefreshDatabase;

    public function test_auditpro_requires_an_active_subscription(): void
    {
        $user = User::factory()->create();
        $this->createTeam($user);

        $this->actingAs($user)
            ->get(route('audit.index'))
            ->assertRedirect(route('billing.plans', ['module' => 'audit']));
    }

    public function test_only_the_current_team_subscription_grants_team_access(): void
    {
        $user = User::factory()->create();
        $subscribedTeam = $this->createTeam($user);
        $currentTeam = $this->createTeam($user);
        $this->subscribe($subscribedTeam);
        $user->update(['current_team_id' => $currentTeam->id]);

        $this->assertFalse($user->fresh()->hasModule('audit'));

        $this->actingAs($user)
            ->get(route('audit.index'))
            ->assertRedirect(route('billing.plans', ['module' => 'audit']));
    }

    public function test_auditpro_provisions_the_default_team_template(): void
    {
        $user = User::factory()->create();
        $team = $this->createTeam($user);
        $this->subscribe($team);

        $this->actingAs($user)
            ->get(route('audit.index'))
            ->assertOk()
            ->assertSee('Business Maturity Assessment');

        $this->assertDatabaseHas('auditpro_templates', [
            'team_id' => $team->id,
            'slug' => 'business-maturity',
            'is_default' => true,
        ]);
        $this->assertSame(25, AuditQuestion::withoutGlobalScopes()->where('team_id', $team->id)->count());
    }

    public function test_audits_are_isolated_from_other_teams(): void
    {
        $user = User::factory()->create();
        $team = $this->createTeam($user);
        $this->subscribe($team);
        $otherUser = User::factory()->create();
        $otherTeam = $this->createTeam($otherUser);
        $template = AuditTemplate::withoutGlobalScopes()->create([
            'team_id' => $otherTeam->id,
            'name' => 'Private template',
            'slug' => 'private-template',
        ]);
        $audit = Audit::withoutGlobalScopes()->create([
            'team_id' => $otherTeam->id,
            'template_id' => $template->id,
            'created_by' => $otherUser->id,
            'status' => 'completed',
        ]);

        $this->actingAs($user)
            ->get(route('audit.results', $audit))
            ->assertNotFound();
    }

    public function test_a_scale_assessment_can_be_completed(): void
    {
        $user = User::factory()->create();
        $team = $this->createTeam($user);
        $template = AuditTemplate::create([
            'team_id' => $team->id,
            'name' => 'Short assessment',
            'slug' => 'short-assessment',
            'created_by' => $user->id,
        ]);
        $pillar = AuditPillar::create([
            'team_id' => $team->id,
            'template_id' => $template->id,
            'name' => 'Operations',
            'position' => 1,
        ]);
        $question = AuditQuestion::create([
            'team_id' => $team->id,
            'template_id' => $template->id,
            'pillar_id' => $pillar->id,
            'question' => 'Processes are documented.',
            'position' => 1,
        ]);
        $audit = Audit::create([
            'team_id' => $team->id,
            'template_id' => $template->id,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user);

        Livewire::test(Assessment::class, ['audit' => $audit])
            ->set("answers.{$question->id}.value", 4)
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertRedirect(route('audit.results', $audit));

        $this->assertDatabaseHas('auditpro_audits', ['id' => $audit->id, 'status' => 'completed']);
        $this->assertDatabaseHas('auditpro_results', [
            'audit_id' => $audit->id,
            'level' => 'Operations',
            'average_score' => 4,
            'maturity_level' => 'Strong',
        ]);
    }

    private function createTeam(User $user): Team
    {
        $team = Team::create(['name' => fake()->company(), 'owner_id' => $user->id]);
        $team->members()->attach($user->id, ['role' => 'owner']);
        $user->update(['current_team_id' => $team->id]);

        return $team;
    }

    private function subscribe(Team $team): void
    {
        $module = Module::create([
            'key' => 'audit',
            'name' => 'AuditPro',
            'route_prefix' => 'audit',
        ]);
        $plan = Plan::create([
            'name' => 'AuditPro test plan',
            'slug' => 'audit-test-plan-'.fake()->unique()->randomNumber(),
            'billable_scope' => 'both',
        ]);
        $plan->modules()->attach($module);

        ToolSubscription::create([
            'billable_type' => Team::class,
            'billable_id' => $team->id,
            'plan_id' => $plan->id,
            'payment_method' => 'bank',
            'billing_interval' => 'monthly',
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);
    }
}
