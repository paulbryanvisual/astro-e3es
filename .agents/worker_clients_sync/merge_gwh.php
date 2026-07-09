<?php
// PHP Script to merge gwh content into goodall-witcher-hospital (ID 1459)

$dest_id = 1459;
$post = get_post($dest_id, ARRAY_A);

if (!$post) {
    echo "Error: Destination post $dest_id not found.\n";
    exit(1);
}

// 1. Set featured image meta
update_post_meta($dest_id, '_thumbnail_id', 6471);
echo "Success: Updated featured image _thumbnail_id to 6471 for post $dest_id.\n";

// 2. Modify post content
$content = $post['post_content'];

// Replace background image in intro banner
$old_image = 'http://e3es2026.local/wp-content/uploads/2026/06/taj-mahal-placeholder.png';
$new_image = 'http://e3es2026.local/wp-content/uploads/2026/06/gwh-hero-ghw-crane.jpg';

$content = str_replace($old_image, $new_image, $content);

// Prepend the relationship description paragraph from gwh
$paragraph = "\n\t<!-- wp:paragraph -->\n<p>Goodall-Witcher Healthcare is a long-standing E3 partner in Central Texas. This healthcare facility partnered with E3 to address aging HVAC infrastructure and improve energy efficiency across their campus. E3’s Design+Build approach allowed the project to be completed with minimal disruption to patient care operations.</p>\n<!-- /wp:paragraph -->\n";

// Insert right after the intro banner block (ends with <!-- /wp:e3es/intro-banner -->)
$intro_banner_end = '<!-- /wp:e3es/intro-banner -->';
$pos = strpos($content, $intro_banner_end);

if ($pos !== false) {
    $insert_pos = $pos + strlen($intro_banner_end);
    $content = substr_replace($content, $paragraph, $insert_pos, 0);
    echo "Success: Prepended relationship description paragraph.\n";
} else {
    echo "Warning: Could not find intro banner block end. Prepended to the beginning instead.\n";
    $content = $paragraph . $content;
}

// Update the post content
$updated_post = array(
    'ID'           => $dest_id,
    'post_content' => $content,
);

$res = wp_update_post($updated_post);

if (is_wp_error($res)) {
    echo "Error: Failed to update post content: " . $res->get_error_message() . "\n";
    exit(1);
}

echo "Success: Updated post content for post $dest_id.\n";
?>
