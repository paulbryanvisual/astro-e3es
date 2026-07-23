<?php
/**
 * Restore Boyd ISD Video Iframe
 * Restores the Vimeo iframe inside the video embed block of Boyd ISD with kses filters disabled.
 */

$wp_load = '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';
if (!file_exists($wp_load)) {
    die("Cannot find wp-load.php at: $wp_load\n");
}
require_once $wp_load;

wp_set_current_user(1);
if (function_exists('kses_remove_filters')) {
    kses_remove_filters();
}

echo "🔧 Restoring Boyd ISD video iframe...\n";

$p = get_page_by_path('boyd-isd', OBJECT, 'clients');
if (!$p) {
    die("Cannot find Boyd ISD client post.\n");
}

$content = $p->post_content;

// Parse the content blocks
$blocks = parse_blocks($content);

$updated = false;
foreach ($blocks as &$b) {
    if ($b['blockName'] === 'e3es/video-embed') {
        $video_id = "1179578579";
        $iframe_html = '<iframe src="https://player.vimeo.com/video/' . $video_id . '?badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen title="Boyd ISD Case Study Video"></iframe>';
        
        $b['innerHTML'] = '<section class="wp-block-e3es-video-embed db-video-section"><h3 class="db-video-section__title">Boyd ISD Case Study Video</h3><p class="db-video-section__intro">This video highlights the energy efficiency improvements and facility upgrades implemented across the district. Watch the case study to see the impact of single-source accountability.</p><div class="db-video-wrapper">' . $iframe_html . '</div></section>';
        $b['innerContent'] = [ $b['innerHTML'] ];
        $updated = true;
        break;
    }
}

if ($updated) {
    $new_content = serialize_blocks($blocks);
    
    $res = wp_update_post([
        'ID' => $p->ID,
        'post_content' => wp_slash($new_content)
    ]);
    
    if (is_wp_error($res)) {
        echo "  [ERROR] Update failed: " . $res->get_error_message() . "\n";
    } else {
        echo "  Successfully restored video iframe!\n";
    }
} else {
    echo "  [ERROR] Could not find e3es/video-embed block in content.\n";
}
