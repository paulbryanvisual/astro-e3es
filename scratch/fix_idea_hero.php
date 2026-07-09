<?php
// PHP Script to fix IDEA Public Schools hero image

$post_id = 1468;
$image_name = 'Jason Flowers - IDEA Reference Sheet-scaled.png';
$wp_upload_dir = wp_upload_dir();
$local_file = $wp_upload_dir['basedir'] . '/2026/06/' . $image_name;

if (!file_exists($local_file)) {
    // Try without -scaled
    $image_name = 'Jason Flowers - IDEA Reference Sheet.png';
    $local_file = $wp_upload_dir['basedir'] . '/2026/06/' . $image_name;
}

if (!file_exists($local_file)) {
    echo "Error: IDEA image file not found.\n";
    exit(1);
}

echo "Found local file: $local_file\n";

$filename = basename($local_file);
$attachment = get_page_by_title(pathinfo($filename, PATHINFO_FILENAME), OBJECT, 'attachment');
$attach_id = 0;

if ($attachment) {
    $attach_id = $attachment->ID;
    echo "Attachment already exists (ID: $attach_id).\n";
} else {
    $filetype = wp_check_filetype($filename, null);
    $attachment_data = array(
        'guid'           => $wp_upload_dir['baseurl'] . '/2026/07/' . $filename,
        'post_mime_type' => $filetype['type'],
        'post_title'     => preg_replace('/\.[^.]+$/', '', $filename),
        'post_content'   => '',
        'post_status'    => 'inherit'
    );
    
    $new_subdir = $wp_upload_dir['path'] . '/' . $filename;
    copy($local_file, $new_subdir);
    
    $attach_id = wp_insert_attachment($attachment_data, $new_subdir, $post_id);
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata($attach_id, $new_subdir);
    wp_update_attachment_metadata($attach_id, $attach_data);
    echo "Successfully imported to media library (ID: $attach_id).\n";
}

// Update featured image
update_post_meta($post_id, '_thumbnail_id', $attach_id);
echo "Updated featured image for post $post_id to attachment ID $attach_id.\n";

// Update post content
$post = get_post($post_id);
if ($post) {
    $content = $post->post_content;
    $attach_url = wp_get_attachment_url($attach_id);
    $placeholder_url = 'http://e3es2026.local/wp-content/uploads/2026/06/taj-mahal-placeholder.png';
    
    $new_content = str_replace($placeholder_url, $attach_url, $content);
    
    // Also check for escaped backslashes
    $escaped_placeholder = str_replace('/', '\\/', $placeholder_url);
    $escaped_attach_url = str_replace('/', '\\/', $attach_url);
    $new_content = str_replace($escaped_placeholder, $escaped_attach_url, $new_content);
    
    wp_update_post(array(
        'ID' => $post_id,
        'post_content' => $new_content
    ));
    echo "Replaced placeholder image with $attach_url in post $post_id content.\n";
}
?>
