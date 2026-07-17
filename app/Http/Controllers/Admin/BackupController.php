<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Backup;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\InvoiceMaker\Models\Invoice;
use Modules\InvoiceMaker\Models\Payment;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    public function index(Request $request)
    {
        $backups = Backup::latest()->paginate(20)->withQueryString();

        return view('admin.backups.index', compact('backups'));
    }

    public function exportUsers()
    {
        return $this->streamCsv('users_'.now()->format('Ymd_His').'.csv', User::all(), [
            'id', 'name', 'email', 'current_team_id', 'email_verified_at', 'created_at',
        ]);
    }

    public function exportTeams()
    {
        return $this->streamCsv('teams_'.now()->format('Ymd_His').'.csv', Team::all(), [
            'id', 'name', 'industry', 'size', 'created_at',
        ]);
    }

    public function exportInvoices()
    {
        $invoices = Invoice::withoutGlobalScope('current_team')->with('client')->get();

        $headers = ['id', 'team_id', 'invoice_number', 'client_name', 'status', 'grand_total', 'amount_paid', 'currency', 'invoice_date', 'due_date', 'created_at'];

        $response = new StreamedResponse(function () use ($invoices, $headers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            foreach ($invoices as $invoice) {
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

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="invoices_'.now()->format('Ymd_His').'.csv"');

        return $response;
    }

    public function exportPayments()
    {
        $payments = Payment::withoutGlobalScope('current_team')->with('invoice')->get();

        $headers = ['id', 'team_id', 'invoice_number', 'amount', 'method', 'date', 'reference', 'created_at'];

        $response = new StreamedResponse(function () use ($payments, $headers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            foreach ($payments as $payment) {
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

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="payments_'.now()->format('Ymd_His').'.csv"');

        return $response;
    }

    public function createSqlDump(Request $request)
    {
        $disk = $request->input('disk', 'local');
        if (! in_array($disk, ['local', 's3'], true)) {
            $disk = 'local';
        }

        $fileName = 'backup_'.now()->format('Ymd_His').'.sql';
        $path = 'backups/'.$fileName;
        $tempPath = storage_path('app/private/'.$path);

        if (! is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s %s > %s 2>/dev/null || sqlite3 %s .dump > %s',
            escapeshellarg(config('database.connections.mysql.host', 'localhost')),
            escapeshellarg(config('database.connections.mysql.port', '3306')),
            escapeshellarg(config('database.connections.mysql.username', 'root')),
            escapeshellarg(config('database.connections.mysql.password', '')),
            escapeshellarg(config('database.connections.mysql.database', '')),
            escapeshellarg($tempPath),
            escapeshellarg(config('database.connections.sqlite.database')),
            escapeshellarg($tempPath)
        );

        exec($command);

        $contents = file_get_contents($tempPath);
        $size = $contents === false ? 0 : strlen($contents);

        Storage::disk($disk)->put($path, $contents);

        if (file_exists($tempPath)) {
            unlink($tempPath);
        }

        $backup = Backup::create([
            'name' => $fileName,
            'path' => $path,
            'disk' => $disk,
            'type' => 'database',
            'size' => $size,
            'completed_at' => now(),
        ]);

        return redirect()->route('admin.backups.index')->with('success', __('admin.backups.created', ['disk' => $disk]));
    }

    public function download(Backup $backup)
    {
        $disk = $backup->disk ?? 'local';
        abort_unless(Storage::disk($disk)->exists($backup->path), 404);

        return Storage::disk($disk)->download($backup->path, $backup->name);
    }

    public function destroy(Backup $backup)
    {
        $backup->deleteFile();
        $backup->delete();

        return redirect()->route('admin.backups.index')->with('success', __('admin.backups.deleted'));
    }

    private function streamCsv(string $fileName, $rows, array $columns)
    {
        $response = new StreamedResponse(function () use ($rows, $columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);

            foreach ($rows as $row) {
                fputcsv($handle, $row->only($columns));
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$fileName.'"');

        return $response;
    }
}
