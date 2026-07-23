<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class EnvController extends Controller
{
    public function index()
    {
        $path = base_path('.env');

        abort_unless(is_readable($path), 403, __('The .env file is not readable.'));

        $entries = $this->parseEnv($path);

        return view('admin.env.index', [
            'entries' => $entries,
            'writable' => is_writable($path),
        ]);
    }

    public function update(Request $request)
    {
        $path = base_path('.env');

        abort_unless(is_readable($path) && is_writable($path), 403, __('The .env file is not readable or writable.'));

        $validated = $request->validate([
            'env' => ['required', 'array'],
            'env.*.key' => ['required', 'string', 'regex:/^[A-Za-z_][A-Za-z0-9_]*$/'],
            'env.*.value' => ['nullable', 'string'],
            'new_key' => ['nullable', 'string', 'regex:/^[A-Za-z_][A-Za-z0-9_]*$/'],
            'new_value' => ['nullable', 'string'],
        ]);

        $contents = File::get($path);

        foreach ($validated['env'] as $entry) {
            $contents = $this->setEnvValue($contents, $entry['key'], $entry['value'] ?? '');
        }

        if (! empty($validated['new_key'])) {
            $contents = $this->setEnvValue($contents, $validated['new_key'], $validated['new_value'] ?? '');
        }

        File::put($path, $contents);

        Artisan::call('config:clear');

        return redirect()->route('admin.env.index')->with('success', __('Environment variables updated and config cache cleared.'));
    }

    protected function parseEnv(string $path): array
    {
        $entries = [];

        foreach (file($path) as $line) {
            $line = rtrim($line, "\n\r");
            $trimmed = ltrim($line);

            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                continue;
            }

            if (preg_match('/^([A-Za-z_][A-Za-z0-9_]*)=(.*)$/', $line, $matches)) {
                $entries[] = [
                    'key' => $matches[1],
                    'value' => $this->unquoteValue($matches[2]),
                ];
            }
        }

        return $entries;
    }

    protected function setEnvValue(string $contents, string $key, string $value): string
    {
        $pattern = '/^'.preg_quote($key, '/').'=.*/m';
        $quotedValue = $this->quoteValue($value);
        $line = $key.'='.$quotedValue;

        if (preg_match($pattern, $contents)) {
            return preg_replace($pattern, $line, $contents, 1);
        }

        return $contents."\n".$line;
    }

    protected function quoteValue(string $value): string
    {
        if ($value === '') {
            return '';
        }

        if (preg_match('/^[A-Za-z0-9_\.\-\/:@=+]+$/', $value)) {
            return $value;
        }

        return '"'.addcslashes($value, '"\\').'"';
    }

    protected function unquoteValue(string $raw): string
    {
        $raw = trim($raw);

        if (preg_match('/^"(.*)"$/s', $raw, $matches)) {
            return str_replace(['\\"', '\\\\'], ['"', '\\'], $matches[1]);
        }

        if (preg_match("/^'(.*)'$/s", $raw, $matches)) {
            return str_replace(["\\'", '\\\\'], ["'", '\\'], $matches[1]);
        }

        return $raw;
    }
}
