<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\Plan;
use App\Models\Team;
use App\Models\ToolSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\LoopEngine\Models\Process;
use Tests\TestCase;

class AiAssistantKnowledgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_assistant_replies_with_loop_engine_process_source(): void
    {
        $user = User::factory()->create();
        $team = Team::create(['name' => fake()->company(), 'owner_id' => $user->id]);
        $team->members()->attach($user->id, ['role' => 'owner']);
        $user->update(['current_team_id' => $team->id]);

        $module = Module::firstOrCreate(
            ['key' => 'loop-engine'],
            ['name' => 'Loop Engine', 'route_prefix' => 'loopengine']
        );
        $plan = Plan::create([
            'name' => 'AI test plan',
            'slug' => 'ai-test-plan-'.fake()->unique()->randomNumber(),
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

        $process = Process::create([
            'team_id' => $team->id,
            'user_id' => $user->id,
            'name_en' => 'Create a Customer',
            'name_de' => 'Kunden anlegen',
            'description_en' => 'Step-by-step process for creating a new customer record.',
            'description_de' => 'Schritt-für-Schritt-Prozess zur Neuanlage eines Kunden.',
            'status' => 'published',
            'version' => 1,
            'is_latest_version' => true,
        ]);
        $process->steps()->create([
            'team_id' => $team->id,
            'order' => 1,
            'question_en' => 'Open the CRM and click New Customer.',
            'question_de' => 'Öffne das CRM und klicke auf Neuer Kunde.',
            'help_text_en' => 'Make sure you have the customer email address ready.',
            'help_text_de' => 'Stelle sicher, dass du die Kunden-E-Mail-Adresse bereithältst.',
            'step_type' => 'action',
            'is_required' => true,
        ]);

        $response = $this->actingAs($user)
            ->withHeaders(['Accept' => 'application/json'])
            ->postJson(route('assistant.store'), [
                'message' => 'How do I create a customer?',
                'module_key' => null,
                'page_url' => null,
            ]);

        $response->assertOk()
            ->assertJsonPath('reply', fn ($reply) => str_contains($reply, 'customer'))
            ->assertJsonPath('sources', fn ($sources) => count($sources) > 0 && str_contains($sources[0]['title'], 'Customer'));
    }
}
