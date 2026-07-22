<?php

namespace App\Support;

use App\Models\Module;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class GlobalSearch
{
    public function __construct(protected ModuleStats $moduleStats) {}

    protected array $searchableColumns = [
        'name', 'title', 'name_en', 'name_de', 'statement', 'description',
        'email', 'first_name', 'last_name', 'company', 'website',
        'invoice_number', 'patient_ref', 'doctor_name', 'question',
        'project_name', 'company_name', 'notes',
    ];

    public function search(User $user, string $query): array
    {
        $term = trim($query);
        $results = [];

        if ($term === '') {
            return $results;
        }

        $modules = Module::where('is_active', true)->orderBy('name')->get();

        foreach ($modules as $module) {
            if (! $user->hasModule($module->key)) {
                continue;
            }

            $modelClass = $this->moduleStats->modelFor($module->key);

            if (! $modelClass || ! class_exists($modelClass)) {
                continue;
            }

            $records = $this->searchModel($modelClass, $term, $user, $module->route_prefix);

            if ($records->isNotEmpty()) {
                $results[] = [
                    'module' => $module->name,
                    'module_key' => $module->key,
                    'route_prefix' => $module->route_prefix,
                    'records' => $records->all(),
                ];
            }
        }

        return $results;
    }

    protected function searchModel(string $modelClass, string $term, User $user, string $routePrefix)
    {
        $model = new $modelClass;
        $table = $model->getTable();
        $columns = Schema::getColumnListing($table);

        $searchable = array_intersect($this->searchableColumns, $columns);

        if (empty($searchable)) {
            return collect();
        }

        $query = $modelClass::query()->limit(10);

        if ($user->current_team_id && in_array('team_id', $columns)) {
            $query->where('team_id', $user->current_team_id);
        }

        $query->where(function ($q) use ($searchable, $term) {
            foreach ($searchable as $column) {
                $q->orWhereRaw("LOWER({$column}) LIKE ?", ['%'.mb_strtolower($term).'%']);
            }
        });

        return $query->get()->map(function ($record) use ($routePrefix) {
            return [
                'id' => $record->getKey(),
                'title' => $this->titleFor($record),
                'url' => url('app/'.$routePrefix),
            ];
        });
    }

    protected function titleFor($record): string
    {
        foreach ($this->searchableColumns as $column) {
            $value = $record->getAttribute($column);
            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return '#'.$record->getKey();
    }
}
