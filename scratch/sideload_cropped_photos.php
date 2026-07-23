<?php
require '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';

$json_file = '/Users/bryanpaul/Local Sites/astro-e3es/scratch/crop_list.json';
if (!file_exists($json_file)) {
    die("Error: crop_list.json not found\n");
}
$crop_list = json_decode(file_get_contents($json_file), true);

require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

function upload_cropped_photo($file_path, $post_id) {
    if (!file_exists($file_path)) {
        return null;
    }
    
    global $wpdb;
    $filename = basename($file_path);
    $attachment_id = $wpdb->get_var($wpdb->prepare(
        "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s",
        '%' . $filename
    ));
    
    if ($attachment_id) {
        return wp_get_attachment_url($attachment_id);
    }
    
    // Copy file to temp location
    $tmp = download_url('file://' . $file_path);
    if (is_wp_error($tmp)) {
        // Direct copy fallback
        $uploads = wp_upload_dir();
        $new_file = $uploads['path'] . '/' . $filename;
        copy($file_path, $new_file);
        
        $attachment = [
            'guid'           => $uploads['url'] . '/' . $filename, 
            'post_mime_type' => wp_check_filetype($new_file)['type'],
            'post_title'     => preg_replace( '/\.[^.]+$/', '', $filename ),
            'post_content'   => '',
            'post_status'    => 'inherit'
        ];
        
        $attach_id = wp_insert_attachment($attachment, $new_file, $post_id);
        $attach_data = wp_generate_attachment_metadata($attach_id, $new_file);
        wp_update_attachment_metadata($attach_id, $attach_data);
        
        return wp_get_attachment_url($attach_id);
    }
    
    $file_array = [
        'name'     => $filename,
        'tmp_name' => $tmp
    ];
    
    $id = media_handle_sideload($file_array, $post_id);
    if (is_wp_error($id)) {
        return null;
    }
    
    return wp_get_attachment_url($id);
}

$updated_count = 0;

foreach ($crop_list as $client) {
    $slug = $client['slug'];
    $title = $client['title'];
    $folder = $client['folder'];
    
    $p = get_page_by_path($slug, OBJECT, 'clients');
    if (!$p) continue;
    
    $photo_path = "$folder/images/{$slug}-cropped-layout-photo.jpg";
    if (!file_exists($photo_path)) {
        echo "  [WARNING] Cropped photo not found for: $slug\n";
        continue;
    }
    
    // Upload the photo
    $photo_url = upload_cropped_photo($photo_path, $p->ID);
    if (!$photo_url) {
        echo "  [WARNING] Failed to upload photo for: $slug\n";
        continue;
    }
    
    $blocks = parse_blocks($p->post_content);
    $changed = false;
    
    $replace_png_bg = function(&$blocks) use (&$replace_png_bg, $photo_url, &$changed) {
        foreach ($blocks as &$b) {
            if ($b['blockName'] === 'e3es/project') {
                if (!empty($b['attrs']['heroImageUrl'])) {
                    $url = $b['attrs']['heroImageUrl'];
                    if (strtolower(pathinfo($url, PATHINFO_EXTENSION)) === 'png') {
                        $b['attrs']['heroImageUrl'] = $photo_url;
                        // Replace in markup
                        $b['innerHTML'] = str_replace($url, $photo_url, $b['innerHTML']);
                        $b['innerContent'] = [ $b['innerHTML'] ];
                        $changed = true;
                    }
                }
            }
            if ($b['blockName'] === 'e3es/intro-banner') {
                if (!empty($b['attrs']['bgImageUrl'])) {
                    $url = $b['attrs']['bgImageUrl'];
                    if (strtolower(pathinfo($url, PATHINFO_EXTENSION)) === 'png') {
                        $b['attrs']['bgImageUrl'] = $photo_url;
                        // Replace in markup
                        $b['innerHTML'] = str_replace($url, $photo_url, $b['innerHTML']);
                        $b['innerContent'] = [ $b['innerHTML'] ];
                        $changed = true;
                    }
                }
            }
            if (!empty($b['innerBlocks'])) {
                $replace_png_bg($b['innerBlocks']);
            }
        }
    };
    
    $replace_png_bg($blocks);
    
    if ($changed) {
        $new_content = serialize_blocks($blocks);
        wp_update_post([
            'ID' => $p->ID,
            'post_content' => $new_content
        ]);
        echo "Successfully updated logo background with cropped photo for: $slug\n";
        $updated_count++;
    }
}

echo "Done! Replaced logo background on $updated_count client pages.\n";
