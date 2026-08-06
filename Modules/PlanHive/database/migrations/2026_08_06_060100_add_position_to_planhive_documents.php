<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('planhive_documents', function (Blueprint $table): void {
            $table->unsignedInteger('position')->default(0)->after('project_id');
            $table->index(['project_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::table('planhive_documents', function (Blueprint $table): void {
            $table->dropIndex(['project_id', 'position']);
            $table->dropColumn('position');
        });
    }
};
