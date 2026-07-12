<?php

namespace Modules\AuditPro\Livewire;

use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AuditPro\Models\AuditTemplate;

#[Layout('layouts.shell')]
class TemplateList extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public ?int $templateId = null;

    public string $name = '';

    public string $description = '';

    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $template = AuditTemplate::findOrFail($id);
        $this->templateId = $template->id;
        $this->name = $template->name;
        $this->description = $template->description ?? '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        if ($this->templateId) {
            AuditTemplate::findOrFail($this->templateId)->update($validated);
        } else {
            $baseSlug = Str::slug($validated['name']) ?: 'template';
            $slug = $baseSlug;
            $suffix = 2;

            while (AuditTemplate::where('slug', $slug)->exists()) {
                $slug = "{$baseSlug}-{$suffix}";
                $suffix++;
            }

            AuditTemplate::create($validated + [
                'slug' => $slug,
                'created_by' => auth()->id(),
            ]);
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $template = AuditTemplate::withCount('audits')->findOrFail($id);

        if ($template->audits_count > 0) {
            session()->flash('error', __('Templates with audit history cannot be deleted.'));

            return;
        }

        $template->delete();
        session()->flash('success', __('Template deleted.'));
    }

    private function resetForm(): void
    {
        $this->reset(['templateId', 'name', 'description']);
        $this->resetValidation();
    }

    public function render()
    {
        $templates = AuditTemplate::query()
            ->when($this->search, fn ($query) => $query->where(fn ($searchQuery) => $searchQuery
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('description', 'like', "%{$this->search}%")))
            ->withCount(['pillars', 'questions', 'audits'])
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->paginate(10);

        return view('auditpro::livewire.template-list', compact('templates'));
    }
}
