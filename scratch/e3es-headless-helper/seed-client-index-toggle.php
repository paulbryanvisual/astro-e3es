<?php
// Initialize WordPress load context
require_once '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';

if (php_sapi_name() !== 'cli') {
    die("This script must be run via command line (CLI).\n");
}

$allowed_slugs = array(
    'boyd-isd', 'bryan-isd', 'caldwell-isd', 'carrizo-springs-cisd', 'cooke-county',
    'donna-isd', 'edcouch-elsa-isd', 'ferris-isd', 'glen-rose-medical-center', 'goodall-witcher-hospital',
    'granbury-isd', 'greenville-isd', 'hondo-isd', 'houston-community-college', 'kountze-isd',
    'lake-worth-isd', 'manor-isd', 'mercedes-isd', 'needville-isd', 'port-neches-groves-isd',
    'prosper-isd', 'raymondville-isd', 'ricardo-isd', 'rio-hondo-isd', 'royal-isd'
);

$args = array(
    'post_type'      => 'clients',
    'posts_per_page' => -1,
    'post_status'    => 'any'
);

$query = new WP_Query($args);
$posts = $query->posts;

echo "Found " . count($posts) . " clients in total.\n";

$updated_count = 0;
foreach ($posts as $post) {
    $slug = $post->post_name;
    $should_show = in_array($slug, $allowed_slugs);
    
    // Save as boolean/integer representation in DB
    update_post_meta($post->ID, '_e3_client_show_in_index', $should_show ? 1 : 0);
    
    $status_str = $should_show ? 'SHOW' : 'HIDE';
    echo " -> Client: {$post->post_title} (slug: {$slug}) set to {$status_str}\n";
    $updated_count++;
}

wp_cache_flush();
echo "Successfully updated {$updated_count} clients!\n";
