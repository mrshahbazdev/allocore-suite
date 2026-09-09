<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\GlossaryTerm;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route;
use Modules\AuditPro\Models\Audit;
use Modules\BookIntelligence\Models\QuestionMapping;
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
        $user = $request->user();
        $teamId = $user?->current_team_id;
        $results = [];

        if ($query !== '') {
            // 1. FAQs & Questions (Publicly accessible)
            if (class_exists(QuestionMapping::class)) {
                $faqs = QuestionMapping::withoutGlobalScope('current_team')
                    ->where('is_active', true)
                    ->search($query)
                    ->limit(6)
                    ->get()
                    ->map(fn ($faq) => [
                        'type' => 'faq',
                        'title' => $faq->question,
                        'description' => $faq->answer_excerpt ?: $faq->problem_statement,
                        'category' => $faq->category,
                        'url' => route('faq.index', ['q' => $faq->question]),
                    ]);

                if ($faqs->isNotEmpty()) {
                    $results['faqs'] = [
                        'module' => __('Häufige Fragen & Antworten (FAQ)'),
                        'records' => $faqs,
                    ];
                }
            }

            // 2. Glossary Terms (Publicly accessible)
            if (class_exists(GlossaryTerm::class)) {
                $terms = GlossaryTerm::where('is_published', true)
                    ->where(function ($q) use ($query) {
                        $q->where('term', 'like', "%{$query}%")
                            ->orWhere('definition', 'like', "%{$query}%");
                    })
                    ->limit(6)
                    ->get()
                    ->map(fn ($term) => [
                        'type' => 'glossary',
                        'title' => $term->term,
                        'description' => $term->definition,
                        'url' => route('glossary.show', $term),
                    ]);

                if ($terms->isNotEmpty()) {
                    $results['glossary'] = [
                        'module' => __('Glossar & Fachbegriffe'),
                        'records' => $terms,
                    ];
                }
            }

            // 3. Blog Articles (Publicly accessible)
            if (class_exists(BlogPost::class)) {
                $posts = BlogPost::where('is_published', true)
                    ->where(function ($q) use ($query) {
                        $q->where('title', 'like', "%{$query}%")
                            ->orWhere('excerpt', 'like', "%{$query}%");
                    })
                    ->limit(6)
                    ->get()
                    ->map(fn ($post) => [
                        'type' => 'blog',
                        'title' => $post->title,
                        'description' => $post->excerpt,
                        'url' => route('blog.show', $post->slug),
                    ]);

                if ($posts->isNotEmpty()) {
                    $results['blog'] = [
                        'module' => __('Blog & Fachartikel'),
                        'records' => $posts,
                    ];
                }
            }

            // 4. Authenticated Team Records (Contacts, Invoices, Leads, etc.)
            if ($teamId) {
                foreach ($this->searchable as $group => [$model, $fields, $route]) {
                    if (!class_exists($model)) {
                        continue;
                    }

                    $q = $model::query()->where('team_id', $teamId);
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
                        $results[$group] = [
                            'module' => __(ucfirst($group)),
                            'records' => $items,
                        ];
                    }
                }
            }

            // 5. Public Pages
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
                $results['pages'] = [
                    'module' => __('Seiten'),
                    'records' => $pages,
                ];
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
