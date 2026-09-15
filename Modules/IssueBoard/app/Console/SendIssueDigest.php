<?php

namespace Modules\IssueBoard\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Modules\IssueBoard\Enums\IssueStatus;
use Modules\IssueBoard\Mail\IssueDigest;
use Modules\IssueBoard\Models\Issue;
use Modules\IssueBoard\Models\IssueComment;

class SendIssueDigest extends Command
{
    protected $signature = 'issueboard:digest {--dry-run : Nur anzeigen, nicht versenden}';

    protected $description = 'Sendet die Uebersicht offener Aufgaben an Ali und die Entwicklung';

    public function handle(): int
    {
        if (! config('issueboard.digest.enabled')) {
            $this->info('Digest ist deaktiviert.');

            return self::SUCCESS;
        }

        $base = Issue::withoutGlobalScope('tenant')->with('project', 'assignee', 'author');

        $newIssues = (clone $base)->where('status', IssueStatus::New->value)->latest()->get();
        $withAli = (clone $base)->where('status', IssueStatus::WithAli->value)->latest()->get();
        $overdue = (clone $base)->open()->whereDate('due_date', '<', now())->get();

        $openQuestions = IssueComment::with('issue', 'user')
            ->where('is_question', true)
            ->whereNull('answered_at')
            ->latest()
            ->get();

        if ($newIssues->isEmpty() && $withAli->isEmpty() && $openQuestions->isEmpty() && $overdue->isEmpty()) {
            $this->info('Nichts offen - keine Mail versendet.');

            return self::SUCCESS;
        }

        foreach ($this->recipients() as $user) {
            $this->line("-> {$user->email}");

            if (! $this->option('dry-run')) {
                Mail::to($user)->send(new IssueDigest($newIssues, $withAli, $openQuestions, $overdue, $user->name));
            }
        }

        return self::SUCCESS;
    }

    protected function recipients()
    {
        $userModel = config('issueboard.user_model');

        if ($explicit = config('issueboard.digest.recipients')) {
            return $userModel::whereIn('email', $explicit)->get();
        }

        return $userModel::query()
            ->when(
                Schema::hasColumn((new $userModel)->getTable(), 'role'),
                fn ($q) => $q->whereIn('role', ['ali', 'dev', 'admin'])
            )
            ->get();
    }
}
