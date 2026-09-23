<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\GlossaryTerm;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Modules\AuditPro\Models\Audit;
use Modules\BookIntelligence\Models\QuestionMapping;
use Modules\FinancialPlatform\Models\Company;
use Modules\FinancialPlatform\Models\Lead;
use Modules\InvoiceMaker\Models\Client;
use Modules\InvoiceMaker\Models\Invoice;
use Modules\LeadQuality\Models\Contact;

class GlobalSearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $query = trim((string) $request->get('q', ''));
        $user = $request->user();
        $teamId = $user?->current_team_id;
        $results = [];

        if ($query !== '') {
            // 1. FAQs & Questions (Publicly accessible)
            try {
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
                            'icon' => '💡',
                            'records' => $faqs,
                        ];
                    }
                }
            } catch (\Throwable $e) {}

            // 2. Glossary Terms (Publicly accessible)
            try {
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
                            'icon' => '📚',
                            'records' => $terms,
                        ];
                    }
                }
            } catch (\Throwable $e) {}

            // 3. Blog Articles (Publicly accessible)
            try {
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
                            'icon' => '📰',
                            'records' => $posts,
                        ];
                    }
                }
            } catch (\Throwable $e) {}

            // 4. Authenticated Team Records (Contacts, Invoices, Leads, etc.)
            if ($teamId) {
                // Invoices Search
                try {
                    if (class_exists(Invoice::class)) {
                        $invoices = Invoice::query()
                            ->where('team_id', $teamId)
                            ->where(function ($iq) use ($query) {
                                $iq->where('invoice_number', 'like', "%{$query}%")
                                    ->orWhere('notes', 'like', "%{$query}%")
                                    ->orWhereHas('client', function ($cq) use ($query) {
                                        $cq->where('name', 'like', "%{$query}%");
                                    });
                            })
                            ->with('client')
                            ->limit(5)
                            ->get()
                            ->map(fn ($inv) => [
                                'type' => 'invoices',
                                'title' => 'Rechnung #' . $inv->invoice_number . ($inv->client?->name ? ' (' . $inv->client->name . ')' : ''),
                                'description' => ($inv->grand_total ? number_format((float) $inv->grand_total, 2, ',', '.') . ' €' : '') . ' · Status: ' . ucfirst($inv->status ?? 'Draft'),
                                'url' => Route::has('invoicemaker.invoices.show') ? route('invoicemaker.invoices.show', $inv) : null,
                            ]);

                        if ($invoices->isNotEmpty()) {
                            $results['invoices'] = [
                                'module' => __('Rechnungen'),
                                'icon' => '🧾',
                                'records' => $invoices,
                            ];
                        }
                    }
                } catch (\Throwable $e) {}

                // Clients Search
                try {
                    if (class_exists(Client::class)) {
                        $clients = Client::query()
                            ->where('team_id', $teamId)
                            ->where(function ($cq) use ($query) {
                                $cq->where('name', 'like', "%{$query}%")
                                    ->orWhere('company_name', 'like', "%{$query}%")
                                    ->orWhere('email', 'like', "%{$query}%");
                            })
                            ->limit(5)
                            ->get()
                            ->map(fn ($client) => [
                                'type' => 'clients',
                                'title' => $client->name . ($client->company_name ? ' — ' . $client->company_name : ''),
                                'description' => $client->email ?: '',
                                'url' => Route::has('invoicemaker.clients.show') ? route('invoicemaker.clients.show', $client) : null,
                            ]);

                        if ($clients->isNotEmpty()) {
                            $results['clients'] = [
                                'module' => __('Kunden'),
                                'icon' => '👥',
                                'records' => $clients,
                            ];
                        }
                    }
                } catch (\Throwable $e) {}

                // Contacts Search
                try {
                    if (class_exists(Contact::class)) {
                        $contacts = Contact::query()
                            ->where('team_id', $teamId)
                            ->where(function ($cq) use ($query) {
                                $cq->where('name', 'like', "%{$query}%")
                                    ->orWhere('email', 'like', "%{$query}%")
                                    ->orWhere('company', 'like', "%{$query}%");
                            })
                            ->limit(5)
                            ->get()
                            ->map(fn ($contact) => [
                                'type' => 'contacts',
                                'title' => $contact->name . ($contact->company ? ' — ' . $contact->company : ''),
                                'description' => $contact->email ?: '',
                                'url' => Route::has('leadquality.contacts.show') ? route('leadquality.contacts.show', $contact) : null,
                            ]);

                        if ($contacts->isNotEmpty()) {
                            $results['contacts'] = [
                                'module' => __('Kontakte'),
                                'icon' => '📇',
                                'records' => $contacts,
                            ];
                        }
                    }
                } catch (\Throwable $e) {}

                // Leads Search
                try {
                    if (class_exists(Lead::class)) {
                        $leads = Lead::query()
                            ->where('team_id', $teamId)
                            ->where(function ($lq) use ($query) {
                                $lq->where('name', 'like', "%{$query}%")
                                    ->orWhere('email', 'like', "%{$query}%")
                                    ->orWhere('company_name', 'like', "%{$query}%");
                            })
                            ->limit(5)
                            ->get()
                            ->map(fn ($lead) => [
                                'type' => 'leads',
                                'title' => $lead->name . ($lead->company_name ? ' — ' . $lead->company_name : ''),
                                'description' => $lead->email ?: '',
                                'url' => Route::has('leads.show') ? route('leads.show', $lead) : null,
                            ]);

                        if ($leads->isNotEmpty()) {
                            $results['leads'] = [
                                'module' => __('Leads'),
                                'icon' => '🎯',
                                'records' => $leads,
                            ];
                        }
                    }
                } catch (\Throwable $e) {}

                // Audits Search
                try {
                    if (class_exists(Audit::class)) {
                        $audits = Audit::query()
                            ->where('team_id', $teamId)
                            ->where('id', 'like', "%{$query}%")
                            ->limit(5)
                            ->get()
                            ->map(fn ($audit) => [
                                'type' => 'audits',
                                'title' => __('Audit') . ' #' . $audit->id,
                                'description' => 'Status: ' . ($audit->status ?? 'In progress'),
                                'url' => Route::has('audit.report') ? route('audit.report', $audit) : null,
                            ]);

                        if ($audits->isNotEmpty()) {
                            $results['audits'] = [
                                'module' => __('Audits'),
                                'icon' => '📊',
                                'records' => $audits,
                            ];
                        }
                    }
                } catch (\Throwable $e) {}
            }

            // 5. Public Pages
            try {
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
                        'description' => 'Allocore Informationsseite',
                        'url' => route('pages.show', $page->slug),
                    ]);

                if ($pages->isNotEmpty()) {
                    $results['pages'] = [
                        'module' => __('Seiten'),
                        'icon' => '📄',
                        'records' => $pages,
                    ];
                }
            } catch (\Throwable $e) {}
        }

        return view('search.index', compact('query', 'results'));
    }
}
