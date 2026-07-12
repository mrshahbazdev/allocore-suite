<?php

namespace App\Support;

use App\Models\User;
use Closure;

class DashboardWidgetRegistry
{
    /** @var array<string, array{view: string|Closure, order: int}> */
    protected array $widgets = [];

    /**
     * Modules register a dashboard widget for their key.
     * $view is a blade view name, or a closure receiving the user and returning a view name.
     */
    public function register(string $moduleKey, string|Closure $view, int $order = 100): void
    {
        $this->widgets[$moduleKey] = ['view' => $view, 'order' => $order];
    }

    /**
     * Widgets for modules the given user has access to.
     *
     * @return array<string, string> moduleKey => view name
     */
    public function forUser(User $user): array
    {
        $result = [];

        foreach (collect($this->widgets)->sortBy('order') as $moduleKey => $widget) {
            if ($user->hasModule($moduleKey)) {
                $view = $widget['view'];
                $result[$moduleKey] = $view instanceof Closure ? $view($user) : $view;
            }
        }

        return $result;
    }
}
