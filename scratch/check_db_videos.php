<?php
require_once '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';

$posts = get_posts(array(
    'post_type' => 'clients',
    'posts_per_page' => -1,
    's' => 'video-embed'
));

foreach ($posts as $post) {
    if (strpos($post->post_content, 'video-embed') !== false) {
        echo "=== Post: " . $post->post_title . " (ID: " . $post->ID . ") ===\n";
        // Extract the block content
        if (preg_match('/<!-- wp:e3es\/video-embed.*?-->[\s\S]*?<!-- \/wp:e3es\/video-embed -->/', $post->post_content, $matches)) {
            echo $matches[0] . "\n\n";
        } else {
            echo "Block comments not found, raw content:\n" . substr($post->post_content, 0, 500) . "...\n\n";
        }
    }
}
