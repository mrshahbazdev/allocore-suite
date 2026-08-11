<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;

class DataForSeoSettingController extends Controller
{
    public function index(): View
    {
        return view('admin.dataforseo.index', [
            'login' => SiteSetting::value('dataforseo_login', ''),
            'hasPassword' => filled(SiteSetting::value('dataforseo_password', '')),
            'baseUrl' => SiteSetting::value('dataforseo_base_url', config('services.dataforseo.base_url', 'https://api.dataforseo.com')),
            'timeout' => SiteSetting::value('dataforseo_timeout', config('services.dataforseo.timeout', 30)),
            'cacheTtl' => SiteSetting::value('dataforseo_cache_ttl', config('services.dataforseo.cache_ttl', 86400)),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'login' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'base_url' => 'nullable|string|max:255',
            'timeout' => 'nullable|integer|min:1|max:300',
            'cache_ttl' => 'nullable|integer|min:0|max:604800',
        ]);

        SiteSetting::setGlobal('dataforseo_login', $validated['login'] ?? '');

        if (filled($validated['password'] ?? '')) {
            SiteSetting::setGlobal('dataforseo_password', Crypt::encryptString($validated['password']));
        }

        SiteSetting::setGlobal('dataforseo_base_url', rtrim((string) ($validated['base_url'] ?? ''), '/') ?: 'https://api.dataforseo.com');
        SiteSetting::setGlobal('dataforseo_timeout', (string) ($validated['timeout'] ?? 30));
        SiteSetting::setGlobal('dataforseo_cache_ttl', (string) ($validated['cache_ttl'] ?? 86400));

        return redirect()->route('admin.dataforseo.index')->with('success', __('DataForSEO settings updated.'));
    }
}
