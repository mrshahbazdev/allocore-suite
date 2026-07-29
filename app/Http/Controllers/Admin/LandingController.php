<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index()
    {
        $blocks = SiteSetting::value('landing_blocks', []);

        return view('admin.landing.index', compact('blocks'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'blocks' => 'nullable|array',
            'blocks.*.type' => 'required|in:hero,features,text,image,cta,faq',
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
            'hero' => [
                'type' => 'hero',
                'enabled' => filter_var($block['enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'heading' => $this->string($block, 'heading'),
                'subheading' => $this->string($block, 'subheading'),
                'image' => $this->string($block, 'image'),
                'cta_text' => $this->string($block, 'cta_text'),
                'cta_url' => $this->string($block, 'cta_url'),
                'style' => $this->style($block),
                'animation' => $this->string($block, 'animation'),
            ],
            'features' => [
                'type' => 'features',
                'enabled' => filter_var($block['enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'title' => $this->string($block, 'title'),
                'items' => collect($block['items'] ?? [])
                    ->map(fn ($i) => ['title' => $this->string($i, 'title'), 'description' => $this->string($i, 'description')])
                    ->filter(fn ($i) => filled($i['title']))
                    ->values()
                    ->all(),
                'style' => $this->style($block),
                'animation' => $this->string($block, 'animation'),
            ],
            'text' => [
                'type' => 'text',
                'enabled' => filter_var($block['enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'content' => $this->string($block, 'content'),
                'style' => $this->style($block),
                'animation' => $this->string($block, 'animation'),
            ],
            'image' => [
                'type' => 'image',
                'enabled' => filter_var($block['enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'src' => $this->string($block, 'src'),
                'alt' => $this->string($block, 'alt'),
                'style' => $this->style($block),
                'animation' => $this->string($block, 'animation'),
            ],
            'cta' => [
                'type' => 'cta',
                'enabled' => filter_var($block['enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'title' => $this->string($block, 'title'),
                'text' => $this->string($block, 'text'),
                'button_text' => $this->string($block, 'button_text'),
                'button_url' => $this->string($block, 'button_url'),
                'style' => $this->style($block),
                'animation' => $this->string($block, 'animation'),
            ],
            'faq' => [
                'type' => 'faq',
                'enabled' => filter_var($block['enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'title' => $this->string($block, 'title'),
                'items' => collect($block['items'] ?? [])
                    ->map(fn ($i) => ['question' => $this->string($i, 'question'), 'answer' => $this->string($i, 'answer')])
                    ->filter(fn ($i) => filled($i['question']))
                    ->values()
                    ->all(),
                'style' => $this->style($block),
                'animation' => $this->string($block, 'animation'),
            ],
            default => $block,
        };
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
        ];
    }

    private function string(array $data, string $key, string $default = ''): string
    {
        return is_string($data[$key] ?? $default) ? strip_tags($data[$key] ?? $default) : $default;
    }
}
