<?php

// Prevent accidental web execution (shared-hosting safety).
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo "Forbidden\n";
    exit(1);
}

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$status = Illuminate\Support\Facades\Artisan::call('queue:work', [
    '--stop-when-empty' => true,
    '--tries' => 3,
    '--timeout' => 1200,
    '--sleep' => 3,
]);

$output = Illuminate\Support\Facades\Artisan::output();
echo $output;
exit($status);
