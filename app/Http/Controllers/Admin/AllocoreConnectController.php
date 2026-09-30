<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Client\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class AllocoreConnectController extends Controller
{
    private const SESSION_TENANTS = 'allocore_connect.tenants';

    private const SESSION_PENDING = 'allocore_connect.pending';

    private const SESSION_OAUTH = 'allocore_connect.oauth';

    /**
     * OAuth-ähnlicher Flow: Admin wird zum Manager weitergeleitet, meldet sich
     * dort an, wählt den Mandanten und kommt mit einem Code zurück.
     */
    public function start(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'manager_url' => 'required|url',
            'source_name' => 'nullable|string|max:255',
        ]);

        $manager = rtrim($data['manager_url'], '/');
        $state = bin2hex(random_bytes(16));

        $request->session()->put(self::SESSION_OAUTH, [
            'manager_url' => $manager,
            'state' => $state,
        ]);

        $url = $manager.'/connect/authorize?'.http_build_query([
            'redirect_uri' => route('admin.allocore.callback'),
            'state' => $state,
            'source_name' => $data['source_name'] ?? 'Allocore Suite',
        ]);

        return redirect()->away($url);
    }

    public function callback(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => 'required|string',
            'state' => 'nullable|string',
        ]);

        $oauth = $request->session()->get(self::SESSION_OAUTH);
        if (! $oauth || ! hash_equals($oauth['state'] ?? '', (string) ($data['state'] ?? ''))) {
            return redirect()->route('admin.allocore.index')->with('error', __('allocore.connect_failed', ['status' => 'state']));
        }

        $manager = rtrim($oauth['manager_url'], '/');
        $resp = Http::timeout(15)->acceptJson()->post($manager.'/api/v1/connect/exchange', [
            'code' => $data['code'],
        ]);

        if ($resp->failed() || ! $resp->json('webhook_url')) {
            return redirect()->route('admin.allocore.index')->with('error', __('allocore.connect_failed', ['status' => $resp->status()]));
        }

        SiteSetting::setGlobal('allocore.webhook_url', $resp->json('webhook_url'));
        SiteSetting::setGlobal('allocore.manager_url', $manager);
        SiteSetting::setGlobal('allocore.tenant_id', (string) $resp->json('tenant_id'));
        SiteSetting::setGlobal('allocore.tenant_name', (string) $resp->json('tenant_name'));
        SiteSetting::setGlobal('allocore.connected_at', now()->toDateTimeString());

        $request->session()->forget(self::SESSION_OAUTH);

        return redirect()->route('admin.allocore.index')->with('success', __('allocore.connected'));
    }

    public function index(): View
    {
        return view('admin.allocore.index', [
            'webhookUrl' => SiteSetting::value('allocore.webhook_url'),
            'managerUrl' => SiteSetting::value('allocore.manager_url', 'https://dirksoelter.de'),
            'tenantId' => SiteSetting::value('allocore.tenant_id'),
            'tenantName' => SiteSetting::value('allocore.tenant_name'),
            'connectedAt' => SiteSetting::value('allocore.connected_at'),
            'tenants' => session(self::SESSION_TENANTS, []),
            'pending' => session(self::SESSION_PENDING),
        ]);
    }

    public function tenants(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'manager_url' => 'required|url',
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $manager = rtrim($data['manager_url'], '/');
        $resp = $this->postConnect($manager, [
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        if ($resp->failed()) {
            return back()->with('error', __('allocore.connect_failed', ['status' => $resp->status()]))->withInput();
        }

        $tenants = $resp->json('tenants', []);
        if (empty($tenants)) {
            return back()->with('error', __('allocore.no_tenants'))->withInput();
        }

        $request->session()->put(self::SESSION_TENANTS, $tenants);
        $request->session()->put(self::SESSION_PENDING, $data);

        return redirect()->route('admin.allocore.index')->with('success', __('allocore.pick_tenant'));
    }

    public function link(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tenant_id' => 'required|string',
            'source_name' => 'nullable|string|max:255',
        ]);

        $pending = $request->session()->get(self::SESSION_PENDING);
        if (! $pending) {
            return redirect()->route('admin.allocore.index')->with('error', __('allocore.expired'));
        }

        $manager = rtrim($pending['manager_url'], '/');
        $resp = $this->postConnect($manager, [
            'email' => $pending['email'],
            'password' => $pending['password'],
            'tenant_id' => $data['tenant_id'],
            'source_name' => $data['source_name'] ?? 'Allocore Suite',
        ]);

        if ($resp->failed() || ! $resp->json('webhook_url')) {
            return back()->with('error', __('allocore.connect_failed', ['status' => $resp->status()]));
        }

        $tenants = collect($request->session()->get(self::SESSION_TENANTS, []));
        $tenantName = $tenants->firstWhere('id', $data['tenant_id'])['name'] ?? $data['tenant_id'];

        SiteSetting::setGlobal('allocore.webhook_url', $resp->json('webhook_url'));
        SiteSetting::setGlobal('allocore.manager_url', $manager);
        SiteSetting::setGlobal('allocore.tenant_id', $data['tenant_id']);
        SiteSetting::setGlobal('allocore.tenant_name', $tenantName);
        SiteSetting::setGlobal('allocore.connected_at', now()->toDateTimeString());

        $request->session()->forget([self::SESSION_TENANTS, self::SESSION_PENDING]);

        return redirect()->route('admin.allocore.index')->with('success', __('allocore.connected'));
    }

    public function disconnect(Request $request): RedirectResponse
    {
        $locales = array_merge(config('app.available_locales', ['en']), [config('app.fallback_locale', 'en')]);

        foreach (['allocore.webhook_url', 'allocore.manager_url', 'allocore.tenant_id', 'allocore.tenant_name', 'allocore.connected_at'] as $key) {
            SiteSetting::where('key', $key)->orWhere('key', 'like', $key.'_%')->delete();
            foreach ($locales as $locale) {
                Cache::forget('site_setting_'.$key.'_'.$locale);
            }
        }

        return redirect()->route('admin.allocore.index')->with('success', __('allocore.disconnected'));
    }

    private function postConnect(string $manager, array $payload): Response
    {
        return Http::timeout(15)->acceptJson()->post($manager.'/api/v1/connect', $payload);
    }
}
