<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoicemaker_templates', function (Blueprint $table) {
            $table->string('logo_position')->default('left')->after('header_style');
            $table->string('signature_path')->nullable()->after('logo_position');
            $table->boolean('enable_qr')->default(false)->after('signature_path');
            $table->string('font_family')->default('sans')->change();
            $table->string('header_style')->default('default')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoicemaker_templates', function (Blueprint $table) {
            $table->dropColumn(['logo_position', 'signature_path', 'enable_qr']);
        });
    }
};
