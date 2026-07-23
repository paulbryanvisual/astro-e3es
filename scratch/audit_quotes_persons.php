<?php
require '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';

$quotes = get_posts([
    'post_type' => 'quotes',
    'posts_per_page' => -1,
    'post_status' => 'any'
]);

$report = [];
foreach ($quotes as $q) {
    $person_id = get_post_meta($q->ID, '_e3_quote_person_id', true);
    $person_post = $person_id ? get_post($person_id) : null;
    
    $report[] = [
        'id' => $q->ID,
        'title' => $q->post_title,
        'person_id' => $person_id,
        'person_name' => $person_post ? $person_post->post_title : 'MISSING/NOT FOUND',
        'person_post_type' => $person_post ? $person_post->post_type : 'N/A'
    ];
}

file_put_contents('/Users/bryanpaul/Local Sites/astro-e3es/scratch/quotes_audit.json', json_encode($report, JSON_PRETTY_PRINT));
echo "Audited " . count($report) . " quotes. Saved to quotes_audit.json\n";
