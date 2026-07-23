<?php
require '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';

echo "=== EXISTING CLIENT-SERVICES TERMS ===\n";
$terms = get_terms(array(
    'taxonomy' => 'client-services',
    'hide_empty' => false,
));
foreach ($terms as $t) {
    echo "- ID: {$t->term_id}, Slug: {$t->slug}, Name: {$t->name}\n";
}

echo "\n=== CLIENTS POSTS AND THEIR SERVICES ===\n";
$posts = get_posts(array(
    'post_type' => 'clients',
    'posts_per_page' => -1,
));
foreach ($posts as $p) {
    $services = wp_get_post_terms($p->ID, 'client-services', array('fields' => 'names'));
    echo "- ID: {$p->ID}, Title: {$p->post_title}, Services: " . implode(', ', $services) . "\n";
}
