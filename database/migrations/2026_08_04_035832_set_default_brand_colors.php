<?php

use App\Models\SiteSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    public function up(): void
    {
        $primary = SiteSetting::where('key', 'primary_color')->first();

        if ($primary && in_array($primary->value, ['#4f46e5', '#ff9200'], true)) {
            $primary->update(['value' => '#ff9200']);
            Cache::forget('site_setting_primary_color_en');
            Cache::forget('site_setting_primary_color_de');
            Cache::forget('site_setting_primary_color');
        }

        $accent = SiteSetting::where('key', 'accent_color')->first();

        if (! $accent) {
            SiteSetting::create(['key' => 'accent_color', 'value' => '#0094af']);
        } elseif (blank($accent->value) || $accent->value === '#4f46e5') {
            $accent->update(['value' => '#0094af']);
            Cache::forget('site_setting_accent_color_en');
            Cache::forget('site_setting_accent_color_de');
            Cache::forget('site_setting_accent_color');
        }
    }

    public function down(): void
    {
        // Brand colors are not reversible to the old indigo default.
    }
};
