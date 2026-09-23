<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookintelligence_books', function (Blueprint $table): void {
            $table->text('affiliate_link')->nullable()->change();
            $table->text('cover_url')->nullable()->change();
        });

        Schema::table('bookintelligence_authors', function (Blueprint $table): void {
            $table->text('website')->nullable()->change();
        });

        Schema::table('bookintelligence_publishers', function (Blueprint $table): void {
            $table->text('website')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('bookintelligence_books', function (Blueprint $table): void {
            $table->string('affiliate_link')->nullable()->change();
            $table->string('cover_url')->nullable()->change();
        });

        Schema::table('bookintelligence_authors', function (Blueprint $table): void {
            $table->string('website')->nullable()->change();
        });

        Schema::table('bookintelligence_publishers', function (Blueprint $table): void {
            $table->string('website')->nullable()->change();
        });
    }
};
