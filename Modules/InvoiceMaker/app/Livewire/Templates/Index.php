<?php

namespace Modules\InvoiceMaker\Livewire\Templates;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Modules\InvoiceMaker\Models\Template;
use Modules\InvoiceMaker\Services\InvoiceMakerContext;

#[Layout('layouts.shell')]
class Index extends Component
{
    public ?int $selectedId = null;

    public string $name = '';

    public string $primary_color = '#4f46e5';

    public string $font_family = 'sans';

    public string $logo_position = 'left';

    public string $header_style = 'default';

    public ?string $payment_terms = null;

    public ?string $footer_message = null;

    public bool $show_tax = true;

    public bool $show_discount = true;

    public bool $enable_qr = false;

    public function mount(InvoiceMakerContext $context): void
    {
        $context->profile();
    }

    public function edit(Template $template): void
    {
        $this->selectedId = $template->id;
        $this->fill($template->only([
            'name',
            'primary_color',
            'font_family',
            'logo_position',
            'header_style',
            'payment_terms',
            'footer_message',
            'show_tax',
            'show_discount',
            'enable_qr',
        ]));
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'primary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'font_family' => ['required', 'in:sans,serif,mono'],
            'logo_position' => ['required', 'in:left,center,right'],
            'header_style' => ['required', 'in:default,bold,minimal'],
            'payment_terms' => ['nullable', 'string'],
            'footer_message' => ['nullable', 'string'],
            'show_tax' => ['boolean'],
            'show_discount' => ['boolean'],
            'enable_qr' => ['boolean'],
        ]);

        $template = $this->selectedId ? Template::findOrFail($this->selectedId) : new Template;
        $template->fill($data)->save();
        $this->selectedId = $template->id;
        session()->flash('success', __('Template saved.'));
    }

    public function makeDefault(Template $template): void
    {
        Template::query()->update(['is_default' => false]);
        $template->update(['is_default' => true]);
    }

    public function render()
    {
        $templates = Template::orderByDesc('is_default')->orderBy('name')->get();

        return view('invoicemaker::livewire.templates.index', compact('templates'));
    }
}
