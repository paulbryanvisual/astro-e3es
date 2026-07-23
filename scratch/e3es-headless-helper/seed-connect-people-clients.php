<?php
/**
 * Seed Connect People to Clients
 *
 * Automatically links posts in 'people' Custom Post Type to corresponding posts
 * in 'clients' Custom Post Type based on titles, roles, and associated quotes.
 * Creates client posts if they are missing.
 *
 * Trigger: visit any WP page with ?e3_connect_people=1 while logged in as admin.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'admin_init', 'e3_connect_people_to_clients' );
function e3_connect_people_to_clients() {
    if ( empty( $_GET['e3_connect_people'] ) || $_GET['e3_connect_people'] !== '1' ) {
        return;
    }

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Unauthorized' );
    }

    // Prevent timeout
    set_time_limit( 300 );

    // Get all people CPT posts
    $people_posts = get_posts( array(
        'post_type'   => 'people',
        'post_status' => 'any',
        'numberposts' => -1,
    ) );

    // Mapping keywords to standard Client names
    $mapping_keywords = array(
        'Boyd ISD'                  => 'Boyd ISD',
        'Boyd'                      => 'Boyd ISD',
        'Bryan ISD'                 => 'Bryan ISD',
        'Bryan'                     => 'Bryan ISD',
        'Carrizo Springs CISD'      => 'Carrizo Springs CISD',
        'Carrizo Springs'           => 'Carrizo Springs CISD',
        'Donna ISD'                 => 'Donna ISD',
        'Donna'                     => 'Donna ISD',
        'Edcouch-Elsa ISD'          => 'Edcouch-Elsa ISD',
        'Edcouch-Elsa'              => 'Edcouch-Elsa ISD',
        'Granbury ISD'              => 'Granbury ISD',
        'Granbury'                  => 'Granbury ISD',
        'Highland Park ISD'         => 'Highland Park ISD',
        'Highland Park'             => 'Highland Park ISD',
        'Keene ISD'                 => 'Keene ISD',
        'Keene'                     => 'Keene ISD',
        'Little Elm ISD'            => 'Little Elm ISD',
        'Little Elm'                => 'Little Elm ISD',
        'Plano ISD'                 => 'Plano ISD',
        'Plano'                     => 'Plano ISD',
        'Port Neches-Groves'        => 'Port Neches-Groves ISD',
        'San Jacinto'               => 'San Jacinto Community College',
        'Stockdale'                 => 'City of Stockdale',
        'Mercedes ISD'              => 'Mercedes ISD',
        'Mercedes'                  => 'Mercedes ISD',
        'Caldwell'                  => 'Caldwell ISD',
        'Seguin ISD'                => 'Seguin ISD',
        'Seguin'                    => 'Seguin ISD',
        'Trenton'                   => 'Trenton ISD',
        'Trenton ISD'               => 'Trenton ISD',
        'Goodall-Witcher'           => 'Goodall-Witcher Healthcare',
        'Houston Community College' => 'Houston Community College',
        'HCC'                       => 'Houston Community College',
        'GWH'                       => 'Goodall-Witcher Healthcare',
    );

    $connected_count = 0;
    $created_clients_count = 0;
    $skipped_count = 0;
    $log_messages = array();

    foreach ( $people_posts as $person ) {
        $person_id = $person->ID;
        $person_name = $person->post_title;

        // 1. Get current role/title metadata
        $person_title = get_post_meta( $person_id, '_e3_person_title', true );

        // 2. Fetch associated quotes to look at video titles
        $quotes = get_posts( array(
            'post_type'   => 'quotes',
            'post_status' => 'any',
            'meta_key'    => '_e3_quote_person_id',
            'meta_value'  => $person_id,
            'numberposts' => -1,
        ) );

        $quote_video_titles = array();
        foreach ( $quotes as $quote ) {
            $video_title = get_post_meta( $quote->ID, '_e3_quote_video_title', true );
            if ( ! empty( $video_title ) ) {
                $quote_video_titles[] = $video_title;
            }
        }

        // Combine all descriptive text
        $search_corpus = $person_name . ' | ' . $person_title . ' | ' . implode( ' | ', $quote_video_titles );

        // 3. Match against keywords
        $matched_client_name = '';
        foreach ( $mapping_keywords as $keyword => $standard_name ) {
            if ( stripos( $search_corpus, $keyword ) !== false ) {
                $matched_client_name = $standard_name;
                break;
            }
        }

        if ( empty( $matched_client_name ) ) {
            $skipped_count++;
            $log_messages[] = "⚠️ Could not determine client for <strong>" . esc_html( $person_name ) . "</strong> (Title: " . esc_html( $person_title ?: 'None' ) . ")";
            continue;
        }

        // 4. Find or create the Client post
        $client_post = get_page_by_path( sanitize_title( $matched_client_name ), OBJECT, 'clients' );
        if ( ! $client_post ) {
            // Try fallback title search
            $client_posts = get_posts( array(
                'post_type'   => 'clients',
                'title'       => $matched_client_name,
                'post_status' => 'any',
                'numberposts' => 1,
            ) );
            if ( ! empty( $client_posts ) ) {
                $client_post = $client_posts[0];
            }
        }

        if ( ! $client_post ) {
            // Create the missing client post
            $new_client_id = wp_insert_post( array(
                'post_type'   => 'clients',
                'post_title'  => $matched_client_name,
                'post_status' => 'publish',
            ) );

            if ( ! is_wp_error( $new_client_id ) ) {
                $client_id = $new_client_id;
                $created_clients_count++;
                $log_messages[] = "✨ Created new client post: <strong>" . esc_html( $matched_client_name ) . "</strong>";
            } else {
                $log_messages[] = "❌ Failed to create client <strong>" . esc_html( $matched_client_name ) . "</strong>: " . $new_client_id->get_error_message();
                continue;
            }
        } else {
            $client_id = $client_post->ID;
        }

        // 5. Update the meta field linking person to client
        update_post_meta( $person_id, '_e3_person_client_id', intval( $client_id ) );
        $connected_count++;
        $log_messages[] = "🔗 Connected <strong>" . esc_html( $person_name ) . "</strong> (Title: " . esc_html( $person_title ) . ") to client <strong>" . esc_html( $matched_client_name ) . "</strong>";
    }

    // Prepare variables for the clean HTML output template
    $total_people = count( $people_posts );
    $results_html = "<h2>People to Clients Connection Results</h2>" .
        "<p><strong>Total People Checked:</strong> {$total_people}</p>" .
        "<p><strong>Connected:</strong> {$connected_count}</p>" .
        "<p><strong>Missing Clients Created:</strong> {$created_clients_count}</p>" .
        "<p><strong>Unmatched/Skipped:</strong> {$skipped_count}</p>" .
        "<hr />" .
        "<h3>Log Detail</h3>" .
        "<ul><li>" . implode( '</li><li>', $log_messages ) . "</li></ul>" .
        "<p><a href='" . admin_url( 'edit.php?post_type=people' ) . "'>&larr; Back to People</a></p>";

    wp_die( $results_html, 'People connection Seeding Results', array( 'back_link' => true ) );
}
