<?php
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

// Simulate the frontend request
$_POST['url'] = 'https://t.me/aniwavebd/3';

// Bootstrap Laravel
require_once 'bootstrap/app.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);

// Create a request
$request = Request::create('/admin/telegram/preview', 'POST', [
    'url' => 'https://t.me/aniwavebd/3'
]);

// Set CSRF token (normally comes from middleware)
$request->headers->set('X-CSRF-TOKEN', 'test');

// Handle the request
$response = $kernel->handle($request);

// Get the content
$content = $response->getContent();
$statusCode = $response->getStatusCode();

echo "Status Code: $statusCode\n";
echo "Content-Type: " . $response->headers->get('Content-Type') . "\n";
echo "Content:\n";
echo $content;
?>