<?php
// PHP Script to duplicate post ID 3809

$source_id = 3809;
$post = get_post($source_id, ARRAY_A);

if (!$post) {
    echo "Error: Source post $source_id not found.\n";
    exit(1);
}

// Prepare post data for duplication
$new_post_data = array(
    'post_title'    => $post['post_title'] . ' (Duplicate)',
    'post_content'  => $post['post_content'],
    'post_status'   => 'draft', // let's make it a draft, or copy status? The prompt says: "Publish all 76 draft client posts in WordPress using WP-CLI", maybe this duplicate will become draft first and then we publish it or keep it as draft? Let's check status.
    'post_type'     => $post['post_type'],
    'post_author'   => $post['post_author'],
    'post_excerpt'  => $post['post_excerpt'],
    'post_parent'   => $post['post_parent'],
);

// Insert the duplicated post
$new_post_id = wp_insert_post($new_post_data);

if (is_wp_error($new_post_id)) {
    echo "Error: Failed to insert duplicated post. " . $new_post_id->get_error_message() . "\n";
    exit(1);
}

echo "Success: Duplicated post $source_id to new post ID $new_post_id.\n";

// Copy post meta
$post_meta = get_post_custom($source_id);
foreach ($post_meta as $key => $values) {
    // Avoid copying internal edit locks or unique keys if appropriate, but copy everything else
    if ($key === '_edit_lock' || $key === '_edit_last') {
        continue;
    }
    foreach ($values as $value) {
        // Check if value is serialized
        $value = maybe_unserialize($value);
        add_post_meta($new_post_id, $key, $value);
    }
}

// Copy taxonomies
$taxonomies = get_object_taxonomies($post['post_type']);
foreach ($taxonomies as $taxonomy) {
    $post_terms = wp_get_object_terms($source_id, $taxonomy, array('fields' => 'slugs'));
    wp_set_object_terms($new_post_id, $post_terms, $taxonomy);
}

echo "Success: Copied meta and terms for new post ID $new_post_id.\n";
?>
