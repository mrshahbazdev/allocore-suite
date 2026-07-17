<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class SiteSettingController extends Controller
{
    private array $fields = [
        'site_name',
        'hero_heading',
        'hero_subheading',
        'hero_cta_primary_label',
        'hero_cta_secondary_label',
        'hero_cta_primary_link',
        'hero_cta_secondary_link',
        'cta_primary_label',
        'cta_secondary_label',
        'cta_primary_link',
        'cta_secondary_link',
        'feature_auth_title',
        'feature_auth_desc',
        'feature_teams_title',
        'feature_teams_desc',
        'feature_billing_title',
        'feature_billing_desc',
        'feature_analytics_title',
        'feature_analytics_desc',
        'footer_text',
    ];

    public function index()
    {
        $settings = [];

        foreach ($this->fields as $field) {
            $settings[$field] = SiteSetting::value($field, '');
        }

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $rules = array_fill_keys($this->fields, 'nullable|string|max:1000');
        $validated = $request->validate($rules);

        foreach ($validated as $key => $value) {
            SiteSetting::set($key, $value);
        }

        return back()->with('success', __('Site settings updated.'));
    }
}
