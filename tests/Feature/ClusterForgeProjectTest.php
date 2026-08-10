<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\Plan;
use App\Models\Team;
use App\Models\ToolSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Modules\ClusterForge\Jobs\GenerateProjectJob;
use Modules\ClusterForge\Models\Project;
use Modules\ClusterForge\Services\DataForSeoService;
use Modules\ClusterForge\Services\GeminiService;
use Modules\ClusterForge\Services\KeywordClusterGenerator;
use Tests\TestCase;

class ClusterForgeProjectTest extends TestCase
{
    use RefreshDatabase;

    private function createTeam(User $user): Team
    {
        $team = Team::create(['name' => fake()->company(), 'owner_id' => $user->id]);
        $team->members()->attach($user->id, ['role' => 'owner']);
        $user->update(['current_team_id' => $team->id]);

        return $team;
    }

    private function subscribe(Team $team): void
    {
        $module = Module::firstOrCreate(['key' => 'keyword-cluster'], [
            'name' => 'ClusterForge',
            'route_prefix' => 'clusters',
        ]);
        $plan = Plan::create([
            'name' => 'Cluster test plan',
            'slug' => 'cluster-test-plan-'.fake()->unique()->randomNumber(),
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

    public function test_index_requires_subscription(): void
    {
        $user = User::factory()->create();
        $this->createTeam($user);

        $this->actingAs($user)
            ->get(route('clusterforge.index'))
            ->assertRedirect(route('billing.plans', ['module' => 'keyword-cluster']));
    }

    public function test_authenticated_user_can_view_empty_index(): void
    {
        $user = User::factory()->create();
        $team = $this->createTeam($user);
        $this->subscribe($team);

        $this->actingAs($user)
            ->get(route('clusterforge.index'))
            ->assertOk()
            ->assertSee('Topic Clusters');
    }

    public function test_user_can_create_project_and_dispatch_job(): void
    {
        Bus::fake();

        $user = User::factory()->create();
        $team = $this->createTeam($user);
        $this->subscribe($team);

        $response = $this->actingAs($user)->post(route('clusterforge.store'), [
            'topic' => 'content marketing',
            'website' => 'acme.com',
        ]);

        $project = Project::firstOrFail();
        $response->assertRedirect(route('clusterforge.show', $project));

        $this->assertSame($user->id, $project->user_id);
        $this->assertSame('content marketing', $project->topic);
        $this->assertSame(Project::STATUS_PENDING, $project->status);
        $this->assertSame($team->id, $project->team_id);
        Bus::assertDispatched(GenerateProjectJob::class, fn ($j) => $j->projectId === $project->id);
    }

    public function test_project_stores_current_ui_locale_as_language(): void
    {
        Bus::fake();

        $user = User::factory()->create();
        $team = $this->createTeam($user);
        $this->subscribe($team);

        $this->actingAs($user)->get(route('clusterforge.index'));

        $this->actingAs($user)
            ->post(route('clusterforge.store', ['lang' => 'de']), [
                'topic' => 'Content-Marketing',
                'website' => 'acme.de',
            ]);

        $this->assertSame('de', Project::firstOrFail()->language);
    }

    public function test_user_cannot_view_other_teams_project(): void
    {
        $owner = User::factory()->create();
        $ownerTeam = $this->createTeam($owner);
        $this->subscribe($ownerTeam);

        $project = Project::create([
            'team_id' => $ownerTeam->id,
            'user_id' => $owner->id,
            'topic' => 'x',
            'website' => 'y',
        ]);

        $other = User::factory()->create();
        $otherTeam = $this->createTeam($other);
        $this->subscribe($otherTeam);

        $this->actingAs($other)
            ->get(route('clusterforge.show', $project))
            ->assertNotFound();
    }

    public function test_status_endpoint_returns_progress(): void
    {
        $user = User::factory()->create();
        $team = $this->createTeam($user);
        $this->subscribe($team);

        $project = Project::create([
            'team_id' => $team->id,
            'user_id' => $user->id,
            'topic' => 'x',
            'website' => 'y',
            'status' => Project::STATUS_GENERATING_QUESTIONS,
        ]);

        $this->actingAs($user)
            ->getJson(route('clusterforge.status', $project))
            ->assertOk()
            ->assertJson([
                'id' => $project->id,
                'status' => 'generating_questions',
                'is_in_progress' => true,
            ]);
    }

    public function test_generator_persists_subtopics_questions_answers_and_pages(): void
    {
        $user = User::factory()->create();
        $team = $this->createTeam($user);

        $project = Project::create([
            'team_id' => $team->id,
            'user_id' => $user->id,
            'topic' => 'content marketing',
            'website' => 'acme.com',
        ]);

        $fake = $this->makeFakeGemini();
        $generator = new KeywordClusterGenerator($fake, new DataForSeoService);

        $generator->generateSubtopics($project);
        $this->assertCount(5, $project->subtopics()->get());

        foreach ($project->subtopics as $sub) {
            $generator->generateQuestionsForSubtopic($sub);
            $this->assertCount(10, $sub->questions()->get());
            $generator->generateAnswersForSubtopic($sub);
            $this->assertSame(10, $sub->questions()->whereNotNull('answer')->count());
            $generator->generateClusterPage($sub->fresh());
        }

        $generator->generatePillarPage($project->fresh());

        $project->refresh();
        $this->assertNotEmpty($project->pillar_title);
        $this->assertNotEmpty($project->pillar_content);
        foreach ($project->subtopics as $sub) {
            $this->assertNotEmpty($sub->cluster_title);
            $this->assertNotEmpty($sub->cluster_content);
        }
    }

    public function test_user_can_delete_project(): void
    {
        $user = User::factory()->create();
        $team = $this->createTeam($user);
        $this->subscribe($team);

        $project = Project::create([
            'team_id' => $team->id,
            'user_id' => $user->id,
            'topic' => 'delete me',
            'website' => 'example.com',
        ]);

        $this->actingAs($user)
            ->delete(route('clusterforge.destroy', $project))
            ->assertRedirect(route('clusterforge.index'))
            ->assertSessionHas('success', 'Project deleted.');

        $this->assertDatabaseMissing('clusterforge_projects', ['id' => $project->id]);
    }

    public function test_browser_delete_form_redirects_to_index(): void
    {
        $user = User::factory()->create();
        $team = $this->createTeam($user);
        $this->subscribe($team);

        $project = Project::create([
            'team_id' => $team->id,
            'user_id' => $user->id,
            'topic' => 'delete me',
            'website' => 'example.com',
        ]);

        $this->actingAs($user)
            ->post(route('clusterforge.destroy', $project), ['_method' => 'DELETE'])
            ->assertRedirect(route('clusterforge.index'));

        $this->assertDatabaseMissing('clusterforge_projects', ['id' => $project->id]);
    }

    public function test_delete_without_method_override_is_not_allowed(): void
    {
        $user = User::factory()->create();
        $team = $this->createTeam($user);
        $this->subscribe($team);

        $project = Project::create([
            'team_id' => $team->id,
            'user_id' => $user->id,
            'topic' => 'delete me',
            'website' => 'example.com',
        ]);

        $this->actingAs($user)
            ->post(route('clusterforge.destroy', $project))
            ->assertStatus(405);

        $this->assertDatabaseHas('clusterforge_projects', ['id' => $project->id]);
    }

    public function test_generator_prompts_include_project_language(): void
    {
        $user = User::factory()->create();
        $team = $this->createTeam($user);

        $project = Project::create([
            'team_id' => $team->id,
            'user_id' => $user->id,
            'topic' => 'Content-Marketing',
            'website' => 'acme.de',
            'language' => 'de',
        ]);

        $capturing = new class extends GeminiService
        {
            public array $prompts = [];

            public function __construct() {}

            public function generateText(string $prompt, float $temperature = 0.7): string
            {
                $this->prompts[] = $prompt;
                if (str_contains($prompt, '[ANSWER N]')) {
                    $lines = [];
                    for ($i = 1; $i <= 10; $i++) {
                        $lines[] = "[ANSWER $i]";
                        $lines[] = "Antwort $i.";
                        $lines[] = '';
                    }

                    return implode("\n", $lines);
                }

                return 'Pillar body';
            }

            public function generateJson(string $prompt, float $temperature = 0.6): array
            {
                $this->prompts[] = $prompt;
                if (stripos($prompt, 'sub-topics that together form') !== false) {
                    return array_map(fn ($i) => [
                        'title' => "Unterthema $i",
                        'long_tail_keyword' => "schlagwort $i",
                        'description' => "Beschreibung $i",
                    ], range(1, 5));
                }
                if (str_contains($prompt, 'questions that real users')) {
                    return array_map(fn ($i) => "Frage $i?", range(1, 10));
                }
                if (str_contains($prompt, 'cluster page that targets the long-tail keyword')) {
                    return ['title' => 'Titel', 'meta_description' => 'meta', 'introduction_markdown' => 'Einleitung'];
                }

                return ['title' => 'Pillar-Titel', 'meta_description' => 'pillar meta'];
            }
        };

        $generator = new KeywordClusterGenerator($capturing, new DataForSeoService);

        $generator->generateSubtopics($project);
        foreach ($project->subtopics as $sub) {
            $generator->generateQuestionsForSubtopic($sub);
            $generator->generateAnswersForSubtopic($sub);
            $generator->generateClusterPage($sub->fresh());
        }
        $generator->generatePillarPage($project->fresh());

        $allPrompts = implode("\n---\n", $capturing->prompts);
        $this->assertStringContainsString('Write ALL output in German', $allPrompts);
        foreach ($capturing->prompts as $p) {
            $this->assertStringContainsString('Write ALL output in German', $p);
        }
        $this->assertStringContainsString('Häufig gestellte Fragen', $project->fresh()->subtopics->first()->cluster_content);
    }

    public function test_export_pillar_and_cluster(): void
    {
        $user = User::factory()->create();
        $team = $this->createTeam($user);
        $this->subscribe($team);

        $project = Project::create([
            'team_id' => $team->id,
            'user_id' => $user->id,
            'topic' => 'My Topic',
            'website' => 'example.com',
            'pillar_title' => 'Pillar Title',
            'pillar_meta_description' => 'Pillar meta',
            'pillar_content' => '# Pillar\n\nbody',
            'status' => Project::STATUS_COMPLETED,
        ]);

        $sub = $project->subtopics()->create([
            'title' => 'Subtopic',
            'long_tail_keyword' => 'long tail',
            'cluster_title' => 'Cluster Title',
            'cluster_meta_description' => 'Cluster meta',
            'cluster_content' => '# Cluster\n\ncontent',
        ]);

        $this->actingAs($user)
            ->get(route('clusterforge.export.pillar', $project))
            ->assertOk()
            ->assertHeader('Content-Disposition', 'attachment; filename="pillar-my-topic.md"')
            ->assertSee('Pillar Title');

        $this->actingAs($user)
            ->get(route('clusterforge.export.cluster', [$project, $sub]))
            ->assertOk()
            ->assertHeader('Content-Disposition', 'attachment; filename="cluster-long-tail.md"')
            ->assertSee('Cluster Title');
    }

    protected function makeFakeGemini(): GeminiService
    {
        return new class extends GeminiService
        {
            public function __construct() {}

            public function generateText(string $prompt, float $temperature = 0.7): string
            {
                if (str_contains($prompt, '[ANSWER N]')) {
                    $lines = [];
                    for ($i = 1; $i <= 10; $i++) {
                        $lines[] = "[ANSWER $i]";
                        $lines[] = "Answer $i.";
                        $lines[] = '';
                    }

                    return implode("\n", $lines);
                }

                return 'fake text';
            }

            public function generateJson(string $prompt, float $temperature = 0.6): array
            {
                if (stripos($prompt, 'sub-topics that together form') !== false) {
                    return array_map(fn ($i) => [
                        'title' => "Subtopic $i",
                        'long_tail_keyword' => "keyword $i",
                        'description' => "Description $i",
                    ], range(1, 5));
                }
                if (str_contains($prompt, 'questions that real users')) {
                    return array_map(fn ($i) => "Question $i?", range(1, 10));
                }
                if (str_contains($prompt, 'Write a clear, useful answer')) {
                    return array_map(fn ($i) => [
                        'question' => "Question $i?",
                        'answer' => "Answer $i.",
                    ], range(1, 10));
                }
                if (str_contains($prompt, 'cluster page that targets the long-tail keyword')) {
                    return [
                        'title' => 'Cluster Page Title',
                        'meta_description' => 'meta',
                        'introduction_markdown' => 'intro',
                    ];
                }
                if (str_contains($prompt, 'PILLAR page')) {
                    return [
                        'title' => 'Pillar Page Title',
                        'meta_description' => 'pillar meta',
                    ];
                }

                return [];
            }
        };
    }
}
