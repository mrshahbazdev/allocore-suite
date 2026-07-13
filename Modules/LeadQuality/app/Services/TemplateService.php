<?php

namespace Modules\LeadQuality\Services;

use Modules\LeadQuality\Models\Contact;

class TemplateService
{
    public function merge(string $content, Contact $contact): string
    {
        $vars = [
            '{name}' => $contact->name,
            '{company}' => $contact->company ?? 'your company',
            '{industry}' => $contact->industry ?? 'your industry',
            '{position}' => $contact->position ?? 'your role',
        ];

        return str_replace(array_keys($vars), array_values($vars), $content);
    }
}
