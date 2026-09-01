<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    private const BASE_LOCALE = 'de';

    private array $stringFields = [
        'site_name',
        'hero_badge',
        'hero_heading',
        'hero_subheading',
        'hero_cta_primary_label',
        'hero_cta_primary_link',
        'hero_cta_secondary_label',
        'hero_cta_secondary_link',
        'framework_heading',
        'framework_subheading',
        'framework_description',
        'features_heading',
        'features_subheading',
        'feature_auth_title',
        'feature_auth_desc',
        'feature_teams_title',
        'feature_teams_desc',
        'feature_billing_title',
        'feature_billing_desc',
        'feature_analytics_title',
        'feature_analytics_desc',
        'how_heading',
        'how_subheading',
        'modules_heading',
        'modules_subheading',
        'testimonials_heading',
        'testimonials_quote',
        'testimonials_author',
        'cta_heading',
        'cta_subheading',
        'cta_primary_label',
        'cta_primary_link',
        'cta_secondary_label',
        'cta_secondary_link',
        'footer_text',
    ];

    private array $arrayFields = [
        'top_stats' => ['label', 'value'],
        'framework_steps' => ['title', 'desc'],
        'how_steps' => ['title', 'desc'],
    ];

    private array $arrayFilterField = [
        'top_stats' => 'label',
        'framework_steps' => 'title',
        'how_steps' => 'title',
    ];

    public function index(Request $request)
    {
        $locale = $this->locale($request);
        $settings = $this->settings($locale);

        return view('admin.landing.index', compact('settings', 'locale'));
    }

    public function update(Request $request)
    {
        $locale = $this->locale($request);

        $request->validate($this->rules());

        foreach ($this->stringFields as $field) {
            SiteSetting::set($field, $this->string($request, $field), $locale);
        }

        foreach ($this->arrayFields as $field => $keys) {
            SiteSetting::set($field, $this->array($request, $field, $keys), $locale);
        }

        return redirect()->route('admin.landing.index', ['locale' => $locale])
            ->with('success', __('Landing page updated.'));
    }

    private function locale(Request $request): string
    {
        $locale = $request->input('locale', app()->getLocale());
        $available = config('app.available_locales', ['en', 'de']);

        return in_array($locale, $available, true) ? $locale : self::BASE_LOCALE;
    }

    private function settings(string $locale): array
    {
        $settings = [];

        foreach ($this->stringFields as $field) {
            $settings[$field] = SiteSetting::value($field, '', $locale);
        }

        foreach ($this->arrayFields as $field => $keys) {
            $value = SiteSetting::value($field, [], $locale);
            $settings[$field] = is_array($value) ? $value : [];
        }

        return $settings;
    }

    private function rules(): array
    {
        $rules = array_fill_keys($this->stringFields, 'nullable|string|max:2000');

        foreach ($this->arrayFields as $field => $keys) {
            $rules[$field] = 'nullable|array';
            foreach ($keys as $key) {
                $rules[$field.'.*.'.$key] = 'nullable|string|max:2000';
            }
        }

        return $rules;
    }

    private function string(Request $request, string $field, string $default = ''): string
    {
        $value = $request->input($field, $default);

        return is_string($value) ? strip_tags($value) : $default;
    }

    private function array(Request $request, string $field, array $keys): array
    {
        $items = $request->input($field, []);

        if (! is_array($items)) {
            return [];
        }

        $filterKey = $this->arrayFilterField[$field] ?? $keys[0];

        return collect($items)
            ->map(fn ($item) => collect($keys)
                ->mapWithKeys(fn ($key) => [$key => $this->stringFromArray($item, $key)])
                ->all()
            )
            ->filter(fn ($item) => filled($item[$filterKey] ?? ''))
            ->values()
            ->all();
    }

    private function stringFromArray(mixed $item, string $key, string $default = ''): string
    {
        $value = is_array($item) ? ($item[$key] ?? $default) : $default;

        return is_string($value) ? strip_tags($value) : $default;
    }
}
