<?php
require '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';
$page = get_page_by_path('clients');
if ($page) {
    echo "=== PAGE FOUND ===\n";
    echo substr($page->post_content, 0, 1000) . "\n...\n";
    echo "=== RENDERED ===\n";
    echo substr(apply_filters('the_content', $page->post_content), 0, 1000) . "\n";
} else {
    echo "Page not found\n";
}
