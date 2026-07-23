<?php
require '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';
$posts = get_posts([
    'post_type' => 'clients',
    'posts_per_page' => -1,
    'post_status' => 'any'
]);
$data = [];
foreach ($posts as $p) {
    $data[] = [
        'id' => $p->ID,
        'slug' => $p->post_name,
        'title' => $p->post_title
    ];
}
echo json_encode($data, JSON_PRETTY_PRINT);
file_put_contents('/Users/bryanpaul/Local Sites/astro-e3es/scratch/client_ids.json', json_encode($data));
