<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasSimple = Schema::hasColumn('glossary_terms', 'simple_definition');
        $hasBeginner = Schema::hasColumn('glossary_terms', 'is_beginner_friendly');

        Schema::table('glossary_terms', function (Blueprint $table) use ($hasSimple, $hasBeginner) {
            if (! $hasSimple) {
                $table->text('simple_definition')->nullable()->after('definition');
            }

            if (! $hasBeginner) {
                $table->boolean('is_beginner_friendly')->default(false)->after('is_published');
            }
        });
    }

    public function down(): void
    {
        $hasSimple = Schema::hasColumn('glossary_terms', 'simple_definition');
        $hasBeginner = Schema::hasColumn('glossary_terms', 'is_beginner_friendly');

        Schema::table('glossary_terms', function (Blueprint $table) use ($hasSimple, $hasBeginner) {
            if ($hasSimple) {
                $table->dropColumn('simple_definition');
            }

            if ($hasBeginner) {
                $table->dropColumn('is_beginner_friendly');
            }
        });
    }
};
