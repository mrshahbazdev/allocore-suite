<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\AuditPro\Models\Audit;
use Modules\AuditPro\Models\AuditTemplate;
use Modules\FinancialPlatform\Models\Company;
use Modules\FinancialPlatform\Models\KpiResult;
use Modules\FinancialPlatform\Models\Lead;
use Modules\FinancialPlatform\Models\PaypalTransaction;
use Modules\InvoiceMaker\Models\AccountingCategory;
use Modules\InvoiceMaker\Models\CashBookEntry;
use Modules\InvoiceMaker\Models\Client;
use Modules\InvoiceMaker\Models\EmailLog;
use Modules\InvoiceMaker\Models\EmailTemplate;
use Modules\InvoiceMaker\Models\Expense;
use Modules\InvoiceMaker\Models\Product;
use Modules\InvoiceMaker\Models\Template as InvoiceTemplate;
use Modules\LeadQuality\Models\Contact;
use Modules\LeadQuality\Models\EmailAccount;
use Modules\LeadQuality\Models\IcpProfile;
use Modules\LeadQuality\Models\OutreachTemplate;
use Modules\LeadQuality\Models\Sequence;

class ModuleDataController extends Controller
{
    private array $groups = [
        'invoice-maker' => [
            'clients' => [Client::class, ['name', 'company_name', 'email', 'phone', 'currency', 'created_at'], ['name', 'company_name', 'email']],
            'products' => [Product::class, ['name', 'price', 'unit', 'tax_rate', 'stock_quantity', 'created_at'], ['name']],
            'expenses' => [Expense::class, ['category', 'amount', 'date', 'partner_name', 'reference_number', 'created_at'], ['category', 'partner_name', 'reference_number']],
            'cash-book' => [CashBookEntry::class, ['booking_number', 'type', 'amount', 'date', 'source', 'partner_name', 'created_at'], ['booking_number', 'source', 'partner_name']],
            'categories' => [AccountingCategory::class, ['name', 'type', 'booking_account', 'posting_rule', 'created_at'], ['name', 'type']],
            'email-logs' => [EmailLog::class, ['recipient_email', 'subject', 'type', 'status', 'created_at'], ['recipient_email', 'subject']],
            'email-templates' => [EmailTemplate::class, ['name', 'type', 'subject', 'is_default', 'created_at'], ['name', 'subject']],
            'templates' => [InvoiceTemplate::class, ['name', 'is_default', 'primary_color', 'font_family', 'created_at'], ['name']],
        ],
        'lead-os' => [
            'contacts' => [Contact::class, ['name', 'email', 'company', 'status', 'industry', 'created_at'], ['name', 'email', 'company']],
            'icp-profiles' => [IcpProfile::class, ['industry', 'employee_count_range', 'budget_min', 'budget_max', 'role', 'location', 'created_at'], ['industry', 'role', 'location']],
            'sequences' => [Sequence::class, ['name', 'is_active', 'created_at'], ['name']],
            'outreach-templates' => [OutreachTemplate::class, ['name', 'type', 'created_at'], ['name']],
            'email-accounts' => [EmailAccount::class, ['email_address', 'provider', 'is_active', 'created_at'], ['email_address', 'provider']],
        ],
        'financial' => [
            'companies' => [Company::class, ['name', 'industry', 'currency', 'country', 'created_at'], ['name', 'industry', 'country']],
            'leads' => [Lead::class, ['name', 'email', 'company_name', 'status', 'priority', 'created_at'], ['name', 'email', 'company_name']],
            'kpi-results' => [KpiResult::class, ['kpi_name', 'kpi_code', 'value', 'score', 'weight', 'traffic_light', 'year_label', 'created_at'], ['kpi_name', 'kpi_code']],
            'paypal-transactions' => [PaypalTransaction::class, ['paypal_order_id', 'payer_email', 'payer_name', 'amount', 'currency', 'status', 'created_at'], ['paypal_order_id', 'payer_email']],
        ],
        'audit-pro' => [
            'audits' => [Audit::class, ['team_id', 'template_id', 'status', 'created_at'], []],
            'templates' => [AuditTemplate::class, ['name', 'is_default', 'team_id', 'created_at'], ['name']],
        ],
    ];

    public function index(Request $request, string $group, string $resource)
    {
        $config = $this->resolveConfig($group, $resource);

        $query = $config['model']::query();

        if (method_exists($config['model'], 'getGlobalScopes')) {
            $query->withoutGlobalScope('current_team');
        }

        $query->when($request->filled('search'), function ($q) use ($request, $config) {
            $q->where(function ($builder) use ($request, $config) {
                foreach ($config['search'] as $field) {
                    $builder->orWhere($field, 'like', '%'.$request->search.'%');
                }
            });
        });

        $items = $query->latest()->paginate(25)->withQueryString();

        return view('admin.module-data.index', [
            'group' => $group,
            'resource' => $resource,
            'resources' => array_keys($this->groups[$group]),
            'items' => $items,
            'columns' => $config['columns'],
            'title' => __(Str::headline(str_replace('-', ' ', $resource))),
        ]);
    }

    public function show(string $group, string $resource, int $id)
    {
        $config = $this->resolveConfig($group, $resource);

        $item = $config['model']::query();
        if (method_exists($config['model'], 'getGlobalScopes')) {
            $item->withoutGlobalScope('current_team');
        }

        $item = $item->findOrFail($id);

        return view('admin.module-data.show', [
            'group' => $group,
            'resource' => $resource,
            'item' => $item,
            'columns' => $config['columns'],
            'title' => __(Str::headline(str_replace('-', ' ', $resource))),
        ]);
    }

    public function destroy(string $group, string $resource, int $id)
    {
        $config = $this->resolveConfig($group, $resource);

        $item = $config['model']::query();
        if (method_exists($config['model'], 'getGlobalScopes')) {
            $item->withoutGlobalScope('current_team');
        }

        $item->findOrFail($id)->delete();

        return redirect()->route('admin.module-data.index', [$group, $resource])->with('success', __('Deleted.'));
    }

    private function resolveConfig(string $group, string $resource): array
    {
        abort_unless(isset($this->groups[$group][$resource]), 404);

        [$model, $columns, $search] = $this->groups[$group][$resource];

        return compact('model', 'columns', 'search');
    }
}
