<?php

namespace Modules\InvoiceMaker\Livewire\Expenses;

use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\InvoiceMaker\Models\AccountingCategory;
use Modules\InvoiceMaker\Models\CashBookEntry;
use Modules\InvoiceMaker\Models\Expense;
use Modules\InvoiceMaker\Services\AiService;
use Modules\InvoiceMaker\Services\InvoiceMakerContext;

#[Layout('layouts.shell')]
class Create extends Component
{
    use WithFileUploads;

    public $source = 'cash';

    public $category_id = null;

    public $invoice_id = null;

    public $client_id = null;

    public $product_id = null;

    public $category = '';

    public $amount;

    public $date;

    public $description = '';

    public $receipt;

    public $posting_rule = '';

    public $partner_name = '';

    public $reference_number = '';

    protected $rules = [
        'source' => 'required|in:cash,bank',
        'category_id' => 'required|exists:accounting_categories,id',
        'invoice_id' => 'nullable|exists:invoices,id',
        'client_id' => 'nullable|exists:clients,id',
        'product_id' => 'nullable|exists:products,id',
        'amount' => 'required|numeric|min:0',
        'date' => 'required|date',
        'description' => 'required|string|max:255',
        'partner_name' => 'nullable|string|max:255',
        'reference_number' => 'nullable|string|max:255',
        'receipt' => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf|max:2048', // 2MB Max
    ];

    public function mount()
    {
        $this->date = now()->format('Y-m-d');

        // Ensure default categories exist for the business
        $this->ensureDefaultCategories();
    }

    protected function ensureDefaultCategories()
    {
        $business = app(InvoiceMakerContext::class)->profile();
        $defaults = [
            ['name' => 'Travel', 'type' => 'expense', 'posting_rule' => 'Requires receipt. Deductible if business related.'],
            ['name' => 'Office Supplies', 'type' => 'expense', 'posting_rule' => 'Small items under $250.'],
            ['name' => 'Software', 'type' => 'expense', 'posting_rule' => 'SaaS subscriptions and licenses.'],
            ['name' => 'Rent', 'type' => 'expense', 'posting_rule' => 'Monthly office rent.'],
        ];

        foreach ($defaults as $tmpl) {
            AccountingCategory::firstOrCreate(
                ['team_id' => $business->team_id, 'name' => $tmpl['name']],
                $tmpl
            );
        }
    }

    public function updatedCategoryId($value)
    {
        $category = AccountingCategory::find($value);
        $this->posting_rule = $category ? $category->posting_rule : '';
        $this->category = $category ? $category->name : '';
    }

    public function save()
    {
        $this->validate();

        $receiptPath = null;
        if ($this->receipt) {
            $receiptPath = $this->receipt->store('receipts', 'public');
        }

        DB::transaction(function () use ($receiptPath) {
            $business = app(InvoiceMakerContext::class)->profile();
            $invoice_id = $this->invoice_id ?: null;
            $client_id = $this->client_id ?: null;
            $product_id = $this->product_id ?: null;

            $expense = Expense::create([
                'team_id' => $business->team_id,
                'client_id' => $client_id,
                'product_id' => $product_id,
                'category_id' => $this->category_id,
                'category' => $this->category,
                'amount' => $this->amount,
                'date' => $this->date,
                'description' => $this->description,
                'partner_name' => $this->partner_name,
                'reference_number' => $this->reference_number,
                'receipt_path' => $receiptPath,
                'invoice_id' => $invoice_id,
            ]);

            // Create Cash Book Entry
            CashBookEntry::create([
                'team_id' => $business->team_id,
                'date' => $this->date,
                'document_date' => $this->date,
                'amount' => $this->amount,
                'type' => 'expense',
                'source' => $this->source,
                'description' => $this->description,
                'partner_name' => $this->partner_name,
                'reference_number' => $this->reference_number,
                'category_id' => $this->category_id,
                'invoice_id' => $invoice_id,
                'expense_id' => $expense->id,
            ]);
        });

        session()->flash('message', 'Expense and Cash Book entry recorded successfully.');

        return redirect()->route('invoicemaker.expenses.index');
    }

    public function scanReceipt(AiService $aiService)
    {
        if (! $this->receipt) {
            session()->flash('error', __('Please upload a receipt image first.'));

            return;
        }

        if (! in_array(strtolower($this->receipt->extension()), ['png', 'jpg', 'jpeg', 'webp'])) {
            session()->flash('error', __('AI Scanning currently only supports images (PNG, JPG, WEBP).'));

            return;
        }

        $imagePath = $this->receipt->getRealPath();

        $prompt = 'Analyze this receipt and extract the following information in JSON format: '.
            '{"amount": float, "date": "YYYY-MM-DD", "description": "string", "partner_name": "string", "reference_number": "string"}. '.
            'Do not wrap it in markdown block quotes, just output the raw JSON.';

        try {
            $jsonResponse = $aiService->generateText($prompt, $imagePath);

            if (preg_match('/\{.*\}/s', $jsonResponse, $matches)) {
                $jsonResponse = $matches[0];
            }

            $data = json_decode($jsonResponse, true);

            if ($data && json_last_error() === JSON_ERROR_NONE) {
                if (isset($data['amount'])) {
                    $this->amount = $data['amount'];
                }
                if (isset($data['date'])) {
                    $this->date = $data['date'];
                }
                if (isset($data['description'])) {
                    $this->description = $data['description'];
                }
                if (isset($data['partner_name'])) {
                    $this->partner_name = $data['partner_name'];
                }
                if (isset($data['reference_number'])) {
                    $this->reference_number = $data['reference_number'];
                }

                session()->flash('message', __('Receipt scanned successfully!'));
            } else {
                session()->flash('error', __('Failed to parse AI response. Try again.'));
            }
        } catch (\Exception $e) {
            session()->flash('error', __('AI Scanning failed: ').$e->getMessage());
        }
    }

    public function render()
    {
        $business = app(InvoiceMakerContext::class)->profile();
        $categories = $business->accounting_categories ?? AccountingCategory::where('team_id', $business->team_id)->get();
        $invoices = $business->invoices()->orderBy('created_at', 'desc')->get();
        $clients = $business->clients()->orderBy('name')->get();
        $products = $business->products()->orderBy('name')->get();

        return view('invoicemaker::livewire.expenses.create', [
            'categories' => $categories,
            'invoices' => $invoices,
            'clients' => $clients,
            'products' => $products,
        ]);
    }
}
