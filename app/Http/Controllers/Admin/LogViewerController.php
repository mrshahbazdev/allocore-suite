<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LogViewerController extends Controller
{
    public function index(Request $request)
    {
        $logPath = storage_path('logs');
        $files = File::files($logPath);

        $logs = collect($files)
            ->filter(fn ($file) => str_ends_with($file->getFilename(), '.log'))
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->map(fn ($file) => [
                'name' => $file->getFilename(),
                'size' => $file->getSize(),
                'modified' => $file->getMTime(),
            ])
            ->values();

        $current = $request->get('file', $logs->first()['name'] ?? null);
        $content = '';

        if ($current) {
            $filePath = $logPath.'/'.$current;
            if (File::exists($filePath) && str_starts_with(realpath($filePath), realpath($logPath))) {
                $lines = File::lines($filePath);
                $total = $lines->count();
                $content = $lines->skip(max(0, $total - 200))->implode("\n");
            }
        }

        return view('admin.log-viewer.index', compact('logs', 'current', 'content'));
    }
}
