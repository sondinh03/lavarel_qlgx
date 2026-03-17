<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

echo "Testing AttendanceManager Phase 1...\n";

try {
    // Tạo component instance qua container
    $component = app(\App\Http\Livewire\AttendanceManager::class);

    // Set basic properties
    $component->selectedClassId = null; // Empty state
    $component->viewMode = 'desktop';
    $component->parishId = 1; // Mock parish ID

    echo "1. Testing render with empty data...\n";
    $start = microtime(true);
    $view = $component->render();
    $time = microtime(true) - $start;
    echo "   Render time: " . round($time, 3) . "s\n";
    echo "   Render OK!\n";

    echo "\n✅ Phase 1 Render Test PASSED!\n";
    echo "Note: Save validation needs browser test (Alpine integration).\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}