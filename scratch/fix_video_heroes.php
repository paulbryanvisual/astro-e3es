<?php
// PHP Script to import and update featured images/banners for the 5 video/TFC pages.

$targets = array(
    'keene-isd' => array(
        'id' => 3874,
        'image' => 'Keene.png'
    ),
    'plano-isd' => array(
        'id' => 3875,
        'image' => 'Plano.png'
    ),
    'little-elm-isd' => array(
        'id' => 3873,
        'image' => 'Little_Elm.png'
    ),
    'city-of-stockdale' => array(
        'id' => 3872,
        'image' => 'Stockdale.png'
    ),
    'texas-facilities-commission' => array(
        'id' => 1516,
        'image' => 'Jason Flowers - TFC pic-800x600.png'
    )
);

$wp_upload_dir = wp_upload_dir();
$upload_path = $wp_upload_dir['basedir'] . '/2026/06/';

foreach ($targets as $slug => $data) {
    $post_id = $data['id'];
    $image_name = $data['image'];
    $local_file = $upload_path . $image_name;
    
    if (!file_exists($local_file)) {
        // Fallback: search anywhere in uploads
        $uploads_dir = $wp_upload_dir['basedir'];
        $found = false;
        
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($uploads_dir));
        foreach ($it as $file) {
            if ($file->getFilename() === $image_name) {
                $local_file = $file->getRealPath();
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            echo "Error: Image file $image_name not found in uploads directory for $slug.\n";
            continue;
        }
    }
    
    echo "Found image path for $slug: $local_file\n";
    
    // Import into media library if not already imported
    // Search attachment by guid or title
    $filename = basename($local_file);
    $attachment = get_page_by_title(pathinfo($filename, PATHINFO_FILENAME), OBJECT, 'attachment');
    $attach_id = 0;
    
    if ($attachment) {
        $attach_id = $attachment->ID;
        echo "Attachment already exists for $filename (ID: $attach_id).\n";
    } else {
        // Prepare attachment data
        $filetype = wp_check_filetype($filename, null);
        $attachment_data = array(
            'guid'           => $wp_upload_dir['baseurl'] . '/2026/07/' . _wp_relative_upload_path($local_file), 
            'post_mime_type' => $filetype['type'],
            'post_title'     => preg_replace('/\.[^.]+$/', '', $filename),
            'post_content'   => '',
            'post_status'    => 'inherit'
        );
        
        // Copy to current uploads month
        $new_subdir = $wp_upload_dir['path'] . '/' . $filename;
        copy($local_file, $new_subdir);
        
        $attach_id = wp_insert_attachment($attachment_data, $new_subdir, $post_id);
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata($attach_id, $new_subdir);
        wp_update_attachment_metadata($attach_id, $attach_data);
        
        echo "Successfully imported $filename to media library (ID: $attach_id).\n";
    }
    
    // Set featured image
    update_post_meta($post_id, '_thumbnail_id', $attach_id);
    echo "Updated featured image for post $post_id to attachment ID $attach_id.\n";
    
    // Replace taj-mahal-placeholder.png with the new attachment URL in post content
    $post = get_post($post_id);
    if ($post) {
        $content = $post->post_content;
        $attach_url = wp_get_attachment_url($attach_id);
        
        $placeholder_url = 'http://e3es2026.local/wp-content/uploads/2026/06/taj-mahal-placeholder.png';
        
        if (strpos($content, $placeholder_url) !== false) {
            $new_content = str_replace($placeholder_url, $attach_url, $content);
            wp_update_post(array(
                'ID' => $post_id,
                'post_content' => $new_content
            ));
            echo "Replaced placeholder image with $attach_url in post $post_id content.\n";
        } else {
            // Also check for background-image reference with backslashes or other forms
            $escaped_placeholder = str_replace('/', '\\/', $placeholder_url);
            $escaped_attach_url = str_replace('/', '\\/', $attach_url);
            if (strpos($content, $escaped_placeholder) !== false) {
                $new_content = str_replace($escaped_placeholder, $escaped_attach_url, $content);
                wp_update_post(array(
                    'ID' => $post_id,
                    'post_content' => $new_content
                ));
                echo "Replaced escaped placeholder image in post $post_id content.\n";
            } else {
                echo "Placeholder URL not found in post $post_id content. Content was not changed.\n";
            }
        }
    }
}

echo "Done.\n";
?>
