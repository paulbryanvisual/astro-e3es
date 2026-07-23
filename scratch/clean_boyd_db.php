<?php
define('WP_USE_THEMES', false);
require_once('/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php');

$postId = 12;
$post = get_post($postId);

if ($post) {
    $content = $post->post_content;
    
    // We will do clean replacements of the exact lines in the HTML to avoid double-escaping bugs!
    // Line 1: video-embed block comment
    $content = preg_replace(
        '/<!-- wp:e3es\/video-embed.*-->/',
        '<!-- wp:e3es/video-embed {"title":"Boyd ISD Case Study Video","videoUrl":"https://player.vimeo.com/video/1179578579?badge=0\u0026autopause=0\u0026player_id=0\u0026app_id=58479","intro":"This video highlights the energy efficiency improvements and facility upgrades implemented across the district. Watch the case study to see the impact of single-source accountability."} -->',
        $content
    );
    
    // Line 5: project-toc block comment
    $content = preg_replace(
        '/<!-- wp:e3es\/project-toc.*-->/',
        '<!-- wp:e3es/project-toc {"link1Label":"HVAC \u0026 Controls Upgrades","link1Href":"#project-hvac","link2Label":"LED Sports Lighting","link2Href":"#project-lighting","link3Label":"","link3Href":"","link4Label":"","link4Href":""} -->',
        $content
    );
    
    // Line 9: project 1 block comment
    $content = preg_replace(
        '/<!-- wp:e3es\/project {"sectionId":"project-hvac".*?-->/',
        '<!-- wp:e3es/project {"sectionId":"project-hvac","eyebrow":"Project 1","title":"Districtwide HVAC \u0026 Controls Upgrades","heroImageUrl":"http://e3es2026.local/wp-content/uploads/2026/06/hvac.png","focalPointX":0.5,"focalPointY":0.5} -->',
        $content
    );
    
    // Line 96: project 2 block comment
    $content = preg_replace(
        '/<!-- wp:e3es\/project {"sectionId":"project-lighting".*?-->/',
        '<!-- wp:e3es/project {"sectionId":"project-lighting","eyebrow":"Project 2","title":"LED Sports Lighting \u0026 Auxiliary Upgrades","heroImageUrl":"http://e3es2026.local/wp-content/uploads/2026/06/led.jpg","focalPointX":0.5,"focalPointY":0.5} -->',
        $content
    );
    
    // Now replace the HTML body elements
    // video iframe
    $content = str_replace(
        'https://player.vimeo.com/video/1179578579?badge=0u0026amp;autopause=0u0026amp;player_id=0u0026amp;app_id=58479',
        'https://player.vimeo.com/video/1179578579?badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479',
        $content
    );
    $content = str_replace(
        'https://player.vimeo.com/video/1179578579?badge=0\u005c\u0026amp;autopause=0\u005c\u0026amp;player_id=0\u005c\u0026amp;app_id=58479',
        'https://player.vimeo.com/video/1179578579?badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479',
        $content
    );
    
    // project-toc HTML
    $content = str_replace(
        'HVAC u0026amp; Controls Upgrades',
        'HVAC &amp; Controls Upgrades',
        $content
    );
    $content = str_replace(
        'HVAC \u005c\u0026amp; Controls Upgrades',
        'HVAC &amp; Controls Upgrades',
        $content
    );
    
    // project 1 title HTML
    $content = str_replace(
        'Districtwide HVAC u0026amp; Controls Upgrades',
        'Districtwide HVAC &amp; Controls Upgrades',
        $content
    );
    $content = str_replace(
        'Districtwide HVAC \u005c\u0026amp; Controls Upgrades',
        'Districtwide HVAC &amp; Controls Upgrades',
        $content
    );
    
    // project 2 title HTML
    $content = str_replace(
        'LED Sports Lighting u0026amp; Auxiliary Upgrades',
        'LED Sports Lighting &amp; Auxiliary Upgrades',
        $content
    );
    $content = str_replace(
        'LED Sports Lighting \u005c\u0026amp; Auxiliary Upgrades',
        'LED Sports Lighting &amp; Auxiliary Upgrades',
        $content
    );
    
    // Update the database with wp_slash() to preserve JSON backslashes!
    $res = wp_update_post([
        'ID' => $postId,
        'post_content' => wp_slash($content)
    ], true);
    
    if (is_wp_error($res)) {
        echo "Error: " . $res->get_error_message() . "\n";
    } else {
        echo "Success: Cleaned and recovered Boyd ISD (ID 12).\n";
    }
} else {
    echo "Post not found.\n";
}
wp_cache_flush();
