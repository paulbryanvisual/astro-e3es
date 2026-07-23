<?php
define('WP_USE_THEMES', false);
require_once('/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php');

// Bootstrap admin capabilities to bypass KSES filtering
wp_set_current_user( 1 );
if ( function_exists( 'kses_remove_filters' ) ) {
    kses_remove_filters();
}

$postId = 12;
$post = get_post($postId);

if ($post) {
    $content = $post->post_content;

    $counts = [];

    // 1. Recover video-embed block comments and HTML
    $video_comment_pattern = '/<!-- wp:e3es\/video-embed.*?-->/s';
    $video_comment_replace = '<!-- wp:e3es/video-embed {"title":"Boyd ISD Case Study Video","videoUrl":"https://player.vimeo.com/video/1179578579?badge=0\u0026autopause=0\u0026player_id=0\u0026app_id=58479"} -->';
    $content = preg_replace($video_comment_pattern, $video_comment_replace, $content, -1, $c1);
    $counts['video_comment'] = $c1;

    $video_html_pattern = '/<section class="wp-block-e3es-video-embed db-video-section">.*?<\/section>/s';
    $video_html_replace = '<section class="wp-block-e3es-video-embed db-video-section"><h3 class="db-video-section__title">Boyd ISD Case Study Video</h3><p class="db-video-section__intro">This video highlights the energy efficiency improvements and facility upgrades implemented across the district. Watch the case study to see the impact of single-source accountability.</p><div class="db-video-wrapper"><iframe src="https://player.vimeo.com/video/1179578579?badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen title="Boyd ISD Case Study Video"></iframe></div></section>';
    $content = preg_replace($video_html_pattern, $video_html_replace, $content, -1, $c2);
    $counts['video_html'] = $c2;

    // 2. Recover project-toc block comments and HTML
    $toc_comment_pattern = '/<!-- wp:e3es\/project-toc.*?-->/s';
    $toc_comment_replace = '<!-- wp:e3es/project-toc {"link1Label":"HVAC \u0026 Controls Upgrades","link1Href":"#project-hvac","link2Label":"LED Sports Lighting","link2Href":"#project-lighting"} -->';
    $content = preg_replace($toc_comment_pattern, $toc_comment_replace, $content, -1, $c3);
    $counts['toc_comment'] = $c3;

    $toc_html_pattern = '/<nav class="wp-block-e3es-project-toc db-toc".*?<\/nav>/s';
    $toc_html_replace = '<nav class="wp-block-e3es-project-toc db-toc" aria-label="Table of Contents"><span class="db-toc__label">Jump to project:</span><a href="#project-hvac" class="db-toc__link">HVAC &amp; Controls Upgrades</a><span class="db-toc__divider">|</span><a href="#project-lighting" class="db-toc__link">LED Sports Lighting</a></nav>';
    $content = preg_replace($toc_html_pattern, $toc_html_replace, $content, -1, $c4);
    $counts['toc_html'] = $c4;

    // 3. Recover project 1 block comments and HTML
    $project1_comment_pattern = '/<!-- wp:e3es\/project \{"sectionId":"project-hvac".*?-->/s';
    $project1_comment_replace = '<!-- wp:e3es/project {"sectionId":"project-hvac","title":"Districtwide HVAC \u0026 Controls Upgrades","heroImageUrl":"http://e3es2026.local/wp-content/uploads/2026/06/hvac.png"} -->';
    $content = preg_replace($project1_comment_pattern, $project1_comment_replace, $content, -1, $c5);
    $counts['project1_comment'] = $c5;

    $project1_html_pattern = '/<div class="wp-block-e3es-project project-section" id="project-hvac".*?<div class="project-section__content">/s';
    $project1_html_replace = '<div class="wp-block-e3es-project project-section" id="project-hvac" style="--hero-img:url(http://e3es2026.local/wp-content/uploads/2026/06/hvac.png)"><div class="project-section__header"><div class="project-section__hero"><img src="http://e3es2026.local/wp-content/uploads/2026/06/hvac.png" alt="Districtwide HVAC &amp; Controls Upgrades" class="project-section__hero-img" style="object-position:50% 50%"/><div class="project-section__mask project-section__mask--left"></div><div class="project-section__mask project-section__mask--right"></div></div><div class="project-section__info"><span class="project-section__eyebrow">Project 1</span><h2 class="project-section__title">Districtwide HVAC &amp; Controls Upgrades</h2></div></div><div class="project-section__content">';
    $content = preg_replace($project1_html_pattern, $project1_html_replace, $content, -1, $c6);
    $counts['project1_html'] = $c6;

    // 4. Recover project 2 block comments and HTML
    $project2_comment_pattern = '/<!-- wp:e3es\/project \{"sectionId":"project-lighting".*?-->/s';
    $project2_comment_replace = '<!-- wp:e3es/project {"sectionId":"project-lighting","eyebrow":"Project 2","title":"LED Sports Lighting \u0026 Controls Upgrades","heroImageUrl":"http://e3es2026.local/wp-content/uploads/2026/06/led.jpg"} -->';
    $content = preg_replace($project2_comment_pattern, $project2_comment_replace, $content, -1, $c7);
    $counts['project2_comment'] = $c7;

    $project2_html_pattern = '/<div class="wp-block-e3es-project project-section" id="project-lighting".*?<div class="project-section__content">/s';
    $project2_html_replace = '<div class="wp-block-e3es-project project-section" id="project-lighting" style="--hero-img:url(http://e3es2026.local/wp-content/uploads/2026/06/led.jpg)"><div class="project-section__header"><div class="project-section__hero"><img src="http://e3es2026.local/wp-content/uploads/2026/06/led.jpg" alt="LED Sports Lighting &amp; Controls Upgrades" class="project-section__hero-img" style="object-position:50% 50%"/><div class="project-section__mask project-section__mask--left"></div><div class="project-section__mask project-section__mask--right"></div></div><div class="project-section__info"><span class="project-section__eyebrow">Project 2</span><h2 class="project-section__title">LED Sports Lighting &amp; Controls Upgrades</h2></div></div><div class="project-section__content">';
    $content = preg_replace($project2_html_pattern, $project2_html_replace, $content, -1, $c8);
    $counts['project2_html'] = $c8;

    foreach ($counts as $key => $count) {
        echo "Pattern [$key] replaced $count occurrences.\n";
    }

    // Save back to WordPress
    $res = wp_update_post([
        'ID' => $postId,
        'post_content' => wp_slash($content)
    ], true);

    if (is_wp_error($res)) {
        echo "Error: " . $res->get_error_message() . "\n";
    } else {
        echo "Success: Cleaned and recovered Boyd ISD (ID 12) to match native Gutenberg schemas.\n";
    }
} else {
    echo "Post not found.\n";
}
wp_cache_flush();
