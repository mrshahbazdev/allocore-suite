<?php

use App\Models\Module;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Module::updateOrCreate(
            ['key' => 'dental-track'],
            [
                'name' => 'DentalTrack',
                'description' => 'QR-based production tracking system for dental laboratories with real-time dashboards and AI-powered completion predictions.',
                'icon' => 'qr-code',
                'route_prefix' => 'dentaltrack',
            ]
        );
    }

    public function down(): void
    {
        Module::where('key', 'dental-track')->delete();
    }
};
