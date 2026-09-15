<?php

namespace Modules\IssueBoard\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Modules\IssueBoard\Enums\IssueStatus;
use Modules\IssueBoard\Models\Issue;

class IssuePolicy
{
    public function viewAny(Model $user): bool
    {
        return true;
    }

    public function view(Model $user, Issue $issue): bool
    {
        return $this->sameTenant($user, $issue);
    }

    public function create(Model $user): bool
    {
        return true;
    }

    public function update(Model $user, Issue $issue): bool
    {
        if (! $this->sameTenant($user, $issue)) {
            return false;
        }

        return in_array(static::roleOf($user), ['ali', 'dev', 'admin'], true)
            || $issue->created_by === $user->getKey();
    }

    public function delete(Model $user, Issue $issue): bool
    {
        return $this->sameTenant($user, $issue)
            && in_array(static::roleOf($user), ['dev', 'admin'], true);
    }

    public function comment(Model $user, Issue $issue): bool
    {
        return $this->sameTenant($user, $issue);
    }

    /** Darf dieser User die Karte in diese Spalte ziehen? */
    public function moveTo(Model $user, Issue $issue, IssueStatus $status): bool
    {
        if (! $this->sameTenant($user, $issue)) {
            return false;
        }

        $allowed = config('issueboard.transitions')[static::roleOf($user)] ?? [];

        return in_array('*', $allowed, true) || in_array($status->value, $allowed, true);
    }

    public static function roleOf(Model $user): string
    {
        if ($resolver = config('issueboard.role_resolver')) {
            return $resolver($user);
        }

        if ($user instanceof User && $user->isAdmin()) {
            return 'admin';
        }

        if ($user instanceof User) {
            if ($user->hasAnyRole(['saas-developer'])) {
                return 'dev';
            }

            if ($user->hasAnyRole(['owner', 'senior-management'])) {
                return 'ali';
            }
        }

        return $user->role ?? 'client';
    }

    protected function sameTenant(Model $user, Issue $issue): bool
    {
        $column = config('issueboard.tenant_column');

        if (! $column) {
            return true;
        }

        return (int) $issue->tenant_id === (int) ($user->{$column} ?? 0);
    }
}
