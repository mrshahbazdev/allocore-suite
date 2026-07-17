<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Team;
use App\Models\ToolSubscription;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\InvoiceMaker\Models\Invoice;
use Modules\InvoiceMaker\Models\Payment;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.exports.index');
    }

    public function export(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:users,teams,subscriptions,invoices,payments,activity-logs'],
            'start' => ['nullable', 'date'],
            'end' => ['nullable', 'date'],
        ]);

        $type = $validated['type'];
        $start = $validated['start'] ?? null;
        $end = $validated['end'] ?? null;

        $fileName = $type.'_'.now()->format('Ymd_His').'.csv';

        return match ($type) {
            'users' => $this->exportUsers($start, $end, $fileName),
            'teams' => $this->exportTeams($start, $end, $fileName),
            'subscriptions' => $this->exportSubscriptions($start, $end, $fileName),
            'invoices' => $this->exportInvoices($start, $end, $fileName),
            'payments' => $this->exportPayments($start, $end, $fileName),
            'activity-logs' => $this->exportActivityLogs($start, $end, $fileName),
            default => abort(404),
        };
    }

    protected function exportUsers(?string $start, ?string $end, string $fileName): StreamedResponse
    {
        $query = User::query();
        $this->applyDateRange($query, $start, $end, 'created_at');

        return $this->streamCsv($fileName, $query->get(), ['id', 'name', 'email', 'current_team_id', 'email_verified_at', 'created_at']);
    }

    protected function exportTeams(?string $start, ?string $end, string $fileName): StreamedResponse
    {
        $query = Team::query();
        $this->applyDateRange($query, $start, $end, 'created_at');

        return $this->streamCsv($fileName, $query->get(), ['id', 'name', 'industry', 'size', 'created_at']);
    }

    protected function exportSubscriptions(?string $start, ?string $end, string $fileName): StreamedResponse
    {
        $query = ToolSubscription::query()->with('plan');
        $this->applyDateRange($query, $start, $end, 'created_at');

        $rows = $query->get()->map(fn (ToolSubscription $subscription) => [
            'id' => $subscription->id,
            'billable_type' => $subscription->billable_type,
            'billable_id' => $subscription->billable_id,
            'plan' => $subscription->plan?->name,
            'status' => $subscription->status,
            'interval' => $subscription->interval,
            'amount' => $subscription->amount,
            'currency' => $subscription->currency,
            'starts_at' => $subscription->starts_at?->toDateString(),
            'ends_at' => $subscription->ends_at?->toDateString(),
            'created_at' => $subscription->created_at,
        ])->all();

        return $this->streamArray($fileName, array_keys($rows[0] ?? []), $rows);
    }

    protected function exportInvoices(?string $start, ?string $end, string $fileName): StreamedResponse
    {
        $query = Invoice::withoutGlobalScope('current_team')->with('client');
        $this->applyDateRange($query, $start, $end, 'invoice_date');

        $headers = ['id', 'team_id', 'invoice_number', 'client_name', 'status', 'grand_total', 'amount_paid', 'currency', 'invoice_date', 'due_date', 'created_at'];

        return $this->streamCustom($fileName, $headers, function ($handle) use ($query) {
            foreach ($query->cursor() as $invoice) {
                fputcsv($handle, [
                    $invoice->id,
                    $invoice->team_id,
                    $invoice->invoice_number,
                    $invoice->client?->name,
                    $invoice->status,
                    $invoice->grand_total,
                    $invoice->amount_paid,
                    $invoice->currency,
                    $invoice->invoice_date?->toDateString(),
                    $invoice->due_date?->toDateString(),
                    $invoice->created_at,
                ]);
            }
        });
    }

    protected function exportPayments(?string $start, ?string $end, string $fileName): StreamedResponse
    {
        $query = Payment::withoutGlobalScope('current_team')->with('invoice');
        $this->applyDateRange($query, $start, $end, 'date');

        $headers = ['id', 'team_id', 'invoice_number', 'amount', 'method', 'date', 'reference', 'created_at'];

        return $this->streamCustom($fileName, $headers, function ($handle) use ($query) {
            foreach ($query->cursor() as $payment) {
                fputcsv($handle, [
                    $payment->id,
                    $payment->team_id,
                    $payment->invoice?->invoice_number,
                    $payment->amount,
                    $payment->method,
                    $payment->date?->toDateString(),
                    $payment->reference,
                    $payment->created_at,
                ]);
            }
        });
    }

    protected function exportActivityLogs(?string $start, ?string $end, string $fileName): StreamedResponse
    {
        $query = ActivityLog::query();
        $this->applyDateRange($query, $start, $end, 'created_at');

        return $this->streamCsv($fileName, $query->get(), ['id', 'log_name', 'description', 'causer_type', 'causer_id', 'created_at']);
    }

    protected function applyDateRange($query, ?string $start, ?string $end, string $column): void
    {
        if ($start) {
            $query->whereDate($column, '>=', $start);
        }

        if ($end) {
            $query->whereDate($column, '<=', $end);
        }
    }

    protected function streamCsv(string $fileName, $rows, array $columns): StreamedResponse
    {
        return $this->streamCustom($fileName, $columns, function ($handle) use ($rows, $columns) {
            foreach ($rows as $row) {
                fputcsv($handle, $row->only($columns));
            }
        });
    }

    protected function streamArray(string $fileName, array $headers, array $rows): StreamedResponse
    {
        return $this->streamCustom($fileName, $headers, function ($handle) use ($rows) {
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
        });
    }

    protected function streamCustom(string $fileName, array $headers, callable $callback): StreamedResponse
    {
        $response = new StreamedResponse(function () use ($headers, $callback) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            $callback($handle);
            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$fileName.'"');

        return $response;
    }
}
