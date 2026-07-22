<?php

namespace Modules\CashCore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\CashCore\Models\Concerns\BelongsToCurrentTeam;

class CashCategory extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'cashcore_categories';

    protected $fillable = [
        'team_id', 'user_id', 'name', 'type', 'icon', 'color', 'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(CashTransaction::class, 'cashcore_category_id');
    }

    public static function getDefaults(int $teamId, ?int $userId = null): void
    {
        $defaults = [
            ['name' => 'Revenue / Sales', 'type' => 'income', 'icon' => '💰', 'color' => '#22c55e'],
            ['name' => 'Services', 'type' => 'income', 'icon' => '🛠️', 'color' => '#3b82f6'],
            ['name' => 'Other Income', 'type' => 'income', 'icon' => '📥', 'color' => '#8b5cf6'],
            ['name' => 'Rent / Office', 'type' => 'expense', 'icon' => '🏢', 'color' => '#ef4444'],
            ['name' => 'Software / Tools', 'type' => 'expense', 'icon' => '💻', 'color' => '#f59e0b'],
            ['name' => 'Salaries', 'type' => 'expense', 'icon' => '👥', 'color' => '#ec4899'],
            ['name' => 'Marketing', 'type' => 'expense', 'icon' => '📢', 'color' => '#14b8a6'],
            ['name' => 'Insurance', 'type' => 'expense', 'icon' => '🛡️', 'color' => '#6366f1'],
            ['name' => 'Travel', 'type' => 'expense', 'icon' => '✈️', 'color' => '#f97316'],
            ['name' => 'Miscellaneous', 'type' => 'expense', 'icon' => '📦', 'color' => '#78716c'],
        ];

        foreach ($defaults as $cat) {
            self::firstOrCreate(
                ['team_id' => $teamId, 'name' => $cat['name']],
                array_merge($cat, ['team_id' => $teamId, 'user_id' => $userId, 'is_default' => true])
            );
        }
    }
}
