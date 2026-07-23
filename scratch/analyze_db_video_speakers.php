<?php
require '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';

$quotes = get_posts([
    'post_type' => 'quotes',
    'posts_per_page' => -1,
    'post_status' => 'any'
]);

$video_speakers = [];

foreach ($quotes as $q) {
    $video = get_post_meta($q->ID, '_e3_quote_video_title', true);
    if (empty($video)) {
        $video = 'No Video Title';
    }
    
    $person_id = get_post_meta($q->ID, '_e3_quote_person_id', true);
    $person_name = 'MISSING/NOT FOUND';
    $post_type = 'unknown';
    
    if ($person_id) {
        $person_post = get_post($person_id);
        if ($person_post) {
            $person_name = $person_post->post_title;
            $post_type = $person_post->post_type;
        }
    }
    
    if (!isset($video_speakers[$video])) {
        $video_speakers[$video] = [];
    }
    
    $key = $person_name . ' (' . $post_type . ')';
    if (!isset($video_speakers[$video][$key])) {
        $video_speakers[$video][$key] = 0;
    }
    $video_speakers[$video][$key]++;
}

echo "Videos and their database speaker attributions:\n";
foreach ($video_speakers as $video => $speakers) {
    echo "\n🎥 Video: {$video}\n";
    foreach ($speakers as $speaker => $count) {
        echo "  - {$speaker}: {$count} quotes\n";
    }
}
