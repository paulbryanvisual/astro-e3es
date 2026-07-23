<?php
define('WP_USE_THEMES', false);
require_once('/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php');

$postId = 12;
$post = get_post($postId);

if ($post) {
    $content = $post->post_content;
    
    // Replace the specific u0026amp; strings with correct \u0026 JSON attributes
    $content = str_replace('badge=0u0026amp;autopause=0u0026amp;player_id=0u0026amp;app_id=58479', 'badge=0\u0026autopause=0\u0026player_id=0\u0026app_id=58479', $content);
    $content = str_replace('HVAC u0026amp; Controls Upgrades', 'HVAC \u0026 Controls Upgrades', $content);
    $content = str_replace('Districtwide HVAC u0026amp; Controls Upgrades', 'Districtwide HVAC \u0026 Controls Upgrades', $content);
    $content = str_replace('LED Sports Lighting u0026amp; Auxiliary Upgrades', 'LED Sports Lighting \u0026 Auxiliary Upgrades', $content);
    
    // Make sure we also update the actual rendered iframe inside the video embed block
    // In the user's recovered code block, the video iframe src is:
    // https://player.vimeo.com/video/1179578579?badge=0u0026amp;autopause=0u0026amp;player_id=0u0026amp;app_id=58479
    // Wait, let's replace u0026amp; in any iframe src as well with standard & or &amp; for HTML:
    $content = str_replace('badge=0u0026amp;autopause=0u0026amp;player_id=0u0026amp;app_id=58479', 'badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479', $content);
    
    // Let's run wp_update_post with wp_slash() to preserve backslashes in database!
    $res = wp_update_post([
        'ID' => $postId,
        'post_content' => wp_slash($content)
    ], true);
    
    if (is_wp_error($res)) {
        echo "Error: " . $res->get_error_message() . "\n";
    } else {
        echo "Success: Recovered Boyd ISD post content (ID 12).\n";
    }
} else {
    echo "Post not found.\n";
}

wp_cache_flush();
