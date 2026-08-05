<?php

namespace Modules\InvoiceMaker\Livewire\Templates;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\InvoiceMaker\Models\Template;

#[Layout('layouts.shell')]
class Builder extends Component
{
    use WithFileUploads;

    public Template $template;

    public string $name = '';

    public string $primary_color = '';

    public string $font_family = '';

    public string $logo_position = '';

    public string $header_style = 'default';

    public string $footer_message = '';

    public $signature;

    public string $signature_path = '';

    public string $payment_terms = '';

    public bool $show_tax = true;

    public bool $show_discount = true;

    public bool $enable_qr = false;

    protected array $rules = [
        'name' => 'required|string|max:255',
        'primary_color' => 'required|string|max:7',
        'font_family' => 'required|in:sans,serif,mono',
        'logo_position' => 'required|in:left,center,right',
        'header_style' => 'required|string|in:default,bold,minimal',
        'footer_message' => 'nullable|string|max:1000',
        'signature' => 'nullable|image|max:1024',
        'payment_terms' => 'nullable|string|max:1000',
        'show_tax' => 'boolean',
        'show_discount' => 'boolean',
        'enable_qr' => 'boolean',
    ];

    public function mount(?Template $invoice_template = null): void
    {
        $this->template = $invoice_template ?? new Template([
            'name' => '',
            'primary_color' => '#4f46e5',
            'font_family' => 'sans',
            'logo_position' => 'left',
            'header_style' => 'default',
            'footer_message' => '',
            'signature_path' => '',
            'payment_terms' => '',
            'show_tax' => true,
            'show_discount' => true,
            'enable_qr' => false,
        ]);

        if ($this->template->exists) {
            $this->authorize('update', $this->template);
        }

        $this->name = $this->template->name ?? '';
        $this->primary_color = $this->template->primary_color ?? '#4f46e5';
        $this->font_family = $this->template->font_family ?? 'sans';
        $this->logo_position = $this->template->logo_position ?? 'left';
        $this->header_style = $this->template->header_style ?? 'default';
        $this->footer_message = $this->template->footer_message ?? '';
        $this->signature_path = $this->template->signature_path ?? '';
        $this->payment_terms = $this->template->payment_terms ?? '';
        $this->show_tax = $this->template->show_tax ?? true;
        $this->show_discount = $this->template->show_discount ?? true;
        $this->enable_qr = $this->template->enable_qr ?? false;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'primary_color' => $this->primary_color,
            'font_family' => $this->font_family,
            'logo_position' => $this->logo_position,
            'header_style' => $this->header_style,
            'footer_message' => $this->footer_message,
            'payment_terms' => $this->payment_terms,
            'show_tax' => $this->show_tax,
            'show_discount' => $this->show_discount,
            'enable_qr' => $this->enable_qr,
        ];

        if ($this->signature) {
            $data['signature_path'] = $this->signature->store('signatures', 'public');
        }

        $this->template->fill($data);
        $this->template->save();

        session()->flash('message', 'Template saved successfully.');
    }

    public function render()
    {
        return view('invoicemaker::livewire.templates.builder');
    }
}
