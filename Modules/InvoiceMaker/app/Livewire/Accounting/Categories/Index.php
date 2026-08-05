<?php

namespace Modules\InvoiceMaker\Livewire\Accounting\Categories;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Modules\InvoiceMaker\Models\AccountingCategory;
use Modules\InvoiceMaker\Services\InvoiceMakerContext;

#[Layout('layouts.shell')]
class Index extends Component
{
    public $categories;

    public $name;

    public $type = 'expense';

    public $booking_account;

    public $posting_rule;

    public $categoryId;

    public $isEditing = false;

    public $showModal = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'type' => 'required|in:income,expense',
        'booking_account' => 'nullable|string|max:20',
        'posting_rule' => 'nullable|string',
    ];

    public function mount()
    {
        $this->loadCategories();
    }

    public function loadCategories()
    {
        $this->categories = AccountingCategory::where('team_id', app(InvoiceMakerContext::class)->profile()->team_id)
            ->orderBy('name')
            ->get();
    }

    public function openModal($id = null)
    {
        $this->resetInputFields();
        if ($id) {
            $this->isEditing = true;
            $this->categoryId = $id;
            $category = AccountingCategory::find($id);
            $this->name = $category->name;
            $this->type = $category->type;
            $this->booking_account = $category->booking_account;
            $this->posting_rule = $category->posting_rule;
        } else {
            $this->isEditing = false;
        }
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetInputFields();
    }

    public function resetInputFields()
    {
        $this->name = '';
        $this->type = 'expense';
        $this->booking_account = '';
        $this->posting_rule = '';
        $this->categoryId = null;
    }

    public function save()
    {
        $this->validate();

        AccountingCategory::updateOrCreate(
            ['id' => $this->categoryId],
            [
                'team_id' => app(InvoiceMakerContext::class)->profile()->team_id,
                'name' => $this->name,
                'type' => $this->type,
                'booking_account' => $this->booking_account,
                'posting_rule' => $this->posting_rule,
            ]
        );

        session()->flash('message', $this->categoryId ? 'Category updated successfully.' : 'Category created successfully.');
        $this->closeModal();
        $this->loadCategories();
    }

    public function delete($id)
    {
        AccountingCategory::find($id)->delete();
        session()->flash('message', 'Category deleted successfully.');
        $this->loadCategories();
    }

    public function render()
    {
        return view('invoicemaker::livewire.accounting.categories.index');
    }
}
