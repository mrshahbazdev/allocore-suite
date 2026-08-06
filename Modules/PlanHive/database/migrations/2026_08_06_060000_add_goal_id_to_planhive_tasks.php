<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('planhive_tasks', function (Blueprint $table): void {
            $table->foreignId('goal_id')->nullable()->after('project_id')->constrained('planhive_goals')->nullOnDelete();
            $table->index(['goal_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('planhive_tasks', function (Blueprint $table): void {
            $table->dropIndex(['goal_id', 'status']);
            $table->dropConstrainedForeignIds('goal_id');
        });
    }
};
