<!DOCTYPE html>
<html>

<head>
 <meta charset="utf-8">
 <title>{{ __('Cash Book') }} - {{ $business->name }}</title>
 <style>
 body {
 font-family: 'Helvetica', 'Arial', sans-serif;
 font-size: 10px;
 color: #333;
 line-height: 1.4;
 }

 .header {
 margin-bottom: 20px;
 border-bottom: 2px solid #2563eb;
 padding-bottom: 10px;
 }

 .business-info {
 float: left;
 width: 50%;
 }

 .report-info {
 float: right;
 width: 50%;
 text-align: right;
 }

 .clear {
 clear: both;
 }

 h1 {
 margin: 0;
 color: #1e3a8a;
 font-size: 18px;
 text-transform: uppercase;
 }

 .summary-boxes {
 margin-bottom: 20px;
 width: 100%;
 border-collapse: collapse;
 }

 .summary-box {
 border: 1px solid #e5e7eb;
 padding: 10px;
 text-align: center;
 background: #f9fafb;
 width: 33.33%;
 }

 .summary-label {
 font-weight: bold;
 color: #6b7280;
 font-size: 8px;
 text-transform: uppercase;
 }

 .summary-value {
 font-size: 14px;
 font-weight: bold;
 margin-top: 5px;
 }

 table {
 width: 100%;
 border-collapse: collapse;
 margin-top: 10px;
 }

 th {
 background-color: #f3f4f6;
 border: 1px solid #d1d5db;
 padding: 6px 4px;
 text-align: left;
 font-weight: bold;
 color: #4b5563;
 text-transform: uppercase;
 font-size: 8px;
 }

 td {
 border: 1px solid #e5e7eb;
 padding: 6px 4px;
 vertical-align: top;
 }

 .text-right {
 text-align: right;
 }

 .text-center {
 text-align: center;
 }

 .income {
 color: #059669;
 font-weight: bold;
 }

 .expense {
 color: #dc2626;
 font-weight: bold;
 }

 .status {
 font-size: 7px;
 color: #9ca3af;
 text-transform: uppercase;
 }

 .footer {
 position: fixed;
 bottom: 0;
 width: 100%;
 text-align: center;
 font-size: 8px;
 color: #9ca3af;
 border-top: 1px solid #e5e7eb;
 padding-top: 5px;
 }
 </style>
</head>

<body>
 <div class="header">
 <div class="business-info">
 <h1>{{ __('Cash Book') }}</h1>
 <p><strong>{{ $business->name }}</strong><br>
 {{ $business->address }}<br>
 {{ $business->email }} | {{ $business->phone }}</p>
 </div>
 <div class="report-info">
 <p><strong>{{ __('Date Range:') }}</strong> {{ $startDate ?? 'All' }} - {{ $endDate ?? 'All' }}<br>
 <strong>{{ __('Exported on:') }}</strong> {{ now()->format('d.m.Y H:i') }}
 </p>
 </div>
 <div class="clear"></div>
 </div>

 <table class="summary-boxes">
 <tr>
 <td class="summary-box">
 <div class="summary-label">{{ __('Total Income') }}</div>
 <div class="summary-value income">{{ number_format($incomeTotal, 2, ',', '.') }}
 {{ $business->currency }}</div>
 </td>
 <td class="summary-box">
 <div class="summary-label">{{ __('Total Expense') }}</div>
 <div class="summary-value expense">{{ number_format($expenseTotal, 2, ',', '.') }}
 {{ $business->currency }}</div>
 </td>
 <td class="summary-box" style="background: #eff6ff;">
 <div class="summary-label">{{ __('Net Balance') }}</div>
 <div class="summary-value" style="color: #2563eb;">
 {{ number_format($incomeTotal - $expenseTotal, 2, ',', '.') }} {{ $business->currency }}</div>
 </td>
 </tr>
 </table>

 <table>
 <thead>
 <tr>
 <th width="10%">{{ __('Booking #') }}</th>
 <th width="10%">{{ __('Date') }}</th>
 <th width="10%">{{ __('Doc Date') }}</th>
 <th width="10%">{{ __('Reference') }}</th>
 <th width="20%">{{ __('Partner') }}</th>
 <th width="15%">{{ __('Category') }}</th>
 <th width="10%" class="text-right">{{ __('Amount') }}</th>
 <th width="15%">{{ __('Rule/Note') }}</th>
 </tr>
 </thead>
 <tbody>
 @foreach($entries as $entry)
 <tr>
 <td>{{ $entry->booking_number }}</td>
 <td>{{ $entry->date->format('d.m.Y') }}</td>
 <td>{{ $entry->document_date ? $entry->document_date->format('d.m.Y') : ($entry->invoice ? $entry->invoice->invoice_date->format('d.m.Y') : $entry->date->format('d.m.Y')) }}
 </td>
 <td>{{ $entry->reference_number ?? '-' }}</td>
 <td>{{ $entry->partner_name ?? ($entry->invoice && $entry->invoice->client ? ($entry->invoice->client->company_name ?? $entry->invoice->client->name) : $entry->description) }}
 </td>
 <td>{{ $entry->category->name ?? ($entry->type === 'income' ? __('Income') : __('General')) }}</td>
 <td class="text-right {{ $entry->type === 'income' ? 'income' : 'expense' }}">
 {{ $entry->type === 'expense' ? '-' : '' }}{{ number_format($entry->amount, 2, ',', '.') }}
 </td>
 <td style="font-size: 8px; color: #6b7280;">
 {{ $entry->category->posting_rule ?? $entry->description }}
 </td>
 </tr>
 @endforeach
 </tbody>
 </table>

 <div class="footer">
 {{ config('app.name') }} - {{ __('Financial Report') }} - {{ __('Page') }} <span class="pagenum"></span>
 </div>
</body>

</html>