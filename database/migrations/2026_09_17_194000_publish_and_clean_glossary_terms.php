<?php

use App\Models\GlossaryTerm;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ensure all new glossary terms (ID 84-88 and all active terms) are published
        DB::table('glossary_terms')
            ->whereIn('slug', ['dso-days-sales-outstanding', 'zahlungsziel', 'skonto', 'factoring', 'mahnwesen'])
            ->update([
                'is_published' => true,
                'category' => 'Profit',
            ]);

        // 2. Ensure any glossary terms with null or false is_published are activated
        DB::table('glossary_terms')
            ->where('is_published', false)
            ->whereNotIn('slug', ['liquiditat-1'])
            ->update(['is_published' => true]);

        // 3. Set category to Profit/Revenue for terms where category is empty
        DB::table('glossary_terms')
            ->whereNull('category')
            ->orWhere('category', '')
            ->update(['category' => 'Revenue']);

        // 4. Clean up duplicate liquiditat-1
        $duplicate = DB::table('glossary_terms')->where('slug', 'liquiditat-1')->first();
        if ($duplicate) {
            $canonical = DB::table('glossary_terms')
                ->whereIn('slug', ['liquiditaet', 'liquiditat'])
                ->where('id', '!=', $duplicate->id)
                ->first();

            if ($canonical) {
                DB::table('glossary_terms')->where('id', $duplicate->id)->delete();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
