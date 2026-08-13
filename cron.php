<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;

// Prevent accidental web execution (shared-hosting safety).
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo "Forbidden\n";
    exit(1);
}

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$status = Artisan::call('queue:work', [
    '--stop-when-empty' => true,
    '--tries' => 3,
    '--timeout' => 1200,
    '--sleep' => 3,
]);

$output = Artisan::output();
echo $output;
exit($status);
