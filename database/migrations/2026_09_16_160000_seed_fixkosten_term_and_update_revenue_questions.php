<?php

use App\Models\GlossaryTerm;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ensure Fixkosten glossary term exists
        if (Schema::hasTable('glossary_terms')) {
            GlossaryTerm::firstOrCreate(
                ['slug' => 'fixkosten'],
                [
                    'term' => 'Fixkosten',
                    'definition' => 'Fixkosten sind regelmäßige, zeitabhängige Betriebskosten eines Unternehmens (z.B. Miete, Gehälter, Software-Lizenzen, Versicherungen), die unabhängig von der aktuellen Auslastung oder Verkaufsmenge anfallen. Ihre exakte Deckung bestimmt den monatlichen Mindestumsatz.',
                    'simple_definition' => 'Feste monatliche Kosten wie Miete, Löhne und Abos, die Sie immer zahlen müssen – egal wie viel Umsatz Sie machen.',
                    'category' => 'Finanzen',
                    'related_modules' => ['revenue-planner', 'cash-core', 'invoice-maker'],
                    'is_published' => true,
                    'is_beginner_friendly' => true,
                    'sort_order' => 1,
                ]
            );

            GlossaryTerm::firstOrCreate(
                ['slug' => 'businessplan'],
                [
                    'term' => 'Businessplan',
                    'definition' => 'Ein Businessplan beschreibt das Geschäftsmodell, die Marktpositionierung, Vertriebsstrategien und die vollständige Finanz- und Umsatzplanung eines Unternehmens.',
                    'simple_definition' => 'Der schriftliche Fahrplan für Ihr Unternehmen mit allen Zielen, Kosten und Umsatzplanungen.',
                    'category' => 'Strategie',
                    'related_modules' => ['revenue-planner', 'vision-flow'],
                    'is_published' => true,
                    'is_beginner_friendly' => true,
                    'sort_order' => 2,
                ]
            );
        }

        // 2. Update existing audit questions for monthly revenue to point to revenue-planner & fixkosten
        if (Schema::hasTable('auditpro_questions')) {
            DB::table('auditpro_questions')
                ->where(function ($query) {
                    $query->where('question', 'like', '%monthly revenue%')
                        ->orWhere('question', 'like', '%monatliche%Umsatz%')
                        ->orWhere('question', 'like', '%erforderliche%Umsatz%');
                })
                ->update([
                    'recommended_module_key' => 'revenue-planner',
                    'knowledge_slug' => 'fixkosten',
                ]);
        }
    }

    public function down(): void
    {
        // Non-destructive rollback
    }
};
