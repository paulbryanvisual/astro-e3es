<?php
$wp_load = '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';
if (!file_exists($wp_load)) {
    die("Cannot find wp-load.php.\n");
}
require_once $wp_load;

wp_set_current_user(1);

$query = new WP_Query([
    'post_type' => ['page', 'clients', 'services'],
    'post_status' => 'publish',
    'posts_per_page' => -1,
]);

$posts = [];
foreach ($query->posts as $p) {
    // Only check posts that contain blocks prone to validation recovery issues
    $has_layout_block = false;
    foreach (['wp:image', 'wp:group', 'wp:list', 'wp:e3es/'] as $needle) {
        if (strpos($p->post_content, $needle) !== false) {
            $has_layout_block = true;
            break;
        }
    }
    
    if ($has_layout_block) {
        $posts[] = [
            'id' => $p->ID,
            'post_type' => $p->post_type,
            'title' => $p->post_title,
            'slug' => $p->post_name,
        ];
    }
}

file_put_contents('/Users/bryanpaul/Local Sites/astro-e3es/scratch/block_posts.json', json_encode($posts, JSON_PRETTY_PRINT));
echo "Found " . count($posts) . " block posts with layout elements.\n";
