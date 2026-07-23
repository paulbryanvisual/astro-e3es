<?php
require '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';

global $wpdb;
$rows = $wpdb->get_results("SELECT * FROM wp_admin_columns");

echo "=== wp_admin_columns ROWS ===\n";
foreach ($rows as $row) {
    echo "ID: {$row->id} | List: {$row->list_id} | Type: {$row->list_type} | Storage: {$row->storage}\n";
    // Check if the data column (usually named 'data' or similar) has serialized values or URLs
    $data_fields = array();
    foreach ($row as $key => $val) {
        if ($key !== 'id' && $key !== 'list_id' && $key !== 'list_type' && $key !== 'storage') {
            $data_fields[] = "{$key}: " . substr(strval($val), 0, 100) . "...";
        }
    }
    echo "Fields: " . implode(' | ', $data_fields) . "\n\n";
}
