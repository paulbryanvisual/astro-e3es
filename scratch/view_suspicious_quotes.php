<?php
require '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';

$videos_to_check = [
    'Lessons in Learning - Dr. Theresa Williams, Superintendent, Plano ISD',
    'Lessons In Learning - Judd Marshall, Superintendent,  Mount Pleasant ISD',
    'Granbury ISD, case study',
    'How to Get the Most Out of Vendor Relationships',
    'E3 in 3D - Steve Schliesing',
    'Goodall-Witcher Healthcare, case study'
];

foreach ($videos_to_check as $video) {
    echo "\n=========================================\n";
    echo "Video: {$video}\n";
    echo "=========================================\n";
    
    $quotes = get_posts([
        'post_type' => 'quotes',
        'posts_per_page' => -1,
        'post_status' => 'any',
        'meta_query' => [
            [
                'key' => '_e3_quote_video_title',
                'value' => $video,
                'compare' => '='
            ]
        ]
    ]);
    
    // Group by current speaker name parsed from title
    $grouped = [];
    foreach ($quotes as $q) {
        $parts = explode(' on "', $q->post_title, 2);
        $speaker = trim($parts[0]);
        $grouped[$speaker][] = $q;
    }
    
    foreach ($grouped as $speaker => $list) {
        echo "Speaker: {$speaker} (" . count($list) . " quotes)\n";
        echo "Sample Quote: " . get_post_meta($list[0]->ID, '_e3_quote_quote', true) . "\n\n";
    }
}
