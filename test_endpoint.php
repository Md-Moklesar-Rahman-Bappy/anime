<?php
require 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Http\Kernel');

use Illuminate\Http\Request;
use App\Http\Controllers\Admin\ScraperController;

// Create a mock request
$request = Request::create('/admin/telegram/preview', 'POST', [], [], [], [], json_encode([
    'url' => 'https://t.me/aniwavebd/3'
]));

// Add content type header
$request->headers->set('Content-Type', 'application/json');

// Create controller instance (we need to mock its dependencies)
$scraperManager = $app->make(App\Services\Scrapers\ScraperManager::class);
$youtubeService = $app->make(App\Services\YouTubeService::class);
$telegramService = $app->make(App\Services\TelegramService::class);

$controller = new ScraperController($scraperManager, $youtubeService, $telegramService);

// Call the method
$response = $controller->telegramPreview($request);

// Get the content
$content = $response->getContent();
$statusCode = $response->getStatusCode();

echo "Status Code: $statusCode\n";
echo "Content-Type: " . $response->headers->get('Content-Type') . "\n";
echo "Content:\n";
echo $content;
?>