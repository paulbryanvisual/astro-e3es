<?php
require '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';

$posts = get_posts([
    'post_type' => 'employees',
    'posts_per_page' => -1,
    'post_status' => 'any'
]);

$data = [];
foreach ($posts as $p) {
    $data[] = [
        'id' => $p->ID,
        'title' => $p->post_title,
        'slug' => $p->post_name
    ];
}

file_put_contents('/Users/bryanpaul/Local Sites/astro-e3es/scratch/employees_details.json', json_encode($data, JSON_PRETTY_PRINT));
echo "Dumped " . count($data) . " employees. Saved to employees_details.json\n";
