<?php

use App\Models\Module;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Module::where('key', 'time-butler')->update(['name' => 'Time Check']);
    }

    public function down(): void
    {
        Module::where('key', 'time-butler')->update(['name' => 'TimeButler']);
    }
};
