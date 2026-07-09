<?php
// Define path to WordPress directory
define('WP_USE_THEMES', false);
require_once('/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php');

$args = array(
    'post_type' => 'clients',
    'posts_per_page' => -1,
    'post_status' => array('publish', 'draft', 'private', 'pending')
);

$query = new WP_Query($args);
$posts = $query->posts;

$results = array();

foreach ($posts as $post) {
    $postId = $post->ID;
    
    // Featured Image details
    $thumbId = get_post_thumbnail_id($postId);
    $thumbUrl = '';
    if ($thumbId) {
        $thumbUrl = wp_get_attachment_url($thumbId);
    }
    
    // Taxonomies
    $regions = wp_get_post_terms($postId, 'region', array('fields' => 'all'));
    $region_slugs = array_map(function($t) { return $t->slug; }, $regions);
    
    $industries = wp_get_post_terms($postId, 'industry', array('fields' => 'all'));
    $industry_slugs = array_map(function($t) { return $t->slug; }, $industries);
    
    $services = wp_get_post_terms($postId, 'client-services', array('fields' => 'all'));
    $service_slugs = array_map(function($t) { return $t->slug; }, $services);
    
    // Custom Meta
    $region_meta = get_post_meta($postId, '_e3_client_region', true);
    $industry_meta = get_post_meta($postId, '_e3_client_industry', true);
    $services_meta = get_post_meta($postId, '_e3_client_services', true);
    $project_url = get_post_meta($postId, '_e3_client_project_url', true);
    $logo = get_post_meta($postId, '_e3_client_logo', true);
    
    $results[] = array(
        'id' => $postId,
        'slug' => $post->post_name,
        'title' => $post->post_title,
        'status' => $post->post_status,
        'featured_image_id' => $thumbId,
        'featured_image_url' => $thumbUrl,
        'regions' => $region_slugs,
        'industries' => $industry_slugs,
        'services' => $service_slugs,
        'meta' => array(
            'region' => $region_meta,
            'industry' => $industry_meta,
            'services' => $services_meta,
            'project_url' => $project_url,
            'logo' => $logo
        ),
        'content' => $post->post_content
    );
}

file_put_contents('/Users/bryanpaul/Local Sites/astro-e3es/.agents/explorer_clients_audit/local_wp_details.json', json_encode($results, JSON_PRETTY_PRINT));
echo "Successfully exported " . count($results) . " client posts.\n";
?>
