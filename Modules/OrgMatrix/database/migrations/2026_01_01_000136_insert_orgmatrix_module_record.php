<?php

use App\Models\Module;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Module::updateOrCreate(['key' => 'org-matrix'], [
            'name' => 'OrgMatrix',
            'description' => 'Organizational intelligence platform: visualize org structures, roles, people, assignments and plan succession.',
            'icon' => 'user-group',
            'route_prefix' => 'orgmatrix',
        ]);
    }

    public function down(): void
    {
        Module::where('key', 'org-matrix')->delete();
    }
};
