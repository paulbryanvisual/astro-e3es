<?php
/**
 * Dry Run Client Photo Replacements
 * Scans Reference Sheets to find candidate images for each of the 66 clients using the fallback image.
 */

$wp_load = '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';
if (!file_exists($wp_load)) {
    die("Cannot find wp-load.php at: $wp_load\n");
}
require_once $wp_load;

echo "🧪 Starting Dry Run Photo Replacements...\n";

$ref_dir = '/Users/bryanpaul/Dropbox/PaulDropbox/E3/docs/Reference Sheets';

// Find all client posts using the target attachment ID 126
global $wpdb;
$post_ids = $wpdb->get_col("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_thumbnail_id' AND meta_value = '126'");

echo "Found " . count($post_ids) . " client posts using target image 126.\n\n";

// Map all reference folders
$ref_folders = [];
if (is_dir($ref_dir)) {
    $dirs = glob($ref_dir . '/*', GLOB_ONLYDIR);
    foreach ($dirs as $d) {
        $ref_folders[] = basename($d);
    }
}

// Function to normalize name for comparison
function clean_name_for_ref_match($name) {
    $name = str_replace(
        ['isd', 'cisd', 'ccisd', 'consolidated', 'school', 'schools', 'district', 'city of', 'county', 'community', 'college', 'medical center'],
        '',
        strtolower($name)
    );
    return trim(preg_replace('/[^a-z0-9]/', '', $name));
}

$matched_candidates = [];
$missing_candidates = [];

foreach ($post_ids as $post_id) {
    $p = get_post($post_id);
    $title = $p->post_title;
    $slug = $p->post_name;
    
    $clean_title = clean_name_for_ref_match($title);
    $matched_folder = null;
    
    // Fuzzy search for folder
    foreach ($ref_folders as $folder) {
        $clean_folder = clean_name_for_ref_match($folder);
        
        // Exact normalized match, or containment
        if (!empty($clean_title) && !empty($clean_folder)) {
            if ($clean_title === $clean_folder || strpos($clean_folder, $clean_title) !== false || strpos($clean_title, $clean_folder) !== false) {
                $matched_folder = $folder;
                break;
            }
        }
    }
    
    if ($matched_folder) {
        // Find images in this folder recursively
        $folder_path = $ref_dir . '/' . $matched_folder;
        $images = [];
        
        // Iterate directory
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
            // Sort images: prefer files starting with "Jason Flowers" or containing the client name
            usort($images, function($a, $b) use ($title) {
                $fn_a = strtolower(basename($a));
                $fn_b = strtolower(basename($b));
                
                $pref_a = (strpos($fn_a, 'jason flowers') !== false) ? 2 : 0;
                $pref_b = (strpos($fn_b, 'jason flowers') !== false) ? 2 : 0;
                
                // Prefer files with client name
                $clean_client = clean_name_for_ref_match($title);
                if (strpos(clean_name_for_ref_match($fn_a), $clean_client) !== false) $pref_a += 1;
                if (strpos(clean_name_for_ref_match($fn_b), $clean_client) !== false) $pref_b += 1;
                
                // Prefer larger file size (more likely to be a real photo than a tiny icon)
                $size_a = filesize($a);
                $size_b = filesize($b);
                
                if ($pref_a !== $pref_b) {
                    return $pref_b <=> $pref_a; // Descending preference
                }
                return $size_b <=> $size_a; // Descending size
            });
            
            $matched_candidates[] = [
                'id' => $post_id,
                'title' => $title,
                'slug' => $slug,
                'folder' => $matched_folder,
                'candidate_image' => $images[0],
                'image_size' => round(filesize($images[0]) / 1024, 1) . ' KB'
            ];
        } else {
            $missing_candidates[] = [
                'id' => $post_id,
                'title' => $title,
                'slug' => $slug,
                'folder' => $matched_folder,
                'reason' => 'Folder found, but no image files found inside.'
            ];
        }
    } else {
        $missing_candidates[] = [
            'id' => $post_id,
            'title' => $title,
            'slug' => $slug,
            'folder' => 'N/A',
            'reason' => 'No matching Reference Sheets folder found.'
        ];
    }
}

echo "=== CANDIDATES MATCHED (" . count($matched_candidates) . ") ===\n";
foreach ($matched_candidates as $c) {
    echo "• {$c['title']} (ID: {$c['id']}) -> Folder: \"{$c['folder']}\" | Image: \"" . basename($c['candidate_image']) . "\" ({$c['image_size']})\n";
}

echo "\n=== MISSING / NO CANDIDATES (" . count($missing_candidates) . ") ===\n";
foreach ($missing_candidates as $c) {
    echo "• {$c['title']} (ID: {$c['id']}) -> Folder: \"{$c['folder']}\" | Reason: {$c['reason']}\n";
}

echo "\nDry run complete!\n";
