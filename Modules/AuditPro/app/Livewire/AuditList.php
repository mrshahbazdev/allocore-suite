<?php

namespace Modules\AuditPro\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AuditPro\Models\Audit;

#[Layout('layouts.shell')]
class AuditList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $audits = Audit::with(['template', 'creator', 'results'])
            ->when($this->search, fn ($query) => $query->where(fn ($searchQuery) => $searchQuery
                ->whereHas('template', fn ($template) => $template->where('name', 'like', "%{$this->search}%"))
                ->orWhereHas('creator', fn ($creator) => $creator->where('name', 'like', "%{$this->search}%"))))
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->latest()
            ->paginate(15);

        $stats = [
            'total' => Audit::count(),
            'completed' => Audit::where('status', 'completed')->count(),
            'in_progress' => Audit::where('status', 'in_progress')->count(),
        ];

        return view('auditpro::livewire.audit-list', compact('audits', 'stats'));
    }
}
