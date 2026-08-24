<?php

namespace Tests\Feature;

use App\Models\AllocoreScore;
use App\Models\GlossaryTerm;
use App\Models\Module;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\AuditPro\Models\Audit;
use Tests\TestCase;

class DashboardCoachTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_allocore_coach_with_a_score(): void
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

        $this->actingAs($user);

        $audit = Audit::create([
            'team_id' => $team->id,
            'created_by' => $user->id,
            'status' => 'completed',
            'company_name' => $team->name,
            'industry' => $team->industry,
            'size' => $team->size,
        ]);

        $module = Module::create([
            'key' => 'financial-platform',
            'name' => 'Financial Platform',
            'description' => 'Track revenue and financial KPIs for your company.',
            'route_prefix' => 'financial-platform',
            'is_active' => true,
        ]);

        GlossaryTerm::create([
            'term' => 'Revenue Run Rate',
            'slug' => 'revenue-run-rate',
            'definition' => 'Projected annual revenue based on recent monthly recurring revenue.',
            'simple_definition' => 'How much money your business is on track to make in a year.',
            'related_modules' => ['financial-platform'],
            'is_published' => true,
            'is_beginner_friendly' => true,
        ]);

        AllocoreScore::create([
            'team_id' => $team->id,
            'audit_id' => $audit->id,
            'company_name' => $team->name,
            'industry' => 'software',
            'size' => '11-50',
            'company_age' => 5,
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
        ]);

        $response = $this->get(route('dashboard'))
            ->assertOk();

        file_put_contents('/tmp/coach_response.html', $response->getContent());

        $response->assertSee('Personal Allocore Coach')
            ->assertSee('Something positive')
            ->assertSee('Biggest problem')
            ->assertSee('Recommended tool')
            ->assertSee('Knowledge library');
    }

    public function test_dashboard_renders_without_a_score(): void
    {
        $user = User::factory()->create();
        $team = Team::create([
            'name' => 'Beta GmbH',
            'owner_id' => $user->id,
            'industry' => 'consulting',
            'size' => '2-10',
            'country' => 'AT',
            'revenue_range' => '0-1m',
        ]);
        $team->members()->attach($user->id, ['role' => 'owner']);
        $user->update(['current_team_id' => $team->id]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Discover your Allocore Score');
    }
}
