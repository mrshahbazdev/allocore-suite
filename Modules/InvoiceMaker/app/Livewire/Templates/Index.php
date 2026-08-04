<?php

namespace Modules\InvoiceMaker\Livewire\Templates;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Modules\InvoiceMaker\Services\InvoiceMakerContext;

#[Layout('layouts.shell')]
class Index extends Component
{
    public function setDefault(int $id): void
    {
        $template = app(InvoiceMakerContext::class)->profile()->templates()->findOrFail($id);

        app(InvoiceMakerContext::class)->profile()->templates()->update(['is_default' => false]);
        $template->update(['is_default' => true]);

        session()->flash('message', 'Default template updated.');
    }

    public function render()
    {
        $templates = app(InvoiceMakerContext::class)->profile()->templates()->get();

        return view('invoicemaker::livewire.templates.index', compact('templates'));
    }
}
