<?php
define('WP_USE_THEMES', false);
require_once('/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php');

$postId = 1641;
$post = get_post($postId);

if ($post) {
    $content = $post->post_content;
    
    // Pattern to match <!-- wp:buttons --> ... <!-- /wp:buttons -->
    $pattern = '/<!-- wp:buttons -->[\s\S]*?<!-- \/wp:buttons -->/s';
    
    $clean_content = preg_replace($pattern, '', $content);
    
    $res = wp_update_post([
        'ID' => $postId,
        'post_content' => wp_slash($clean_content)
    ], true);
    
    if (is_wp_error($res)) {
        echo "Error: " . $res->get_error_message() . "\n";
    } else {
        echo "Success: Removed all buttons from HVAC System Upgrades page (ID 1641).\n";
    }
} else {
    echo "Post not found.\n";
}

wp_cache_flush();
