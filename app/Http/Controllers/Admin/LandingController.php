<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Support\LandingBlockDefaults;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index()
    {
        $blocks = SiteSetting::value('landing_blocks', []);

        if (empty($blocks)) {
            $blocks = LandingBlockDefaults::blocks();
        }

        return view('admin.landing.index', compact('blocks'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'blocks' => 'nullable|array',
            'blocks.*.type' => 'required|in:hero,features,text,image,cta,faq,stats,testimonials,pricing,steps,logos,divider,spacer',
        ]);

        $blocks = $this->normalizeBlocks($validated['blocks'] ?? []);
        SiteSetting::set('landing_blocks', $blocks);

        return back()->with('success', __('Landing page updated.'));
    }

    private function normalizeBlocks(array $blocks): array
    {
        return collect($blocks)
            ->filter(fn ($block) => filled($block['type'] ?? null))
            ->map(fn ($block) => $this->sanitizeBlock($block))
            ->values()
            ->all();
    }

    private function sanitizeBlock(array $block): array
    {
        $type = $block['type'];

        return match ($type) {
            'hero' => $this->sanitizeBase($block, ['heading', 'subheading', 'image', 'cta_text', 'cta_url']),
            'features' => $this->sanitizeItemBlock($block, ['title', 'description']),
            'text' => $this->sanitizeBase($block, ['content']),
            'image' => $this->sanitizeBase($block, ['src', 'alt']),
            'cta' => $this->sanitizeBase($block, ['title', 'text', 'button_text', 'button_url']),
            'faq' => $this->sanitizeItemBlock($block, ['question', 'answer']),
            'stats' => $this->sanitizeItemBlock($block, ['label', 'value', 'suffix']),
            'testimonials' => $this->sanitizeItemBlock($block, ['quote', 'author', 'role']),
            'pricing' => $this->sanitizeItemBlock($block, ['name', 'price', 'period', 'features', 'cta_text', 'cta_url', 'highlighted']),
            'steps' => $this->sanitizeItemBlock($block, ['title', 'description']),
            'logos' => $this->sanitizeItemBlock($block, ['name', 'image_url']),
            'divider' => $this->sanitizeBase($block, ['color', 'width', 'icon']),
            'spacer' => $this->sanitizeBase($block, ['height']),
            default => $block,
        };
    }

    private function sanitizeBase(array $block, array $fields): array
    {
        $data = [
            'type' => $block['type'],
            'enabled' => filter_var($block['enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'style' => $this->style($block),
            'layout' => $this->layout($block),
            'animation' => $this->string($block, 'animation'),
        ];

        foreach ($fields as $field) {
            $data[$field] = $this->string($block, $field);
        }

        return $data;
    }

    private function sanitizeItemBlock(array $block, array $itemFields): array
    {
        $data = [
            'type' => $block['type'],
            'enabled' => filter_var($block['enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'title' => $this->string($block, 'title'),
            'items' => collect($block['items'] ?? [])
                ->map(fn ($i) => collect($itemFields)->mapWithKeys(fn ($f) => [$f => $this->string($i, $f)])->all())
                ->filter(fn ($i) => filled($i[$itemFields[0]] ?? ''))
                ->values()
                ->all(),
            'style' => $this->style($block),
            'layout' => $this->layout($block),
            'animation' => $this->string($block, 'animation'),
        ];

        return $data;
    }

    private function style(array $block): array
    {
        $style = is_array($block['style'] ?? null) ? $block['style'] : [];

        return [
            'bg' => $this->string($style, 'bg'),
            'text_color' => $this->string($style, 'text_color'),
            'text_align' => in_array($style['text_align'] ?? '', ['left', 'center', 'right']) ? $style['text_align'] : 'center',
            'padding' => in_array($style['padding'] ?? '', ['small', 'medium', 'large']) ? $style['padding'] : 'medium',
            'container' => in_array($style['container'] ?? '', ['default', 'max-w-7xl', 'max-w-5xl', 'max-w-3xl', 'full']) ? $style['container'] : 'default',
            'rounded' => filter_var($style['rounded'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'border' => filter_var($style['border'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ];
    }

    private function layout(array $block): array
    {
        $layout = is_array($block['layout'] ?? null) ? $block['layout'] : [];

        return [
            'columns' => in_array((int) ($layout['columns'] ?? 0), [1, 2, 3, 4]) ? (int) $layout['columns'] : 0,
            'gap' => in_array($layout['gap'] ?? '', ['small', 'medium', 'large']) ? $layout['gap'] : 'medium',
            'align' => in_array($layout['align'] ?? '', ['start', 'center', 'end', 'stretch']) ? $layout['align'] : 'stretch',
        ];
    }

    private function string(array $data, string $key, string $default = ''): string
    {
        $value = $data[$key] ?? $default;

        return is_string($value) ? strip_tags($value) : $default;
    }
}
