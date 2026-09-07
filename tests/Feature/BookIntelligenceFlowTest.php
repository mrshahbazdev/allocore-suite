<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\Plan;
use App\Models\Team;
use App\Models\ToolSubscription;
use App\Models\User;
use App\Services\AiKnowledgeRetrieval;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\BookIntelligence\Models\Author;
use Modules\BookIntelligence\Models\Book;
use Modules\BookIntelligence\Models\Publisher;
use Modules\BookIntelligence\Models\ReadingProgress;
use Modules\BookIntelligence\Models\Topic;
use Tests\TestCase;

class BookIntelligenceFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_requires_book_intelligence_subscription(): void
    {
        $user = User::factory()->create();
        $this->createTeam($user);

        $this->actingAs($user)
            ->get(route('bookintelligence.dashboard'))
            ->assertRedirect(route('billing.plans', ['module' => 'book-intelligence']));
    }

    public function test_user_can_build_and_use_a_guided_book_library(): void
    {
        $user = User::factory()->create();
        $team = $this->createTeam($user);
        $this->subscribe($team);

        $this->actingAs($user)
            ->get(route('bookintelligence.dashboard'))
            ->assertOk()
            ->assertSee(__('Build knowledge people can actually find and use.'))
            ->assertSee(__('Set up in three steps'));

        $this->actingAs($user)
            ->post(route('bookintelligence.setup.authors.store'), [
                'name' => 'Verne Harnish',
                'website' => 'https://example.com/verne-harnish',
                'bio' => 'Business growth author.',
            ])
            ->assertRedirect();

        $this->actingAs($user)
            ->post(route('bookintelligence.setup.publishers.store'), [
                'name' => 'Gazelles',
                'website' => 'https://example.com/gazelles',
            ])
            ->assertRedirect();

        $this->actingAs($user)
            ->post(route('bookintelligence.setup.topics.store'), [
                'name' => 'Business Growth',
                'description' => 'Scaling and operating a growing company.',
            ])
            ->assertRedirect();

        $mainTopic = Topic::where('name', 'Business Growth')->firstOrFail();

        $this->actingAs($user)
            ->post(route('bookintelligence.setup.topics.store'), [
                'name' => 'Execution',
                'parent_id' => $mainTopic->id,
            ])
            ->assertRedirect();

        $author = Author::where('name', 'Verne Harnish')->firstOrFail();
        $publisher = Publisher::where('name', 'Gazelles')->firstOrFail();
        $subtopic = Topic::where('name', 'Execution')->firstOrFail();

        $response = $this->actingAs($user)
            ->post(route('bookintelligence.books.store'), [
                'title' => 'Scaling Up',
                'author_id' => $author->id,
                'publisher_id' => $publisher->id,
                'main_topic_id' => $mainTopic->id,
                'subtopic_ids' => [$subtopic->id],
                'isbn' => '9780986019593',
                'publication_year' => 2014,
                'page_count' => 256,
                'language' => 'en',
                'difficulty' => 'advanced',
                'description' => 'A practical system for scaling people, strategy, execution, and cash.',
                'relevant_roles_text' => "Leadership\nOperations",
                'affiliate_link' => 'https://example.com/books/scaling-up',
                'status' => 'active',
                'reading_status' => 'planned',
                'progress_percent' => 0,
                'reading_notes' => 'Focus on the execution framework.',
            ]);

        $book = Book::where('title', 'Scaling Up')->firstOrFail();
        $response->assertRedirect(route('bookintelligence.books.show', $book));

        $this->assertSame($team->id, $book->team_id);
        $this->assertSame(['Leadership', 'Operations'], $book->relevant_roles);
        $this->assertTrue($book->subtopics->contains($subtopic));
        $this->assertDatabaseHas('bookintelligence_reading_progress', [
            'book_id' => $book->id,
            'user_id' => $user->id,
            'status' => 'planned',
        ]);

        $this->actingAs($user)
            ->get(route('bookintelligence.books.index', [
                'q' => 'Scaling',
                'topic' => $subtopic->id,
                'difficulty' => 'advanced',
                'reading_status' => 'planned',
            ]))
            ->assertOk()
            ->assertSee('Scaling Up')
            ->assertSee('Verne Harnish');

        $this->actingAs($user)
            ->put(route('bookintelligence.books.reading-progress', $book), [
                'reading_status' => 'reading',
                'progress_percent' => 37,
                'reading_notes' => 'Working through the execution chapter.',
            ])
            ->assertRedirect();

        $progress = ReadingProgress::where('book_id', $book->id)->firstOrFail();
        $this->assertSame('reading', $progress->status);
        $this->assertSame(37, $progress->progress_percent);
        $this->assertNotNull($progress->started_at);

        $this->actingAs($user)
            ->get(route('bookintelligence.books.show', $book))
            ->assertOk()
            ->assertSee('A practical system for scaling')
            ->assertSee('Leadership')
            ->assertSee('37%')
            ->assertSeeHtml('step="1"');
    }

    public function test_books_are_isolated_between_teams(): void
    {
        $user = User::factory()->create();
        $team = $this->createTeam($user);
        $this->subscribe($team);

        $otherUser = User::factory()->create();
        $otherTeam = $this->createTeam($otherUser);
        $otherBook = Book::withoutGlobalScopes()->create([
            'team_id' => $otherTeam->id,
            'user_id' => $otherUser->id,
            'title' => 'Private Team Book',
            'slug' => 'private-team-book',
            'language' => 'en',
            'difficulty' => 'intermediate',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('bookintelligence.books.show', $otherBook))
            ->assertNotFound();
    }

    public function test_ai_knowledge_retrieval_can_find_relevant_books(): void
    {
        $user = User::factory()->create();
        $team = $this->createTeam($user);
        $this->subscribe($team);

        $this->actingAs($user);

        Book::create([
            'title' => 'Scaling Sales Teams',
            'slug' => 'scaling-sales-teams',
            'language' => 'en',
            'difficulty' => 'advanced',
            'description' => 'A repeatable framework for hiring sales leaders and scaling a revenue organization.',
            'relevant_roles' => ['Sales', 'Leadership'],
            'status' => 'active',
        ]);

        $results = app(AiKnowledgeRetrieval::class)->search($user, 'How can I scale a sales organization?');

        $this->assertTrue($results->contains(
            fn (array $result) => $result['title'] === 'Scaling Sales Teams'
                && str_contains($result['url'], '/app/books/library/'),
        ));
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
        $module = Module::firstOrCreate(
            ['key' => 'book-intelligence'],
            [
                'name' => 'Knowledge Library',
                'route_prefix' => 'books',
                'is_active' => true,
            ],
        );
        $plan = Plan::create([
            'name' => 'Book Intelligence test plan',
            'slug' => 'book-intelligence-test-plan-'.fake()->unique()->randomNumber(),
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
