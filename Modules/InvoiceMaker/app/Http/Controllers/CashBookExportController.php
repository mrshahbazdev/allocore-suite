<?php

namespace Modules\InvoiceMaker\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Modules\InvoiceMaker\Models\CashBookEntry;
use Modules\InvoiceMaker\Services\InvoiceMakerContext;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CashBookExportController
{
    public function exportExcel(Request $request)
    {
        $business = app(InvoiceMakerContext::class)->profile();
        $query = CashBookEntry::with(['category', 'invoice', 'expense'])
            ->where('team_id', $business->team_id);

        if ($request->has('startDate')) {
            $query->whereDate('date', '>=', $request->startDate);
        }
        if ($request->has('endDate')) {
            $query->whereDate('date', '<=', $request->endDate);
        }

        $entries = $query->orderBy('date', 'asc')
            ->orderBy('booking_number', 'asc')
            ->get();

        $fileName = 'CashBook_'.str_replace(' ', '_', $business->name).'_'.now()->format('Y-m-d').'.xls';

        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ];

        return new StreamedResponse(function () use ($entries, $business) {
            $handle = fopen('php://output', 'w');

            // Output XML/HTML Header for Excel
            fwrite($handle, '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">');
            fwrite($handle, '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">');
            fwrite($handle, '<style>
                table { border-collapse: collapse; }
                th { background-color: #f1f5f9; border: 1px solid #cbd5e1; font-weight: bold; padding: 10px; }
                td { border: 1px solid #e2e8f0; padding: 8px; vertical-align: top; }
                .amount { text-align: right; font-family: monospace; mso-number-format: "Standard"; }
                .text { mso-number-format: "\@"; }
                .income { color: #15803d; }
                .expense { color: #b91c1c; }
            </style></head><body>');

            fwrite($handle, '<table>');
            fwrite($handle, '<thead><tr>');
            foreach ([
                __('Booking Number'),
                __('Date'),
                __('Type'),
                __('Source'),
                __('Category'),
                __('Description'),
                __('Amount').' ('.$business->currency.')',
                __('Linked Job'),
                __('Posting Rule'),
            ] as $header) {
                fwrite($handle, '<th>'.$header.'</th>');
            }
            fwrite($handle, '</tr></thead><tbody>');

            foreach ($entries as $entry) {
                $typeClass = $entry->type === 'income' ? 'income' : 'expense';
                // Use numeric value for calculation, but visual value for the cell content
                $numericAmount = $entry->type === 'income' ? $entry->amount : -$entry->amount;
                // Add explicit minus sign for expenses in display
                $displayAmount = $entry->type === 'income' ? number_format($entry->amount, 2, ',', '.') : '-'.number_format($entry->amount, 2, ',', '.');

                fwrite($handle, '<tr>');
                fwrite($handle, '<td class="text">'.$entry->booking_number.'</td>');
                fwrite($handle, '<td>'.$entry->date->format('d.m.Y').'</td>');
                fwrite($handle, '<td class="'.$typeClass.'">'.ucfirst($entry->type).'</td>');
                fwrite($handle, '<td>'.ucfirst($entry->source).'</td>');
                fwrite($handle, '<td>'.($entry->category ? $entry->category->name : __('N/A')).'</td>');
                fwrite($handle, '<td>'.$entry->description.'</td>');
                // Use x:num attribute for Excel calculation, but $displayAmount for visual clarity
                fwrite($handle, '<td class="amount '.$typeClass.'" x:num="'.$numericAmount.'">'.$displayAmount.'</td>');
                fwrite($handle, '<td>'.($entry->invoice ? $entry->invoice->invoice_number : __('General')).'</td>');
                fwrite($handle, '<td>'.($entry->category ? $entry->category->posting_rule : '').'</td>');
                fwrite($handle, '</tr>');
            }

            fwrite($handle, '</tbody></table></body></html>');
            fclose($handle);
        }, 200, $headers);
    }

    public function exportCsv(Request $request)
    {
        $business = app(InvoiceMakerContext::class)->profile();
        $query = CashBookEntry::with(['category', 'invoice', 'expense'])
            ->where('team_id', $business->team_id);

        if ($request->has('startDate')) {
            $query->whereDate('date', '>=', $request->startDate);
        }
        if ($request->has('endDate')) {
            $query->whereDate('date', '<=', $request->endDate);
        }

        $entries = $query->orderBy('date', 'asc')
            ->orderBy('booking_number', 'asc')
            ->get();

        $fileName = 'CashBook_'.$business->name.'_'.($request->startDate ?? 'all').'_to_'.($request->endDate ?? now()->format('Y-m-d')).'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ];

        return new StreamedResponse(function () use ($entries) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM for Excel UTF-8 support

            fputcsv($handle, [
                __('Booking Number'),
                __('Date'),
                __('Type'),
                __('Source'),
                __('Category'),
                __('Description'),
                __('Amount'),
                __('Linked Job'),
                __('Posting Rule'),
            ]);

            foreach ($entries as $entry) {
                $numericAmount = $entry->type === 'income' ? $entry->amount : -$entry->amount;
                fputcsv($handle, [
                    $entry->booking_number,
                    $entry->date->format('d.m.Y'),
                    ucfirst($entry->type),
                    ucfirst($entry->source),
                    $entry->category ? $entry->category->name : __('N/A'),
                    $entry->description,
                    $numericAmount,
                    $entry->invoice ? $entry->invoice->invoice_number : __('General'),
                    $entry->category ? $entry->category->posting_rule : '',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        $business = app(InvoiceMakerContext::class)->profile();
        $query = CashBookEntry::with(['category', 'invoice', 'expense'])
            ->where('team_id', $business->team_id);

        if ($request->has('startDate')) {
            $query->whereDate('date', '>=', $request->startDate);
        }
        if ($request->has('endDate')) {
            $query->whereDate('date', '<=', $request->endDate);
        }

        $entries = $query->orderBy('date', 'asc')
            ->orderBy('booking_number', 'asc')
            ->get();

        $incomeTotal = $entries->where('type', 'income')->sum('amount');
        $expenseTotal = $entries->where('type', 'expense')->sum('amount');

        $pdf = Pdf::loadView('invoicemaker::exports.cash-book', [
            'business' => $business,
            'entries' => $entries,
            'incomeTotal' => $incomeTotal,
            'expenseTotal' => $expenseTotal,
            'startDate' => $request->startDate,
            'endDate' => $request->endDate,
        ]);

        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('CashBook_'.$business->name.'_'.now()->format('Y-m-d').'.pdf');
    }
}
