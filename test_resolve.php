<?php
require 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Http\Kernel');

// Make the HTTP facade available
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\TelegramService;

// Create service instance
$service = new TelegramService();

// Test resolving the Telegram URL
try {
    $result = $service->resolveMessage('https://t.me/aniwavebd/3');
    echo "resolveMessage('https://t.me/aniwavebd/3') result:\n";
    var_dump($result);
    
    if ($result) {
        echo "\nFile ID: " . ($result['file_id'] ?? 'null') . "\n";
        echo "Direct URL: " . ($result['direct_url'] ?? 'null') . "\n";
        echo "Needs streaming: " . ($result['needs_streaming'] ?? false ? 'yes' : 'no') . "\n";
        echo "File size: " . ($result['file_size'] ?? 0) . " bytes\n";
        if (!empty($result['direct_url'])) {
            echo "Direct URL: " . $result['direct_url'] . "\n";
        }
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}