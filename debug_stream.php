<?php
require 'vendor/autoload.php';

class TestTelegramStreamService extends App\Services\TelegramStreamService {
    public function getStreamerScript() {
        return $this->streamerScript();
    }
    
    public function testRunStreamer(array $args) {
        return $this->runStreamer($args);
    }
}

$service = new TestTelegramStreamService();

echo "Testing streamer script path:\n";
$script = $service->getStreamerScript();
echo "Script path: $script\n";
echo "File exists: " . (file_exists($script) ? 'yes' : 'no') . "\n";

if (file_exists($script)) {
    echo "\nTesting runStreamer with ['info', '3']:\n";
    try {
        $output = $service->testRunStreamer(['info', '3']);
        echo "Raw output: " . var_export($output, true) . "\n";
        if ($output !== null) {
            $data = json_decode($output, true);
            echo "Decoded JSON: " . var_export($data, true) . "\n";
        }
    } catch (\Throwable $e) {
        echo "Exception: " . $e->getMessage() . "\n";
    }
}

echo "\nTesting getMessageInfo(3):\n";
$result = $service->getMessageInfo(3);
echo "Result: " . var_export($result, true) . "\n";
?>