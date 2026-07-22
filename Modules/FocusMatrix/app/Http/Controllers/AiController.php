<?php

namespace Modules\FocusMatrix\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\FocusMatrix\Models\AiSetting;
use Modules\FocusMatrix\Services\Ai\AiManager;

class AiController extends Controller
{
    public function __construct(private readonly AiManager $ai) {}

    public function index(Request $request): View
    {
        $setting = AiSetting::where('user_id', $request->user()->id)->first();

        return view('focusmatrix::ai.index', [
            'setting' => $setting ? [
                'provider' => $setting->provider,
                'model' => $setting->model,
                'enabled' => $setting->enabled,
                'has_key' => $setting->hasKey(),
                'masked_key' => $setting->maskedKey(),
                'calls_this_month' => $setting->calls_this_month,
                'monthly_limit' => $setting->monthly_limit,
                'remaining' => $setting->remainingQuota(),
                'last_called_at' => $setting->last_called_at,
            ] : null,
            'providers' => AiSetting::PROVIDERS,
            'models' => AiSetting::MODEL_OPTIONS,
            'default_models' => AiSetting::DEFAULT_MODELS,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'provider' => ['required', 'in:gemini,openai,anthropic'],
            'api_key' => ['nullable', 'string', 'min:20', 'max:512'],
            'model' => ['nullable', 'string', 'max:64'],
            'enabled' => ['boolean'],
            'monthly_limit' => ['nullable', 'integer', 'min:10', 'max:10000'],
        ]);

        $setting = AiSetting::firstOrNew(['user_id' => $request->user()->id]);
        $setting->provider = $data['provider'];
        $setting->model = $data['model'] ?? AiSetting::DEFAULT_MODELS[$data['provider']] ?? null;
        $setting->enabled = $data['enabled'] ?? true;
        $setting->monthly_limit = $data['monthly_limit'] ?? 200;
        if (! empty($data['api_key'])) {
            $setting->api_key = $data['api_key'];
        }
        $setting->save();

        return back()->with('success', __('AI settings saved.'));
    }

    public function test(Request $request): JsonResponse
    {
        $data = $request->validate([
            'provider' => ['required', 'in:gemini,openai,anthropic'],
            'api_key' => ['required', 'string', 'min:20'],
            'model' => ['nullable', 'string'],
        ]);

        try {
            $provider = $this->ai->makeProvider($data['provider'], $data['api_key'], $data['model'] ?? null);
            $ok = $provider->ping();

            return response()->json(['ok' => $ok, 'message' => $ok ? 'Connected.' : 'Unexpected response.']);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 200);
        }
    }

    public function destroy(Request $request): RedirectResponse
    {
        $setting = AiSetting::where('user_id', $request->user()->id)->first();
        if ($setting) {
            $setting->api_key_encrypted = null;
            $setting->enabled = false;
            $setting->save();
        }

        return back()->with('success', __('AI key removed.'));
    }
}
