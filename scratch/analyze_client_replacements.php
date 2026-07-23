<?php
/**
 * Analyze Client Replacements
 * Scans for matching Flickr folders for the 66+ clients using the fallback image.
 */

$wp_load = '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';
if (!file_exists($wp_load)) {
    die("Cannot find wp-load.php at: $wp_load\n");
}
require_once $wp_load;

echo "🔍 Starting Client Replacements Analysis...\n";

$flickr_downloads_dir = '/Users/bryanpaul/Dropbox/PaulDropbox/E3/flickr_downloads';

// Find all client posts using the target attachment ID 126
global $wpdb;
$thumb_results = $wpdb->get_col("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_thumbnail_id' AND meta_value = '126'");

echo "Found " . count($thumb_results) . " client posts using target image 126 as featured image.\n\n";

// Map all folders in flickr_downloads
$flickr_folders = [];
if (is_dir($flickr_downloads_dir)) {
    $dirs = glob($flickr_downloads_dir . '/*', GLOB_ONLYDIR);
    foreach ($dirs as $d) {
        $flickr_folders[] = basename($d);
    }
}

// Function to normalize name for comparison
function clean_name_for_match($name) {
    $name = str_replace(['isd', 'cisd', 'consolidated', 'school', 'schools', 'district', 'city of', 'county'], '', strtolower($name));
    return trim(preg_replace('/[^a-z0-9]/', '', $name));
}

$has_folder = [];
$no_folder = [];

foreach ($thumb_results as $post_id) {
    $p = get_post($post_id);
    $title = $p->post_title;
    $slug = $p->post_name;
    
    $clean_title = clean_name_for_match($title);
    $matched_folders = [];
    
    foreach ($flickr_folders as $folder) {
        $clean_folder = clean_name_for_match($folder);
        if (!empty($clean_title) && (strpos($clean_folder, $clean_title) !== false || strpos($clean_title, $clean_folder) !== false)) {
            $matched_folders[] = $folder;
        }
    }
    
    if (!empty($matched_folders)) {
        // Count files in matched folders
        $file_count = 0;
        foreach ($matched_folders as $folder) {
            $files = glob($flickr_downloads_dir . '/' . $folder . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
            $file_count += count($files);
        }
        
        $has_folder[] = [
            'id' => $post_id,
            'title' => $title,
            'slug' => $slug,
            'folders' => $matched_folders,
            'files_count' => $file_count
        ];
    } else {
        $no_folder[] = [
            'id' => $post_id,
            'title' => $title,
            'slug' => $slug
        ];
    }
}

echo "=== CLIENTS WITH MATCHING FLICKR FOLDERS (" . count($has_folder) . ") ===\n";
foreach ($has_folder as $c) {
    echo "• {$c['title']} (ID: {$c['id']}) -> Folders: " . implode(', ', $c['folders']) . " ({$c['files_count']} images)\n";
}

echo "\n=== CLIENTS WITH NO MATCHING FLICKR FOLDERS (" . count($no_folder) . ") ===\n";
foreach ($no_folder as $c) {
    echo "• {$c['title']} (ID: {$c['id']}, slug: {$c['slug']})\n";
}

echo "\nAnalysis complete!\n";
