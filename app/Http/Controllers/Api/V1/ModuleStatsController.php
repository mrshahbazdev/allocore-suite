<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Module;
use App\Support\ModuleStats;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ModuleStatsController extends Controller
{
    public function __construct(protected ModuleStats $stats) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'modules' => collect($this->stats->forUser($request->user()))
                ->map(fn ($item, $key) => array_merge(['key' => $key], $item))
                ->values(),
        ]);
    }

    public function show(Request $request, string $module): JsonResponse
    {
        $moduleModel = Module::where('is_active', true)->where('key', $module)->firstOrFail();

        return response()->json($this->stats->forModule($request->user(), $moduleModel));
    }
}
