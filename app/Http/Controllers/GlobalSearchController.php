<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route;
use Modules\AuditPro\Models\Audit;
use Modules\FinancialPlatform\Models\Company;
use Modules\FinancialPlatform\Models\Lead;
use Modules\InvoiceMaker\Models\Client;
use Modules\InvoiceMaker\Models\Invoice;
use Modules\LeadQuality\Models\Contact;

class GlobalSearchController extends Controller
{
    private array $searchable = [
        'contacts' => [Contact::class, ['name', 'email', 'company'], 'leadquality.contacts.show'],
        'companies' => [Company::class, ['name', 'industry'], 'companies.show'],
        'clients' => [Client::class, ['name', 'company_name', 'email'], 'invoicemaker.clients.show'],
        'invoices' => [Invoice::class, ['invoice_number', 'client_name'], 'invoicemaker.invoices.show'],
        'leads' => [Lead::class, ['name', 'email', 'company_name'], 'leads.show'],
        'audits' => [Audit::class, ['id'], 'audit.report'],
    ];

    public function __invoke(Request $request)
    {
        $query = trim($request->get('q', ''));
        $teamId = $request->user()->current_team_id;
        $results = [];

        if ($query && $teamId) {
            foreach ($this->searchable as $group => [$model, $fields, $route]) {
                $q = $model::query();

                $q->where('team_id', $teamId);

                $q->where(function ($q) use ($fields, $query) {
                    foreach ($fields as $field) {
                        $q->orWhere($field, 'like', '%'.$query.'%');
                    }
                });

                $items = $q->limit(5)->get()->map(function ($item) use ($group, $route) {
                    return [
                        'type' => $group,
                        'title' => $this->titleFor($item, $group),
                        'url' => Route::has($route) ? route($route, $item) : null,
                    ];
                });

                if ($items->isNotEmpty()) {
                    $results[$group] = $items;
                }
            }

            $pages = Page::where('is_active', true)
                ->whereHas('translations', function ($q) use ($query) {
                    $q->where('title', 'like', '%'.$query.'%')
                        ->orWhere('slug', 'like', '%'.$query.'%');
                })
                ->limit(5)
                ->get()
                ->map(fn ($page) => [
                    'type' => 'page',
                    'title' => $page->title,
                    'url' => route('pages.show', $page->slug),
                ]);

            if ($pages->isNotEmpty()) {
                $results['pages'] = $pages;
            }
        }

        return view('search.index', compact('query', 'results'));
    }

    private function titleFor($item, string $group): string
    {
        return match ($group) {
            'contacts' => $item->name.($item->company ? ' — '.$item->company : ''),
            'companies' => $item->name,
            'clients' => $item->name.($item->company_name ? ' — '.$item->company_name : ''),
            'invoices' => $item->invoice_number,
            'leads' => $item->name,
            'audits' => __('Audit').' #'.$item->id,
            default => (string) $item->id,
        };
    }
}
