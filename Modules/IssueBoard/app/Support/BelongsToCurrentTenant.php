<?php

namespace Modules\IssueBoard\Support;

use Illuminate\Database\Eloquent\Builder;

/**
 * Minimal tenant scoping so the module works standalone.
 *
 * Allocore hat bereits einen Multi-Tenancy-Trait - wenn ja, diesen Trait
 * im Issue-Model gegen den bestehenden austauschen und diese Datei loeschen.
 * Die Spalte issues.tenant_id ist nullable, also bricht nichts, wenn die App
 * gar keine Mandanten kennt.
 */
trait BelongsToCurrentTenant
{
    protected static function bootBelongsToCurrentTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $query) {
            if (! auth()->check()) {
                return;
            }

            $tenantId = static::currentTenantId();

            $tenantId
                ? $query->where($query->getModel()->getTable().'.tenant_id', $tenantId)
                : $query->whereRaw('1 = 0');
        });

        static::creating(function ($model) {
            $model->tenant_id ??= static::currentTenantId();
        });
    }

    public static function currentTenantId(): ?int
    {
        $column = config('issueboard.tenant_column');

        if (! $column || ! auth()->check()) {
            return null;
        }

        return auth()->user()->{$column} ?? null;
    }
}
