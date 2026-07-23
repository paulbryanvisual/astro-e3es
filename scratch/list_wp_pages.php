<?php
require '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';

$pages = get_posts([
    'post_type' => 'page',
    'posts_per_page' => -1,
    'post_status' => 'any'
]);

echo "Pages in WordPress:\n";
foreach ($pages as $p) {
    echo "- '{$p->post_title}' (Slug: {$p->post_name}, ID: {$p->ID})\n";
}
