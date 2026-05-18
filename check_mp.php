<?php
require __DIR__ . '/vendor/autoload.php';

echo "Autoload loaded\n";
echo "MadelineProto API exists: " . (class_exists('danog\MadelineProto\API') ? 'YES' : 'NO') . "\n";
echo "MadelineProto Tools exists: " . (class_exists('danog\MadelineProto\Tools') ? 'YES' : 'NO') . "\n";

// Check composer.json
$comp = json_decode(file_get_contents(__DIR__ . '/composer.json'), true);
echo "MadelineProto in require: " . (isset($comp['require']['danog/madelineproto']) ? 'YES' : 'NO') . "\n";

// Check the installed packages
$installed = json_decode(file_get_contents(__DIR__ . '/vendor/composer/installed.json'), true);
foreach ($installed['packages'] ?? $installed ?? [] as $pkg) {
    if (str_contains($pkg['name'] ?? '', 'madeline')) {
        echo "Found: " . $pkg['name'] . " v" . ($pkg['version'] ?? '?') . "\n";
    }
    if (str_contains($pkg['name'] ?? '', 'tg-file')) {
        echo "Found: " . $pkg['name'] . " v" . ($pkg['version'] ?? '?') . "\n";
    }
}
