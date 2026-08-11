<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;

class GeminiSettingController extends Controller
{
    public function index(): View
    {
        $fallbacks = SiteSetting::value('gemini_fallback_models');
        $defaultFallbacks = config('services.gemini.fallback_models', ['gemini-2.5-flash', 'gemini-2.0-flash', 'gemini-flash-latest']);
        $fallbacksString = is_array($defaultFallbacks) ? implode(',', $defaultFallbacks) : $defaultFallbacks;

        return view('admin.gemini.index', [
            'provider' => SiteSetting::value('ai_provider', config('services.ai.provider', 'auto')),
            'apiKey' => SiteSetting::value('gemini_api_key', ''),
            'hasApiKey' => filled(SiteSetting::value('gemini_api_key', '')),
            'model' => SiteSetting::value('gemini_model', config('services.gemini.model', 'gemini-flash-latest')),
            'baseUrl' => SiteSetting::value('gemini_base_url', config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta')),
            'timeout' => SiteSetting::value('gemini_timeout', config('services.gemini.timeout', 120)),
            'maxOutputTokens' => SiteSetting::value('gemini_max_output_tokens', config('services.gemini.max_output_tokens', 8192)),
            'maxRetries' => SiteSetting::value('gemini_max_retries', config('services.gemini.max_retries', 3)),
            'retryBaseDelayMs' => SiteSetting::value('gemini_retry_base_delay_ms', config('services.gemini.retry_base_delay_ms', 1500)),
            'fallbackModels' => $fallbacks ?: $fallbacksString,
            'openaiApiKey' => SiteSetting::value('openai_api_key', ''),
            'hasOpenaiApiKey' => filled(SiteSetting::value('openai_api_key', '')),
            'openaiModel' => SiteSetting::value('openai_model', config('services.openai.model', 'gpt-4o-mini')),
            'anthropicApiKey' => SiteSetting::value('anthropic_api_key', ''),
            'hasAnthropicApiKey' => filled(SiteSetting::value('anthropic_api_key', '')),
            'anthropicModel' => SiteSetting::value('anthropic_model', config('services.anthropic.model', 'claude-3-5-sonnet-latest')),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'provider' => 'nullable|string|in:gemini,openai,anthropic,auto',
            'api_key' => 'nullable|string|max:1000',
            'model' => 'nullable|string|max:255',
            'base_url' => 'nullable|string|max:255',
            'timeout' => 'nullable|integer|min:1|max:600',
            'max_output_tokens' => 'nullable|integer|min:1|max:100000',
            'max_retries' => 'nullable|integer|min:0|max:20',
            'retry_base_delay_ms' => 'nullable|integer|min:0|max:60000',
            'fallback_models' => 'nullable|string|max:1000',
            'openai_api_key' => 'nullable|string|max:1000',
            'openai_model' => 'nullable|string|max:255',
            'anthropic_api_key' => 'nullable|string|max:1000',
            'anthropic_model' => 'nullable|string|max:255',
        ]);

        SiteSetting::setGlobal('ai_provider', data_get($validated, 'provider') ?: config('services.ai.provider', 'auto'));

        if (filled($validated['api_key'] ?? '')) {
            SiteSetting::setGlobal('gemini_api_key', Crypt::encryptString($validated['api_key']));
        }

        if (filled($validated['openai_api_key'] ?? '')) {
            SiteSetting::setGlobal('openai_api_key', Crypt::encryptString($validated['openai_api_key']));
        }

        if (filled($validated['anthropic_api_key'] ?? '')) {
            SiteSetting::setGlobal('anthropic_api_key', Crypt::encryptString($validated['anthropic_api_key']));
        }

        SiteSetting::setGlobal('gemini_model', data_get($validated, 'model') ?: config('services.gemini.model', 'gemini-flash-latest'));
        SiteSetting::setGlobal('gemini_base_url', rtrim((string) data_get($validated, 'base_url', ''), '/') ?: config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta'));
        SiteSetting::setGlobal('gemini_timeout', (string) data_get($validated, 'timeout', 120));
        SiteSetting::setGlobal('gemini_max_output_tokens', (string) data_get($validated, 'max_output_tokens', 8192));
        SiteSetting::setGlobal('gemini_max_retries', (string) data_get($validated, 'max_retries', 3));
        SiteSetting::setGlobal('gemini_retry_base_delay_ms', (string) data_get($validated, 'retry_base_delay_ms', 1500));
        SiteSetting::setGlobal('gemini_fallback_models', data_get($validated, 'fallback_models') ?: config('services.gemini.fallback_models', 'gemini-2.5-flash,gemini-flash-latest'));
        SiteSetting::setGlobal('openai_model', data_get($validated, 'openai_model') ?: config('services.openai.model', 'gpt-4o-mini'));
        SiteSetting::setGlobal('anthropic_model', data_get($validated, 'anthropic_model') ?: config('services.anthropic.model', 'claude-3-5-sonnet-latest'));

        return redirect()->route('admin.gemini.index')->with('success', __('AI settings updated.'));
    }
}
