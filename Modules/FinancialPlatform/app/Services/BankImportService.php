<?php

namespace Modules\FinancialPlatform\Services;

use Illuminate\Http\UploadedFile;
use Modules\FinancialPlatform\Models\BankTransaction;

class BankImportService
{
    public function import(UploadedFile $file, int $teamId, int $userId): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        return match ($extension) {
            'csv' => $this->importCsv($file, $teamId, $userId),
            'mt940', 'txt' => $this->importMt940($file, $teamId, $userId),
            default => ['imported' => 0, 'message' => 'Unsupported file type. Use CSV or MT940.'],
        };
    }

    private function importCsv(UploadedFile $file, int $teamId, int $userId): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        if ($handle === false) {
            return ['imported' => 0, 'message' => 'Could not read file.'];
        }

        $headers = fgetcsv($handle, 0, ';');
        if ($headers === false) {
            return ['imported' => 0, 'message' => 'Empty CSV.'];
        }

        $headers = array_map('strtolower', $headers);
        $map = [
            'date' => ['date', 'transaction_date', 'buchungstag', 'datum'],
            'description' => ['description', 'text', 'verwendungszweck', 'zweck'],
            'amount' => ['amount', 'betrag', 'amount_value'],
            'currency' => ['currency', 'waehrung'],
            'type' => ['type', 'art'],
            'category' => ['category', 'kategorie'],
        ];

        $columns = [];
        foreach ($map as $field => $aliases) {
            foreach ($aliases as $alias) {
                $key = array_search($alias, $headers, true);
                if ($key !== false) {
                    $columns[$field] = $key;
                    break;
                }
            }
        }

        if (! isset($columns['date'], $columns['amount'])) {
            fclose($handle);

            return ['imported' => 0, 'message' => 'CSV must contain date and amount columns.'];
        }

        $imported = 0;
        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            $date = $this->parseDate($row[$columns['date']] ?? '');
            $amount = $this->parseAmount($row[$columns['amount']] ?? '');

            if (! $date || $amount === null) {
                continue;
            }

            $type = $this->normalizeType($row[$columns['type']] ?? null, $amount);
            $currency = strtoupper($row[$columns['currency']] ?? 'EUR');

            BankTransaction::create([
                'team_id' => $teamId,
                'user_id' => $userId,
                'transaction_date' => $date,
                'description' => $row[$columns['description']] ?? null,
                'amount' => abs($amount),
                'currency' => $currency,
                'type' => $type,
                'category' => $row[$columns['category']] ?? null,
                'import_source' => 'csv',
            ]);

            $imported++;
        }

        fclose($handle);

        return ['imported' => $imported, 'message' => "Imported {$imported} transactions."];
    }

    private function importMt940(UploadedFile $file, int $teamId, int $userId): array
    {
        $content = (string) file_get_contents($file->getRealPath());
        $lines = explode("\n", $content);

        $imported = 0;
        $currency = 'EUR';
        $currentDate = null;
        $currentAmount = null;
        $currentDescription = null;
        $currentType = null;

        foreach ($lines as $line) {
            $line = trim($line);

            if (str_starts_with($line, ':25:')) {
                $parts = explode('/', substr($line, 4), 2);
                if (isset($parts[1]) && strlen($parts[1]) === 3) {
                    $currency = $parts[1];
                }
            }

            if (preg_match('/^:61:(\d{6})(\d{4})?(C|D)([A-Z]?)(\d+,?\d*)/', $line, $matches)) {
                $currentDate = $this->parseMt940Date($matches[1]);
                $rawAmount = str_replace(',', '.', $matches[5]);
                $currentAmount = (float) $rawAmount;
                $currentType = $matches[3] === 'C' ? 'income' : 'expense';

                continue;
            }

            if (str_starts_with($line, ':86:')) {
                $currentDescription = trim(substr($line, 4));

                if ($currentDate && $currentAmount !== null) {
                    BankTransaction::create([
                        'team_id' => $teamId,
                        'user_id' => $userId,
                        'transaction_date' => $currentDate,
                        'description' => $currentDescription,
                        'amount' => abs($currentAmount),
                        'currency' => $currency,
                        'type' => $currentType ?? 'expense',
                        'category' => null,
                        'import_source' => 'mt940',
                    ]);

                    $imported++;
                }

                $currentDate = null;
                $currentAmount = null;
                $currentDescription = null;
            }
        }

        return ['imported' => $imported, 'message' => "Imported {$imported} MT940 transactions."];
    }

    private function parseDate(string $value): ?string
    {
        $timestamp = strtotime($value);

        return $timestamp !== false ? date('Y-m-d', $timestamp) : null;
    }

    private function parseAmount(string $value): ?float
    {
        $value = str_replace(['.', ','], ['', '.'], $value);
        $value = preg_replace('/[^\d.\-]/', '', $value);

        if ($value === '' || $value === null) {
            return null;
        }

        return is_numeric($value) ? (float) $value : null;
    }

    private function normalizeType(?string $value, float $amount): string
    {
        if ($value === null) {
            return $amount >= 0 ? 'income' : 'expense';
        }

        $value = strtolower($value);

        if (in_array($value, ['income', 'einnahme', 'eingang', 'credit', 'gutschrift'], true)) {
            return 'income';
        }

        if (in_array($value, ['expense', 'ausgabe', 'ausgang', 'debit', 'lastschrift'], true)) {
            return 'expense';
        }

        return $amount >= 0 ? 'income' : 'expense';
    }

    private function parseMt940Date(string $value): ?string
    {
        $year = '20'.substr($value, 0, 2);
        $month = substr($value, 2, 2);
        $day = substr($value, 4, 2);

        if (! checkdate((int) $month, (int) $day, (int) $year)) {
            return null;
        }

        return "{$year}-{$month}-{$day}";
    }
}
