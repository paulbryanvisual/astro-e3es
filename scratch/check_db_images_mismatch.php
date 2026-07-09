<?php
// PHP Script to compare featured images (_thumbnail_id) with bgImageUrl in post content for all clients.

$posts = get_posts(array(
    'post_type' => 'clients',
    'posts_per_page' => -1,
    'post_status' => 'publish'
));

echo "Found " . count($posts) . " clients.\n\n";

$mismatches = 0;

foreach ($posts as $post) {
    $content = $post->post_content;
    $thumbnail_id = get_post_meta($post->ID, '_thumbnail_id', true);
    
    $local_url = '';
    if ($thumbnail_id) {
        $local_url = wp_get_attachment_url($thumbnail_id);
    }
    
    $content_url = '';
    if (preg_match('/bgImageUrl":"([^"]+)"/', $content, $matches)) {
        $content_url = $matches[1];
    } else if (preg_match('/background-image:linear-gradient\([^)]+\),\s*url\(([^)]+)\)/', $content, $matches)) {
        $content_url = $matches[1];
    }
    
    $clean_local = $local_url ? basename(urldecode($local_url)) : 'None';
    $clean_content = $content_url ? basename(urldecode($content_url)) : 'None';
    
    // Clean up extensions and sizes for robust comparison
    $base_local = preg_replace('/-\d+x\d+$/', '', preg_replace('/\.[^.]+$/', '', $clean_local));
    $base_content = preg_replace('/-\d+x\d+$/', '', preg_replace('/\.[^.]+$/', '', $clean_content));
    
    if (strtolower($base_local) !== strtolower($base_content)) {
        echo "Mismatch on client {$post->post_name} (ID: {$post->ID}):\n";
        echo "  -> Featured Image: $clean_local\n";
        echo "  -> Content Banner: $clean_content\n\n";
        $mismatches++;
    }
}

echo "Total mismatches: $mismatches\n";
?>
