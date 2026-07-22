<?php

use App\Models\Module;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Module::updateOrCreate(['key' => 'focus-matrix'], [
            'name' => 'FocusMatrix',
            'description' => 'Bilingual productivity OS for managers using the Only-You-Principle: triage, delegate, drop, self-check and team analytics.',
            'icon' => 'target-arrow',
            'route_prefix' => 'focusmatrix',
        ]);
    }

    public function down(): void
    {
        Module::where('key', 'focus-matrix')->delete();
    }
};
