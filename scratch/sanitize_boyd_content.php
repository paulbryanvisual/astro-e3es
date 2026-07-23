<?php
define('WP_USE_THEMES', false);
require_once('/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php');

$postId = 12;
$post = get_post($postId);

if ($post) {
    $content = $post->post_content;
    
    // First, let's clean up any double-encoded u0026amp; or similar in JSON comments
    // Replace "u0026amp;" or "\u0026amp;" with correct JSON "\u0026"
    $content = str_replace('u0026amp;', '\u0026', $content);
    $content = str_replace('\u0026amp;', '\u0026', $content);
    
    // Let's check for any remaining raw HTML entities inside Gutenberg tags
    // e.g. <a href="#project-hvac" class="db-toc__link">HVAC u0026amp; Controls Upgrades</a>
    // We should convert "u0026amp;" or "u0026" in raw HTML content to proper &amp;
    $content = preg_replace('/(?<!wp:)(?<!{)u0026amp;/', '&amp;', $content);
    $content = preg_replace('/(?<!wp:)(?<!{)u0026/', '&amp;', $content);
    
    // Specifically fix the vimeo badge parameter
    // in Gutenberg attributes it should be \u0026
    $content = str_replace('badge=0\u0026autopause=0\u0026player_id=0\u0026app_id=58479', 'badge=0\u0026autopause=0\u0026player_id=0\u0026app_id=58479', $content);
    // inside the iframe src attribute in the HTML content, it should be standard &amp;
    $content = str_replace('badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479', 'badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479', $content);
    
    // Let's run wp_update_post with wp_slash() to save backslashes!
    $res = wp_update_post([
        'ID' => $postId,
        'post_content' => wp_slash($content)
    ], true);
    
    if (is_wp_error($res)) {
        echo "Error: " . $res->get_error_message() . "\n";
    } else {
        echo "Success: Sanitized and recovered Boyd ISD (ID 12).\n";
    }
} else {
    echo "Post not found.\n";
}

wp_cache_flush();
