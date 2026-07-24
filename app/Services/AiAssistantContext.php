<?php

namespace App\Services;

use App\Models\Module;
use App\Models\User;
use App\Support\ModuleStats;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class AiAssistantContext
{
    public function forModule(User $user, ?string $moduleKey): string
    {
        if (! $moduleKey) {
            return '';
        }

        $module = Module::where('key', $moduleKey)->where('is_active', true)->first();

        if (! $module) {
            return '';
        }

        $stats = app(ModuleStats::class)->forModule($user, $module);
        $parts = ["The user is currently viewing the {$module->name} module ({$module->key})."];

        if (! $stats['accessible']) {
            $parts[] = 'The user is not subscribed to this module.';
        } else {
            $parts[] = "Primary resource: {$stats['primary_resource']} (count: {$stats['primary_resource_count']}).";

            $recent = $this->recentRecords($module, $user);
            if ($recent) {
                $parts[] = "Recent records: {$recent}.";
            }
        }

        return implode(' ', $parts);
    }

    public static function currentModuleKey(): ?string
    {
        $modules = Module::where('is_active', true)->get(['key', 'route_prefix']);

        $urlSegment = request()->segment(2);
        if ($urlSegment) {
            $module = $modules->first(fn ($m) => $m->route_prefix === $urlSegment);
            if ($module) {
                return $module->key;
            }
        }

        $routeName = Route::currentRouteName();
        if ($routeName) {
            $prefix = explode('.', $routeName)[0];
            $normalized = self::normalize($prefix);

            $module = $modules->first(
                fn ($m) => $m->route_prefix === $prefix || self::normalize($m->key) === $normalized
            );

            if ($module) {
                return $module->key;
            }
        }

        return null;
    }

    protected static function normalize(string $value): string
    {
        return strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $value));
    }

    protected function recentRecords(Module $module, User $user): ?string
    {
        $modelClass = app(ModuleStats::class)->modelFor($module->key);

        if (! $modelClass || ! class_exists($modelClass)) {
            return null;
        }

        $model = new $modelClass;
        $table = $model->getTable();

        if (! Schema::hasTable($table)) {
            return null;
        }

        $query = $modelClass::query()->latest();

        if (Schema::hasColumn($table, 'team_id') && $user->current_team_id) {
            $query->where($table.'.team_id', $user->current_team_id);
        }

        $records = $query->limit(5)->get();

        if ($records->isEmpty()) {
            return null;
        }

        return $records->map(fn ($record) => $record->name ?? $record->title ?? '#'.$record->id)->implode(', ');
    }
}
