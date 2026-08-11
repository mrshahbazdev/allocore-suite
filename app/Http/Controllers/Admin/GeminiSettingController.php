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
            'apiKey' => SiteSetting::value('gemini_api_key', ''),
            'hasApiKey' => filled(SiteSetting::value('gemini_api_key', '')),
            'model' => SiteSetting::value('gemini_model', config('services.gemini.model', 'gemini-flash-latest')),
            'baseUrl' => SiteSetting::value('gemini_base_url', config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta')),
            'timeout' => SiteSetting::value('gemini_timeout', config('services.gemini.timeout', 120)),
            'maxOutputTokens' => SiteSetting::value('gemini_max_output_tokens', config('services.gemini.max_output_tokens', 8192)),
            'maxRetries' => SiteSetting::value('gemini_max_retries', config('services.gemini.max_retries', 3)),
            'retryBaseDelayMs' => SiteSetting::value('gemini_retry_base_delay_ms', config('services.gemini.retry_base_delay_ms', 1500)),
            'fallbackModels' => $fallbacks ?: $fallbacksString,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'api_key' => 'nullable|string|max:1000',
            'model' => 'nullable|string|max:255',
            'base_url' => 'nullable|string|max:255',
            'timeout' => 'nullable|integer|min:1|max:600',
            'max_output_tokens' => 'nullable|integer|min:1|max:100000',
            'max_retries' => 'nullable|integer|min:0|max:20',
            'retry_base_delay_ms' => 'nullable|integer|min:0|max:60000',
            'fallback_models' => 'nullable|string|max:1000',
        ]);

        if (filled($validated['api_key'] ?? '')) {
            SiteSetting::setGlobal('gemini_api_key', Crypt::encryptString($validated['api_key']));
        }

        SiteSetting::setGlobal('gemini_model', $validated['model'] ?: config('services.gemini.model', 'gemini-flash-latest'));
        SiteSetting::setGlobal('gemini_base_url', rtrim((string) ($validated['base_url'] ?? ''), '/') ?: config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta'));
        SiteSetting::setGlobal('gemini_timeout', (string) ($validated['timeout'] ?? 120));
        SiteSetting::setGlobal('gemini_max_output_tokens', (string) ($validated['max_output_tokens'] ?? 8192));
        SiteSetting::setGlobal('gemini_max_retries', (string) ($validated['max_retries'] ?? 3));
        SiteSetting::setGlobal('gemini_retry_base_delay_ms', (string) ($validated['retry_base_delay_ms'] ?? 1500));
        SiteSetting::setGlobal('gemini_fallback_models', $validated['fallback_models'] ?? config('services.gemini.fallback_models', 'gemini-2.5-flash,gemini-2.0-flash,gemini-flash-latest'));

        return redirect()->route('admin.gemini.index')->with('success', __('Gemini settings updated.'));
    }
}
