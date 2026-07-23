<?php
require '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';

$posts = get_posts([
    'post_type' => ['page', 'post', 'clients'],
    'posts_per_page' => -1,
    'post_status' => 'any'
]);

echo "Searching for e3es/testimonial-picker blocks in page contents...\n";
$found_count = 0;
foreach ($posts as $p) {
    if (strpos($p->post_content, 'e3es/testimonial-picker') !== false) {
        echo "📄 Found on post: '{$p->post_title}' (ID: {$p->ID}, Type: {$p->post_type})\n";
        // Extract block details
        preg_match_all('/<!-- wp:e3es\/testimonial-picker ({.*?}) \/-->/', $p->post_content, $matches);
        foreach ($matches[1] as $m) {
            echo "   Block attributes: {$m}\n";
        }
        $found_count++;
    }
}
echo "Search complete. Found {$found_count} posts.\n";
