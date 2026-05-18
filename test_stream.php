<?php
require 'vendor/autoload.php';
$service = new App\Services\TelegramStreamService();
$result = $service->getMessageInfo(3);
echo "Result: ";
var_dump($result);
if ($result) {
    echo "File size: " . ($result['file_size'] ?? 'null') . " bytes\n";
    echo "Needs streaming: " . (($result['file_size'] ?? 0) > 20 * 1024 * 1024 ? 'yes' : 'no') . "\n";
}
?>