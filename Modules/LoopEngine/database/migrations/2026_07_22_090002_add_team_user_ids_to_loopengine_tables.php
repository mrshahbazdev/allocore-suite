<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'loopengine_process_steps' => ['team_id', 'user_id'],
            'loopengine_step_options' => ['team_id', 'user_id'],
            'loopengine_step_transitions' => ['team_id', 'user_id'],
            'loopengine_process_runs' => ['user_id'],
            'loopengine_run_responses' => ['team_id', 'user_id'],
            'loopengine_run_logs' => ['team_id'],
            'loopengine_process_templates' => ['team_id', 'user_id'],
            'loopengine_template_ratings' => ['team_id'],
            'loopengine_webhooks' => ['user_id'],
            'loopengine_webhook_logs' => ['team_id', 'user_id'],
        ];

        foreach ($tables as $tableName => $columns) {
            Schema::table($tableName, function (Blueprint $table) use ($columns, $tableName): void {
                foreach ($columns as $column) {
                    if (! Schema::hasColumn($tableName, $column)) {
                        $table->foreignId($column)->nullable()->constrained($column === 'team_id' ? 'teams' : 'users')->onDelete('cascade');
                    }
                }
            });
        }
    }

    public function down(): void
    {
        //
    }
};
