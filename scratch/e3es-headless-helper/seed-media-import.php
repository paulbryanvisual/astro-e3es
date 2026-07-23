<?php
/**
 * E3 Media Import Utility
 * 
 * Imports all images from the Astro public/images directory into the WordPress Media Library.
 * Triggered by visiting: http://e3es2026.local/?e3_import_media=1
 * 
 * This file is included by e3es-headless-helper.php
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'init', 'e3_media_import_handler' );
function e3_media_import_handler() {
    if ( ! isset( $_GET['e3_import_media'] ) || $_GET['e3_import_media'] !== '1' ) {
        return;
    }

    // Only admins can run this (skip auth check on local dev)
    $is_local = ( strpos( $_SERVER['HTTP_HOST'] ?? '', '.local' ) !== false || $_SERVER['HTTP_HOST'] === 'localhost' );
    if ( ! $is_local && ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Unauthorized. Please log in as an admin first.' );
    }

    // Prevent timeout for large imports
    set_time_limit( 600 );

    // Require necessary WordPress files for media handling
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    // Source directory - Astro public/images
    $astro_images_dir = '/Users/bryanpaul/Local Sites/astro-e3es/public/images';

    if ( ! is_dir( $astro_images_dir ) ) {
        wp_die( 'Source images directory not found: ' . $astro_images_dir );
    }

    header( 'Content-Type: text/plain; charset=utf-8' );
    ob_implicit_flush( true );

    echo "=== E3 Media Library Import ===\n\n";

    // Get all existing media to avoid duplicates
    $existing = array();
    $existing_query = new WP_Query( array(
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ) );
    foreach ( $existing_query->posts as $att_id ) {
        $filename = basename( get_attached_file( $att_id ) );
        $existing[ $filename ] = $att_id;
    }
    echo "Found " . count( $existing ) . " existing media attachments.\n\n";

    // Collect all image files recursively
    $image_files = array();
    $extensions  = array( 'jpg', 'jpeg', 'png', 'webp', 'svg', 'gif' );
    $iterator    = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator( $astro_images_dir, RecursiveDirectoryIterator::SKIP_DOTS )
    );

    foreach ( $iterator as $file ) {
        if ( ! $file->isFile() ) continue;
        $ext = strtolower( $file->getExtension() );
        if ( in_array( $ext, $extensions ) ) {
            $image_files[] = $file->getPathname();
        }
    }

    echo "Found " . count( $image_files ) . " image files to process.\n\n";

    $imported  = 0;
    $skipped   = 0;
    $errors    = 0;
    $map       = array(); // filename => WP URL mapping

    foreach ( $image_files as $filepath ) {
        $filename = basename( $filepath );

        // Skip if already exists in media library
        if ( isset( $existing[ $filename ] ) ) {
            $url = wp_get_attachment_url( $existing[ $filename ] );
            $map[ $filename ] = $url;
            $skipped++;
            continue;
        }

        // Determine the relative path for organizing (flickr subdir, etc.)
        $relative = str_replace( $astro_images_dir . '/', '', $filepath );
        $subdir   = dirname( $relative );

        // Build a descriptive title from the filename
        $title = pathinfo( $filename, PATHINFO_FILENAME );
        // Convert dashes/underscores to spaces for readability
        $title_clean = str_replace( array( '-', '_' ), ' ', $title );
        $title_clean = ucwords( $title_clean );

        // Copy file to temp location for WordPress to handle
        $tmp_file = wp_tempnam( $filename );
        if ( ! copy( $filepath, $tmp_file ) ) {
            echo "[ERROR] Could not copy: $relative\n";
            $errors++;
            continue;
        }

        // Detect mime type
        $mime_types = array(
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'webp' => 'image/webp',
            'svg'  => 'image/svg+xml',
            'gif'  => 'image/gif',
        );
        $ext  = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
        $mime = isset( $mime_types[ $ext ] ) ? $mime_types[ $ext ] : 'image/jpeg';

        $file_array = array(
            'name'     => $filename,
            'tmp_name' => $tmp_file,
            'type'     => $mime,
            'error'    => 0,
            'size'     => filesize( $tmp_file ),
        );

        // Build post data with metadata baked into the title/description
        $post_data = array(
            'post_title'   => $title_clean,
            'post_content' => 'Source: ' . $relative,
            'post_excerpt' => ( $subdir !== '.' ) ? 'Album: ' . str_replace( '/', ' > ', $subdir ) : '',
        );

        // Import into media library
        $attachment_id = media_handle_sideload( $file_array, 0, $title_clean, $post_data );

        if ( is_wp_error( $attachment_id ) ) {
            echo "[ERROR] $relative: " . $attachment_id->get_error_message() . "\n";
            @unlink( $tmp_file );
            $errors++;
            continue;
        }

        // Store the subfolder as alt text metadata for easy searching
        if ( $subdir !== '.' ) {
            update_post_meta( $attachment_id, '_wp_attachment_image_alt', $subdir . ' - ' . $title_clean );
        } else {
            update_post_meta( $attachment_id, '_wp_attachment_image_alt', $title_clean );
        }

        $url = wp_get_attachment_url( $attachment_id );
        $map[ $filename ] = $url;

        $imported++;
        if ( $imported % 25 === 0 ) {
            echo "  ... imported $imported images so far ...\n";
        }
    }

    echo "\n=== Import Complete ===\n";
    echo "Imported: $imported\n";
    echo "Skipped (already exist): $skipped\n";
    echo "Errors: $errors\n";
    echo "Total in library: " . ( $imported + $skipped ) . "\n\n";

    // Save the URL mapping as a JSON option for the block seeder to use
    update_option( 'e3_media_url_map', $map );
    echo "URL mapping saved to wp_options as 'e3_media_url_map' (" . count( $map ) . " entries).\n";

    // Also output a sample of mappings
    echo "\n--- Sample URL Mappings ---\n";
    $sample = array_slice( $map, 0, 10, true );
    foreach ( $sample as $fname => $url ) {
        echo "  $fname => $url\n";
    }

    exit;
}
