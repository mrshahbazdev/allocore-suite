<?php

namespace Modules\LeadQuality\Services;

use Illuminate\Support\Facades\Log;
use Modules\LeadQuality\Models\Contact;

class CsvImportService
{
    public function import(string $filePath, int $userId, int $teamId): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        if (! file_exists($filePath) || ! is_readable($filePath)) {
            $results['errors'][] = __("File not found or not readable: {$filePath}");

            return $results;
        }

        $header = null;

        if (($handle = fopen($filePath, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                if (! $header) {
                    $header = array_map('strtolower', $row);

                    if (! in_array('name', $header, true)) {
                        $results['errors'][] = __("Missing 'name' column in CSV header.");
                        fclose($handle);

                        return $results;
                    }

                    continue;
                }

                if (count($header) !== count($row)) {
                    $results['failed']++;
                    $results['errors'][] = __('Skipped row due to column count mismatch.');

                    continue;
                }

                $data = array_combine($header, $row);

                try {
                    Contact::create([
                        'user_id' => $userId,
                        'team_id' => $teamId,
                        'name' => $data['name'] ?? __('Unknown'),
                        'company' => $data['company'] ?? null,
                        'position' => $data['position'] ?? null,
                        'industry' => $data['industry'] ?? null,
                        'role' => $data['role'] ?? null,
                        'source' => $data['source'] ?? __('CSV Import'),
                        'budget' => isset($data['budget']) && is_numeric($data['budget']) ? (float) $data['budget'] : null,
                        'status' => 'new',
                        'notes' => $data['notes'] ?? null,
                        'tags' => isset($data['tags']) ? array_map('trim', explode(',', $data['tags'])) : [],
                    ]);

                    $results['success']++;
                } catch (\Throwable $e) {
                    $results['failed']++;
                    $results['errors'][] = __('Row error: :message', ['message' => $e->getMessage()]);
                    Log::error('LeadQuality CSV Import Error: '.$e->getMessage());
                }
            }

            fclose($handle);
        }

        return $results;
    }
}
