<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('notification_templates')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            $this->dropIndexIfExists('notification_templates', 'notification_templates_key_locale_type_unique');

            DB::statement('ALTER TABLE notification_templates ADD UNIQUE notification_templates_tool_key_locale_type_unique (tool(100), `key`(100), locale(10), type(20))');

            return;
        }

        Schema::table('notification_templates', function (Blueprint $table) {
            $table->dropUnique(['key', 'locale', 'type']);
            $table->unique(['tool', 'key', 'locale', 'type']);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('notification_templates')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            $this->dropIndexIfExists('notification_templates', 'notification_templates_tool_key_locale_type_unique');

            DB::statement('ALTER TABLE notification_templates ADD UNIQUE notification_templates_key_locale_type_unique (`key`(100), locale(10), type(20))');

            return;
        }

        Schema::table('notification_templates', function (Blueprint $table) {
            $table->dropUnique(['tool', 'key', 'locale', 'type']);
            $table->unique(['key', 'locale', 'type']);
        });
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        $exists = DB::select(
            'SELECT 1 FROM information_schema.STATISTICS WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [DB::getDatabaseName(), $table, $index]
        );

        if ($exists) {
            DB::statement("ALTER TABLE {$table} DROP INDEX {$index}");
        }
    }
};
