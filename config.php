<?php
session_start();

// Base URL - must match XAMPP subdirectory
define('BASE_URL', 'http://localhost/anime');
define('BASE_PATH', __DIR__);

// Database configuration (XAMPP defaults)
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'anikoto');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site configuration
define('SITE_NAME', 'Anikoto');
define('SITE_DESC', 'Watch Anime Online, Free Anime Streaming');
define('ITEMS_PER_PAGE', 30);
define('ITEMS_PER_ROW', 6);

define('UPLOAD_PATH', BASE_PATH . '/admin/uploads/videos');
define('UPLOAD_URL', BASE_URL . '/admin/uploads/videos');

// Telegram bot
define('TELEGRAM_BOT_TOKEN', '8850443718:AAGZ1M51kDcN76qa9_0Qre2ltRivRa0LDHk');
define('TELEGRAM_BOT_USERNAME', 'aniwavebd_bot');

date_default_timezone_set('UTC');
