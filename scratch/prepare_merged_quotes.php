<?php
require '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';

$quotes = get_posts([
    'post_type' => 'quotes',
    'posts_per_page' => -1,
    'post_status' => 'any',
    'orderby' => 'ID',
    'order' => 'ASC'
]);

$groups = [];

foreach ($quotes as $q) {
    $video = get_post_meta($q->ID, '_e3_quote_video_title', true);
    if (empty($video)) {
        $video = 'Unknown Video';
    }
    
    $person_id = (int) get_post_meta($q->ID, '_e3_quote_person_id', true);
    if (!$person_id) {
        continue; // skip unlinked quotes
    }
    
    $person_post = get_post($person_id);
    $person_name = $person_post ? $person_post->post_title : 'Unknown Speaker';
    
    $quote_text = get_post_meta($q->ID, '_e3_quote_quote', true);
    if (empty($quote_text)) {
        $quote_text = $q->post_content;
    }
    
    $key = $video . '|||' . $person_id . '|||' . $person_name;
    
    if (!isset($groups[$key])) {
        $groups[$key] = [
            'video' => $video,
            'person_id' => $person_id,
            'person_name' => $person_name,
            'quote_ids' => [],
            'texts' => []
        ];
    }
    
    $groups[$key]['quote_ids'][] = $q->ID;
    $groups[$key]['texts'][] = trim($quote_text);
}

$output = [];
foreach ($groups as $key => $g) {
    $raw_text = implode(' ', $g['texts']);
    // Simple normalization of spaces
    $raw_text = preg_replace('/\s+/', ' ', $raw_text);
    
    $output[] = [
        'video' => $g['video'],
        'person_id' => $g['person_id'],
        'person_name' => $g['person_name'],
        'quote_ids' => $g['quote_ids'],
        'raw_text' => $raw_text
    ];
}

file_put_contents('/Users/bryanpaul/Local Sites/astro-e3es/scratch/raw_merged_quotes.json', json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "Grouped " . count($quotes) . " quotes into " . count($output) . " video-speaker groups.\n";
echo "Saved raw merged quotes to scratch/raw_merged_quotes.json\n";
