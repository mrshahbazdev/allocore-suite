<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\Plan;
use App\Models\Team;
use App\Models\ToolSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\KnowledgeManager\Models\Asset;
use Modules\KnowledgeManager\Models\Project;
use Tests\TestCase;

class KnowledgeManagerFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_requires_subscription(): void
    {
        $user = User::factory()->create();
        $this->createTeam($user);

        $this->actingAs($user)
            ->get(route('knowledgemanager.dashboard'))
            ->assertRedirect(route('billing.plans', ['module' => 'knowledge-manager']));
    }

    public function test_full_knowledge_project_lifecycle(): void
    {
        $user = User::factory()->create();
        $team = $this->createTeam($user);
        $this->subscribe($team);

        $this->actingAs($user)
            ->get(route('knowledgemanager.dashboard'))
            ->assertOk()
            ->assertSee('Knowledge Manager');

        $this->actingAs($user)
            ->get(route('knowledgemanager.projects.index'))
            ->assertOk();

        $response = $this->actingAs($user)
            ->post(route('knowledgemanager.projects.store'), [
                'project' => [
                    'name' => 'Allocore Knowledge Base',
                    'slug' => 'allocore-kb',
                    'description' => 'Capture everything.',
                    'status' => 'draft',
                ],
            ]);

        $response->assertRedirect();

        $project = Project::first();
        $this->assertNotNull($project);
        $this->assertSame('Allocore Knowledge Base', $project->name);

        $this->actingAs($user)
            ->get(route('knowledgemanager.projects.show', $project))
            ->assertOk()
            ->assertSee('Allocore Knowledge Base');

        $this->actingAs($user)
            ->put(route('knowledgemanager.answers.update', $project), [
                'answers' => [
                    'business' => [
                        'what_does_it_do' => 'It runs audits and SOPs.',
                    ],
                    'technology' => [
                        'frontend' => 'Livewire + Alpine.js',
                        'backend' => 'Laravel',
                    ],
                ],
            ])
            ->assertRedirect(route('knowledgemanager.projects.show', $project));

        $this->assertDatabaseHas('knowledge_answers', [
            'project_id' => $project->id,
            'section' => 'business',
            'question_key' => 'what_does_it_do',
            'answer' => 'It runs audits and SOPs.',
        ]);

        $this->actingAs($user)
            ->post(route('knowledgemanager.assets.store', $project), [
                'assets' => [
                    ['type' => 'module', 'name' => 'AuditPro', 'description' => 'Audit module', 'link' => ''],
                    ['type' => 'api', 'name' => 'Public API', 'description' => 'JSON API', 'link' => 'https://example.com/api'],
                ],
            ])
            ->assertRedirect();

        $this->assertSame(2, Asset::count());

        $this->actingAs($user)
            ->get(route('knowledgemanager.documents.index', $project))
            ->assertOk()
            ->assertSee('Architecture Manual');

        foreach (array_keys(config('knowledgemanager.documents')) as $type) {
            $this->actingAs($user)
                ->get(route('knowledgemanager.documents.show', [$project, $type]))
                ->assertOk();
        }
    }

    public function test_projects_are_isolated_between_teams(): void
    {
        $user = User::factory()->create();
        $team = $this->createTeam($user);
        $this->subscribe($team);

        $otherUser = User::factory()->create();
        $otherTeam = $this->createTeam($otherUser);
        $otherProject = Project::withoutGlobalScopes()->create([
            'team_id' => $otherTeam->id,
            'name' => 'Other project',
            'slug' => 'other-project',
            'status' => 'draft',
        ]);

        $this->actingAs($user)
            ->get(route('knowledgemanager.projects.show', $otherProject))
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
            'key' => 'knowledge-manager',
            'name' => 'Knowledge Manager',
            'route_prefix' => 'knowledge',
        ]);
        $plan = Plan::create([
            'name' => 'Knowledge test plan',
            'slug' => 'knowledge-test-plan-'.fake()->unique()->randomNumber(),
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
