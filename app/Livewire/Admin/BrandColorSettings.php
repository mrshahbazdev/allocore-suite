<?php

namespace App\Livewire\Admin;

use App\Models\SiteSetting;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.shell')]
class BrandColorSettings extends Component
{
    public string $primary = '';

    public string $secondary = '';

    public function mount(): void
    {
        $this->primary = SiteSetting::value('primary_color', '#ff9200');
        $this->secondary = SiteSetting::value('accent_color', '#0094af');
    }

    public function save(): void
    {
        $this->validate([
            'primary' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ], [
            'primary.regex' => __('Please enter a valid hex color, e.g. #FF9200.'),
            'secondary.regex' => __('Please enter a valid hex color, e.g. #0094AF.'),
        ]);

        SiteSetting::set('primary_color', $this->primary);
        SiteSetting::set('accent_color', $this->secondary);

        session()->flash('success', __('Brand colors updated.'));
        $this->redirectRoute('admin.brand-colors.index', navigate: true);
    }

    public function resetDefaults(): void
    {
        $this->primary = '#ff9200';
        $this->secondary = '#0094af';
    }

    public function render()
    {
        return view('livewire.admin.brand-color-settings');
    }
}
