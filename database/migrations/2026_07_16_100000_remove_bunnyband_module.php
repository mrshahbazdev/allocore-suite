<?php

use App\Models\Module;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('bunnyband_withdrawal_methods');
        Schema::dropIfExists('bunnyband_settings');
        Schema::dropIfExists('bunnyband_payment_methods');
        Schema::dropIfExists('bunnyband_notifications');
        Schema::dropIfExists('bunnyband_deposit_methods');
        Schema::dropIfExists('bunnyband_user_tasks');
        Schema::dropIfExists('bunnyband_transactions');
        Schema::dropIfExists('bunnyband_tasks');
        Schema::dropIfExists('bunnyband_referrals');
        Schema::dropIfExists('bunnyband_profiles');
        Schema::dropIfExists('bunnyband_levels');

        if (Schema::hasTable('modules')) {
            Module::where('key', 'bunny-band')->delete();
        }
    }

    public function down(): void
    {
        // Restoration is not supported; module can be re-installed from a previous commit.
    }
};
