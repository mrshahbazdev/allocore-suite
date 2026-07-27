<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->string('subdomain')->nullable()->unique()->after('size');
            $table->string('custom_domain')->nullable()->unique()->after('subdomain');
            $table->string('logo')->nullable()->after('custom_domain');
            $table->string('favicon')->nullable()->after('logo');
            $table->string('primary_color')->nullable()->after('favicon');
            $table->string('accent_color')->nullable()->after('primary_color');
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn(['subdomain', 'custom_domain', 'logo', 'favicon', 'primary_color', 'accent_color']);
        });
    }
};
