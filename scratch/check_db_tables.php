<?php
require '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';

global $wpdb;
$tables = $wpdb->get_col("SHOW TABLES");

echo "=== DATABASE TABLES ===\n";
foreach ($tables as $table) {
    echo "- {$table}\n";
}
