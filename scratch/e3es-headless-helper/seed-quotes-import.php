<?php
/**
 * Seed Quotes Import
 *
 * One-time import script that creates Quote posts from quotes.csv.
 * Trigger: visit any WP page with ?e3_seed_quotes=1 while logged in as admin.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'admin_init', 'e3_seed_quotes' );
function e3_seed_quotes() {
    if ( empty( $_GET['e3_seed_quotes'] ) || $_GET['e3_seed_quotes'] !== '1' ) {
        return;
    }

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Unauthorized' );
    }

    // Prevent timeout on large imports
    set_time_limit( 300 );

    $quotes_csv_path = plugin_dir_path( __FILE__ ) . 'quotes.csv';
    $speakers_csv_path = plugin_dir_path( __FILE__ ) . 'speakers.csv';

    if ( ! file_exists( $quotes_csv_path ) ) {
        wp_die( 'quotes.csv not found in plugin directory.' );
    }

    // 1. Parse speakers.csv to build a name-to-info map
    $speakers_map = array();
    if ( file_exists( $speakers_csv_path ) ) {
        if ( ( $handle = fopen( $speakers_csv_path, 'r' ) ) !== false ) {
            $header = fgetcsv( $handle ); // Skip header
            while ( ( $data = fgetcsv( $handle ) ) !== false ) {
                if ( count( $data ) >= 2 ) {
                    $name = trim( $data[0] );
                    $title = trim( $data[1] );
                    $portrait_link = isset( $data[2] ) ? trim( $data[2] ) : '';
                    $speakers_map[ $name ] = array(
                        'title' => $title,
                        'portrait_file' => basename( $portrait_link )
                    );
                }
            }
            fclose( $handle );
        }
    }

    // 2. Read quotes.csv and create quotes
    $created = 0;
    $skipped = 0;
    $errors = array();

    if ( ( $handle = fopen( $quotes_csv_path, 'r' ) ) !== false ) {
        $header = fgetcsv( $handle ); // Expected: Score,Name of the Person,Quote,Video Title,Link to Video,Timestamp
        
        while ( ( $data = fgetcsv( $handle ) ) !== false ) {
            if ( count( $data ) < 6 ) {
                continue;
            }

            $score = trim( $data[0] );
            $person_name = trim( $data[1] );
            $quote_text = trim( $data[2] );
            $video_title = trim( $data[3] );
            $video_link = trim( $data[4] );
            $timestamp = trim( $data[5] );

            if ( empty( $person_name ) || empty( $quote_text ) ) {
                continue;
            }

            // A. Check if quote already exists for this quote text
            $existing_quotes = get_posts( array(
                'post_type'   => 'quotes',
                'meta_query'  => array(
                    array(
                        'key'     => '_e3_quote_quote',
                        'value'   => $quote_text,
                        'compare' => '='
                    )
                ),
                'numberposts' => 1,
                'post_status' => 'any'
            ) );

            if ( ! empty( $existing_quotes ) ) {
                $skipped++;
                continue;
            }

            // B. Find or create the linked person post
            $person_id = 0;

            // Search for existing person in employees or people custom post types
            $person_posts = get_posts( array(
                'post_type'   => array( 'people', 'employees' ),
                'title'       => $person_name,
                'numberposts' => 1,
                'post_status' => 'any'
            ) );

            if ( ! empty( $person_posts ) ) {
                $person_id = $person_posts[0]->ID;
            } else {
                // Determine their title and portrait from speakers map or defaults
                if ( isset( $speakers_map[ $person_name ] ) ) {
                    $person_title = $speakers_map[ $person_name ]['title'];
                    $portrait_file = $speakers_map[ $person_name ]['portrait_file'];
                } else {
                    $person_title = 'Representative';
                    $portrait_file = '';
                }

                // Create a new post in the 'people' custom post type
                $new_person_post_id = wp_insert_post( array(
                    'post_type'   => 'people',
                    'post_title'  => $person_name,
                    'post_status' => 'publish'
                ) );

                if ( ! is_wp_error( $new_person_post_id ) ) {
                    $person_id = $new_person_post_id;
                    update_post_meta( $person_id, '_e3_person_title', $person_title );

                    if ( ! empty( $portrait_file ) ) {
                        $attachment_id = e3_import_local_portrait( $portrait_file, $person_id, $person_name );
                        if ( $attachment_id ) {
                            set_post_thumbnail( $person_id, $attachment_id );
                        }
                    }
                }
            }

            // C. Create the Quote post
            $quote_title = $person_name . ' on "' . $video_title . '"';
            if ( $timestamp && $timestamp !== '[Testimonial]' ) {
                $quote_title .= ' ' . $timestamp;
            }

            $new_quote_id = wp_insert_post( array(
                'post_type'    => 'quotes',
                'post_title'   => $quote_title,
                'post_content' => $quote_text,
                'post_status'  => 'publish'
            ) );

            if ( is_wp_error( $new_quote_id ) ) {
                $errors[] = "Failed inserting quote for {$person_name}: " . $new_quote_id->get_error_message();
                continue;
            }

            // Set quote custom fields
            update_post_meta( $new_quote_id, '_e3_quote_person_id', intval( $person_id ) );
            update_post_meta( $new_quote_id, '_e3_quote_quote', $quote_text );
            update_post_meta( $new_quote_id, '_e3_quote_score', intval( $score ) );
            update_post_meta( $new_quote_id, '_e3_quote_video_title', $video_title );
            update_post_meta( $new_quote_id, '_e3_quote_video_link', $video_link );
            update_post_meta( $new_quote_id, '_e3_quote_timestamp', $timestamp );

            $created++;
        }
        fclose( $handle );
    }

    // Output results
    wp_die(
        "<h2>Quotes Seeding Complete</h2>" .
        "<p><strong>Created quotes:</strong> {$created}</p>" .
        "<p><strong>Skipped (already exists):</strong> {$skipped}</p>" .
        ( ! empty( $errors ) ? "<p><strong>Errors:</strong><br>" . implode( '<br>', $errors ) . "</p>" : '' ) .
        "<p><a href='" . admin_url( 'edit.php?post_type=quotes' ) . "'>View Quotes &rarr;</a></p>",
        'Quotes Import Results',
        array( 'back_link' => true )
    );
}

/**
 * Helper to import local portrait images into WP Media Library
 */
if ( ! function_exists( 'e3_import_local_portrait' ) ) {
    function e3_import_local_portrait( $filename, $post_id, $desc = '' ) {
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

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
}
