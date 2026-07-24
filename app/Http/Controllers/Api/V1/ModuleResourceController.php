<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Module;
use App\Support\ModuleStats;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ModuleResourceController extends Controller
{
    public function index(Request $request, string $module): JsonResponse
    {
        $moduleModel = $this->resolveModule($module, $request);
        $modelClass = app(ModuleStats::class)->modelFor($moduleModel->key);

        if (! $modelClass || ! class_exists($modelClass)) {
            return response()->json(['message' => __('Module has no API resource.')], 404);
        }

        /** @var Model $query */
        $query = $modelClass::query();

        if ($request->boolean('with_recent')) {
            $query->latest();
        }

        return response()->json($query->paginate($request->integer('per_page', 25)));
    }

    public function show(Request $request, string $module, int $id): JsonResponse
    {
        $moduleModel = $this->resolveModule($module, $request);
        $modelClass = app(ModuleStats::class)->modelFor($moduleModel->key);

        if (! $modelClass || ! class_exists($modelClass)) {
            return response()->json(['message' => __('Module has no API resource.')], 404);
        }

        $record = $modelClass::findOrFail($id);

        return response()->json($record);
    }

    protected function resolveModule(string $module, Request $request): Module
    {
        $moduleModel = Module::where('is_active', true)
            ->where(function ($query) use ($module) {
                $query->where('key', $module)->orWhere('route_prefix', $module);
            })
            ->firstOrFail();

        abort_if(! $request->user()?->hasModule($moduleModel->key), 403, __('Module not accessible.'));

        return $moduleModel;
    }
}
