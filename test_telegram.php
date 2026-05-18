<?php
require 'vendor/autoload.php';
$service = new App\Services\TelegramService();
$result = $service->resolveMessage('https://t.me/aniwavebd/3');
var_dump($result);