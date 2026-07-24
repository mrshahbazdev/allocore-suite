<?php

namespace App\Services;

use App\Models\Module;
use App\Models\User;
use App\Support\ModuleStats;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Spatie\SimpleExcel\SimpleExcelReader;

class ModuleImporter
{
    public function supportedModules(): Collection
    {
        return Module::where('is_active', true)
            ->get()
            ->filter(fn (Module $module) => $this->modelFor($module->key) !== null)
            ->values();
    }

    public function preview(string $path, int $limit = 5): array
    {
        $rows = $this->reader($path)->getRows()->toArray();

        return array_slice($rows, 0, $limit);
    }

    public function headers(string $path): array
    {
        $first = $this->reader($path)->getRows()->toArray()[0] ?? [];

        return array_keys($first);
    }

    public function importableColumns(string $moduleKey): array
    {
        $modelClass = $this->modelFor($moduleKey);

        if (! $modelClass || ! class_exists($modelClass)) {
            return [];
        }

        $model = new $modelClass;
        $fillable = $model->getFillable();
        $table = $model->getTable();

        if (empty($fillable) && Schema::hasTable($table)) {
            $fillable = Schema::getColumnListing($table);
            $fillable = array_diff($fillable, ['id', 'created_at', 'updated_at', 'team_id', 'user_id']);
        }

        return $fillable;
    }

    public function import(string $moduleKey, string $path, User $user, array $mapping): array
    {
        $modelClass = $this->modelFor($moduleKey);

        if (! $modelClass || ! class_exists($modelClass)) {
            throw new \RuntimeException(__('Module has no importable records.'));
        }

        $importable = $this->importableColumns($moduleKey);
        $rows = $this->reader($path)->getRows()->toArray();

        $created = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $data = [];

            foreach ($mapping as $column => $header) {
                if (! in_array($column, $importable, true) || empty($header)) {
                    continue;
                }

                $data[$column] = $row[$header] ?? null;
            }

            if (empty(array_filter($data, fn ($v) => $v !== null && $v !== ''))) {
                continue;
            }

            $modelTable = (new $modelClass)->getTable();
            $columns = Schema::getColumnListing($modelTable);

            if (in_array('team_id', $columns, true)) {
                $data['team_id'] = $user->current_team_id;
            }

            if (in_array('user_id', $columns, true)) {
                $data['user_id'] = $user->id;
            }

            try {
                $modelClass::create($data);
                $created++;
            } catch (\Throwable $e) {
                $errors[] = __('Row :row: :message', ['row' => $index + 1, 'message' => $e->getMessage()]);
            }
        }

        return ['created' => $created, 'errors' => $errors];
    }

    protected function reader(string $path): SimpleExcelReader
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return $extension === 'xlsx'
            ? SimpleExcelReader::create($path, 'xlsx')
            : SimpleExcelReader::create($path, 'csv');
    }

    protected function modelFor(string $moduleKey): ?string
    {
        return app(ModuleStats::class)->modelFor($moduleKey);
    }
}
