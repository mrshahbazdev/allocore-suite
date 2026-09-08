<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revenue_planner_plans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name')->default('Jahres-Umsatzplan');
            $table->string('industry_type', 64)->default('service'); // service, trade, agency, commerce, other
            $table->unsignedSmallInteger('fiscal_year')->default(date('Y'));
            
            // Step 1: Target Revenue Inputs & Computed
            $table->decimal('fixed_costs_annual', 12, 2)->default(0);
            $table->decimal('fixed_costs_monthly', 12, 2)->default(0);
            $table->json('fixed_costs_breakdown')->nullable();
            $table->decimal('owner_compensation_annual', 12, 2)->default(0);
            $table->decimal('owner_compensation_monthly', 12, 2)->default(0);
            $table->json('owner_compensation_breakdown')->nullable();
            $table->decimal('profit_target_annual', 12, 2)->default(0);
            $table->decimal('tax_rate_percent', 5, 2)->default(30.00);
            $table->decimal('contribution_margin_percent', 5, 2)->default(100.00);
            $table->decimal('target_revenue_annual', 12, 2)->default(0);
            $table->decimal('target_revenue_monthly', 12, 2)->default(0);

            // Step 2: Actuals & Gap
            $table->decimal('actual_revenue_annual', 12, 2)->default(0);
            $table->decimal('actual_revenue_monthly', 12, 2)->default(0);
            $table->decimal('revenue_gap_annual', 12, 2)->default(0);
            $table->decimal('revenue_gap_monthly', 12, 2)->default(0);
            $table->decimal('coverage_ratio_percent', 5, 2)->default(0);

            // Step 3: Existing Customer Levers
            $table->decimal('existing_customer_potential_annual', 12, 2)->default(0);
            $table->json('existing_customer_levers')->nullable();
            $table->decimal('remaining_gap_annual', 12, 2)->default(0);
            $table->decimal('remaining_gap_monthly', 12, 2)->default(0);

            // Step 4: Reverse Sales Funnel
            $table->decimal('average_deal_size', 12, 2)->default(2500);
            $table->decimal('lead_to_close_rate_percent', 5, 2)->default(20.00);
            $table->unsignedInteger('required_won_deals_annual')->default(0);
            $table->unsignedInteger('required_won_deals_monthly')->default(0);
            $table->unsignedInteger('required_leads_annual')->default(0);
            $table->unsignedInteger('required_leads_monthly')->default(0);

            // Step 5: Seasonality & Roadmap
            $table->string('seasonality_pattern', 32)->default('even'); // even, summer_dip, q4_peak, b2b_service
            $table->json('monthly_distribution')->nullable();
            $table->json('action_roadmap')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 24)->default('active'); // active, archived, draft
            $table->timestamps();

            $table->index(['team_id', 'fiscal_year']);
            $table->index(['team_id', 'status']);
        });

        Schema::create('revenue_planner_scenarios', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('plan_id')->constrained('revenue_planner_plans')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('price_increase_percent', 5, 2)->default(0);
            $table->decimal('deal_size_multiplier', 5, 2)->default(1.00);
            $table->decimal('conversion_rate_delta_percent', 5, 2)->default(0);
            $table->decimal('lead_volume_multiplier', 5, 2)->default(1.00);
            $table->decimal('simulated_annual_revenue', 12, 2)->default(0);
            $table->decimal('simulated_revenue_gap', 12, 2)->default(0);
            $table->unsignedInteger('simulated_required_leads')->default(0);
            $table->json('simulated_monthly_distribution')->nullable();
            $table->timestamps();

            $table->index(['team_id', 'plan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revenue_planner_scenarios');
        Schema::dropIfExists('revenue_planner_plans');
    }
};
