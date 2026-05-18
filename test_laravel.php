<?php
require 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';

// Make the HTTP facade available
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\TelegramStreamService;

// Create service instance
$service = new TelegramStreamService();

// Test the streamer directly
try {
    $result = $service->getMessageInfo(3);
    echo "getMessageInfo(3) result:\n";
    var_dump($result);
    
    if ($result) {
        echo "\nFile size: " . ($result['file_size'] ?? 0) . " bytes\n";
        echo "Needs streaming (>20MB): " . ((($result['file_size'] ?? 0) > 20 * 1024 * 1024) ? 'yes' : 'no') . "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}