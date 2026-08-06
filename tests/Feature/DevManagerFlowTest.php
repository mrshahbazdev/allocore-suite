<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\Plan;
use App\Models\Team;
use App\Models\ToolSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\DevManager\Models\Idea;
use Tests\TestCase;

class DevManagerFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_requires_subscription(): void
    {
        $user = User::factory()->create();
        $this->createTeam($user);

        $this->actingAs($user)
            ->get(route('devmanager.dashboard'))
            ->assertRedirect(route('billing.plans', ['module' => 'dev-manager']));
    }

    public function test_full_idea_lifecycle(): void
    {
        $user = User::factory()->create();
        $team = $this->createTeam($user);
        $this->subscribe($team);

        $this->actingAs($user)
            ->get(route('devmanager.dashboard'))
            ->assertOk()
            ->assertSee('SaaS Development Manager');

        $response = $this->actingAs($user)
            ->post(route('devmanager.ideas.store'), [
                'idea' => [
                    'title' => 'Allocore Knowledge OS',
                    'description' => 'A unified system for knowledge.',
                    'problem' => 'Knowledge is trapped in heads.',
                    'audience' => 'SMEs',
                    'value' => 'Scaleable knowledge.',
                    'cost_of_problem' => 'High risk.',
                    'status' => 'draft',
                ],
            ]);

        $response->assertRedirect();

        $idea = Idea::first();
        $this->assertNotNull($idea);
        $this->assertSame('Allocore Knowledge OS', $idea->title);

        $this->actingAs($user)
            ->get(route('devmanager.ideas.show', $idea))
            ->assertOk()
            ->assertSee('Allocore Knowledge OS');

        $this->actingAs($user)
            ->post(route('devmanager.requirements.store', $idea), [
                'title' => 'Idea capture form',
                'description' => 'Capture problem and value.',
                'priority' => 'high',
                'status' => 'draft',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('devmanager_requirements', [
            'idea_id' => $idea->id,
            'title' => 'Idea capture form',
        ]);

        $this->actingAs($user)
            ->get(route('devmanager.backlog.index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('devmanager.roadmap.index'))
            ->assertOk();
    }

    public function test_ideas_are_isolated_between_teams(): void
    {
        $user = User::factory()->create();
        $team = $this->createTeam($user);
        $this->subscribe($team);

        $otherUser = User::factory()->create();
        $otherTeam = $this->createTeam($otherUser);
        $otherIdea = Idea::withoutGlobalScopes()->create([
            'team_id' => $otherTeam->id,
            'user_id' => $otherUser->id,
            'title' => 'Other idea',
            'status' => 'draft',
        ]);

        $this->actingAs($user)
            ->get(route('devmanager.ideas.show', $otherIdea))
            ->assertNotFound();
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
            'key' => 'dev-manager',
            'name' => 'SaaS Development Manager',
            'route_prefix' => 'dev',
        ]);
        $plan = Plan::create([
            'name' => 'Dev test plan',
            'slug' => 'dev-test-plan-'.fake()->unique()->randomNumber(),
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
