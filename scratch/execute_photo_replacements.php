<?php
/**
 * Execute Client Photo Replacements
 * Uploads client-specific photos and replaces the fallback chiller lift image database-wide.
 */

$wp_load = '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';
if (!file_exists($wp_load)) {
    die("Cannot find wp-load.php at: $wp_load\n");
}
require_once $wp_load;

// WordPress administration setup for sideloading
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

wp_set_current_user(1);
if (function_exists('kses_remove_filters')) {
    kses_remove_filters();
}

echo "🚀 Starting Client Photo Replacements...\n";

$ref_dir = '/Users/bryanpaul/Dropbox/PaulDropbox/E3/docs/Reference Sheets';

// Find all client posts using the target attachment ID 126
global $wpdb;
$post_ids = $wpdb->get_col("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_thumbnail_id' AND meta_value = '126'");

echo "Found " . count($post_ids) . " client posts to process.\n\n";

// Map all reference folders
$ref_folders = [];
if (is_dir($ref_dir)) {
    $dirs = glob($ref_dir . '/*', GLOB_ONLYDIR);
    foreach ($dirs as $d) {
        $ref_folders[] = basename($d);
    }
}

// Function to normalize name for comparison
function clean_name_for_replacement_match($name) {
    $name = str_replace(
        ['isd', 'cisd', 'ccisd', 'consolidated', 'school', 'schools', 'district', 'city of', 'county', 'community', 'college', 'medical center'],
        '',
        strtolower($name)
    );
    return trim(preg_replace('/[^a-z0-9]/', '', $name));
}

// Helper to recursively update intro-banner bgImageUrl inside parsed blocks
function update_intro_banner_bg($blocks, $old_url_part, $new_url) {
    $updated = false;
    foreach ($blocks as &$block) {
        if (empty($block['blockName'])) {
            continue;
        }
        
        if ($block['blockName'] === 'e3es/intro-banner') {
            if (!empty($block['attrs']['bgImageUrl']) && strpos($block['attrs']['bgImageUrl'], $old_url_part) !== false) {
                $old_bg = $block['attrs']['bgImageUrl'];
                $block['attrs']['bgImageUrl'] = $new_url;
                
                // Update innerHTML
                $block['innerHTML'] = str_replace($old_bg, $new_url, $block['innerHTML']);
                // Update innerContent
                if (!empty($block['innerContent'])) {
                    foreach ($block['innerContent'] as &$chunk) {
                        if (is_string($chunk)) {
                            $chunk = str_replace($old_bg, $new_url, $chunk);
                        }
                    }
                }
                $updated = true;
            }
        }
        
        if (!empty($block['innerBlocks'])) {
            if (update_intro_banner_bg($block['innerBlocks'], $old_url_part, $new_url)) {
                $updated = true;
            }
        }
    }
    return $updated;
}

$success_count = 0;
$skipped_count = 0;
$skipped_clients = [];

foreach ($post_ids as $post_id) {
    $p = get_post($post_id);
    $title = $p->post_title;
    $slug = $p->post_name;
    
    // Skip Glen Rose Medical Center if it somehow got targeted
    if (strpos(strtolower($title), 'glen rose') !== false) {
        echo "Skipping Glen Rose Medical Center (ID: $post_id) to preserve original photo.\n";
        continue;
    }
    
    $candidate_image = null;
    
    // Hardcoded overrides for specific cases
    if ($slug === 'ennis-isd') {
        $candidate_image = '/Users/bryanpaul/Dropbox/PaulDropbox/E3/docs/Reference Sheets/Ennis ISD/images/ennis-image2.jpg';
    } elseif ($slug === 'little-elm-isd') {
        $candidate_image = '/Users/bryanpaul/Dropbox/PaulDropbox/E3/assets/vimeo_downloads/Lessons In Learning - Mike Lamb Superintendent Little Elm ISD_946653874/screengrabs/Lessons In Learning - Mike Lamb Superintendent Little Elm ISD_946653874_121s.jpg';
    } elseif ($slug === 'plano-isd') {
        $candidate_image = '/Users/bryanpaul/Dropbox/PaulDropbox/E3/assets/vimeo_downloads/Lessons in Learning - Dr Theresa Williams Superintendent Plano ISD_1007829512/screengrabs/Lessons in Learning - Dr Theresa Williams Superintendent Plano ISD_1007829512_48s.jpg';
    } else {
        // Standard Reference Sheets matching
        $clean_title = clean_name_for_replacement_match($title);
        $matched_folder = null;
        
        foreach ($ref_folders as $folder) {
            $clean_folder = clean_name_for_replacement_match($folder);
            if (!empty($clean_title) && !empty($clean_folder)) {
                if ($clean_title === $clean_folder || strpos($clean_folder, $clean_title) !== false || strpos($clean_title, $clean_folder) !== false) {
                    $matched_folder = $folder;
                    break;
                }
            }
        }
        
        if ($matched_folder) {
            $folder_path = $ref_dir . '/' . $matched_folder;
            $images = [];
            
            $dir_iter = new RecursiveDirectoryIterator($folder_path, RecursiveDirectoryIterator::SKIP_DOTS);
            $iter = new RecursiveIteratorIterator($dir_iter, RecursiveIteratorIterator::LEAVES_ONLY);
            foreach ($iter as $fileinfo) {
                if ($fileinfo->isFile()) {
                    $ext = strtolower($fileinfo->getExtension());
                    $fn = $fileinfo->getFilename();
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp']) && strpos(strtolower($fn), 'logo') === false && strpos(strtolower($fn), 'map') === false) {
                        $images[] = $fileinfo->getPathname();
                    }
                }
            }
            
            if (!empty($images)) {
                // Sort to find the best image candidate
                usort($images, function($a, $b) use ($title) {
                    $fn_a = strtolower(basename($a));
                    $fn_b = strtolower(basename($b));
                    
                    $pref_a = (strpos($fn_a, 'jason flowers') !== false) ? 2 : 0;
                    $pref_b = (strpos($fn_b, 'jason flowers') !== false) ? 2 : 0;
                    
                    $clean_client = clean_name_for_replacement_match($title);
                    if (strpos(clean_name_for_replacement_match($fn_a), $clean_client) !== false) $pref_a += 1;
                    if (strpos(clean_name_for_replacement_match($fn_b), $clean_client) !== false) $pref_b += 1;
                    
                    $size_a = filesize($a);
                    $size_b = filesize($b);
                    
                    if ($pref_a !== $pref_b) {
                        return $pref_b <=> $pref_a;
                    }
                    return $size_b <=> $size_a;
                });
                
                $candidate_image = $images[0];
            }
        }
    }
    
    if ($candidate_image && file_exists($candidate_image)) {
        echo "Processing \"$title\"...\n";
        echo "  Found candidate image: \"" . basename($candidate_image) . "\"\n";
        
        // Sideload candidate image
        $temp_file = tempnam(sys_get_temp_dir(), 'sideload');
        copy($candidate_image, $temp_file);
        
        $file_array = [
            'name' => basename($candidate_image),
            'tmp_name' => $temp_file
        ];
        
        // Disable post-parent assignment to keep attachment standalone
        $attachment_id = media_handle_sideload($file_array, 0);
        
        if (is_wp_error($attachment_id)) {
            echo "  [ERROR] Sideload failed: " . $attachment_id->get_error_message() . "\n";
            $skipped_count++;
            $skipped_clients[] = $title . " (Sideload error)";
            @unlink($temp_file);
        } else {
            $new_img_url = wp_get_attachment_url($attachment_id);
            echo "  Successfully uploaded! Attachment ID: $attachment_id | URL: $new_img_url\n";
            
            // 1. Update featured image
            update_post_meta($post_id, '_thumbnail_id', $attachment_id);
            echo "    Updated featured image meta.\n";
            
            // 2. Update block content banner bgImageUrl
            if (!empty($p->post_content)) {
                $blocks = parse_blocks($p->post_content);
                // Search-and-replace url containing "54474213788"
                if (update_intro_banner_bg($blocks, '54474213788', $new_img_url)) {
                    $new_content = serialize_blocks($blocks);
                    wp_update_post([
                        'ID' => $post_id,
                        'post_content' => wp_slash($new_content)
                    ]);
                    echo "    Updated intro banner block content.\n";
                }
            }
            
            $success_count++;
        }
    } else {
        echo "  [WARNING] No confirmed candidate photo found for \"$title\".\n";
        $skipped_count++;
        $skipped_clients[] = $title;
    }
}

echo "\n=== REPLACEMENT SUMMARY ===\n";
echo "Successfully replaced: $success_count clients\n";
echo "Skipped: $skipped_count clients\n";
if (!empty($skipped_clients)) {
    echo "Skipped clients list:\n";
    foreach ($skipped_clients as $sc) {
        echo "  - $sc\n";
    }
}

echo "\n🏁 Replacement execution complete!\n\n";
