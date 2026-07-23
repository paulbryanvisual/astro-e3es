<?php
require '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

// Helper to import local portrait images into WP Media Library
function local_import_portrait( $filename, $post_id, $desc = '' ) {
    $uploads = wp_upload_dir();
    $file_path = $uploads['basedir'] . '/vimeo_portraits/' . $filename;

    if ( ! file_exists( $file_path ) ) {
        return false;
    }

    // Check if attachment already exists
    $existing_attachment = get_posts( array(
        'post_type'   => 'attachment',
        'meta_key'    => '_wp_attached_file',
        'meta_value'  => 'vimeo_portraits/' . $filename,
        'numberposts' => 1,
        'post_status' => 'inherit'
    ) );

    if ( ! empty( $existing_attachment ) ) {
        return $existing_attachment[0]->ID;
    }

    // Copy to temp directory
    $temp_dir = get_temp_dir();
    $temp_file = $temp_dir . '/' . basename( $file_path );
    copy( $file_path, $temp_file );

    $file_array = array(
        'name'     => basename( $file_path ),
        'tmp_name' => $temp_file,
    );

    $id = media_handle_sideload( $file_array, $post_id, $desc );

    if ( is_wp_error( $id ) ) {
        @unlink( $temp_file );
        return false;
    }

    return $id;
}

// 1. Parse speakers.csv to build a name-to-info map
$speakers_map = array();
$speakers_csv_path = '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-content/plugins/e3es-headless-helper/speakers.csv';
if ( file_exists( $speakers_csv_path ) ) {
    if ( ( $handle = fopen( $speakers_csv_path, 'r' ) ) !== false ) {
        $header = fgetcsv( $handle ); // Skip header
        while ( ( $data = fgetcsv( $handle ) ) !== false ) {
            if ( count( $data ) >= 2 ) {
                $name = trim( $data[0] );
                $title = trim( $data[1] );
                $portrait_link = isset( $data[2] ) ? trim( $data[2] ) : '';
                $speakers_map[ strtolower($name) ] = array(
                    'name' => $name,
                    'title' => $title,
                    'portrait_file' => basename( $portrait_link )
                );
            }
        }
        fclose( $handle );
    }
}

// 2. Fetch all quotes
$quotes = get_posts([
    'post_type' => 'quotes',
    'posts_per_page' => -1,
    'post_status' => 'any'
]);

echo "Found " . count($quotes) . " quotes to check.\n";

$created_people = 0;
$linked_quotes = 0;
$skipped_quotes = 0;

// Cache existing people/employees by title (case-insensitive) to prevent repeated DB queries
$people_cache = [];
$existing_people_posts = get_posts([
    'post_type' => ['people', 'employees'],
    'posts_per_page' => -1,
    'post_status' => 'any'
]);
foreach ($existing_people_posts as $ep) {
    $people_cache[ strtolower(trim($ep->post_title)) ] = $ep->ID;
}

foreach ($quotes as $q) {
    $title = $q->post_title;
    
    // Parse person name from title: "{Person Name} on "{Video Title}""
    $parts = explode(' on "', $title, 2);
    if (count($parts) < 2) {
        // Fallback: try " on " without quote
        $parts = explode(' on ', $title, 2);
    }
    
    if (count($parts) < 2) {
        echo "⚠️ Could not parse person from title: {$title}\n";
        $skipped_quotes++;
        continue;
    }
    
    $person_name = trim($parts[0]);
    if (empty($person_name)) {
        $skipped_quotes++;
        continue;
    }
    
    $person_key = strtolower($person_name);
    $person_id = 0;
    
    if (isset($people_cache[$person_key])) {
        $person_id = $people_cache[$person_key];
    } else {
        // Create new person CPT 'people'
        // Check if there is a speakers.csv match for capitalization
        $display_name = $person_name;
        $person_title = 'Representative';
        $portrait_file = '';
        
        if (isset($speakers_map[$person_key])) {
            $display_name = $speakers_map[$person_key]['name'];
            $person_title = $speakers_map[$person_key]['title'];
            $portrait_file = $speakers_map[$person_key]['portrait_file'];
        }
        
        $new_person_post_id = wp_insert_post( array(
            'post_type'   => 'people',
            'post_title'  => $display_name,
            'post_status' => 'publish'
        ) );
        
        if ( ! is_wp_error( $new_person_post_id ) ) {
            $person_id = $new_person_post_id;
            $people_cache[$person_key] = $person_id;
            $created_people++;
            
            update_post_meta( $person_id, '_e3_person_title', $person_title );
            
            if ( ! empty( $portrait_file ) ) {
                $attachment_id = local_import_portrait( $portrait_file, $person_id, $display_name );
                if ( $attachment_id ) {
                    set_post_thumbnail( $person_id, $attachment_id );
                }
            }
            
            echo "🆕 Created Person: {$display_name} (ID: {$person_id}, Title: {$person_title})\n";
        } else {
            echo "❌ Failed to create person: {$display_name} - " . $new_person_post_id->get_error_message() . "\n";
            $skipped_quotes++;
            continue;
        }
    }
    
    // Check if currently linked person matches
    $current_person_id = get_post_meta($q->ID, '_e3_quote_person_id', true);
    if (intval($current_person_id) !== intval($person_id)) {
        update_post_meta($q->ID, '_e3_quote_person_id', intval($person_id));
        $linked_quotes++;
    } else {
        $skipped_quotes++;
    }
}

echo "\n====================================\n";
echo "        SYNC RESULTS SUMMARY        \n";
echo "====================================\n";
echo "Total Quotes Processed: " . count($quotes) . "\n";
echo "New People Created: {$created_people}\n";
echo "Quotes Updated/Linked: {$linked_quotes}\n";
echo "Quotes Unchanged/Skipped: {$skipped_quotes}\n";
