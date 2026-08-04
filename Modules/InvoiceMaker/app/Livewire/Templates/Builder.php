<?php

namespace Modules\InvoiceMaker\Livewire\Templates;

use Illuminate\Support\Facades\Storage;
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

    public string $primary_color = '#4f46e5';

    public string $font_family = 'sans';

    public string $logo_position = 'left';

    public string $header_style = 'default';

    public ?string $footer_message = null;

    public ?string $signature_path = null;

    public $signature;

    public ?string $payment_terms = null;

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

    public function mount(Template $template): void
    {
        $this->template = $template;
        $this->name = $template->name;
        $this->primary_color = $template->primary_color;
        $this->font_family = $template->font_family;
        $this->logo_position = $template->logo_position;
        $this->header_style = $template->header_style ?? 'default';
        $this->footer_message = $template->footer_message ?? '';
        $this->signature_path = $template->signature_path ?? '';
        $this->payment_terms = $template->payment_terms ?? '';
        $this->show_tax = $template->show_tax ?? true;
        $this->show_discount = $template->show_discount ?? true;
        $this->enable_qr = $template->enable_qr ?? false;
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
            $path = $this->signature->store('signatures', 'public');
            if ($this->signature_path && Storage::disk('public')->exists($this->signature_path)) {
                Storage::disk('public')->delete($this->signature_path);
            }
            $data['signature_path'] = $path;
            $this->signature_path = $path;
        }

        $this->template->update($data);

        session()->flash('success', __('Template updated successfully.'));
    }

    public function removeSignature(): void
    {
        if ($this->signature_path && Storage::disk('public')->exists($this->signature_path)) {
            Storage::disk('public')->delete($this->signature_path);
        }
        $this->signature_path = null;
        $this->template->update(['signature_path' => null]);
    }

    public function render()
    {
        return view('invoicemaker::livewire.templates.builder');
    }
}
