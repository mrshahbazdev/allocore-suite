<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Module 6: Content & SEO Opportunities
        Schema::create('bookintelligence_content_opportunities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('book_id')->nullable()->constrained('bookintelligence_books')->nullOnDelete();
            $table->string('title');
            $table->string('target_keyword')->nullable();
            $table->string('content_type', 32)->default('blog'); // blog, faq, whitepaper, newsletter, linkedin
            $table->string('search_intent', 32)->default('informational');
            $table->string('estimated_demand', 16)->default('high'); // high, medium, niche
            $table->json('target_audience')->nullable();
            $table->text('angle_hook')->nullable();
            $table->string('status', 24)->default('idea'); // idea, in_progress, generated, published, archived
            $table->foreignId('generated_post_id')->nullable()->constrained('posts')->nullOnDelete();
            $table->timestamps();

            $table->index(['team_id', 'content_type']);
            $table->index(['team_id', 'status']);
        });

        // Module 7: Automated 8-Section Blog Creation
        Schema::create('bookintelligence_generated_blogs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('book_id')->nullable()->constrained('bookintelligence_books')->nullOnDelete();
            $table->foreignId('opportunity_id')->nullable()->constrained('bookintelligence_content_opportunities')->nullOnDelete();
            $table->foreignId('post_id')->nullable()->constrained('posts')->nullOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->string('target_keyword')->nullable();
            $table->text('meta_description')->nullable();
            $table->longText('section_1_problem')->nullable();
            $table->longText('section_2_root_causes')->nullable();
            $table->longText('section_3_solutions')->nullable();
            $table->longText('section_4_implementation')->nullable();
            $table->longText('section_5_common_mistakes')->nullable();
            $table->longText('section_6_summary')->nullable();
            $table->longText('section_7_cta')->nullable();
            $table->json('section_8_recommended_book')->nullable();
            $table->longText('full_html_content')->nullable();
            $table->string('status', 24)->default('draft'); // draft, published, archived
            $table->timestamps();

            $table->index(['team_id', 'status']);
        });

        // Module 8: Affiliate Link Click & Revenue Tracking
        Schema::create('bookintelligence_affiliate_clicks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained('bookintelligence_books')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('referrer_url')->nullable();
            $table->string('source_type', 32)->default('direct'); // blog_post, tool_guidance, search_engine, library, direct
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->boolean('is_converted')->default(false);
            $table->decimal('commission_amount', 8, 2)->default(0.00);
            $table->timestamps();

            $table->index(['team_id', 'book_id']);
            $table->index(['team_id', 'source_type']);
        });

        // Module 9: Book-to-Content Repurposed Bundles
        Schema::create('bookintelligence_repurposed_bundles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained('bookintelligence_books')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('blog_articles')->nullable();     // 20 blog concepts
            $table->json('linkedin_posts')->nullable();    // 50 LinkedIn posts
            $table->json('faq_articles')->nullable();      // 20 FAQ articles
            $table->json('checklists')->nullable();        // 10 Checklists
            $table->json('practical_guides')->nullable();  // 10 Guides
            $table->json('whitepaper_concepts')->nullable(); // 5 Whitepapers
            $table->timestamps();

            $table->unique(['team_id', 'book_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookintelligence_repurposed_bundles');
        Schema::dropIfExists('bookintelligence_affiliate_clicks');
        Schema::dropIfExists('bookintelligence_generated_blogs');
        Schema::dropIfExists('bookintelligence_content_opportunities');
    }
};
