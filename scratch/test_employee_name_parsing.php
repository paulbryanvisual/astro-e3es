<?php
require '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';

$employees = get_posts([
    'post_type' => 'employees',
    'posts_per_page' => -1,
    'post_status' => 'any'
]);

function get_clean_name_from_slug($slug) {
    $parts = explode('-', $slug);
    $clean_parts = [];
    foreach ($parts as $p) {
        $p_lower = strtolower($p);
        if (in_array($p_lower, ['pe', 'cem', 'leed', 'ap', 'pmp', 'eit', 'shrm', 'cp', 'pe,', 'cem,'])) {
            continue;
        }
        $clean_parts[] = $p;
    }
    
    // Take the first two words (e.g. first name and last name)
    // Some slugs might have only one word or three words, but first two is standard
    $name_parts = array_slice($clean_parts, 0, 2);
    $fullname = implode(' ', $name_parts);
    return ucwords($fullname);
}

$mapping = [];
foreach ($employees as $e) {
    $parsed_name = get_clean_name_from_slug($e->post_name);
    $mapping[ strtolower($parsed_name) ] = [
        'id' => $e->ID,
        'original_title' => $e->post_title,
        'slug' => $e->post_name,
        'parsed_name' => $parsed_name
    ];
}

file_put_contents('/Users/bryanpaul/Local Sites/astro-e3es/scratch/employee_name_mapping.json', json_encode($mapping, JSON_PRETTY_PRINT));
echo "Parsed " . count($mapping) . " employees. Saved to employee_name_mapping.json\n";
