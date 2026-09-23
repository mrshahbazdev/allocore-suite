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

        // 4. Update SEO term category to Influence
        DB::table('glossary_terms')
            ->where('slug', 'seo')
            ->update([
                'category' => 'Influence',
                'is_published' => true,
            ]);

        // 5. Provision SEA (Suchmaschinenwerbung) term under Influence if not exists
        $seaExists = DB::table('glossary_terms')->where('slug', 'sea')->exists();
        if (! $seaExists) {
            DB::table('glossary_terms')->insert([
                'term' => 'SEA (Suchmaschinenwerbung)',
                'slug' => 'sea',
                'category' => 'Influence',
                'simple_definition' => 'Kostenpflichtige Werbeanzeigen in Suchmaschinen (z. B. Google Ads), um sofort an oberster Stelle für relevante Suchbegriffe gefunden zu werden und qualifizierte Interessenten zu gewinnen.',
                'definition' => "Search Engine Advertising (SEA), im Deutschen als Suchmaschinenwerbung bekannt, umfasst das Schalten bezahlter Text- und Shopping-Anzeigen in Suchmaschinen wie Google oder Bing.\n\nIm Gegensatz zu SEO (organische Reichweite) liefert SEA sofortige Sichtbarkeit und messbare Klicks ab dem ersten Tag der Kampagnenschaltung.\n\n• Funktionsweise: Die Abrechnung erfolgt typischerweise nach dem Pay-per-Click-Modell (PPC). Unternehmen bieten auf spezifische Keywords und zahlen nur, wenn ein Nutzer auf die Anzeige klickt.\n• Zielgerichtetes Targeting: Anzeigen können präzise nach Standort (Local SEA), Zielgruppe, Gerät und Suchintention ausgesteuert werden.\n• Synergie mit SEO: SEA liefert wertvolle Keyword- und Conversion-Daten, die direkt für die langfristige organische SEO-Strategie genutzt werden können.\n• Wichtigste Steuerungs-KPIs: Click-Through-Rate (CTR), Cost-per-Click (CPC), Conversion-Rate und Return on Ad Spend (ROAS).",
                'is_beginner_friendly' => true,
                'is_published' => true,
                'sort_order' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 6. Clean up duplicate liquiditat-1
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
