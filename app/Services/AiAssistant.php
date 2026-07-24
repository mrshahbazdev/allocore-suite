<?php

namespace App\Services;

use App\Models\AiChatMessage;
use App\Models\Module;
use App\Models\User;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AiAssistant
{
    public function ask(User $user, string $message, ?string $moduleKey = null, ?string $pageUrl = null): string
    {
        $messages = $this->buildMessages($user, $message, $moduleKey, $pageUrl);

        if ($apiKey = config('services.openai.key')) {
            try {
                return $this->callOpenAi($apiKey, $messages);
            } catch (HttpClientException $e) {
                report($e);
            }
        }

        return $this->localReply($user, $message, $moduleKey);
    }

    protected function callOpenAi(string $apiKey, array $messages): string
    {
        $response = Http::withToken($apiKey)
            ->timeout(30)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => config('services.openai.model', 'gpt-4o-mini'),
                'messages' => $messages,
                'max_tokens' => 1024,
            ]);

        return $response->json('choices.0.message.content')
            ?? 'I could not generate a response right now. Please try again.';
    }

    protected function buildMessages(User $user, string $message, ?string $moduleKey, ?string $pageUrl): array
    {
        $moduleNames = $this->userModules($user)->pluck('name')->implode(', ');
        $allModules = Module::where('is_active', true)->pluck('name', 'key')->map(
            fn ($name, $key) => "$key: $name"
        )->implode("\n");

        $context = app(AiAssistantContext::class)->forModule($user, $moduleKey);

        $system = <<<PROMPT
You are a helpful assistant inside Allocore Suite, a multi-tenant SaaS platform.
The user currently has access to these modules: {$moduleNames}.
All available modules:
{$allModules}
{$context}
Current page module key: {$moduleKey}.
Current page URL: {$pageUrl}.
Answer concisely. If the user asks about a module they do not subscribe to, suggest they visit the Tools page to subscribe.
PROMPT;

        $history = AiChatMessage::where('user_id', $user->id)
            ->where('team_id', $user->current_team_id)
            ->latest()
            ->limit(10)
            ->get()
            ->sortBy('id')
            ->map(fn (AiChatMessage $msg) => [
                'role' => $msg->role,
                'content' => $msg->content,
            ])
            ->values()
            ->all();

        return array_merge(
            [['role' => 'system', 'content' => $system]],
            $history,
            [['role' => 'user', 'content' => $message]]
        );
    }

    protected function localReply(User $user, string $message, ?string $moduleKey): string
    {
        $modules = $this->userModules($user);
        $lower = strtolower($message);

        if (Str::contains($lower, ['hello', 'hi', 'hey'])) {
            return __('Hello! I can help you with your subscribed tools. Ask me anything about them.');
        }

        if (Str::contains($lower, ['invoice', 'rechnung'])) {
            if (! $modules->contains('key', 'invoice-maker')) {
                return __('To manage invoices, subscribe to InvoiceMaker from the Tools page.');
            }

            return __('You can create invoices in InvoiceMaker → Invoices. I can also help you check overdue amounts from the billing dashboard.');
        }

        if (Str::contains($lower, ['lead', 'contact', 'prospect'])) {
            if (! $modules->contains('key', 'lead-quality')) {
                return __('To track leads, subscribe to LeadOS from the Tools page.');
            }

            return __('LeadOS helps you capture leads, score them with AI, and run outreach sequences.');
        }

        if (Str::contains($lower, ['project', 'task', 'plan'])) {
            if (! $modules->contains('key', 'plan-hive')) {
                return __('For project management, subscribe to PlanHive from the Tools page.');
            }

            return __('PlanHive gives you projects, tasks, goals, calendar and reminders.');
        }

        if (Str::contains($lower, ['kpi', 'metric', 'measure'])) {
            if ($modules->contains('key', 'smart-kpi')) {
                return __('SmartKpi has advanced KPI relationships, forecasts and problem management. You can also use KpiTool for catalog-style KPIs.');
            }
            if ($modules->contains('key', 'kpi-tool')) {
                return __('KpiTool has a KPI catalog, monthly spreadsheet, targets and CSV import/export.');
            }

            return __('For KPIs, subscribe to KpiTool or SmartKpi from the Tools page.');
        }

        $matches = [];
        foreach (Module::where('is_active', true)->get() as $module) {
            if (Str::contains($lower, [str_replace('-', '', $module->key), strtolower($module->name)])) {
                $matches[] = $module;
            }
        }

        foreach ($matches as $module) {
            if (! $modules->contains('key', $module->key)) {
                return sprintf(
                    __('%s looks useful for that. You can subscribe to it from the Tools page.'),
                    $module->name
                );
            }

            return sprintf(__('You are subscribed to %s. Open it from the sidebar to start.'), $module->name);
        }

        return __('I can help you navigate your tools. Try asking about invoices, leads, projects, KPIs, or any module you want to use.');
    }

    protected function userModules(User $user): Collection
    {
        return Module::where('is_active', true)
            ->get()
            ->filter(fn (Module $module) => $user->hasModule($module->key))
            ->values();
    }
}
