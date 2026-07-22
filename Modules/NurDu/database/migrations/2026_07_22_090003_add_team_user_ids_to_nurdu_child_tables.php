<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            'nurdu_guiding_principles' => ['team_id', 'user_id'],
            'nurdu_strategic_priorities' => ['team_id', 'user_id'],
            'nurdu_action_items' => ['team_id', 'user_id'],
        ] as $tableName => $columns) {
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
