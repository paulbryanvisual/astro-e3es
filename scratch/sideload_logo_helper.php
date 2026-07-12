<?php
/**
 * PHP Helper to register sideloaded logo and update post content
 */

$wp_load = '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';
if (!file_exists($wp_load)) {
    die("ERROR: Cannot find wp-load.php\n");
}
require_once $wp_load;
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

if ($argc < 5) {
    die("ERROR: Missing arguments. Usage: php script.php <post_id> <filename> <temp_file_path> <external_url>\n");
}

$post_id = intval($argv[1]);
$filename = $argv[2];
$temp_file_path = $argv[3];
$external_url = $argv[4];

if ($temp_file_path !== 'check_only' && !file_exists($temp_file_path)) {
    die("ERROR: Temporary file does not exist at: $temp_file_path\n");
}

$post = get_post($post_id);
if (!$post) {
    die("ERROR: Post ID $post_id not found\n");
}

// Check if this filename is already in our media library
global $wpdb;
$attachment_id = $wpdb->get_var($wpdb->prepare(
    "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s",
    '%' . $filename
));

$local_url = '';
if ($attachment_id) {
    $local_url = wp_get_attachment_url($attachment_id);
    echo "SUCCESS_EXISTS:$local_url\n";
} else {
    if ($temp_file_path === 'check_only') {
        die("NOT_FOUND\n");
    }
    $file_array = [
        'name' => $filename,
        'tmp_name' => $temp_file_path
    ];

    $id = media_handle_sideload($file_array, $post_id);
    if (is_wp_error($id)) {
        die("ERROR: Sideload failed: " . $id->get_error_message() . "\n");
    }

    $local_url = wp_get_attachment_url($id);
    echo "SUCCESS_NEW:$local_url\n";
}

if ($local_url) {
    $updated = false;
    
    // Replace the external URL with the local URL in the post content
    $content = $post->post_content;
    if (strpos($content, $external_url) !== false) {
        $content = str_replace($external_url, $local_url, $content);
        wp_update_post([
            'ID' => $post_id,
            'post_content' => $content
        ]);
        $updated = true;
        echo "DATABASE_CONTENT_UPDATED\n";
    }
    
    // Also replace in client metadata if it matches
    $logo_meta = get_post_meta($post_id, '_e3_client_logo', true);
    if ($logo_meta === $external_url || ($logo_meta && strpos($logo_meta, $external_url) !== false)) {
        update_post_meta($post_id, '_e3_client_logo', $local_url);
        $updated = true;
        echo "DATABASE_METADATA_UPDATED\n";
    }
    
    if (!$updated) {
        echo "NO_MATCH_FOUND\n";
    }
}
