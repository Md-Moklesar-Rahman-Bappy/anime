<?php
require __DIR__ . '/config.php';
require __DIR__ . '/db.php';

$tables = [
    "CREATE TABLE IF NOT EXISTS `contacts` (
        `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL,
        `email` VARCHAR(255) NOT NULL,
        `message` TEXT NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
];

foreach ($tables as $sql) {
    try {
        DB::query($sql);
        echo "  [OK] Table created\n";
    } catch (Exception $e) {
        echo "  [ERR] " . $e->getMessage() . "\n";
    }
}
echo "Done.\n";
