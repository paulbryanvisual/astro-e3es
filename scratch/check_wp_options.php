<?php
require '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';

global $wpdb;
$options = $wpdb->get_results("SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE 'ac_%' OR option_name LIKE 'cpac_%' OR option_name LIKE '%admin_column%'");

echo "=== ADMIN COLUMNS OPTIONS IN DATABASE ===\n";
foreach ($options as $opt) {
    echo "- {$opt->option_name}\n";
}
