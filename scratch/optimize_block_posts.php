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

$pages = [];
$services = [];
$clients = [];

foreach ($query->posts as $p) {
    $has_layout_block = false;
    foreach (['wp:image', 'wp:group', 'wp:list', 'wp:e3es/'] as $needle) {
        if (strpos($p->post_content, $needle) !== false) {
            $has_layout_block = true;
            break;
        }
    }
    
    if (!$has_layout_block) {
        continue;
    }

    $item = [
        'id' => $p->ID,
        'post_type' => $p->post_type,
        'title' => $p->post_title,
        'slug' => $p->post_name,
    ];

    if ($p->post_type === 'page') {
        $pages[] = $item;
    } elseif ($p->post_type === 'services') {
        $services[] = $item;
    } elseif ($p->post_type === 'clients') {
        $clients[] = $item;
    }
}

// Select a representative sample
$optimized = [];

// 1. All standard pages (29 posts) - since they are unique/hand-crafted
$optimized = array_merge($optimized, $pages);

// 2. Sample of services: 5 parent services and 8 child services
// Parent services: lighting, hvac, controls-automation, water-wastewater, financing-auditing
$parent_slugs = ['lighting', 'hvac', 'controls-automation', 'water-wastewater', 'financing-auditing'];
$parent_services = [];
$child_services = [];

foreach ($services as $s) {
    if (in_array($s['slug'], $parent_slugs) || strpos($s['slug'], 'hvac-system-upgrades') !== false) {
        $parent_services[] = $s;
    } else {
        $child_services[] = $s;
    }
}

$optimized = array_merge($optimized, $parent_services);
$optimized = array_merge($optimized, array_slice($child_services, 0, 10));

// 3. Sample of clients: 10 client pages (various school districts, hospitals, etc.)
$optimized = array_merge($optimized, array_slice($clients, 0, 10));

file_put_contents('/Users/bryanpaul/Local Sites/astro-e3es/scratch/block_posts.json', json_encode($optimized, JSON_PRETTY_PRINT));
echo "Optimized list contains " . count($optimized) . " posts (Pages: " . count($pages) . ", Services: " . (count($parent_services) + min(count($child_services), 10)) . ", Clients: " . min(count($clients), 10) . ").\n";
