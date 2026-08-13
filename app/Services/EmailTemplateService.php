<?php

namespace App\Services;

use App\Models\NotificationTemplate;
use Illuminate\Support\Facades\Blade;

class EmailTemplateService
{
    public function render(string $tool, string $key, array $variables = [], ?string $locale = null): array
    {
        $template = NotificationTemplate::findByKey($key, 'email', $locale, $tool);

        if (! $template) {
            return [
                'subject' => '',
                'html' => '',
                'text' => '',
            ];
        }

        $body = $template->body ?? '';

        $html = $this->compile($body, $variables);

        return [
            'subject' => $this->compile($template->subject ?? '', $variables),
            'html' => $html,
            'text' => $this->textFromHtml($html),
            'variables' => $template->variables ?? [],
        ];
    }

    public function compile(?string $content, array $variables = []): string
    {
        if (blank($content)) {
            return '';
        }

        try {
            return Blade::render($content, $variables);
        } catch (\Throwable $e) {
            report($e);

            return $content;
        }
    }

    public function textFromHtml(string $html): string
    {
        $text = strip_tags($html);
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }
}
