<?php

namespace Modules\InvoiceMaker\Policies;

use App\Models\User;
use Modules\InvoiceMaker\Models\Client;
use Modules\InvoiceMaker\Models\Expense;
use Modules\InvoiceMaker\Models\Invoice;
use Modules\InvoiceMaker\Models\Product;
use Modules\InvoiceMaker\Models\Profile;
use Modules\InvoiceMaker\Models\Template;

class InvoiceMakerPolicy
{
    public function view(User $user, Invoice|Client|Product|Expense|Template $model): bool
    {
        return $this->belongsToTeam($user, $model);
    }

    public function update(User $user, Invoice|Client|Product|Expense|Template $model): bool
    {
        return $this->belongsToTeam($user, $model);
    }

    public function delete(User $user, Expense|Client|Product|Invoice|Template $model): bool
    {
        return $this->belongsToTeam($user, $model);
    }

    public function manageTeam(User $user, Profile $profile): bool
    {
        return $this->belongsToTeam($user, $profile);
    }

    protected function belongsToTeam(User $user, mixed $model): bool
    {
        if (! $user->currentTeam) {
            return false;
        }

        return $user->currentTeam->id === ($model->team_id ?? null);
    }
}
