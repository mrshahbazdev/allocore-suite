<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notification_templates', function (Blueprint $table) {
            $table->dropUnique(['key', 'locale', 'type']);
            $table->unique(['tool', 'key', 'locale', 'type']);
        });
    }

    public function down(): void
    {
        Schema::table('notification_templates', function (Blueprint $table) {
            $table->dropUnique(['tool', 'key', 'locale', 'type']);
            $table->unique(['key', 'locale', 'type']);
        });
    }
};
