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
                'heading' => $this->string($block, 'heading'),
                'subheading' => $this->string($block, 'subheading'),
                'image' => $this->string($block, 'image'),
                'cta_text' => $this->string($block, 'cta_text'),
                'cta_url' => $this->string($block, 'cta_url'),
            ],
            'features' => [
                'type' => 'features',
                'title' => $this->string($block, 'title'),
                'items' => collect($block['items'] ?? [])
                    ->map(fn ($i) => ['title' => $this->string($i, 'title'), 'description' => $this->string($i, 'description')])
                    ->filter(fn ($i) => filled($i['title']))
                    ->values()
                    ->all(),
            ],
            'text' => [
                'type' => 'text',
                'content' => $this->string($block, 'content'),
            ],
            'image' => [
                'type' => 'image',
                'src' => $this->string($block, 'src'),
                'alt' => $this->string($block, 'alt'),
            ],
            'cta' => [
                'type' => 'cta',
                'title' => $this->string($block, 'title'),
                'text' => $this->string($block, 'text'),
                'button_text' => $this->string($block, 'button_text'),
                'button_url' => $this->string($block, 'button_url'),
            ],
            'faq' => [
                'type' => 'faq',
                'title' => $this->string($block, 'title'),
                'items' => collect($block['items'] ?? [])
                    ->map(fn ($i) => ['question' => $this->string($i, 'question'), 'answer' => $this->string($i, 'answer')])
                    ->filter(fn ($i) => filled($i['question']))
                    ->values()
                    ->all(),
            ],
            default => $block,
        };
    }

    private function string(array $data, string $key, string $default = ''): string
    {
        return is_string($data[$key] ?? $default) ? strip_tags($data[$key] ?? $default) : $default;
    }
}
