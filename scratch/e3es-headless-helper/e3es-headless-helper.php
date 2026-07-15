<?php
/**
 * Plugin Name: E3 Headless Helper
 * Description: Programmatically registers Clients and Employees custom post types and custom fields, and exposes them to the REST API and WPGraphQL.
 * Version: 1.1.0
 * Author: Paul Bryan
 * Author URI: https://www.prettygrand.com
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Register Custom Post Types
add_action( 'init', 'e3_register_headless_post_types' );

// Enable classic menus and wide alignments for block layouts
add_action( 'after_setup_theme', function() {
    add_theme_support( 'menus' );
    add_theme_support( 'align-wide' );
} );

// Register REST API for Menus
add_action( 'rest_api_init', function () {
    register_rest_route( 'e3es/v1', '/menu', array(
        'methods' => 'GET',
        'callback' => 'e3es_get_menu',
        'permission_callback' => '__return_true'
    ) );
} );

function e3es_get_menu( $request ) {
    $menu_name = $request->get_param( 'name' ) ?: 'Header Menu';
    $menu_items = wp_get_nav_menu_items( $menu_name );
    if ( ! $menu_items ) {
        return new WP_Error( 'no_menu', 'Invalid menu', array( 'status' => 404 ) );
    }
    
    // Build hierarchy
    $menu_list = array();
    foreach ( $menu_items as $item ) {
        if ( empty( $item->menu_item_parent ) ) {
            $item->child_items = array();
            $menu_list[ $item->ID ] = $item;
        }
    }
    foreach ( $menu_items as $item ) {
        if ( ! empty( $item->menu_item_parent ) ) {
            if ( isset( $menu_list[ $item->menu_item_parent ] ) ) {
                $menu_list[ $item->menu_item_parent ]->child_items[] = $item;
            }
        }
    }
    return array_values( $menu_list );
}
function e3_register_headless_post_types() {
    // Clients Custom Post Type
    $client_labels = array(
        'name'               => _x( 'Clients', 'post type general name' ),
        'singular_name'      => _x( 'Client', 'post type singular name' ),
        'menu_name'          => _x( 'Clients', 'admin menu' ),
        'name_admin_bar'     => _x( 'Client', 'add new on admin bar' ),
        'add_new'            => _x( 'Add New', 'client' ),
        'add_new_item'       => __( 'Add New Client' ),
        'new_item'           => __( 'New Client' ),
        'edit_item'          => __( 'Edit Client' ),
        'view_item'          => __( 'View Client' ),
        'all_items'          => __( 'All Clients' ),
        'search_items'       => __( 'Search Clients' ),
        'parent_item_colon'  => __( 'Parent Clients:' ),
        'not_found'          => __( 'No clients found.' ),
        'not_found_in_trash' => __( 'No clients found in Trash.' )
    );

    $client_args = array(
        'labels'             => $client_labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'clients' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-portfolio',
        'supports'           => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
        'show_in_rest'       => true, // Required for Gutenberg and REST API
        'show_in_graphql'    => true, // Required for WPGraphQL
        'graphql_single_name' => 'client',
        'graphql_plural_name'=> 'clients',
    );

    register_post_type( 'clients', $client_args );

    // Services Custom Post Type
    $service_labels = array(
        'name'               => _x( 'Services', 'post type general name' ),
        'singular_name'      => _x( 'Service', 'post type singular name' ),
        'menu_name'          => _x( 'Services', 'admin menu' ),
        'name_admin_bar'     => _x( 'Service', 'add new on admin bar' ),
        'add_new'            => _x( 'Add New', 'service' ),
        'add_new_item'       => __( 'Add New Service' ),
        'new_item'           => __( 'New Service' ),
        'edit_item'          => __( 'Edit Service' ),
        'view_item'          => __( 'View Service' ),
        'all_items'          => __( 'All Services' ),
        'search_items'       => __( 'Search Services' ),
        'parent_item_colon'  => __( 'Parent Services:' ),
        'not_found'          => __( 'No services found.' ),
        'not_found_in_trash' => __( 'No services found in Trash.' )
    );

    $service_args = array(
        'labels'             => $service_labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'services' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => true,
        'menu_position'      => 6,
        'menu_icon'          => 'dashicons-admin-tools',
        'supports'           => array( 'title', 'editor', 'thumbnail', 'custom-fields', 'page-attributes' ),
        'show_in_rest'       => true, // Required for Gutenberg and REST API
        'show_in_graphql'    => true, // Required for WPGraphQL
        'graphql_single_name' => 'service',
        'graphql_plural_name'=> 'services',
    );

    register_post_type( 'services', $service_args );

    // Employees Custom Post Type
    $employee_labels = array(
        'name'               => _x( 'Employees', 'post type general name' ),
        'singular_name'      => _x( 'Employee', 'post type singular name' ),
        'menu_name'          => _x( 'Employees', 'admin menu' ),
        'name_admin_bar'     => _x( 'Employee', 'add new on admin bar' ),
        'add_new'            => _x( 'Add New', 'employee' ),
        'add_new_item'       => __( 'Add New Employee' ),
        'new_item'           => __( 'New Employee' ),
        'edit_item'          => __( 'Edit Employee' ),
        'view_item'          => __( 'View Employee' ),
        'all_items'          => __( 'All Employees' ),
        'search_items'       => __( 'Search Employees' ),
        'parent_item_colon'  => __( 'Parent Employees:' ),
        'not_found'          => __( 'No employees found.' ),
        'not_found_in_trash' => __( 'No employees found in Trash.' )
    );

    $employee_args = array(
        'labels'             => $employee_labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'employees' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 7,
        'menu_icon'          => 'dashicons-businessman',
        'supports'           => array( 'title', 'editor', 'thumbnail', 'custom-fields', 'page-attributes' ),
        'show_in_rest'       => true, // Required for Gutenberg and REST API
        'show_in_graphql'    => true, // Required for WPGraphQL
        'graphql_single_name' => 'employee',
        'graphql_plural_name'=> 'employees',
    );

    register_post_type( 'employees', $employee_args );

    // People Custom Post Type
    $people_labels = array(
        'name'               => _x( 'People', 'post type general name' ),
        'singular_name'      => _x( 'Person', 'post type singular name' ),
        'menu_name'          => _x( 'People', 'admin menu' ),
        'name_admin_bar'     => _x( 'Person', 'add new on admin bar' ),
        'add_new'            => _x( 'Add New', 'person' ),
        'add_new_item'       => __( 'Add New Person' ),
        'new_item'           => __( 'New Person' ),
        'edit_item'          => __( 'Edit Person' ),
        'view_item'          => __( 'View Person' ),
        'all_items'          => __( 'All People' ),
        'search_items'       => __( 'Search People' ),
        'parent_item_colon'  => __( 'Parent People:' ),
        'not_found'          => __( 'No people found.' ),
        'not_found_in_trash' => __( 'No people found in Trash.' )
    );

    $people_args = array(
        'labels'             => $people_labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'people' ),
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 8,
        'menu_icon'          => 'dashicons-groups',
        'supports'           => array( 'title', 'thumbnail', 'custom-fields' ),
        'show_in_rest'       => true,
        'show_in_graphql'    => true,
        'graphql_single_name' => 'person',
        'graphql_plural_name'=> 'people',
    );

    register_post_type( 'people', $people_args );

    // Quotes Custom Post Type
    $quote_labels = array(
        'name'               => _x( 'Quotes', 'post type general name' ),
        'singular_name'      => _x( 'Quote', 'post type singular name' ),
        'menu_name'          => _x( 'Quotes', 'admin menu' ),
        'name_admin_bar'     => _x( 'Quote', 'add new on admin bar' ),
        'add_new'            => _x( 'Add New', 'quote' ),
        'add_new_item'       => __( 'Add New Quote' ),
        'new_item'           => __( 'New Quote' ),
        'edit_item'          => __( 'Edit Quote' ),
        'view_item'          => __( 'View Quote' ),
        'all_items'          => __( 'All Quotes' ),
        'search_items'       => __( 'Search Quotes' ),
        'parent_item_colon'  => __( 'Parent Quotes:' ),
        'not_found'          => __( 'No quotes found.' ),
        'not_found_in_trash' => __( 'No quotes found in Trash.' )
    );

    $quote_args = array(
        'labels'             => $quote_labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'quotes' ),
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 9,
        'menu_icon'          => 'dashicons-format-quote',
        'supports'           => array( 'title', 'custom-fields' ),
        'show_in_rest'       => true,
        'show_in_graphql'    => true,
        'graphql_single_name' => 'quote',
        'graphql_plural_name'=> 'quotes',
    );

    register_post_type( 'quotes', $quote_args );

    // Register Native Taxonomies for Clients
    register_taxonomy( 'industry', 'clients', array(
        'labels'             => array(
            'name'          => __( 'Industries' ),
            'singular_name' => __( 'Industry' ),
        ),
        'hierarchical'       => true,
        'show_in_rest'       => true,
        'show_admin_column'  => true,
        'show_in_graphql'    => true,
        'graphql_single_name'=> 'industry',
        'graphql_plural_name'=> 'industries',
    ) );

    register_taxonomy( 'region', 'clients', array(
        'labels'             => array(
            'name'          => __( 'Regions' ),
            'singular_name' => __( 'Region' ),
        ),
        'hierarchical'       => true,
        'show_in_rest'       => true,
        'show_admin_column'  => true,
        'show_in_graphql'    => true,
        'graphql_single_name'=> 'region',
        'graphql_plural_name'=> 'regions',
    ) );

    register_taxonomy( 'client-services', 'clients', array(
        'labels'             => array(
            'name'          => __( 'Services Provided' ),
            'singular_name' => __( 'Service Provided' ),
        ),
        'hierarchical'       => true,
        'show_in_rest'       => true,
        'show_admin_column'  => true,
        'show_in_graphql'    => true,
        'graphql_single_name'=> 'clientService',
        'graphql_plural_name'=> 'clientServices',
    ) );

    // Seed default terms for native taxonomies
    $regions = array(
        'panhandle'    => 'Far West Texas',
        'west'         => 'West Texas',
        'north'        => 'North Texas',
        'northeast'    => 'North East Texas',
        'southeast'    => 'South East Texas',
        'central'      => 'Central Texas',
        'hill-country' => 'Hill Country',
        'south'        => 'South Texas',
    );
    foreach ( $regions as $slug => $name ) {
        if ( ! term_exists( $slug, 'region' ) ) {
            wp_insert_term( $name, 'region', array( 'slug' => $slug ) );
        }
    }

    $industry_options = array(
        'k12-schools'     => 'K-12 Schools',
        'higher-education'=> 'Higher Education',
        'municipal'       => 'Municipal',
        'healthcare'      => 'Healthcare',
    );
    foreach ( $industry_options as $slug => $name ) {
        if ( ! term_exists( $slug, 'industry' ) ) {
            wp_insert_term( $name, 'industry', array( 'slug' => $slug ) );
        }
    }

    $services_options = array(
        'hvac'                  => 'HVAC',
        'lighting'              => 'Lighting',
        'water-plumbing'        => 'Water & Plumbing',
        'building-controls'     => 'Building Controls',
        'building-envelope'     => 'Building Envelope',
        'energy-infrastructure' => 'Energy Infrastructure',
    );
    foreach ( $services_options as $slug => $name ) {
        $term = term_exists( $slug, 'client-services' );
        if ( ! $term ) {
            $term = wp_insert_term( $name, 'client-services', array( 'slug' => $slug ) );
        }
        $term_id = is_array( $term ) ? $term['term_id'] : intval( $term );

        if ( $term_id ) {
            // Check if matching services post exists
            $posts = get_posts( array(
                'post_type'   => 'services',
                'name'        => $slug,
                'post_status' => 'any',
                'numberposts' => 1
            ) );
            if ( ! empty( $posts ) ) {
                $post_id = $posts[0]->ID;
            } else {
                // Create new matching service page
                $post_id = wp_insert_post( array(
                    'post_title'   => $name,
                    'post_name'    => $slug,
                    'post_type'    => 'services',
                    'post_status'  => 'publish',
                    'post_content' => '<!-- wp:paragraph --><p>Turnkey solutions for ' . esc_html($name) . '.</p><!-- /wp:paragraph -->'
                ) );
                // Add defaults for newly created service cards
                if ( $post_id ) {
                    update_post_meta( $post_id, '_e3_service_image', '/images/hvac.jpg' );
                    update_post_meta( $post_id, '_e3_service_excerpt', 'Turnkey engineering and installation services for ' . esc_html($name) . '.' );
                }
            }
            if ( $post_id ) {
                update_term_meta( $term_id, '_e3_service_page_id', $post_id );
            }
        }
    }
}


// Page type taxonomy and ensure-default-terms removed as services is now a separate Custom Post Type

// Register Meta Fields
add_action( 'init', 'e3_register_headless_meta' );
function e3_register_headless_meta() {
    // Term Meta for client-services taxonomy
    register_term_meta( 'client-services', '_e3_service_page_id', array(
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'integer',
        'description'  => 'Linked post ID in services post type'
    ) );

    // Services Meta Fields
    register_post_meta( 'services', '_e3_service_image', array(
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'string',
        'description'  => 'Image URL for the service grid card',
        'auth_callback' => function() { return current_user_can( 'edit_posts' ); }
    ) );

    register_post_meta( 'services', 'service_excerpt', array(
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
    ) );

    // Register Cross Post Parent Meta for multiple post types
    $post_types = ['page', 'services', 'clients'];
    foreach ($post_types as $pt) {
        register_post_meta( $pt, 'cross_post_parent', array(
            'show_in_rest' => true,
            'single' => true,
            'type' => 'string',
        ) );
    }

    register_post_meta( 'services', '_e3_service_excerpt', array(
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'string',
        'description'  => 'Short description excerpt for the service grid card',
        'auth_callback' => function() { return current_user_can( 'edit_posts' ); }
    ) );

    // Clients Meta Fields
    register_post_meta( 'clients', '_e3_client_region', array(
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'string',
        'description'  => 'Region of the client project',
        'auth_callback' => function() { return current_user_can( 'edit_posts' ); }
    ) );

    register_post_meta( 'clients', '_e3_client_industry', array(
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'string',
        'description'  => 'Industry sector of the client',
        'auth_callback' => function() { return current_user_can( 'edit_posts' ); }
    ) );

    register_post_meta( 'clients', '_e3_client_services', array(
        'show_in_rest' => array(
            'schema' => array(
                'type'  => 'array',
                'items' => array(
                    'type' => 'string'
                ),
            ),
        ),
        'single'       => true,
        'type'         => 'array',
        'description'  => 'Services provided to the client',
        'auth_callback' => function() { return current_user_can( 'edit_posts' ); }
    ) );

    register_post_meta( 'clients', '_e3_client_project_url', array(
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'string',
        'description'  => 'External URL of the client project',
        'auth_callback' => function() { return current_user_can( 'edit_posts' ); }
    ) );

    register_post_meta( 'clients', '_e3_client_year', array(
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'string',
        'description'  => 'Completion year of the client project',
        'auth_callback' => function() { return current_user_can( 'edit_posts' ); }
    ) );

    register_post_meta( 'clients', '_e3_client_contract', array(
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'string',
        'description'  => 'Contract type of the client project',
        'auth_callback' => function() { return current_user_can( 'edit_posts' ); }
    ) );

    register_post_meta( 'clients', '_e3_client_location', array(
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'string',
        'description'  => 'Location city of the client project',
        'auth_callback' => function() { return current_user_can( 'edit_posts' ); }
    ) );

    register_post_meta( 'clients', '_e3_client_scope', array(
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'string',
        'description'  => 'Raw scope text of the client project',
        'auth_callback' => function() { return current_user_can( 'edit_posts' ); }
    ) );

    register_post_meta( 'clients', '_e3_client_logo', array(
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'string',
        'description'  => 'Client logo URL',
        'auth_callback' => function() { return current_user_can( 'edit_posts' ); }
    ) );

    register_post_meta( 'clients', '_e3_client_focal_point_x', array(
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'string',
        'description'  => 'Focal Point X coordinate (0 to 1)',
        'auth_callback' => function() { return current_user_can( 'edit_posts' ); }
    ) );

    register_post_meta( 'clients', '_e3_client_focal_point_y', array(
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'string',
        'description'  => 'Focal Point Y coordinate (0 to 1)',
        'auth_callback' => function() { return current_user_can( 'edit_posts' ); }
    ) );

    register_post_meta( 'clients', '_e3_client_show_in_index', array(
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'boolean',
        'description'  => 'Show client in listing index page',
        'auth_callback' => function() { return current_user_can( 'edit_posts' ); }
    ) );


    // Employees Meta Fields
    register_post_meta( 'employees', '_e3_employee_role', array(
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'string',
        'description'  => 'Job role/title of the employee',
        'auth_callback' => function() { return current_user_can( 'edit_posts' ); }
    ) );

    register_post_meta( 'employees', '_e3_employee_email', array(
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'string',
        'description'  => 'Email address of the employee',
        'auth_callback' => function() { return current_user_can( 'edit_posts' ); }
    ) );

    register_post_meta( 'employees', '_e3_employee_order', array(
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'integer',
        'description'  => 'Sorting order of the employee',
        'auth_callback' => function() { return current_user_can( 'edit_posts' ); }
    ) );

    register_post_meta( 'employees', '_e3_employee_division', array(
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'string',
        'description'  => 'Division/department of the employee',
        'auth_callback' => function() { return current_user_can( 'edit_posts' ); }
    ) );

    register_post_meta( 'employees', '_e3_employee_tagline', array(
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'string',
        'description'  => 'Short tagline for the employee',
        'auth_callback' => function() { return current_user_can( 'edit_posts' ); }
    ) );

    // People Meta Fields
    register_post_meta( 'people', '_e3_person_title', array(
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'string',
        'description'  => 'Job title or role of the person',
        'auth_callback' => function() { return current_user_can( 'edit_posts' ); }
    ) );

    register_post_meta( 'people', '_e3_person_client_id', array(
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'integer',
        'description'  => 'Linked Client post ID',
        'auth_callback' => function() { return current_user_can( 'edit_posts' ); }
    ) );

    // Quotes Meta Fields
    register_post_meta( 'quotes', '_e3_quote_person_id', array(
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'integer',
        'description'  => 'Connected People post ID',
        'auth_callback' => function() { return current_user_can( 'edit_posts' ); }
    ) );

    register_post_meta( 'quotes', '_e3_quote_quote', array(
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'string',
        'description'  => 'The quote text',
        'auth_callback' => function() { return current_user_can( 'edit_posts' ); }
    ) );

    register_post_meta( 'quotes', '_e3_quote_service', array(
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'string',
        'description'  => 'Service category for the quote',
        'auth_callback' => function() { return current_user_can( 'edit_posts' ); }
    ) );

    register_post_meta( 'quotes', '_e3_quote_industry', array(
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'string',
        'description'  => 'Industry vertical for the quote',
        'auth_callback' => function() { return current_user_can( 'edit_posts' ); }
    ) );

    register_post_meta( 'quotes', '_e3_quote_region', array(
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'string',
        'description'  => 'Geographic region for the quote',
        'auth_callback' => function() { return current_user_can( 'edit_posts' ); }
    ) );

    register_post_meta( 'quotes', '_e3_quote_keyword', array(
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'string',
        'description'  => 'Keyword tag for filtering quotes',
        'auth_callback' => function() { return current_user_can( 'edit_posts' ); }
    ) );

    register_post_meta( 'quotes', '_e3_quote_score', array(
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'integer',
        'description'  => 'Quote Score',
        'auth_callback' => function() { return current_user_can( 'edit_posts' ); }
    ) );

    register_post_meta( 'quotes', '_e3_quote_video_title', array(
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'string',
        'description'  => 'Video Title',
        'auth_callback' => function() { return current_user_can( 'edit_posts' ); }
    ) );

    register_post_meta( 'quotes', '_e3_quote_video_link', array(
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'string',
        'description'  => 'Link to Video',
        'auth_callback' => function() { return current_user_can( 'edit_posts' ); }
    ) );

    register_post_meta( 'quotes', '_e3_quote_timestamp', array(
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'string',
        'description'  => 'Quote Timestamp',
        'auth_callback' => function() { return current_user_can( 'edit_posts' ); }
    ) );
}

// Expose Meta Fields to WPGraphQL
add_action( 'graphql_register_types', 'e3_register_graphql_meta_fields' );
function e3_register_graphql_meta_fields() {
    if ( ! function_exists( 'register_graphql_field' ) ) {
        return;
    }

    // Clients GraphQL Fields
    register_graphql_field( 'Client', 'region', array(
        'type'        => 'String',
        'description' => __( 'Region of the client project' ),
        'resolve'     => function( $post ) {
            return get_post_meta( $post->databaseId, '_e3_client_region', true );
        }
    ) );

    register_graphql_field( 'Client', 'yearCompleted', array(
        'type'        => 'String',
        'description' => __( 'Year completed of the client project' ),
        'resolve'     => function( $post ) {
            return get_post_meta( $post->databaseId, '_e3_client_year', true );
        }
    ) );

    register_graphql_field( 'Client', 'contractType', array(
        'type'        => 'String',
        'description' => __( 'Contract type of the client project' ),
        'resolve'     => function( $post ) {
            return get_post_meta( $post->databaseId, '_e3_client_contract', true );
        }
    ) );

    register_graphql_field( 'Client', 'location', array(
        'type'        => 'String',
        'description' => __( 'Location city of the client project' ),
        'resolve'     => function( $post ) {
            return get_post_meta( $post->databaseId, '_e3_client_location', true );
        }
    ) );

    register_graphql_field( 'Client', 'scope', array(
        'type'        => 'String',
        'description' => __( 'General scope of the client project' ),
        'resolve'     => function( $post ) {
            return get_post_meta( $post->databaseId, '_e3_client_scope', true );
        }
    ) );

    register_graphql_field( 'Client', 'industry', array(
        'type'        => 'String',
        'description' => __( 'Industry sector of the client' ),
        'resolve'     => function( $post ) {
            return get_post_meta( $post->databaseId, '_e3_client_industry', true );
        }
    ) );

    register_graphql_field( 'Client', 'services', array(
        'type'        => array( 'list_of' => 'String' ),
        'description' => __( 'Services provided to the client' ),
        'resolve'     => function( $post ) {
            $val = get_post_meta( $post->databaseId, '_e3_client_services', true );
            return is_array( $val ) ? $val : array();
        }
    ) );

    register_graphql_field( 'Client', 'projectUrl', array(
        'type'        => 'String',
        'description' => __( 'External URL of the client project' ),
        'resolve'     => function( $post ) {
            return get_post_meta( $post->databaseId, '_e3_client_project_url', true );
        }
    ) );

    register_graphql_field( 'Client', 'clientLogo', array(
        'type'        => 'String',
        'description' => __( 'Client logo URL' ),
        'resolve'     => function( $post ) {
            return get_post_meta( $post->databaseId, '_e3_client_logo', true );
        }
    ) );

    register_graphql_field( 'Client', 'focalPointX', array(
        'type'        => 'String',
        'description' => __( 'Focal Point X coordinate (0 to 1)' ),
        'resolve'     => function( $post ) {
            return get_post_meta( $post->databaseId, '_e3_client_focal_point_x', true );
        }
    ) );

    register_graphql_field( 'Client', 'focalPointY', array(
        'type'        => 'String',
        'description' => __( 'Focal Point Y coordinate (0 to 1)' ),
        'resolve'     => function( $post ) {
            return get_post_meta( $post->databaseId, '_e3_client_focal_point_y', true );
        }
    ) );


    // Services GraphQL Fields
    register_graphql_field( 'Service', 'serviceImage', array(
        'type' => 'String',
        'description' => __( 'Image filename for the service', 'e3es' ),
        'resolve' => function( $post ) {
            return get_post_meta( $post->ID, 'service_image', true );
        }
    ) );
    
    register_graphql_field( 'Service', 'serviceExcerpt', array(
        'type' => 'String',
        'description' => __( 'Excerpt for the service', 'e3es' ),
        'resolve' => function( $post ) {
            return get_post_meta( $post->ID, 'service_excerpt', true );
        }
    ) );
    
    // Cross Post Type Relationships (Breadcrumbs)
    $cross_post_types = ['Page', 'Service', 'Client'];
    foreach ($cross_post_types as $type) {
        register_graphql_field( $type, 'crossPostParent', array(
            'type' => 'String', // Can be an ID or a string identifier
            'description' => __( 'Parent post ID across different post types for breadcrumb generation.', 'e3es' ),
            'resolve' => function( $post ) {
                return get_post_meta( $post->ID, 'cross_post_parent', true );
            }
        ) );
    }

    // Employees GraphQL Fields
    register_graphql_field( 'Employee', 'role', array(
        'type'        => 'String',
        'description' => __( 'Job role/title of the employee' ),
        'resolve'     => function( $post ) {
            return get_post_meta( $post->databaseId, '_e3_employee_role', true );
        }
    ) );

    register_graphql_field( 'Employee', 'email', array(
        'type'        => 'String',
        'description' => __( 'Email address of the employee' ),
        'resolve'     => function( $post ) {
            return get_post_meta( $post->databaseId, '_e3_employee_email', true );
        }
    ) );

    register_graphql_field( 'Employee', 'order', array(
        'type'        => 'Int',
        'description' => __( 'Sorting order of the employee' ),
        'resolve'     => function( $post ) {
            return (int) get_post_meta( $post->databaseId, '_e3_employee_order', true );
        }
    ) );

    register_graphql_field( 'Employee', 'division', array(
        'type'        => 'String',
        'description' => __( 'Division/department of the employee' ),
        'resolve'     => function( $post ) {
            return get_post_meta( $post->databaseId, '_e3_employee_division', true );
        }
    ) );

    register_graphql_field( 'Employee', 'tagline', array(
        'type'        => 'String',
        'description' => __( 'Short tagline for the employee' ),
        'resolve'     => function( $post ) {
            return get_post_meta( $post->databaseId, '_e3_employee_tagline', true );
        }
    ) );

    // People GraphQL Fields
    register_graphql_field( 'Person', 'personTitle', array(
        'type'        => 'String',
        'description' => __( 'Job title or role of the person' ),
        'resolve'     => function( $post ) {
            return get_post_meta( $post->databaseId, '_e3_person_title', true );
        }
    ) );

    register_graphql_field( 'Person', 'clientId', array(
        'type'        => 'Int',
        'description' => __( 'Linked Client post ID' ),
        'resolve'     => function( $post ) {
            $val = get_post_meta( $post->databaseId, '_e3_person_client_id', true );
            return $val ? (int) $val : null;
        }
    ) );

    // Quotes GraphQL Fields
    register_graphql_field( 'Quote', 'personId', array(
        'type'        => 'Int',
        'description' => __( 'Connected People post ID' ),
        'resolve'     => function( $post ) {
            return (int) get_post_meta( $post->databaseId, '_e3_quote_person_id', true );
        }
    ) );

    register_graphql_field( 'Quote', 'quote', array(
        'type'        => 'String',
        'description' => __( 'The quote text' ),
        'resolve'     => function( $post ) {
            return get_post_meta( $post->databaseId, '_e3_quote_quote', true );
        }
    ) );

    register_graphql_field( 'Quote', 'quoteService', array(
        'type'        => 'String',
        'description' => __( 'Service category for the quote' ),
        'resolve'     => function( $post ) {
            return get_post_meta( $post->databaseId, '_e3_quote_service', true );
        }
    ) );

    register_graphql_field( 'Quote', 'quoteIndustry', array(
        'type'        => 'String',
        'description' => __( 'Industry vertical for the quote' ),
        'resolve'     => function( $post ) {
            return get_post_meta( $post->databaseId, '_e3_quote_industry', true );
        }
    ) );

    register_graphql_field( 'Quote', 'quoteRegion', array(
        'type'        => 'String',
        'description' => __( 'Geographic region for the quote' ),
        'resolve'     => function( $post ) {
            return get_post_meta( $post->databaseId, '_e3_quote_region', true );
        }
    ) );

    register_graphql_field( 'Quote', 'keyword', array(
        'type'        => 'String',
        'description' => __( 'Keyword tag for filtering quotes' ),
        'resolve'     => function( $post ) {
            return get_post_meta( $post->databaseId, '_e3_quote_keyword', true );
        }
    ) );

    register_graphql_field( 'Quote', 'score', array(
        'type'        => 'Int',
        'description' => __( 'Quote Score' ),
        'resolve'     => function( $post ) {
            return (int) get_post_meta( $post->databaseId, '_e3_quote_score', true );
        }
    ) );

    register_graphql_field( 'Quote', 'videoTitle', array(
        'type'        => 'String',
        'description' => __( 'Video Title' ),
        'resolve'     => function( $post ) {
            return get_post_meta( $post->databaseId, '_e3_quote_video_title', true );
        }
    ) );

    register_graphql_field( 'Quote', 'videoLink', array(
        'type'        => 'String',
        'description' => __( 'Link to Video' ),
        'resolve'     => function( $post ) {
            return get_post_meta( $post->databaseId, '_e3_quote_video_link', true );
        }
    ) );

    register_graphql_field( 'Quote', 'timestamp', array(
        'type'        => 'String',
        'description' => __( 'Quote Timestamp' ),
        'resolve'     => function( $post ) {
            return get_post_meta( $post->databaseId, '_e3_quote_timestamp', true );
        }
    ) );
}
// Enqueue Gutenberg block editor scripts
add_action( 'enqueue_block_editor_assets', 'e3_enqueue_block_editor_assets' );
function e3_enqueue_block_editor_assets() {
    $dir_path = plugin_dir_path( __FILE__ );

    $editor_scripts_path = $dir_path . 'editor-scripts.js';
    $editor_scripts_ver  = file_exists( $editor_scripts_path ) ? filemtime( $editor_scripts_path ) : '1.0.0';

    $fa_icons_path = $dir_path . 'fa-icons.js';
    $fa_icons_ver  = file_exists( $fa_icons_path ) ? filemtime( $fa_icons_path ) : '1.0.0';

    $editor_blocks_path = $dir_path . 'editor-blocks.js';
    $editor_blocks_ver  = file_exists( $editor_blocks_path ) ? filemtime( $editor_blocks_path ) : '1.0.0';

    $editor_styles_path = $dir_path . 'editor-styles.css';
    $editor_styles_ver  = file_exists( $editor_styles_path ) ? filemtime( $editor_styles_path ) : '1.0.0';

    wp_enqueue_script(
        'e3-editor-scripts',
        plugin_dir_url( __FILE__ ) . 'editor-scripts.js',
        array( 'wp-blocks', 'wp-dom-ready', 'wp-edit-post', 'wp-data', 'wp-notices' ),
        $editor_scripts_ver
    );

    $js = "
    (function() {
        if (typeof wp === 'undefined' || typeof wp.data === 'undefined') return;

        let wasSaving = false;
        let pollInterval = null;
        let noticeId = 'astro-build-status';

        function checkBuildStatus() {
            fetch('/wp-json/e3es/v1/build-status')
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'in_progress' || data.status === 'queued') {
                        wp.data.dispatch('core/notices').createNotice(
                            'info', 
                            '⏳ Astro build in progress on Cloudflare...', 
                            { id: noticeId, isDismissible: false, type: 'snackbar' }
                        );
                    } else {
                        clearInterval(pollInterval);
                        pollInterval = null;
                        wp.data.dispatch('core/notices').removeNotice(noticeId);
                        wp.data.dispatch('core/notices').createNotice(
                            'success', 
                            '✅ Astro build complete! Your changes are now live.', 
                            { id: noticeId + '-done', isDismissible: true, type: 'snackbar' }
                        );
                    }
                })
                .catch(err => {
                    clearInterval(pollInterval);
                    pollInterval = null;
                });
        }

        wp.data.subscribe(() => {
            const editor = wp.data.select('core/editor');
            if (!editor) return;

            const isSavingPost = editor.isSavingPost();
            const isAutosavingPost = editor.isAutosavingPost();

            if (isSavingPost && !isAutosavingPost) {
                wasSaving = true;
            } else if (wasSaving && !isSavingPost) {
                wasSaving = false;
                
                if (!pollInterval) {
                    wp.data.dispatch('core/notices').createNotice(
                        'info', 
                        '🚀 Astro build triggered. Checking status...', 
                        { id: noticeId, isDismissible: false, type: 'snackbar' }
                    );
                    
                    setTimeout(() => {
                        checkBuildStatus();
                        pollInterval = setInterval(checkBuildStatus, 5000);
                    }, 4000);
                }
            }
        });

        // Log iframe body classes for diagnostics
        function logIframeClasses() {
            const iframe = document.querySelector('iframe[name=editor-canvas]');
            if (iframe && iframe.contentDocument && iframe.contentDocument.body) {
                const outerBodyClasses = document.body.className;
                const iframeBodyClasses = iframe.contentDocument.body.className;
                fetch('/get_post_type.php?log=' + encodeURIComponent('OUTER: ' + outerBodyClasses + ' | IFRAME: ' + iframeBodyClasses));
            } else {
                setTimeout(logIframeClasses, 1000);
            }
        }
        if (document.readyState === 'complete') {
            setTimeout(logIframeClasses, 2000);
        } else {
            window.addEventListener('load', () => {
                setTimeout(logIframeClasses, 2000);
            });
        }
    })();
    ";
    wp_add_inline_script( 'e3-editor-scripts', $js );

    wp_enqueue_script(
        'e3-fa-icons',
        plugins_url( 'fa-icons.js', __FILE__ ),
        array(),
        $fa_icons_ver,
        true
    );

    wp_enqueue_script(
        'e3-editor-blocks',
        plugins_url( 'editor-blocks.js', __FILE__ ),
        array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-plugins', 'wp-edit-post', 'wp-data', 'wp-dom-ready', 'wp-server-side-render', 'wp-api-fetch', 'e3-fa-icons' ),
        $editor_blocks_ver,
        true
    );

    wp_enqueue_style(
        'e3-editor-styles',
        plugins_url( 'editor-styles.css', __FILE__ ),
        array(),
        $editor_styles_ver
    );

    wp_enqueue_style(
        'e3-google-fonts',
        'https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;500;700;900&display=swap',
        array(),
        null
    );
}

// Load editor styles inside the Gutenberg editor canvas iframe
add_filter( 'block_editor_settings_all', 'e3es_add_editor_styles_to_iframe', 10, 2 );
function e3es_add_editor_styles_to_iframe( $editor_settings, $editor_context ) {
    $editor_styles_file = plugin_dir_path( __FILE__ ) . 'editor-styles.css';
    if ( file_exists( $editor_styles_file ) ) {
        $editor_settings['editorStyles'][] = array(
            'css' => file_get_contents( $editor_styles_file )
        );
    }
    return $editor_settings;
}

// Register E3ES custom inline arrow button styles for Gutenberg Block Styles panel
add_action( 'init', 'e3es_register_inline_arrow_button_styles' );
function e3es_register_inline_arrow_button_styles() {
    register_block_style(
        'core/button',
        array(
            'name'  => 'e3es-button-inline-green-arrow',
            'label' => __( 'Inline Green Arrow', 'e3es' ),
        )
    );

    register_block_style(
        'core/button',
        array(
            'name'  => 'e3es-button-inline-orange-arrow',
            'label' => __( 'Inline Orange Arrow', 'e3es' ),
        )
    );

    register_block_style(
        'core/list',
        array(
            'name'  => 'grid-2-col',
            'label' => __( '2-Column Grid', 'e3es' ),
        )
    );

    register_block_style(
        'core/list',
        array(
            'name'  => 'grid-3-col',
            'label' => __( '3-Column Grid', 'e3es' ),
        )
    );

    register_block_style(
        'core/button',
        array(
            'name'  => 'e3es-button-inline-white-arrow',
            'label' => __( 'Inline White Arrow (dark bg)', 'e3es' ),
        )
    );

    // Skew-Arrow image style: applies the two-column skew + double-arrow clip-path effect
    register_block_style(
        'core/image',
        array(
            'name'  => 'skew-arrow',
            'label' => __( 'Skew Arrow', 'e3es' ),
        )
    );

    // Normal Bullets style: reverts list cards back to traditional bullet points
    register_block_style(
        'core/list',
        array(
            'name'  => 'normal-bullets',
            'label' => __( 'Normal Bullets', 'e3es' ),
        )
    );
}

// Register dynamic block texas-interactive-map in PHP
add_action( 'init', 'e3_register_texas_map_block' );
function e3_register_texas_map_block() {
    $regions = array(
        array( 'attr' => 'panhandle',   'label' => 'Far West Texas' ),
        array( 'attr' => 'west',        'label' => 'West Texas' ),
        array( 'attr' => 'north',       'label' => 'North Texas' ),
        array( 'attr' => 'northeast',   'label' => 'North East Texas' ),
        array( 'attr' => 'southeast',   'label' => 'South East Texas' ),
        array( 'attr' => 'central',     'label' => 'Central Texas' ),
        array( 'attr' => 'hillCountry', 'label' => 'Hill Country' ),
        array( 'attr' => 'south',       'label' => 'South Texas' ),
    );

    $defaults = array(
        'panhandle'   => array(
            'headline' => 'Far West Texas',
            'text'     => 'From El Paso to the Permian Basin, E3 delivers energy solutions to school districts and municipalities in the far western reaches of Texas.',
            'photo'    => 'https://www.e3es.com/next/images/clients/Prosper-ISD-after-0217-Edit-768x575.jpg'
        ),
        'west'        => array(
            'headline' => 'West Texas',
            'text'     => 'Serving the wide-open spaces of West Texas with innovative HVAC, lighting, and water treatment solutions for rural communities.',
            'photo'    => 'https://www.e3es.com/next/images/clients/Sanger-Exterior-Resized-768x530.jpg'
        ),
        'north'       => array(
            'headline' => 'North Texas',
            'text'     => 'E3 partners with school districts and cities across the DFW Metroplex and North Texas to modernize aging infrastructure.',
            'photo'    => 'https://www.e3es.com/next/images/clients/55182270675_296ab7a759_k-768x512.jpg'
        ),
        'northeast'   => array(
            'headline' => 'North East Texas',
            'text'     => 'From Tyler to Texarkana, E3 brings turnkey design+build solutions to communities in the Piney Woods region.',
            'photo'    => 'https://www.e3es.com/next/images/clients/51585631196_8cbb6f338f_h-768x512.jpg'
        ),
        'southeast'   => array(
            'headline' => 'South East Texas',
            'text'     => 'Serving the Houston metro and Gulf Coast with comprehensive energy and water infrastructure upgrades.',
            'photo'    => 'https://www.e3es.com/next/images/clients/KOUNTZE-768x514.jpeg'
        ),
        'central'     => array(
            'headline' => 'Central Texas',
            'text'     => 'The E3 home base, delivering transformative projects to Austin, Waco, Temple, and the surrounding communities.',
            'photo'    => 'https://www.e3es.com/next/images/clients/51496446012_7397fcb563_k-768x512.jpg'
        ),
        'hillCountry' => array(
            'headline' => 'Hill Country',
            'text'     => 'From San Antonio to Fredericksburg, E3 provides tailored energy solutions for the Texas Hill Country.',
            'photo'    => 'https://www.e3es.com/next/images/clients/Needville-ISD-photo-768x557.jpg'
        ),
        'south'       => array(
            'headline' => 'South Texas',
            'text'     => 'Partnering with Rio Grande Valley communities to upgrade facilities and reduce energy costs across South Texas.',
            'photo'    => 'https://www.e3es.com/next/images/clients/Carrizo-Springs-8-768x576.jpg'
        ),
    );

    $attrs = array(
        'defaultPhoto'    => array( 'type' => 'string', 'default' => 'http://e3es2026.local/wp-content/uploads/2026/06/Texas-Funding-Solutions-600x400-2.jpg' ),
        'defaultHeadline' => array( 'type' => 'string', 'default' => 'Texas Educational Facility Upgrades' ),
        'defaultText'     => array( 'type' => 'string', 'default' => 'E3 Entegral Solutions is a premier Texas-based energy services company, partnering with public school districts statewide to modernize classrooms, upgrade mechanical systems, improve indoor air quality, and secure funding—all while delivering guaranteed utility savings. Click a region on the map or use the buttons below to explore our local project success stories.' ),
    );

    foreach ( $regions as $r ) {
        $p = $r['attr'];
        $d = isset( $defaults[ $p ] ) ? $defaults[ $p ] : array( 'headline' => $r['label'], 'text' => '', 'photo' => '' );
        $attrs[ $p . 'Photo' ]      = array( 'type' => 'string', 'default' => $d['photo'] );
        $attrs[ $p . 'Headline' ]   = array( 'type' => 'string', 'default' => $d['headline'] );
        $attrs[ $p . 'Text' ]       = array( 'type' => 'string', 'default' => $d['text'] );
        $attrs[ $p . 'LinkPageId' ] = array( 'type' => 'number', 'default' => 0 );
    }

    register_block_type( 'e3es/texas-interactive-map', array(
        'render_callback' => 'e3_render_texas_map',
        'attributes'      => $attrs,
    ) );
}


// ── Dynamic PHP render callbacks for e3es/two-column and e3es/section-icon ──
add_action( 'init', 'e3_register_custom_block_renderers' );
function e3_register_custom_block_renderers() {
    // Two-Column: LEGACY — thin wrapper, inner blocks are direct children (heading/paragraph/buttons)
    register_block_type( 'e3es/two-column', array(
        'render_callback' => 'e3_render_two_column',
        'attributes'      => array(
            'bgStyle'   => array( 'type' => 'string',  'default' => 'white' ),
            'reverse'   => array( 'type' => 'boolean', 'default' => false ),
            'mapSpill'  => array( 'type' => 'boolean', 'default' => false ),
            'listLabel' => array( 'type' => 'string',  'default' => '' ),
            'imageUrl'  => array( 'type' => 'string',  'default' => '' ),
            'imageAlt'  => array( 'type' => 'string',  'default' => '' ),
            'icon'      => array( 'type' => 'string',  'default' => '' ),
        ),
    ) );

    // Two-Column Cover: NEW — thin wrapper, inner blocks use core/columns + core/cover architecture
    register_block_type( 'e3es/two-column-cover', array(
        'render_callback' => 'e3_render_two_column_cover',
        'attributes'      => array(
            'bgStyle'   => array( 'type' => 'string',  'default' => 'white' ),
            'reverse'   => array( 'type' => 'boolean', 'default' => false ),
            'mapSpill'  => array( 'type' => 'boolean', 'default' => false ),
            'listLabel' => array( 'type' => 'string',  'default' => '' ),
        ),
    ) );

    // Section Icon: outputs inline SVG from PHP icon registry
    register_block_type( 'e3es/section-icon', array(
        'render_callback' => 'e3_render_section_icon',
        'attributes'      => array(
            'icon'  => array( 'type' => 'string', 'default' => '' ),
            'size'  => array( 'type' => 'string', 'default' => 'md' ),
            'color' => array( 'type' => 'string', 'default' => 'green' ),
        ),
    ) );

    // FAQ Section: thin wrapper — PHP outputs the section/container, $content = rendered inner blocks
    register_block_type( 'e3es/faq-section', array(
        'render_callback' => 'e3_render_faq_section',
    ) );

    // Mini Testimonial Callout: dynamic block — supports manual content or linked testimonial post
    register_block_type( 'e3es/mini-testimonial', array(
        'render_callback' => 'e3_render_mini_testimonial',
        'attributes'      => array(
            'layout'        => array( 'type' => 'string',  'default' => 'callout' ),
            'mode'          => array( 'type' => 'string',  'default' => 'manual' ),
            'testimonialId' => array( 'type' => 'integer', 'default' => 0 ),
            'quote'         => array( 'type' => 'string',  'default' => '' ),
            'cite'          => array( 'type' => 'string',  'default' => '' ),
            'photoUrl'      => array( 'type' => 'string',  'default' => '' ),
            'caseStudyUrl'  => array( 'type' => 'string',  'default' => '' ),
            'caseStudyText' => array( 'type' => 'string',  'default' => 'Read Case Study' ),
            'bgStyle'       => array( 'type' => 'string',  'default' => 'white' ),
        ),
    ) );

    // Testimonial Picker: dynamic block — server-rendered from selected testimonial ID
    register_block_type( 'e3es/testimonial-picker', array(
        'render_callback' => 'e3_render_testimonial_picker',
        'attributes'      => array(
            'testimonialId' => array( 'type' => 'integer', 'default' => 0 ),
        ),
    ) );


    // Team Directory: dynamic block — server-rendered list of all employees
    register_block_type( 'e3es/team-directory', array(
        'render_callback' => 'e3_render_team_directory',
        'attributes'      => array(),
    ) );

    // Full-Width Testimonial: wide blockquote with avatar + optional case study link
    register_block_type( 'e3es/full-width-testimonial', array(
        'render_callback' => 'e3_render_full_width_testimonial',
        'attributes'      => array(
            'quote'         => array( 'type' => 'string', 'default' => '' ),
            'byline'        => array( 'type' => 'string', 'default' => '' ),
            'photoUrl'      => array( 'type' => 'string', 'default' => '' ),
            'caseStudyUrl'  => array( 'type' => 'string', 'default' => '' ),
            'caseStudyText' => array( 'type' => 'string', 'default' => 'Read Case Study' ),
            'bgStyle'       => array( 'type' => 'string', 'default' => 'white' ),
        ),
    ) );

    // Rep Contact Card: "Meet [Name]" regional rep sidebar card
    register_block_type( 'e3es/rep-contact-card', array(
        'render_callback' => 'e3_render_rep_contact_card',
        'attributes'      => array(
            'name'       => array( 'type' => 'string', 'default' => '' ),
            'role'       => array( 'type' => 'string', 'default' => '' ),
            'bio'        => array( 'type' => 'string', 'default' => '' ),
            'photoUrl'   => array( 'type' => 'string', 'default' => '' ),
            'emailLabel' => array( 'type' => 'string', 'default' => 'Email' ),
            'emailHref'  => array( 'type' => 'string', 'default' => '' ),
            'callLabel'  => array( 'type' => 'string', 'default' => 'Schedule a Call' ),
            'callHref'   => array( 'type' => 'string', 'default' => '' ),
        ),
    ) );

    // Region Showcase: thin InnerBlocks wrapper — scrollable client card section
    register_block_type( 'e3es/region-showcase', array(
        'render_callback' => 'e3_render_region_showcase',
        'attributes'      => array(
            'heading' => array( 'type' => 'string', 'default' => 'Featured Projects' ),
            'bgStyle' => array( 'type' => 'string', 'default' => 'white' ),
        ),
    ) );

    // Services Grid: dynamic block — manual picker or auto taxonomy query
    register_block_type( 'e3es/services-grid', array(
        'render_callback' => 'e3_render_services_grid',
        'attributes'      => array(
            'mode'        => array( 'type' => 'string',  'default' => 'manual' ),
            'selectedIds' => array(
                'type'    => 'array',
                'default' => array(),
                'items'   => array( 'type' => 'integer' ),
            ),
            'parentId'    => array( 'type' => 'integer', 'default' => 0 ),
            'limit'       => array( 'type' => 'integer', 'default' => 4 ),
            'orderBy'     => array( 'type' => 'string',  'default' => 'menu_order' ),
        ),
    ) );

    // Clients Grid: dynamic block — manual picker or auto taxonomy query
    register_block_type( 'e3es/clients-grid', array(
        'render_callback' => 'e3_render_clients_grid',
        'attributes'      => array(
            'mode'        => array( 'type' => 'string',  'default' => 'manual' ),
            'selectedIds' => array(
                'type'    => 'array',
                'default' => array(),
                'items'   => array( 'type' => 'integer' ),
            ),
            'taxonomy'    => array( 'type' => 'string',  'default' => '' ),
            'termSlug'    => array( 'type' => 'string',  'default' => '' ),
            'limit'       => array( 'type' => 'integer', 'default' => 4 ),
            'orderBy'     => array( 'type' => 'string',  'default' => 'title' ),
        ),
    ) );

    // Client Finder: dynamic block with filters, search, and Texas SVG map
    register_block_type( 'e3es/client-finder', array(
        'render_callback' => 'e3_render_client_finder',
        'attributes'      => array(
            'onlyShowFeatured'   => array( 'type' => 'boolean', 'default' => true ),
            'showRegionFilter'   => array( 'type' => 'boolean', 'default' => true ),
            'showIndustryFilter' => array( 'type' => 'boolean', 'default' => true ),
            'showServiceFilter'  => array( 'type' => 'boolean', 'default' => true ),
            'showSearchFilter'   => array( 'type' => 'boolean', 'default' => true ),
            'showMap'            => array( 'type' => 'boolean', 'default' => true ),
            'showCardTags'       => array( 'type' => 'boolean', 'default' => true ),
        ),
    ) );
}

// ── Services Grid render ─────────────────────────────────────────────────────
function e3_render_services_grid( $attrs ) {
    $mode         = $attrs['mode']        ?? 'manual';
    $selected_ids = $attrs['selectedIds'] ?? array();
    $parent_id    = (int) ( $attrs['parentId'] ?? 0 );
    $limit        = (int) ( $attrs['limit']    ?? 4 );
    $order_by     = $attrs['orderBy']     ?? 'menu_order';

    $query_args = array(
        'post_type'      => 'services',
        'post_status'    => 'publish',
        'posts_per_page' => $limit,
    );

    if ( $mode === 'manual' && ! empty( $selected_ids ) ) {
        $query_args['post__in'] = array_map( 'intval', $selected_ids );
        $query_args['orderby']  = 'post__in';
        $query_args['posts_per_page'] = count( $selected_ids );
    } else {
        if ( $parent_id ) {
            $query_args['post_parent'] = $parent_id;
        }
        switch ( $order_by ) {
            case 'title':
                $query_args['orderby'] = 'title';
                $query_args['order']   = 'ASC';
                break;
            case 'date':
                $query_args['orderby'] = 'date';
                $query_args['order']   = 'DESC';
                break;
            default:
                $query_args['orderby'] = 'menu_order';
                $query_args['order']   = 'ASC';
                break;
        }
    }

    $posts = get_posts( $query_args );
    if ( empty( $posts ) ) {
        return '';
    }

    $html = '<section class="services"><div class="services__container"><div class="services__grid">';

    foreach ( $posts as $post ) {
        $title     = esc_html( $post->post_title );
        $excerpt   = esc_html( get_post_meta( $post->ID, 'service_excerpt', true ) );
        $thumb_url = get_the_post_thumbnail_url( $post->ID, 'medium_large' );
        $image_meta = get_post_meta( $post->ID, '_e3_service_image', true );
        $permalink = get_permalink( $post->ID );

        // Prefer featured image, fall back to service_image meta
        $image_url = $thumb_url ?: $image_meta;

        if ( $permalink ) {
            $html .= '<a href="' . esc_url( $permalink ) . '" class="services__card">';
        } else {
            $html .= '<div class="services__card">';
        }
        if ( $image_url ) {
            $html .= '<img src="' . esc_url( $image_url ) . '" alt="' . $title . '" class="services__card-image" />';
        }
        $html .= '<div class="services__card-content">';
        $html .= '<h3 class="services__card-title">' . $title . '</h3>';
        if ( $excerpt ) {
            $html .= '<p class="services__card-text">' . $excerpt . '</p>';
        }
        if ( $permalink ) {
            $html .= '<span class="services__card-link">Learn More →</span>';
        }
        $html .= '</div>';
        if ( $permalink ) {
            $html .= '</a>';
        } else {
            $html .= '</div>';
        }
    }

    $html .= '</div></div></section>';
    return $html;
}

// ── Clients Grid render ──────────────────────────────────────────────────────
function e3_render_clients_grid( $attrs ) {
    $mode         = $attrs['mode']        ?? 'manual';
    $selected_ids = $attrs['selectedIds'] ?? array();
    $taxonomy     = $attrs['taxonomy']    ?? '';
    $term_slug    = $attrs['termSlug']    ?? '';
    $limit        = (int) ( $attrs['limit']    ?? 4 );
    $order_by     = $attrs['orderBy']     ?? 'title';

    $query_args = array(
        'post_type'      => 'clients',
        'post_status'    => 'publish',
        'posts_per_page' => $limit,
    );

    if ( $mode === 'manual' && ! empty( $selected_ids ) ) {
        $query_args['post__in'] = array_map( 'intval', $selected_ids );
        $query_args['orderby']  = 'post__in';
        $query_args['posts_per_page'] = count( $selected_ids );
    } else {
        if ( $taxonomy && $term_slug ) {
            $query_args['tax_query'] = array(
                array(
                    'taxonomy' => sanitize_key( $taxonomy ),
                    'field'    => 'slug',
                    'terms'    => sanitize_title( $term_slug ),
                ),
            );
        }
        switch ( $order_by ) {
            case 'date':
                $query_args['orderby'] = 'date';
                $query_args['order']   = 'DESC';
                break;
            default:
                $query_args['orderby'] = 'title';
                $query_args['order']   = 'ASC';
                break;
        }
    }

    $posts = get_posts( $query_args );
    if ( empty( $posts ) ) {
        return '';
    }

    $html = '<section class="clients-grid"><div class="clients-grid__container"><div class="clients-grid__grid">';

    foreach ( $posts as $post ) {
        $title     = esc_html( $post->post_title );
        $thumb_url = get_the_post_thumbnail_url( $post->ID, 'medium_large' );
        $logo_url  = get_post_meta( $post->ID, '_e3_client_logo', true );
        $permalink = get_permalink( $post->ID );
        $excerpt   = esc_html( wp_trim_words( $post->post_excerpt ?: $post->post_content, 20, '…' ) );
        $focal_x   = get_post_meta( $post->ID, '_e3_client_focal_point_x', true ) ?: '0.5';
        $focal_y   = get_post_meta( $post->ID, '_e3_client_focal_point_y', true ) ?: '0.5';

        $image_url = $thumb_url ?: $logo_url;

        $html .= '<div class="clients-grid__card">';
        if ( $permalink ) {
            $html .= '<a href="' . esc_url( $permalink ) . '" class="clients-grid__card-link">';
        }
        if ( $image_url ) {
            $obj_pos = ( $focal_x * 100 ) . '% ' . ( $focal_y * 100 ) . '%';
            $html .= '<img src="' . esc_url( $image_url ) . '" alt="' . $title . '" class="clients-grid__card-image" style="object-position:' . esc_attr( $obj_pos ) . '" />';
        }
        $html .= '<div class="clients-grid__card-content">';
        $html .= '<h3 class="clients-grid__card-title">' . $title . '</h3>';
        if ( $excerpt ) {
            $html .= '<p class="clients-grid__card-text">' . $excerpt . '</p>';
        }
        $html .= '</div>';
        if ( $permalink ) {
            $html .= '</a>';
        }
        $html .= '</div>';
    }

    $html .= '</div></div></section>';
    return $html;
}

// ── Client Finder dynamic render callback ────────────────────────────────────
function e3_render_client_finder( $attrs ) {
    $only_show_featured   = isset( $attrs['onlyShowFeatured'] ) ? (bool) $attrs['onlyShowFeatured'] : true;
    $show_region_filter   = isset( $attrs['showRegionFilter'] ) ? (bool) $attrs['showRegionFilter'] : true;
    $show_industry_filter = isset( $attrs['showIndustryFilter'] ) ? (bool) $attrs['showIndustryFilter'] : true;
    $show_service_filter  = isset( $attrs['showServiceFilter'] ) ? (bool) $attrs['showServiceFilter'] : true;
    $show_search_filter   = isset( $attrs['showSearchFilter'] ) ? (bool) $attrs['showSearchFilter'] : true;
    $show_map             = isset( $attrs['showMap'] ) ? (bool) $attrs['showMap'] : true;
    $show_card_tags       = isset( $attrs['showCardTags'] ) ? (bool) $attrs['showCardTags'] : true;

    $query_args = array(
        'post_type'      => 'clients',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    );

    if ( $only_show_featured ) {
        $query_args['meta_query'] = array(
            array(
                'key'     => '_e3_client_show_in_index',
                'value'   => '1',
                'compare' => '=',
            ),
        );
    }

    $posts = get_posts( $query_args );

    ob_start();
    ?>
    <section class="clients-finder-section" style="padding: 4rem 2rem; background: var(--color-bg-white);">
        <div class="clients-finder-container" style="max-width: 1400px; margin: 0 auto; display: flex; gap: 3rem; align-items: flex-start; flex-wrap: wrap;">
            
            <!-- Left Sidebar: Map & Filters -->
            <div class="finder-sidebar" style="flex: 0 0 350px; background: var(--color-bg-light); padding: 2rem; border-radius: 0; box-shadow: 0 4px 15px rgba(0,0,0,0.05); position: sticky; top: 100px;">
                <h3 style="font-size: 1.2rem; color: var(--color-primary-dark); margin-bottom: 1rem; border-bottom: 2px solid var(--color-primary-green); padding-bottom: 0.5rem;">Filters</h3>
                
                <?php if ( $show_map ) : ?>
                <label for="region-filter" style="display: block; font-size: 0.9rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--color-text-main);">Region</label>
                <!-- Map -->
                <div style="margin-top: -1rem; margin-left: -2rem; margin-right: -2rem;">
                    <svg id="texas-map-svg" viewBox="0 0 941.76 907.17" class="texas-svg-map" xmlns="http://www.w3.org/2000/svg">
                      <defs>
                        <style>
                          .texas-svg-map { width: 100%; max-width: 650px; height: auto; filter: drop-shadow(0 10px 20px rgba(0,0,0,0.15)); pointer-events: none; }
                          .texas-region { cursor: pointer; pointer-events: all; }
                          .texas-region path, .texas-region polygon, .texas-region rect { transition: all 0.3s ease; }
                          .texas-svg-map.has-active .texas-region:not(.active) path,
                          .texas-svg-map.has-active .texas-region:not(.active) polygon,
                          .texas-svg-map.has-active .texas-region:not(.active) rect { 
                              fill: #11411d !important;
                              transition: fill 0.3s ease;
                          }
                          .texas-region:hover, .texas-region.active { 
                              filter: brightness(1.2) drop-shadow(0 8px 16px rgba(0,0,0,0.5)); 
                              stroke: #ffffff; 
                              stroke-width: 4px; 
                              transform: scale(1.02);
                              transform-origin: center;
                              transform-box: fill-box;
                          }
                          .cls-1 { fill: #2b5434; }
                          .cls-2 { fill: #598e64; }
                          .cls-3 { fill: #31623d; }
                          .cls-4 { fill: #034411; }
                          .cls-5 { fill: #5b8764; }
                          .cls-6 { fill: #65b776; }
                          .cls-7 { fill: #54725a; }
                          .cls-8 { fill: #115620; }
                          .cls-9 { fill: none; }
                        </style>
                      </defs>
                      <g id="Layer_1-2" data-name="Layer 1">
                        <g class="texas-region" data-region="panhandle">
                          <path class="cls-1" d="M625.37,271.22c-3.01-.1-4.59-.27-6.39-3.06-6.58-9.19-16.48,14.39-19.8,2.98.36-12.2-8.33-1.94-8.68-9.28-.05-4.19-1.51-9.2-6.52-5.59-3.56.59-9.11-1.64-12.88-1.13-2.48-.02-1.4,6.05-4.73,4.49-6.32-7.95-15.5-1.81-23.12-6.03-3.75-3.62-10.11-1.56-14.61-3.1-2.33-1.43-.54-4.57-2.19-6.73-2.42-2.19-3.04-7.27-6.69-7.09-4.38-1.98-5.68-3.6-8.22,1.67-2.51.26-2.98-2.32-6.84-.25-10.36,5.8-11.52-9.11-19.29-11.6-1.46-1.04-4.6,0-5.36-1.51-1.15-34.93,1.6-104.83-1.86-133.08-55.21.18-122.84-1.67-173.01-2.19-1.76,103.62-6.17,210.23-9.82,313.06-27.93-.6-84.62-3.23-108.69-3.72-1.97,27.93-1.31,53.4-3.71,83.08-.94,7.07,1.81,8.26-6.01,10.84-4.13,6.35,5.25.95,6.4,18.25,1.89,5.53,5.99,9.43,8.57,14.47-.31,16.55-4.5,15.32,6.36,30.43.88,10.24,10.27,16.23,18.87,20.16,5.92,9.06,14.63,16.01,25.45,18.46,4.36,4.92,10.16,9.4,16.94,9.86,6.97,7.77,25.03,19.67,31.44,5.88,2.02-1.99,3.99-5.7,6.61-7.36,1.98-1,4.46-1.67,5.74-3.68,1.35-2.35-.84-5.87,1.16-8.11,3.61-4.48,3.61-10.47,6.72-15.26,1.02-4.57,4.97-7.46,6.35-11.46,4.79-2.35,10.8-1.55,15.86-2.66,3.34-1.38,3.24-8.25,8.02-4.82,9.84,5.84,10.08,2.93,19.54,5.2,3.29-.09,11.61.56,11.61.56,0,0,.22-29.67,1.17-33.49-.73-5.57,3.61-4.21,5.18-7.68-3.27-7.86,7.46-18.36-1.92-24.22-1.19-.7-3.29.1-3.66-1.63-2.85-13.26.88-20.67-11.7-20.97-2.88-.07-20.83-4.72-20.83-4.72l66.15,1.83s-.1-49.4.39-67.86c34.3-.18,102.16-2.52,134.48-.3,2.9,4.56,12.03,20.41,14.34,24.02,2.55,2.23,6.92-.89,10.06,0,2.14.45,2.61,2.32,4.1,1.84,4.84-3.34,15.89-8.44,19.17-12.65-11.29-23.53-5.92,5.32-22.26-27.27-.57-1.71,1.94-2.31,2.6-4.28.67-4.47.02-10.01.06-14.42-.17-2.93-4-1.41-5.52-2.68-.96-5.37-.47-20.63-.68-25.25.81-3.39,7.13.29,8.44-3.22.39-1.12.68-2.24,2.09-2.13,5.28-.02,20.58.06,26.87-.04.85-.06.81-.77.82-1.45.15-4.35-.61-21.34-.12-28.22,6.3-1.31,17.85-.4,24.51-1.2,1.75-4.97-.61-28.35-.03-34.32.1-.71-.37-1.25-.94-1.39Z"/>
                        </g>
                        <g class="texas-region" data-region="north">
                          <path class="cls-2" d="M715.7,340.75c.36-4.12,1.69-5.37,5.57-5.84,1.48-6.23-.62-23.26-.3-32.15-.08-4.83-.6-20.36-.71-25.83-.19-.88.44-1.53.93-2.02.42-.5-1.48-2.05-2.34-2.56-3.61-3.41-7.48.07-11.45.93-2.91.23-6.38.94-7,4.18-.35,4.14-4.79,1.38-6.9,3.58-1.35,1.52-2.62,4.39-4.28,1.37-2.65-4.05-8.26-4.21-12.26-7.03-1.19-.77-.43-2.2-.76-3.28-.35-1.12-3.93-1.63-5.18-.86-1.54,1.03-1.65,4.85-4.26,3.87-4.66-3.08-5.59.66-8.74-4.9-1.3-2.17-4.08-4.23-5.05-.8-.78,7.99-5.71,3.47-5.98,10.3-.51,3.84-1.69,4.56-4.04.73-.97-2.78.24-6.15-2.12-8.64-1.38-1.95-2.89.78-4.37,1.39-5.14.03-3.57,1.94-7.2,3.64-3.07.59-2.74-4.3-4.53-5.68-3.29-4.21-.01,31.64-.89,32.94-4.75,1.59-14.4.41-19.96,1.01-2.16.37-5.44-.84-5.23,1.43.02,4.36.31,24.01.27,27.87-.44,1.76-3.29.82-4.72,1.04-7.37.39-18.56-.78-24.44.55-.85,6.49-9.09.95-9.18,4.97.06,5.04.25,25.19.33,29.5.4,2.79,6.1-.27,6.56,2.86.12,3.23.41,8.39,0,11.7-.36,2.24-4.94,2.61-3.5,5.11,2.29,3.81,10.39,17.25,12.58,20.86,1.14,2.01,2.94-.7,4.94-.33,1.83.12,2.32,3.51,4.23,2.51,7.58-4.22,20.47-12.87,28.81-18.41,5.53-4.12,4.21.37,8.82,1.79,2.51.79,3.45-1.93,5.27-2.82,5.23-1.76,14.99-4.82,21.11-5.91,2.25,1,8.57,16.85,10.76,13.78,5.57-3.47,23.67-15,30.18-18.8,2.24-1.08,10.23-2.89,18.79-3.7,3.34-.31-.53-29.18.22-32.59.69-1.72,4.3-.7,6.05-1.19,5.1-.98-.59-2.28-.03-4.56Z"/>
                        </g>
                        <g class="texas-region" data-region="central">
                          <path class="cls-3" d="M575.28,479.41c-2.46-3.6.38-12.63-5.57-12.29.67-6.15-3.17-3.02-4.04-11.94-1.13-3.45-4.09-4.67-6.79-5.52-1.36-7.24-3.16-.15-15.86-5.89-2.51-1.35.68-2.94,1.79-3.98,2.93-2.33,11.53-9.07,14.14-11.15,1.97-1.18.24-2.41-.48-3.94-3.57-5.98-12.26-20.64-14.07-23.62.39-2.01-16.92-.35-19.02-.83-38.39,1.28-84.34-2.33-121.68.2-.26,15.98-1.57,69.14-1.57,69.14,0,0-63.94-.62-69.97-1.48-2.05-.82,9.92,4.29,12.94,6.06,4.5,2.43,10.53.8,14.71,3.81,9.41,5.29,4.36,21.47,16.09,23.54,3.01,2.32,1.14,7.24-1.25,9.24-5.01,2.98,1.04,7.64.08,9.37-2.01,1.64-4.75,3.91-4.26,6.82.09,7.57-.04,35.79-.04,35.79,0,0,9.87.53,12.21.68s8.67,1.12,11.69,5.77c1.07,1.65,24.36,23.13,30.23,27.38,9.21,6.17,8.1,2.37,10.55-5.77,2.47-4.74.24-12.11,1.79-17.36,59.39-2.71,33.63,11.21,43.45-28.74.64-2.82,2.45-3.46,4.96-3.03,14.01,1.72,6.67-5.56,9.09-14.73,5.17-1.16,21.01.45,26.7-.46,2.19-19.29-6.57-13.59,19.23-14.68,2.94.13.98-3.89,1.46-7.8-.03-5.38-.18-13.45-.22-18.52.15-1.57-.55-2.76,1.05-2.8,4.21-.17,27.12-.92,32.11-1.14,1.31-.03,1.01-1.42.56-2.16Z"/>
                        </g>
                        <g class="texas-region" data-region="south">
                          <path class="cls-4" d="M647.17,675.13c-3.84-3.51-8.6-1.3-13.52-5.17-.64-3.21,11.36-13.54,5.16-14.76-5.92-2.82-7.06-2.7-9.95-8.46-1.53-.9-5.35-.21-6.96-1.65-3.36-5.91-4.28-8.89-11.58-10.33-4.08-2.99-5.49.53-9.22-.48-3.11-1.92-4.74-5.01-7.34-7.59-.85-1.29-2.97-2.62-2.89-4.17,3.56-3.62,17.42-15.05,24.68-21.5,1.06-1.04,3.06-2.03,1.92-3.11-13.11-13.04.39-10.79-20.74-10.16-5.74.7-1.62-5.45-4.56-7.71-5.22-4.16-14.85-7.17-12.95-15.46-1.08-3.99-6.74-1.39-9.72-2.33-7.95-2.26-19.97,5.75-24.96-3.67-.66-2.61.93-23.35-1.4-23.41-4.08-.15-16.36.36-20.47.29-3.27-.17-1.08-6.99-1.85-9.16-.13-1.1-.5-1.32-1.46-1.36-4.98.27-21.82-.67-27.1.05-2.73,12.22,5.29,16.59-11.8,14.57-3.62,1.78-2.77,7.37-3.81,11.13-2.27,4.63-.43,9.82-2.35,14.53-.62,2.91,3.42,5.87-2.28,5.9-6.86-.38-29.14.08-36.3-.09-1.67-.03-1.57.57-1.66,2.69-.56,5.94.63,13.24-2.06,18.24-6.37,10.99-2.59,9.64,3.17,18.2,1.12,3.04,1.07,8.07,2.74,11.21,4.04,5.2,6.61,11.03,9.32,16.96,6.01,9.96,12.05,21.58,16.4,32.43,4.99,6.46,13.47,12.45,17.27,18.73,4.23,4.66,9.63,12.73,13.18,17.95,2.26,3.72,8.22,2.64,10.77,6.03,1.39,1.71.81,4.65,2.3,6.39,3.72,2.48.5,9.53-.61,12.14,2.23,6.04,7.54,4.47,5.03,13.64.06,3.95,2.2,8.53,4.43,11.78,2.68,5.88,11.54,21.04,13.65,29.09.97,1.58,2.82,2.79,3.04,4.74-.03,3.69,1.72,6.21,5.82,5.73,4.98.14,6.94,2.77,11.64,2.43,4.65.87,7.15,5.8,11.36,7.79,10.91,1.04,20.21,6.74,29.29,12.48,2.32,1.37,5.13.21,7.31.83,1.53.65,3.17.84,4.73.09,6.18-1.97,12.22.96,18.55,1.44,3.63-.43,5.5,2.54,7.56,4.96,2.96,2.34,6.65,4.61,10.04,6.56,9.62,3.46,4.32-4.05,10.09-5.34,2.11-.72,4.6-1.95,6.71-2.01.97-.09,2.17.13,2.78-.8,1.46-4.71.23-9.71-.57-14.45-2.49-10.35-4.7-20.92-8.11-30.49-1.25-12.11-3.94-36.61-4.87-45.59-.45-9.16,6.36-17.22,7.64-26.17,5.29-12.59,10.33-20.51,19.82-30.46,2.22-1.81-16.28-2.66-25.31-3.08Z"/>
                        </g>
                        <g class="texas-region" data-region="west">
                          <path class="cls-5" d="M188.46,399.11c-26.63-2.3-76.43-6.08-101.72-6.87,8.76,16.64,30.03,43.31,42.69,59.33,1.33,2.31,2.06,5.46,4.72,6.55,8.11,2.95,12.22,11.85,15.64,19.25,5.72,7.78,10.3,6.35,17.52,10.85,1.13,1.09,1.05,2.5,2.72,3.27,3.04,1.39,5.39,4.39,8.43,6.01,2.24-.24,7.81-1.26,7.91-4.6,2.35-26.69,2.65-67.01,4.22-93.74,0,0-1.35,0-2.13-.05Z"/>
                        </g>
                        <g class="texas-region" data-region="hill-country">
                          <path class="cls-6" d="M672.48,678.22c13.46-9.72,28.22-22.41,41.81-32.02,10.15-6.35,22.56-10.98,32.23-18.06,1.68-1.69,14.33-7.97,5.07-7.47-22.72-13.6-6.74-7.08-17.69-18.17-1.13-3.88-.64-9.52-3.97-12.4-21.04-24.33.09-18.88-13.11-35.67-1.66-1.15-1.12-3.13-1.56-4.87-.92-2.86,1.16-5.4.25-8.49-.45-3.71-5.51-5.65-4.33-9.5.36-2.47,2.4-3.8,3.63-5.69,2.72-5.75-4.93-6.5-1.26-10.09,2.01-2.17,1.43-4.46-.63-6.23-3.33-6.42-2.97-13.01-3.37-20.23,1.21-7.68-5.92-13.11-5.26-20.51-2.89-6.36-10.24-21.38-.26-24.47,4.48-3.03,21.76-11.36,26.07-15.63.6-4.69-2-4.55-4.66-7.07-1.45-3.45-6.14-3.72-7.33-7.27-.43-3.09-11.6-27.06-14.35-27.57-3.94-1.98-7.56-8.14-12.72-6.39-7.76,4.01-23.92,16.72-31.24,18.24-3.72-2.32-5.95-11.46-10.08-13.29-6.87,1.37-17.13,4.25-23.43,6.63-1.98,2.95-4.16.87-6.28-.66-1.44-.97-2.12-.81-3.79.22-9.86,6.32-21.29,12.68-30.92,18.6-3.84,1.99,2.92,6.2-1.39,7.97-3.45,2.12-9.84,6.26-13.26,8.12-1.57.82-2.43-1.24-3.81-1.26-7.19,3.36-22.72,12.62-28.37,15.52-.83.43-1.68.97-1.74,1.96.02,3.79,4.01,3.65,6.81,4.11,3.1,1.01,5.91,3.71,9.19,2.82.44-.09.93-.09,1.1.36,1.21,5.87,3.71-.65,7.35,8.87,1.16,1.94,2.24,3.98,3.04,6.04.16,6.64,4.18,2.63,5,8.08.05,1.93,2.64,8.31-.49,7.89-37.61,2.53-28.68-10.1-29.35,28.7-.42,2.85-22.53-1.53-20.54,2.93,2,30.29-7.7,24.81,21.44,24.61,1.08-.08,1.93.15,1.89,1.36.12,3.37.31,17.01.42,21.23-.04,3.57,7.2,4.59,9.95,6.22,5.7.68,11.39-3.54,17.12-1.09,1.65.72,3.66.44,5.17.58,1.98,8.38,9.51,12.7,15.7,17.56.74,2.1-.04,6.67,2.93,7.51,14.07.29,10.85-5.19,20.74,7.03.66.89.99,1.24.37,1.94-3.22,2.88-22.52,19.45-26.17,22.66-1.02.7-.28,1.6.38,2.32,18.43,22.01,6.86,4.99,26.07,15.38,2,1.75,2.19,5.26,3.91,6.96,2.75,2.24,8.45.82,9.53,5.51,1.26,3.74,10.09,1.9,8.92,6.63-.7,2.77-9.16,13.12-6.96,14.55,2.63-.09,4.53,1.56,6.65,2.71,3.92.03,7.06.99,10.59,2.63,6.62.15,17.87,0,25-.32ZM577.95,564.78h0s.03-.04.03-.04l.02.02-.04.02h0ZM719.36,577.92h.08v-.17h.27l-.35.36v-.18h0ZM714.27,553.28l-.07.02-.02-.02h.1ZM709,500.18h.12l-.1.1-.02-.1h0ZM700.44,656.41l-.16-.66h.16v.66h0Z"/>
                        </g>
                        <g class="texas-region" data-region="southeast">
                          <path class="cls-7" d="M867.07,461.62c-.95-2.6-1.22-6.24-2.56-8.76-2.65-1.95-3.51-5.24-5.69-7.88-2.35-6.24-3.33-14.65-10.32-17.95-7.47-8.02-20.65-4.73-31.29-5.5-9.1.86-3.53-14.17-12.97-13.24-18.92,1.96-37.87-5.22-24.74,22.06,1.62,2.68.6,5.49-2.43,6.62-5.57,1.62-6.98-6.92-11.43-8.18-10.08-1.8-21.42,1.52-31.33,4.72-2.85.36-.07-5.92-3.61-6.93-8.81,2.9-25.99,14.79-33.39,18.4-7.06,7.2,5.14,29.6,7.35,38.51,2.51,8.35-.21,17.76,4.38,25.73,2.59,6.5-2.34,5.75,2.13,13.86,0,3.67-4.32,6.72-3.76,10.14,1.53,4.71,5.21,8.55,3.57,13.95-1.15,7.4,7.8,9.61,5.22,16.63.06,1.71.77,3.45-.23,5.04-1.13,1.92-.15,3.78-.06,6.25-.46,3.45,1.67,5.88,4.25,7.9,2.26,2.36,3.49,5.41,5.88,7.62,3.28,3.28,2.57,8.31,4.34,12.35.74,1.76,2.05,3.11,2.76,4.53,1.03,1.87,1,4.35,2.77,5.76,11.1,2.99,10.16,12.85,17.29,9.77,11.7-3.13,18-14.61,27.23-21.54,5.9-4.12,13.81-9.49,19.93-13.84,3.82-2.78-.78-5.11,3.19-7.64,13.79-10.38,28.55-17.36,45.98-20.03,1.86-.14,4.1.13,3.81-2.16-.3-3.17-3.69-4.69-5.02-7.19-2.56-10.79,12.74-15.25,11.89-25.78-.48-3.85-.63-8.08-3.14-11.25-2.73-4.82,3.4-6.5.74-15.31,1.1-7.95,7.51-14.57,8.11-22.83.89-4.78-.16-9.24,1.16-13.83ZM808.34,410.84v.17l-.16-.16h.16ZM702.22,474.17c-.22-.03-1.55.68-.57-.2.16-.11.22-.32.3-.03.09.05.25.07.3.17l-.02.06Z"/>
                        </g>
                        <g class="texas-region" data-region="northeast">
                          <path class="cls-8" d="M848.18,422.34c.89-12.68-9.57-17.6-15.25-26.33-3.7-36.34-2.49-70.91-6.57-107.61-6.78-6.62-18.34,4.41-24.48-3.92-4.17-2.02-8.95-3.94-13.61-4.88-2.88-1.94-5.54-3.83-9.19-3.64-3-.71-4.01-4.43-6.75-5.74-4.97-2.43-10.17-7.53-16.13-6.62-13.45,11.6-14.44,1.53-23.84,2.67-5.57,2.63-15.4,4.2-14.62,12.31.69,14.3.16,30.45.97,45.01-.29,2.87,1.29,6.88-.97,9.08-4.02,1.34-5.29,5.52-5.6,9.35-.72,3.07-5.34.93-4.85,5.33-.45,33.97,9.27,30.01-18.7,30.68-1.83.07-3.32.34-2.88,1.55.74,1.69,3.78,2.66,5.62,3.27,19.24,11.06,21.91,32.11,24.93,35.85s13.6,15.96,14.1,17.77c1.76,4.41,7.87-1.1,10.9-1.38,7.93-1.28,16.56-5.46,24.17-1.68,3.79,1.88,7.51,10.12,12.06,7.73,14.19-7.46-.43-19.42,2.17-25.66s19.6-3.2,24.2-4.14c8.26.8,1.61,14.68,11.76,13.37,5.88.31,15.17.85,21.64,1.2,4.42.16,10.05,3.08,10.91-3.57ZM806.42,289.91l.03-.12.08.68-.5-.17.39-.39h0ZM805.84,290.46l-.04.04h-.05s.09-.04.09-.04ZM735.58,267.46l-.14-.1.14-.55v.65h0Z"/>
                        </g>
                        <rect class="cls-9" width="941.76" height="907.17"/>
                      </g>
                    </svg>
                </div>
                <?php endif; ?>

                <!-- Filters -->
                <div class="finder-controls" style="display: flex; flex-direction: column; gap: 1rem; margin-top: 1rem;">
                    
                    <?php if ( $show_region_filter ) : ?>
                    <div class="finder-control">
                        <label for="region-filter" style="display: block; font-size: 0.9rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--color-text-main);">Region</label>
                        <select id="region-filter" style="width: 100%; padding: 0.75rem; border: 1px solid #ccc; border-radius: 0; font-family: 'Raleway', sans-serif; font-size: 1rem; appearance: auto;">
                            <option value="All">All Regions</option>
                            <option value="panhandle">West Texas</option>
                            <option value="west">Far West Texas</option>
                            <option value="north">North Texas</option>
                            <option value="northeast">North East Texas</option>
                            <option value="southeast">South East Texas</option>
                            <option value="central">Central Texas</option>
                            <option value="hill-country">Hill Country</option>
                            <option value="south">South Texas</option>
                        </select>
                    </div>
                    <?php else : ?>
                        <select id="region-filter" style="display: none;"><option value="All">All Regions</option></select>
                    <?php endif; ?>

                    <?php if ( $show_industry_filter ) : ?>
                    <div class="finder-control">
                        <label for="industry-filter" style="display: block; font-size: 0.9rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--color-text-main);">Industry</label>
                        <select id="industry-filter" style="width: 100%; padding: 0.75rem; border: 1px solid #ccc; border-radius: 0; font-family: 'Raleway', sans-serif; font-size: 1rem; appearance: auto;">
                            <option value="All">All Industries</option>
                            <option value="K-12">K-12 Schools</option>
                            <option value="Higher Education">Higher Education</option>
                            <option value="Healthcare">Healthcare</option>
                            <option value="Municipalities">Municipalities</option>
                        </select>
                    </div>
                    <?php else : ?>
                        <select id="industry-filter" style="display: none;"><option value="All">All Industries</option></select>
                    <?php endif; ?>

                    <?php if ( $show_service_filter ) : ?>
                    <div class="finder-control">
                        <label for="service-filter" style="display: block; font-size: 0.9rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--color-text-main);">Service</label>
                        <select id="service-filter" style="width: 100%; padding: 0.75rem; border: 1px solid #ccc; border-radius: 0; font-family: 'Raleway', sans-serif; font-size: 1rem; appearance: auto;">
                            <option value="All">All Services</option>
                            <option value="HVAC">HVAC</option>
                            <option value="Lighting">Lighting</option>
                            <option value="Water & Plumbing">Water & Plumbing</option>
                            <option value="Building Controls">Building Controls</option>
                            <option value="Building Envelope">Building Envelope</option>
                            <option value="Energy Infrastructure">Energy Infrastructure</option>
                        </select>
                    </div>
                    <?php else : ?>
                        <select id="service-filter" style="display: none;"><option value="All">All Services</option></select>
                    <?php endif; ?>

                    <?php if ( $show_search_filter ) : ?>
                    <div class="finder-control">
                        <label for="client-search" style="display: block; font-size: 0.9rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--color-text-main);">Search</label>
                        <input type="text" id="client-search" placeholder="Search clients..." style="width: 100%; padding: 0.75rem; border: 1px solid #ccc; border-radius: 0; font-family: 'Raleway', sans-serif; font-size: 1rem;">
                    </div>
                    <?php else : ?>
                        <input type="hidden" id="client-search" value="">
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Column: Results -->
            <div class="finder-results-wrapper" style="flex: 1; min-width: 300px;">
                <div class="finder-results" id="client-results" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem;">
                    <?php foreach ( $posts as $post ) : 
                        $title = esc_html( $post->post_title );
                        $permalink = get_permalink( $post->ID );
                        $url = wp_make_link_relative( $permalink );
                        if ( strpos( $url, '/' ) !== 0 ) {
                            $url = '/' . $url;
                        }

                        $thumb_url = get_the_post_thumbnail_url( $post->ID, 'medium_large' );
                        $logo_url  = get_post_meta( $post->ID, '_e3_client_logo', true );
                        $image_url = $thumb_url ?: $logo_url;

                        $industries = wp_get_post_terms( $post->ID, 'industry', array( 'fields' => 'names' ) );
                        $industry = ! empty( $industries ) && ! is_wp_error( $industries ) ? $industries[0] : 'Other';

                        $regions = wp_get_post_terms( $post->ID, 'region', array( 'fields' => 'slugs' ) );
                        $region = ! empty( $regions ) && ! is_wp_error( $regions ) ? $regions[0] : 'All';

                        $region_names = wp_get_post_terms( $post->ID, 'region', array( 'fields' => 'names' ) );
                        $region_label = ! empty( $region_names ) && ! is_wp_error( $region_names ) ? $region_names[0] : '';

                        $services = wp_get_post_terms( $post->ID, 'client-services', array( 'fields' => 'names' ) );
                        if ( is_wp_error( $services ) ) {
                            $services = array();
                        }
                    ?>
                        <a href="<?= esc_url( $url ) ?>" class="client-card" 
                           data-name="<?= esc_attr( strtolower( $title ) ) ?>" 
                           data-industry="<?= esc_attr( $industry ) ?>" 
                           data-region="<?= esc_attr( $region ) ?>" 
                           data-services="<?= esc_attr( wp_json_encode( $services ) ) ?>"
                           style="background: white; border-radius: 0; box-shadow: 0 4px 10px rgba(0,0,0,0.05); text-decoration: none; color: inherit; transition: transform 0.2s ease, box-shadow 0.2s ease; overflow: hidden; display: flex; flex-direction: column; justify-content: flex-start; min-height: 150px;">
                            
                            <div style="height: 200px; overflow: hidden; position: relative;">
                                <?php if ( $image_url ) : ?>
                                    <img src="<?= esc_url( $image_url ) ?>" width="600" height="400" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;" class="client-card-img" alt="<?= $title ?>" loading="lazy" />
                                <?php else : ?>
                                    <div style="width: 100%; height: 100%; background: #eee; display: flex; align-items: center; justify-content: center; color: #999;">No Image</div>
                                <?php endif; ?>
                                
                                <?php if ( $show_card_tags ) : ?>
                                <div style="position: absolute; top: 1rem; right: 1rem; display: flex; gap: 0.5rem; flex-wrap: wrap; justify-content: flex-end;">
                                    <span style="font-size: 0.75rem; background: var(--color-bg-light); padding: 0.25rem 0.5rem; border-radius: 0; color: var(--color-text-main); font-weight: 700; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"><?= esc_html( $industry ) ?></span>
                                    <span class="region-label" data-region-val="<?= esc_attr( $region ) ?>" style="font-size: 0.75rem; background: var(--color-primary-green); padding: 0.25rem 0.5rem; border-radius: 0; color: white; font-weight: 700; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"><?= esc_html( $region_label ) ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div style="padding: 1.5rem; text-align: left;">
                                <h3 style="font-size: 1.2rem; color: var(--color-primary-dark); margin-bottom: 0.75rem; text-transform: uppercase;"><?= $title ?></h3>
                                <?php if ( $show_card_tags && ! empty( $services ) ) : ?>
                                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                    <?php foreach ( $services as $s ) : ?>
                                        <span style="font-size: 0.75rem; background: #e8f5e9; padding: 0.25rem 0.5rem; border-radius: 0; color: var(--color-primary-green); border: 1px solid #c8e6c9;"><?= esc_html( $s ) ?></span>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
                <div id="no-results" style="display: none; text-align: center; padding: 3rem; background: var(--color-bg-light); border-radius: 0; color: var(--color-text-main);">
                    <h3>We couldn't find a match.</h3>
                    <p>Try removing some filters.</p>
                    <button class="btn btn--outline" id="clear-filters-btn" style="margin-top: 1rem;">Clear Filters</button>
                </div>
            </div>

        </div>
    </section>

    <script type="text/javascript">
        (function() {
            function initFilters() {
                const searchInput = document.getElementById('client-search');
                const industryFilter = document.getElementById('industry-filter');
                const regionFilter = document.getElementById('region-filter');
                const serviceFilter = document.getElementById('service-filter');
                const resultsContainer = document.getElementById('client-results');
                const clientCards = document.querySelectorAll('.client-card');
                const noResults = document.getElementById('no-results');
                const clearBtn = document.getElementById('clear-filters-btn');
                const mapRegions = document.querySelectorAll('.texas-region');
                const svgMapObj = document.getElementById('texas-map-svg');

                if (!resultsContainer || !clientCards.length) return;

                // Set up hover states
                clientCards.forEach(card => {
                    card.addEventListener('mouseover', () => {
                        card.style.transform = 'translateY(-5px)';
                        card.style.boxShadow = '0 10px 20px rgba(0,0,0,0.1)';
                        const img = card.querySelector('.client-card-img');
                        if(img) img.style.transform = 'scale(1.05)';
                    });
                    card.addEventListener('mouseout', () => {
                        card.style.transform = 'translateY(0)';
                        card.style.boxShadow = '0 4px 10px rgba(0,0,0,0.05)';
                        const img = card.querySelector('.client-card-img');
                        if(img) img.style.transform = 'scale(1)';
                    });
                    
                    // Update region labels to display friendly names dynamically
                    const regionSpan = card.querySelector('.region-label');
                    if (regionSpan && regionFilter) {
                        const rawRegion = regionSpan.getAttribute('data-region-val');
                        const optionMatch = regionFilter.querySelector(`option[value="${rawRegion}"]`);
                        if (optionMatch && rawRegion !== 'All') {
                            regionSpan.textContent = optionMatch.textContent;
                        }
                    }
                });

                if (mapRegions.length) {
                    mapRegions.forEach((region, index) => {
                        region.setAttribute('data-original-order', index);
                    });
                }

                function filterClients() {
                    const search = searchInput ? searchInput.value.toLowerCase() : '';
                    const ind = industryFilter ? industryFilter.value : 'All';
                    const reg = regionFilter ? regionFilter.value : 'All';
                    const srv = serviceFilter ? serviceFilter.value : 'All';

                    let visibleCount = 0;

                    clientCards.forEach(card => {
                        const cName = card.getAttribute('data-name');
                        const cInd = card.getAttribute('data-industry');
                        const cReg = card.getAttribute('data-region');
                        const cSrvStr = card.getAttribute('data-services');
                        const cSrv = cSrvStr ? JSON.parse(cSrvStr) : [];

                        const matchSearch = !search || cName.includes(search);
                        const matchInd = ind === 'All' || cInd === ind;
                        const matchReg = reg === 'All' || cReg === reg;
                        const matchSrv = srv === 'All' || cSrv.includes(srv);

                        if (matchSearch && matchInd && matchReg && matchSrv) {
                            card.style.display = 'flex';
                            visibleCount++;
                        } else {
                            card.style.display = 'none';
                        }
                    });

                    if (visibleCount === 0) {
                        resultsContainer.style.display = 'none';
                        if (noResults) noResults.style.display = 'block';
                    } else {
                        resultsContainer.style.display = 'grid';
                        if (noResults) noResults.style.display = 'none';
                    }

                    updateMapHighlights(reg);
                }

                function updateMapHighlights(activeRegion) {
                    if (!svgMapObj) return;
                    if (activeRegion === 'All') {
                        svgMapObj.classList.remove('has-active');
                        mapRegions.forEach(p => p.classList.remove('active'));
                        
                        const parent = document.getElementById('Layer_1-2');
                        if (parent) {
                            const regionsArray = Array.from(parent.querySelectorAll('.texas-region'));
                            regionsArray.sort((a, b) => parseInt(a.getAttribute('data-original-order')) - parseInt(b.getAttribute('data-original-order')));
                            regionsArray.forEach(reg => parent.appendChild(reg));
                        }
                    } else {
                        svgMapObj.classList.add('has-active');
                        mapRegions.forEach(p => {
                            if (p.getAttribute('data-region') === activeRegion) {
                                p.classList.add('active');
                                p.parentNode.appendChild(p); // bring to front
                            } else {
                                p.classList.remove('active');
                            }
                        });
                    }
                }

                if (searchInput) searchInput.addEventListener('input', filterClients);
                if (industryFilter) industryFilter.addEventListener('change', filterClients);
                if (regionFilter) regionFilter.addEventListener('change', filterClients);
                if (serviceFilter) serviceFilter.addEventListener('change', filterClients);

                if (clearBtn) {
                    clearBtn.addEventListener('click', () => {
                        if (searchInput) searchInput.value = '';
                        if (industryFilter) industryFilter.value = 'All';
                        if (regionFilter) regionFilter.value = 'All';
                        if (serviceFilter) serviceFilter.value = 'All';
                        filterClients();
                    });
                }

                let hoverTimeout = null;
                let switchTimeout = null;

                if (mapRegions.length) {
                    mapRegions.forEach(region => {
                        region.style.pointerEvents = 'all';
                        region.style.cursor = 'pointer';
                        
                        region.addEventListener('click', (e) => {
                            if (!regionFilter) return;
                            const regId = e.currentTarget.getAttribute('data-region');
                            if (regionFilter.value === regId) {
                                regionFilter.value = 'All';
                                if (serviceFilter) serviceFilter.value = 'All'; 
                            } else {
                                regionFilter.value = regId;
                            }
                            filterClients();
                            
                            const container = document.querySelector('.clients-finder-container');
                            if (container) {
                                container.scrollIntoView({ behavior: 'smooth', block: 'start' });
                            }
                        });
                        
                        region.addEventListener('mouseover', (e) => {
                            const p = e.currentTarget;
                            if (p !== p.parentNode.lastElementChild) {
                                p.parentNode.appendChild(p);
                            }
                            if (regionFilter && regionFilter.value !== 'All') return; 
                            clearTimeout(hoverTimeout);
                            clearTimeout(switchTimeout);
                            switchTimeout = setTimeout(() => {
                                const hoverReg = p.getAttribute('data-region');
                                svgMapObj.classList.add('has-active');
                                mapRegions.forEach(reg => {
                                    if (reg.getAttribute('data-region') === hoverReg) {
                                        reg.classList.add('active');
                                    } else {
                                        reg.classList.remove('active');
                                    }
                                });
                            }, 250);
                        });
                        
                        region.addEventListener('mouseout', () => {
                            if (regionFilter && regionFilter.value !== 'All') {
                                const lockedReg = document.querySelector(`.texas-region[data-region="${regionFilter.value}"]`);
                                if (lockedReg) lockedReg.parentNode.appendChild(lockedReg);
                                return;
                            }
                            clearTimeout(switchTimeout);
                            hoverTimeout = setTimeout(() => {
                                svgMapObj.classList.remove('has-active');
                                mapRegions.forEach(reg => reg.classList.remove('active'));
                                
                                const parent = document.getElementById('Layer_1-2');
                                if (parent) {
                                    const regionsArray = Array.from(parent.querySelectorAll('.texas-region'));
                                    regionsArray.sort((a, b) => parseInt(a.getAttribute('data-original-order')) - parseInt(b.getAttribute('data-original-order')));
                                    regionsArray.forEach(reg => parent.appendChild(reg));
                                }
                            }, 250);
                        });
                    });
                }

                // Parse query parameters on page load to set initial filters
                const urlParams = new URLSearchParams(window.location.search);
                const regionParam = urlParams.get('region');
                const industryParam = urlParams.get('industry');
                const serviceParam = urlParams.get('service');

                if (regionParam && regionFilter) {
                    regionFilter.value = regionParam;
                    updateMapHighlights(regionParam);
                }
                if (industryParam && industryFilter) {
                    industryFilter.value = industryParam;
                }
                if (serviceParam && serviceFilter) {
                    serviceFilter.value = serviceParam;
                }

                // Initial render call
                filterClients();
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initFilters);
            } else {
                initFilters();
            }
        })();
    </script>
    <?php
    return ob_get_clean();
}


// ── REST API: Services & Clients list for editor pickers ─────────────────────
add_action( 'rest_api_init', 'e3_register_grid_picker_rest_routes' );
function e3_register_grid_picker_rest_routes() {
    register_rest_route( 'e3es/v1', '/services/list', array(
        'methods'             => 'GET',
        'callback'            => 'e3_rest_list_services',
        'permission_callback' => function() { return current_user_can( 'edit_posts' ); },
    ) );

    register_rest_route( 'e3es/v1', '/clients/list', array(
        'methods'             => 'GET',
        'callback'            => 'e3_rest_list_clients',
        'permission_callback' => function() { return current_user_can( 'edit_posts' ); },
    ) );

    register_rest_route( 'e3es/v1', '/clients/taxonomies', array(
        'methods'             => 'GET',
        'callback'            => 'e3_rest_client_taxonomies',
        'permission_callback' => function() { return current_user_can( 'edit_posts' ); },
    ) );
}

function e3es_decode_entities( $value ) {
    if ( is_array( $value ) ) {
        return array_map( 'e3es_decode_entities', $value );
    }
    if ( is_string( $value ) ) {
        return html_entity_decode( html_entity_decode( $value, ENT_QUOTES, 'UTF-8' ), ENT_QUOTES, 'UTF-8' );
    }
    return $value;
}

function e3_rest_list_services( $request ) {
    $posts = get_posts( array(
        'post_type'      => 'services',
        'post_status'    => 'publish',
        'posts_per_page' => 100,
        'orderby'        => 'menu_order title',
        'order'          => 'ASC',
    ) );

    $result = array();
    foreach ( $posts as $post ) {
        $thumb = get_the_post_thumbnail_url( $post->ID, 'thumbnail' );
        $result[] = array(
            'id'        => $post->ID,
            'title'     => $post->post_title,
            'excerpt'   => get_post_meta( $post->ID, 'service_excerpt', true ),
            'thumbnail' => $thumb ?: '',
            'parent'    => $post->post_parent,
            'menuOrder' => $post->menu_order,
        );
    }
    return rest_ensure_response( e3es_decode_entities( $result ) );
}

function e3_rest_list_clients( $request ) {
    $posts = get_posts( array(
        'post_type'      => 'clients',
        'post_status'    => 'publish',
        'posts_per_page' => 200,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ) );

    $result = array();
    foreach ( $posts as $post ) {
        $thumb = get_the_post_thumbnail_url( $post->ID, 'thumbnail' );
        $result[] = array(
            'id'        => $post->ID,
            'title'     => $post->post_title,
            'thumbnail' => $thumb ?: '',
        );
    }
    return rest_ensure_response( e3es_decode_entities( $result ) );
}

function e3_rest_client_taxonomies( $request ) {
    $taxonomies = array( 'industry', 'region', 'client-services' );
    $result = array();

    foreach ( $taxonomies as $tax ) {
        $terms = get_terms( array(
            'taxonomy'   => $tax,
            'hide_empty' => false,
        ) );
        $term_list = array();
        if ( ! is_wp_error( $terms ) ) {
            foreach ( $terms as $term ) {
                $term_list[] = array(
                    'slug'  => $term->slug,
                    'name'  => $term->name,
                    'count' => $term->count,
                );
            }
        }
        $result[ $tax ] = $term_list;
    }

    return rest_ensure_response( $result );
}

// ── Full-Width Testimonial render ────────────────────────────────────────────
function e3_render_full_width_testimonial( $attrs ) {
    $quote    = $attrs['quote']         ?? '';
    $byline   = $attrs['byline']        ?? '';
    $photo    = $attrs['photoUrl']      ?? '';
    $cs_url   = $attrs['caseStudyUrl']  ?? '';
    $cs_text  = $attrs['caseStudyText'] ?? 'Read Case Study';
    $bg       = sanitize_html_class( $attrs['bgStyle'] ?? 'white' );

    if ( ! $quote ) return '';

    $html  = '<div class="full-width-testimonial full-width-testimonial--' . $bg . '">';
    if ( $photo ) {
        $html .= '<div class="full-width-testimonial__avatar"><img src="' . esc_url( $photo ) . '" alt="' . esc_attr( $byline ) . '" /></div>';
    }
    $html .= '<div class="full-width-testimonial__body">';
    $html .= '<div class="full-width-testimonial__quote">' . wp_kses_post( $quote ) . '</div>';
    if ( $byline ) {
        $html .= '<div class="full-width-testimonial__byline">&mdash; ' . esc_html( $byline ) . '</div>';
    }
    if ( $cs_url ) {
        $html .= '<a href="' . esc_url( $cs_url ) . '" class="full-width-testimonial__link">' . esc_html( $cs_text ) . '</a>';
    }
    $html .= '</div></div>';
    return $html;
}

// ── Rep Contact Card render ──────────────────────────────────────────────────
function e3_render_rep_contact_card( $attrs ) {
    $name        = $attrs['name']       ?? '';
    $role        = $attrs['role']       ?? '';
    $bio         = $attrs['bio']        ?? '';
    $photo       = $attrs['photoUrl']   ?? '';
    $email_label = $attrs['emailLabel'] ?? 'Email';
    $email_href  = $attrs['emailHref']  ?? '';
    $call_label  = $attrs['callLabel']  ?? 'Schedule a Call';
    $call_href   = $attrs['callHref']   ?? '';

    if ( ! $name ) return '';

    $html  = '<div class="rep-contact-card">';
    if ( $photo ) {
        $html .= '<div class="rep-contact-card__photo-wrap"><img src="' . esc_url( $photo ) . '" alt="' . esc_attr( $name ) . '" class="rep-contact-card__photo" /></div>';
    }
    $html .= '<h3 class="rep-contact-card__name">Meet ' . esc_html( $name ) . '</h3>';
    if ( $role ) {
        $html .= '<p class="rep-contact-card__role">' . esc_html( $role ) . '</p>';
    }
    if ( $bio ) {
        $html .= '<blockquote class="rep-contact-card__bio">' . esc_html( $bio ) . '</blockquote>';
    }
    $html .= '<div class="rep-contact-card__buttons">';
    if ( $email_href ) {
        $html .= '<a href="' . esc_url( $email_href ) . '" class="btn btn--primary">' . esc_html( $email_label ) . '</a>';
    }
    if ( $call_href ) {
        $html .= '<a href="' . esc_url( $call_href ) . '" class="btn btn--outline">' . esc_html( $call_label ) . '</a>';
    }
    $html .= '</div></div>';
    return $html;
}

// ── Region Showcase render ───────────────────────────────────────────────────
function e3_render_region_showcase( $attrs, $content ) {
    $heading = $attrs['heading'] ?? 'Featured Projects';
    $bg      = sanitize_html_class( $attrs['bgStyle'] ?? 'white' );

    $html  = '<section class="wp-block-e3es-region-showcase region-showcase region-showcase--' . $bg . '">';
    $html .= '<div class="region-showcase__container" style="position: relative;">';
    if ( $heading ) {
        $html .= '<h2 class="region-showcase__heading section-title">' . esc_html( $heading ) . '</h2>';
    }
    // Slider navigation buttons
    $html .= '<button class="region-showcase__btn region-showcase__btn--prev" aria-label="Previous slide">&#10094;</button>';
    $html .= '<button class="region-showcase__btn region-showcase__btn--next" aria-label="Next slide">&#10095;</button>';

    $html .= '<div class="region-showcase__slider-wrap">';
    $html .= '<div class="region-showcase__track">' . $content . '</div>';
    $html .= '</div></div>';
    $html .= '<script>
    (function() {
        function initSlider() {
            var containers = document.querySelectorAll(".wp-block-e3es-region-showcase");
            containers.forEach(function(container) {
                var track = container.querySelector(".region-showcase__track");
                var prevBtn = container.querySelector(".region-showcase__btn--prev");
                var nextBtn = container.querySelector(".region-showcase__btn--next");
                if (!track || !prevBtn || !nextBtn) return;

                var currentIndex = 0;
                var totalSlides = track.children.length;

                function updateSlider() {
                    var card = track.children[0];
                    if (!card) return;
                    
                    var style = window.getComputedStyle(track);
                    var gap = parseFloat(style.gap) || 32;
                    var slideWidth = card.offsetWidth + gap;
                    
                    var visibleSlides = 1;
                    if (window.innerWidth >= 992) {
                        visibleSlides = 3;
                    } else if (window.innerWidth >= 768) {
                        visibleSlides = 2;
                    }

                    track.style.transform = "translateX(-" + (currentIndex * slideWidth) + "px)";
                    
                    prevBtn.disabled = currentIndex === 0;
                    nextBtn.disabled = currentIndex >= (totalSlides - visibleSlides);
                }

                nextBtn.addEventListener("click", function() {
                    var visibleSlides = 1;
                    if (window.innerWidth >= 992) {
                        visibleSlides = 3;
                    } else if (window.innerWidth >= 768) {
                        visibleSlides = 2;
                    }
                    if (currentIndex < (totalSlides - visibleSlides)) {
                        currentIndex++;
                        updateSlider();
                    }
                });

                prevBtn.addEventListener("click", function() {
                    if (currentIndex > 0) {
                        currentIndex--;
                        updateSlider();
                    }
                });

                window.addEventListener("resize", updateSlider);
                setTimeout(updateSlider, 200);
            });
        }

        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", initSlider);
        } else {
            initSlider();
        }
    })();
    </script>';
    $html .= '</section>';
    return $html;
}


function e3_render_team_directory( $attrs ) {
    $employees = get_posts( array(
        'post_type'      => 'employees',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
    ) );

    if ( empty( $employees ) ) {
        return '<p>No team members found.</p>';
    }

    $out = '<section class="team-directory"><div class="team-directory__grid">';

    foreach ( $employees as $emp ) {
        // Skip drafts, privates, or trashed employees
        if ( get_post_status( $emp->ID ) !== 'publish' ) {
            continue;
        }

        $role     = get_post_meta( $emp->ID, '_e3_employee_role', true );
        $division = get_post_meta( $emp->ID, '_e3_employee_division', true );
        $thumb    = get_the_post_thumbnail_url( $emp->ID, 'medium' );

        $out .= '<div class="team-directory__card">';
        if ( $thumb ) {
            $out .= '<img class="team-directory__photo" src="' . esc_url( $thumb ) . '" alt="' . esc_attr( $emp->post_title ) . '" loading="lazy">';
        }
        $out .= '<div class="team-directory__info">';
        $out .= '<h3 class="team-directory__name">' . esc_html( $emp->post_title ) . '</h3>';
        if ( $role ) {
            $out .= '<p class="team-directory__role">' . esc_html( $role ) . '</p>';
        }
        if ( ! empty( $emp->post_content ) ) {
            $out .= '<div class="team-directory__description">' . apply_filters( 'the_content', $emp->post_content ) . '</div>';
        }
        $out .= '</div></div>';
    }

    $out .= '</div></section>';
    return $out;
}

// ── Auto-update team page when any employee post changes ────────────────────
add_action( 'save_post_employees', 'e3_touch_team_page_on_employee_change', 20, 3 );
add_action( 'trashed_post', 'e3_touch_team_page_on_employee_trash', 20 );
add_action( 'untrashed_post', 'e3_touch_team_page_on_employee_trash', 20 );

function e3_touch_team_page_on_employee_change( $post_id, $post, $update ) {
    if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
        return;
    }
    e3_touch_team_page();
}

function e3_touch_team_page_on_employee_trash( $post_id ) {
    if ( get_post_type( $post_id ) !== 'employees' ) {
        return;
    }
    e3_touch_team_page();
}

function e3_touch_team_page() {
    // Find the team page by slug
    $team_page = get_page_by_path( 'about-us/team' );
    if ( ! $team_page ) {
        $team_page = get_page_by_path( 'team' );
    }
    if ( ! $team_page ) {
        // Fallback: look for post ID 24
        $team_page = get_post( 24 );
    }
    if ( $team_page && $team_page->post_type === 'page' ) {
        // Touch the modified date to trigger cache invalidation / rebuild
        wp_update_post( array(
            'ID'                => $team_page->ID,
            'post_modified'     => current_time( 'mysql' ),
            'post_modified_gmt' => current_time( 'mysql', true ),
        ) );
    }
}

function e3_render_two_column( $attrs, $content ) {
    // $content is the FULL static save-function HTML (including section wrapper,
    // db-feature__content, inner blocks, AND db-feature__image-wrapper).
    // We must NOT reconstruct the structure — just ensure /wp-content/ image
    // paths are absolute so Astro/Cloudflare Workers can load them.
    if ( ! $content ) {
        return '';
    }
    return preg_replace_callback(
        '/\bsrc="(\/wp-content\/[^"]+)"/i',
        function ( $m ) {
            return 'src="' . esc_url( site_url( $m[1] ) ) . '"';
        },
        $content
    );
}

// Two-Column Cover: same structure as legacy but uses wp-block-e3es-two-column-cover class
function e3_render_two_column_cover( $attrs, $content ) {
    $bg    = sanitize_html_class( $attrs['bgStyle'] ?? 'white' );
    $rev   = ! empty( $attrs['reverse'] );
    $spill = ! empty( $attrs['mapSpill'] );

    $sec = 'wp-block-e3es-two-column-cover db-feature db-feature--' . $bg . ( $spill ? ' db-feature--map-spill' : '' );
    $con = 'db-feature__container' . ( $rev ? ' db-feature__container--reverse' : '' );

    return '<section class="' . esc_attr( $sec ) . '"><div class="' . esc_attr( $con ) . '">'
        . $content
        . '</div></section>';
}


function e3_render_section_icon( $attrs ) {
    $icon  = $attrs['icon']  ?? '';
    $size  = sanitize_html_class( $attrs['size']  ?? 'md' );
    $color = sanitize_html_class( $attrs['color'] ?? 'green' );
    if ( ! $icon ) return '';

    $icons = e3_fa_icons();
    if ( ! isset( $icons[ $icon ] ) ) return '';
    $d = $icons[ $icon ];

    $paths = '';
    foreach ( $d['paths'] as $p ) {
        $paths .= '<path d="' . esc_attr( $p ) . '" fill="currentColor"/>';
    }
    $vb = '0 0 ' . $d['w'] . ' ' . $d['h'];

    return '<div class="e3es-icon e3es-icon--' . $size . ' e3es-icon--' . $color . '">'
        . '<svg xmlns="http://www.w3.org/2000/svg" viewBox="' . esc_attr( $vb ) . '" aria-hidden="true" focusable="false">'
        . $paths . '</svg></div>';
}

function e3_split_keywords_string( $str ) {
    $str = preg_replace( '/\b(and)\b(?! espcs)/i', ',', $str );
    $parts = explode( ',', $str );
    $keywords = [];
    foreach ( $parts as $part ) {
        $part = trim( $part );
        if ( empty( $part ) ) {
            continue;
        }
        
        $part = preg_replace( '/^and\s+/i', '', $part );
        $part = trim( $part );
        
        $lower = strtolower( $part );
        if ( strpos( $lower, 'financing' ) !== false ) {
            $part = 'Project Financing';
        } elseif ( strpos( $lower, 'hvac' ) !== false ) {
            $part = 'HVAC Upgrades';
        } elseif ( strpos( $lower, 'lighting' ) !== false || strpos( $lower, 'led' ) !== false ) {
            $part = 'LED Lighting';
        } elseif ( strpos( $lower, 'water' ) !== false ) {
            $part = 'Water Conservation';
        } elseif ( strpos( $lower, 'controls' ) !== false || strpos( $lower, 'automation' ) !== false ) {
            $part = 'Smart Controls';
        } else {
            $part = ucwords( $part );
        }
        
        if ( ! in_array( $part, $keywords ) ) {
            $keywords[] = $part;
        }
    }
    return $keywords;
}

function e3_get_faq_keywords( $description ) {
    if ( empty( $description ) ) {
        return [ 'Process', 'Services', 'Results' ];
    }
    
    // Check if it's default sentence
    if ( preg_match( '/commonly asked questions about our process, services, and results/i', $description ) ) {
        return [ 'Process', 'Services', 'Results' ];
    }
    
    // Check if it's dynamic client page sentence
    if ( preg_match( '/commonly asked questions about our energy efficiency solutions(?:, including| and)?\s+(.+?)\s+implemented for/i', $description, $matches ) ) {
        return e3_split_keywords_string( $matches[1] );
    }

    // Default split by comma
    if ( strpos( $description, ',' ) !== false ) {
        return e3_split_keywords_string( $description );
    }
    
    return [ ucwords( trim( $description ) ) ];
}

function e3_render_faq_section( $attrs, $content ) {
    $title = $attrs['title'] ?? 'Frequently Asked Questions';
    $description = $attrs['description'] ?? 'process, services, results';

    // If the saved content contains the description wrapper, extract the text from it to be fully dynamic
    if ( preg_match( '/<p\s+class="faq-section__desc"[^>]*>(.*?)<\/p>/is', $content, $matches ) ) {
        $description = html_entity_decode( strip_tags( $matches[1] ) );
    }

    // Parse description into keywords
    $keywords = e3_get_faq_keywords( $description );
    
    // Build keywords HTML
    $keywords_html = '';

    // If it has a desc-wrapper, replace it with the keywords
    if ( preg_match( '/<div\s+class="faq-section__desc-wrapper">.*?<\/div>/is', $content ) ) {
        $content = preg_replace( '/<div\s+class="faq-section__desc-wrapper">.*?<\/div>/is', $keywords_html, $content, 1 );
    } else {
        // Find faq-section__title element and insert keywords_html after it
        if ( preg_match( '/(<h2\s+class="faq-section__title"[^>]*>.*?<\/h2>)/is', $content, $title_matches ) ) {
            $content = str_replace( $title_matches[1], $title_matches[1] . "\n" . $keywords_html, $content );
        } else {
            // If title is also missing, insert both title and keywords
            $title_html = '<h2 class="faq-section__title">' . esc_html( $title ) . '</h2>';
            $pattern = '/(<div\s+class="[^\"]*faq-section__container[^\"]*">)/i';
            $replacement = '$1' . "\n" . $title_html . "\n" . $keywords_html;
            $content = preg_replace( $pattern, $replacement, $content, 1 );
        }
    }

    return $content;
}

/**
 * Shared helper: fetch testimonial data from a post ID.
 * Returns array of [ quote, person_name, person_title, photo_url ] or null.
 */
function e3_get_testimonial_data( $testimonial_id ) {
    $testimonial = get_post( $testimonial_id );
    if ( ! $testimonial || ! in_array( $testimonial->post_type, array( 'quotes', 'testimonials' ) ) ) {
        return null;
    }

    $quote     = get_post_meta( $testimonial_id, '_e3_quote_quote', true );
    if ( ! $quote ) {
        $quote = get_post_meta( $testimonial_id, '_e3_testimonial_quote', true );
    }

    $person_id = (int) get_post_meta( $testimonial_id, '_e3_quote_person_id', true );
    if ( ! $person_id ) {
        $person_id = (int) get_post_meta( $testimonial_id, '_e3_testimonial_person_id', true );
    }

    $person_name  = '';
    $person_title = '';
    $photo_url    = '';

    if ( $person_id ) {
        $person = get_post( $person_id );
        if ( $person && $person->post_type === 'people' ) {
            $person_name  = $person->post_title;
            $person_title = get_post_meta( $person_id, '_e3_person_title', true );
            $thumb_id     = get_post_thumbnail_id( $person_id );
            if ( $thumb_id ) {
                $photo_url = wp_get_attachment_image_url( $thumb_id, 'thumbnail' );
            }
        }
    }

    return array(
        'quote'        => $quote,
        'person_name'  => $person_name,
        'person_title' => $person_title,
        'photo_url'    => $photo_url,
    );
}

/**
 * Mini Testimonial — dynamic render callback.
 * Supports manual content (mode=manual) or linked testimonial post (mode=linked).
 */
function e3_render_mini_testimonial( $attrs ) {
    $layout = $attrs['layout'] ?? 'callout';
    $mode   = $attrs['mode']   ?? 'manual';

    if ( 'linked' === $mode ) {
        $testimonial_id = (int) ( $attrs['testimonialId'] ?? 0 );
        if ( ! $testimonial_id ) {
            return '';
        }
        $data = e3_get_testimonial_data( $testimonial_id );
        if ( ! $data || ! $data['quote'] ) {
            return '';
        }
        $quote       = $data['quote'];
        $person_name = $data['person_name'];
        $person_title = $data['person_title'];
        $photo_url   = $data['photo_url'];
        $cite        = $person_name ? $person_name . ( $person_title ? ', ' . $person_title : '' ) : '';
    } else {
        $quote       = $attrs['quote']    ?? '';
        $cite        = $attrs['cite']     ?? '';
        $photo_url   = $attrs['photoUrl'] ?? '';
        $person_name = $cite;
        $person_title = '';
        if ( ! $quote ) {
            return '';
        }
    }

    if ( 'full-width' === $layout ) {
        $cs_url  = $attrs['caseStudyUrl']  ?? '';
        $cs_text = $attrs['caseStudyText'] ?? 'Read Case Study';
        $bg      = sanitize_html_class( $attrs['bgStyle'] ?? 'white' );

        $html  = '<div class="full-width-testimonial full-width-testimonial--' . $bg . '">';
        if ( $photo_url ) {
            $html .= '<div class="full-width-testimonial__avatar"><img src="' . esc_url( $photo_url ) . '" alt="' . esc_attr( $person_name ) . '" /></div>';
        }
        $html .= '<div class="full-width-testimonial__body">';
        $html .= '<div class="full-width-testimonial__quote">' . wp_kses_post( $quote ) . '</div>';
        if ( $cite ) {
            $html .= '<div class="full-width-testimonial__byline">&mdash; ' . esc_html( $cite ) . '</div>';
        }
        if ( $cs_url ) {
            $html .= '<a href="' . esc_url( $cs_url ) . '" class="full-width-testimonial__link">' . esc_html( $cs_text ) . '</a>';
        }
        $html .= '</div></div>';
        return $html;
    }

    if ( 'picker' === $layout ) {
        $html  = '<div class="testimonial-picker">';
        $html .= '<blockquote class="testimonial-picker__quote">' . esc_html( $quote ) . '</blockquote>';
        $html .= '<div class="testimonial-picker__footer">';
        if ( $photo_url ) {
            $html .= '<img src="' . esc_url( $photo_url ) . '" alt="' . esc_attr( $person_name ) . '" class="testimonial-picker__photo" />';
        }
        if ( $person_name ) {
            $html .= '<div class="testimonial-picker__person">';
            $html .= '<span class="testimonial-picker__name">' . esc_html( $person_name ) . '</span>';
            if ( $person_title ) {
                $html .= '<span class="testimonial-picker__title">' . esc_html( $person_title ) . '</span>';
            }
            $html .= '</div>';
        }
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }

    // Default 'callout' layout
    $html  = '<div class="mini-testimonial">';
    $html .= '<blockquote class="mini-testimonial__quote">' . esc_html( $quote ) . '</blockquote>';
    $html .= '<div class="mini-testimonial__footer">';
    if ( $photo_url ) {
        $html .= '<img src="' . esc_url( $photo_url ) . '" alt="' . esc_attr( $person_name ) . '" class="mini-testimonial__photo" />';
    }
    if ( $cite ) {
        $html .= '<cite class="mini-testimonial__cite">' . esc_html( $cite ) . '</cite>';
    }
    $html .= '</div>';
    $html .= '</div>';

    return $html;
}

/**
 * Testimonial Picker — dynamic render callback.
 * Fetches the selected testimonial and its connected person, outputs BEM HTML.
 */
function e3_render_testimonial_picker( $attrs ) {
    $testimonial_id = (int) ( $attrs['testimonialId'] ?? 0 );
    if ( ! $testimonial_id ) {
        return '';
    }

    $data = e3_get_testimonial_data( $testimonial_id );
    if ( ! $data || ! $data['quote'] ) {
        return '';
    }

    $quote        = $data['quote'];
    $person_name  = $data['person_name'];
    $person_title = $data['person_title'];
    $photo_url    = $data['photo_url'];

    $html  = '<div class="testimonial-picker">';
    $html .= '<blockquote class="testimonial-picker__quote">' . esc_html( $quote ) . '</blockquote>';
    $html .= '<div class="testimonial-picker__footer">';
    if ( $photo_url ) {
        $html .= '<img src="' . esc_url( $photo_url ) . '" alt="' . esc_attr( $person_name ) . '" class="testimonial-picker__photo" />';
    }
    if ( $person_name ) {
        $html .= '<div class="testimonial-picker__person">';
        $html .= '<span class="testimonial-picker__name">' . esc_html( $person_name ) . '</span>';
        if ( $person_title ) {
            $html .= '<span class="testimonial-picker__title">' . esc_html( $person_title ) . '</span>';
        }
        $html .= '</div>';
    }
    $html .= '</div>';
    $html .= '</div>';

    return $html;
}

// ── REST API endpoints for Testimonial Picker block ──────────────────────────
add_action( 'rest_api_init', 'e3_register_testimonial_rest_routes' );
function e3_register_testimonial_rest_routes() {
    // Search testimonials by various fields
    register_rest_route( 'e3es/v1', '/testimonials/search', array(
        'methods'             => 'GET',
        'callback'            => 'e3_rest_search_testimonials',
        'permission_callback' => function() { return current_user_can( 'edit_posts' ); },
        'args'                => array(
            'search'    => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
            'person_id' => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
            'client_id' => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
            'service'   => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
            'industry'  => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
            'region'    => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
            'keyword'   => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
        ),
    ) );

    // List all people for person picker / filter
    register_rest_route( 'e3es/v1', '/people', array(
        'methods'             => 'GET',
        'callback'            => 'e3_rest_list_people',
        'permission_callback' => function() { return current_user_can( 'edit_posts' ); },
    ) );

    // Distinct filter values for testimonial search UI
    register_rest_route( 'e3es/v1', '/testimonials/filters', array(
        'methods'             => 'GET',
        'callback'            => 'e3_rest_testimonial_filters',
        'permission_callback' => function() { return current_user_can( 'edit_posts' ); },
    ) );
}

function e3_rest_search_testimonials( $request ) {
    $search    = $request->get_param( 'search' );
    $person_id = (int) $request->get_param( 'person_id' );
    $client_id = (int) $request->get_param( 'client_id' );
    $service   = $request->get_param( 'service' );
    $industry  = $request->get_param( 'industry' );
    $region    = $request->get_param( 'region' );
    $keyword   = $request->get_param( 'keyword' );

    $query_args = array(
        'post_type'      => 'quotes',
        'post_status'    => 'publish',
        'posts_per_page' => 50,
        'orderby'        => 'title',
        'order'          => 'ASC',
    );

    // Build meta_query for exact-match filters
    $meta_filters = array( 'relation' => 'AND' );

    if ( $person_id ) {
        $meta_filters[] = array( 'key' => '_e3_quote_person_id', 'value' => $person_id, 'compare' => '=' );
    }
    if ( $client_id ) {
        // Find people linked to this client
        $people_linked = get_posts( array(
            'post_type'      => 'people',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_query'     => array(
                array(
                    'key'     => '_e3_person_client_id',
                    'value'   => $client_id,
                    'compare' => '=',
                )
            )
        ) );
        if ( ! empty( $people_linked ) ) {
            $meta_filters[] = array( 'key' => '_e3_quote_person_id', 'value' => $people_linked, 'compare' => 'IN' );
        } else {
            // No people linked to this client, force zero results
            $meta_filters[] = array( 'key' => '_e3_quote_person_id', 'value' => -1, 'compare' => '=' );
        }
    }
    if ( $service ) {
        $meta_filters[] = array( 'key' => '_e3_quote_service', 'value' => $service, 'compare' => '=' );
    }
    if ( $industry ) {
        $meta_filters[] = array( 'key' => '_e3_quote_industry', 'value' => $industry, 'compare' => '=' );
    }
    if ( $region ) {
        $meta_filters[] = array( 'key' => '_e3_quote_region', 'value' => $region, 'compare' => '=' );
    }
    if ( $keyword ) {
        $meta_filters[] = array( 'key' => '_e3_quote_keyword', 'value' => $keyword, 'compare' => 'LIKE' );
    }

    if ( count( $meta_filters ) > 1 ) {
        $query_args['meta_query'] = $meta_filters;
    }

    // Text search: also search meta fields and title
    $posts = get_posts( $query_args );

    if ( $search ) {
        // Search by meta fields
        $search_meta_args = $query_args;
        $search_meta_args['meta_query'] = array_merge(
            isset( $query_args['meta_query'] ) ? $query_args['meta_query'] : array( 'relation' => 'AND' ),
            array(
                array(
                    'relation' => 'OR',
                    array( 'key' => '_e3_quote_quote',    'value' => $search, 'compare' => 'LIKE' ),
                    array( 'key' => '_e3_quote_service',  'value' => $search, 'compare' => 'LIKE' ),
                    array( 'key' => '_e3_quote_industry', 'value' => $search, 'compare' => 'LIKE' ),
                    array( 'key' => '_e3_quote_region',   'value' => $search, 'compare' => 'LIKE' ),
                    array( 'key' => '_e3_quote_keyword',  'value' => $search, 'compare' => 'LIKE' ),
                )
            )
        );
        $posts_by_meta = get_posts( $search_meta_args );

        // Search by post title
        $title_args          = $query_args;
        $title_args['s']     = $search;
        $posts_by_title      = get_posts( $title_args );

        // Also search connected people by name
        $people_ids = get_posts( array(
            'post_type'      => 'people',
            'post_status'    => 'publish',
            's'              => $search,
            'posts_per_page' => 50,
            'fields'         => 'ids',
        ) );

        $posts_by_person = array();
        if ( ! empty( $people_ids ) ) {
            $person_meta = isset( $query_args['meta_query'] ) ? $query_args['meta_query'] : array( 'relation' => 'AND' );
            $person_meta[] = array( 'key' => '_e3_quote_person_id', 'value' => $people_ids, 'compare' => 'IN' );
            $person_args   = $query_args;
            $person_args['meta_query'] = $person_meta;
            $posts_by_person = get_posts( $person_args );
        }

        // Merge all results, avoiding duplicates
        $all   = array_merge( $posts_by_meta, $posts_by_title, $posts_by_person );
        $seen  = array();
        $posts = array();
        foreach ( $all as $p ) {
            if ( ! in_array( $p->ID, $seen ) ) {
                $seen[]  = $p->ID;
                $posts[] = $p;
            }
        }
    }

    $admin_url = admin_url( 'post.php' );

    $results = array();
    foreach ( $posts as $post ) {
        $pid          = (int) get_post_meta( $post->ID, '_e3_quote_person_id', true );
        $person_name  = '';
        $person_title = '';
        $photo_url    = '';

        if ( $pid ) {
            $person = get_post( $pid );
            if ( $person ) {
                $person_name  = $person->post_title;
                $person_title = get_post_meta( $pid, '_e3_person_title', true );
                $thumb_id     = get_post_thumbnail_id( $pid );
                if ( $thumb_id ) {
                    $photo_url = wp_get_attachment_image_url( $thumb_id, 'thumbnail' );
                }
            }
        }

        $results[] = array(
            'id'          => $post->ID,
            'title'       => $post->post_title,
            'editUrl'     => add_query_arg( array( 'post' => $post->ID, 'action' => 'edit' ), $admin_url ),
            'quote'       => get_post_meta( $post->ID, '_e3_quote_quote', true ),
            'service'     => get_post_meta( $post->ID, '_e3_quote_service', true ),
            'industry'    => get_post_meta( $post->ID, '_e3_quote_industry', true ),
            'region'      => get_post_meta( $post->ID, '_e3_quote_region', true ),
            'keyword'     => get_post_meta( $post->ID, '_e3_quote_keyword', true ),
            'personId'    => $pid,
            'personName'  => $person_name,
            'personTitle' => $person_title,
            'photoUrl'    => $photo_url,
        );
    }

    return rest_ensure_response( e3es_decode_entities( $results ) );
}

/**
 * Returns distinct filter values (service, industry, region, keyword) for UI dropdowns.
 */
function e3_rest_testimonial_filters( $request ) {
    global $wpdb;

    $meta_keys = array(
        'service'  => '_e3_quote_service',
        'industry' => '_e3_quote_industry',
        'region'   => '_e3_quote_region',
        'keyword'  => '_e3_quote_keyword',
    );

    $results = array(
        'people'   => array(),
        'clients'  => array(),
        'service'  => array(),
        'industry' => array(),
        'region'   => array(),
        'keyword'  => array(),
    );

    // Distinct people linked to testimonials
    $person_ids = $wpdb->get_col( $wpdb->prepare(
        "SELECT DISTINCT meta_value FROM {$wpdb->postmeta}
         WHERE meta_key = %s AND meta_value != ''",
        '_e3_quote_person_id'
    ) );

    $unique_clients = array();

    foreach ( $person_ids as $pid ) {
        $p = get_post( (int) $pid );
        if ( $p && $p->post_type === 'people' && $p->post_status === 'publish' ) {
            $results['people'][] = array(
                'id'    => $p->ID,
                'label' => $p->post_title,
            );

            // Fetch the linked client for this person
            $client_id = (int) get_post_meta( $p->ID, '_e3_person_client_id', true );
            if ( $client_id ) {
                $client = get_post( $client_id );
                if ( $client && $client->post_type === 'clients' && $client->post_status === 'publish' ) {
                    $unique_clients[ $client_id ] = array(
                        'id'    => $client->ID,
                        'label' => $client->post_title,
                    );
                }
            }
        }
    }
    usort( $results['people'], function( $a, $b ) { return strcmp( $a['label'], $b['label'] ); } );

    $results['clients'] = array_values( $unique_clients );
    usort( $results['clients'], function( $a, $b ) { return strcmp( $a['label'], $b['label'] ); } );

    // Distinct values for each meta key
    foreach ( $meta_keys as $field => $meta_key ) {
        $values = $wpdb->get_col( $wpdb->prepare(
            "SELECT DISTINCT meta_value FROM {$wpdb->postmeta}
             WHERE meta_key = %s AND meta_value != ''
             ORDER BY meta_value ASC",
            $meta_key
        ) );
        $results[ $field ] = array_values( array_filter( $values ) );
    }

    return rest_ensure_response( e3es_decode_entities( $results ) );
}

function e3_rest_list_people( $request ) {
    $posts = get_posts( array(
        'post_type'      => 'people',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ) );

    $results = array();
    foreach ( $posts as $post ) {
        $thumb_id  = get_post_thumbnail_id( $post->ID );
        $photo_url = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'thumbnail' ) : '';

        $results[] = array(
            'id'    => $post->ID,
            'name'  => $post->post_title,
            'title' => get_post_meta( $post->ID, '_e3_person_title', true ),
            'photo' => $photo_url,
        );
    }

    return rest_ensure_response( e3es_decode_entities( $results ) );
}

// ── Admin Meta Boxes for People and Quotes ───────────────────────────────────
add_action( 'add_meta_boxes', 'e3_add_people_quotes_meta_boxes' );
function e3_add_people_quotes_meta_boxes() {
    add_meta_box(
        'e3_person_details',
        __( 'Person Details', 'e3es' ),
        'e3_render_person_meta_box',
        'people',
        'normal',
        'high'
    );

    add_meta_box(
        'e3_quote_details',
        __( 'Quote Details', 'e3es' ),
        'e3_render_quote_meta_box',
        'quotes',
        'normal',
        'high'
    );
}

function e3_render_person_meta_box( $post ) {
    wp_nonce_field( 'e3_person_meta_save', 'e3_person_meta_nonce' );
    $person_title     = get_post_meta( $post->ID, '_e3_person_title', true );
    $person_client_id = (int) get_post_meta( $post->ID, '_e3_person_client_id', true );

    $clients = get_posts( array(
        'post_type'      => 'clients',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ) );
    ?>
    <table class="form-table">
        <tr>
            <th scope="row"><label for="e3_person_title"><?= __( 'Title / Role', 'e3es' ) ?></label></th>
            <td>
                <input type="text" id="e3_person_title" name="e3_person_title" value="<?= esc_attr( $person_title ) ?>" class="regular-text" placeholder="e.g. School District Superintendent" />
                <p class="description"><?= __( 'The person\'s job title or role. Name is the post title, Photo is the featured image.', 'e3es' ) ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="e3_person_client_id"><?= __( 'Linked Client', 'e3es' ) ?></label></th>
            <td>
                <select id="e3_person_client_id" name="e3_person_client_id" class="postform">
                    <option value=""><?= __( '— No Client —', 'e3es' ) ?></option>
                    <?php foreach ( $clients as $client ) : ?>
                        <option value="<?= esc_attr( $client->ID ) ?>" <?php selected( $person_client_id, $client->ID ); ?>><?= esc_html( $client->post_title ) ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="description"><?= __( 'Optionally link this person to a client (school district, municipality, etc.).', 'e3es' ) ?></p>
            </td>
        </tr>
    </table>
    <?php
}

function e3_render_quote_meta_box( $post ) {
    wp_nonce_field( 'e3_quote_meta_save', 'e3_quote_meta_nonce' );

    $person_id   = (int) get_post_meta( $post->ID, '_e3_quote_person_id', true );
    $quote       = get_post_meta( $post->ID, '_e3_quote_quote', true );
    $score       = get_post_meta( $post->ID, '_e3_quote_score', true );
    $video_title = get_post_meta( $post->ID, '_e3_quote_video_title', true );
    $video_link  = get_post_meta( $post->ID, '_e3_quote_video_link', true );
    $timestamp   = get_post_meta( $post->ID, '_e3_quote_timestamp', true );
    $service     = get_post_meta( $post->ID, '_e3_quote_service', true );
    $industry    = get_post_meta( $post->ID, '_e3_quote_industry', true );
    $region      = get_post_meta( $post->ID, '_e3_quote_region', true );
    $keyword     = get_post_meta( $post->ID, '_e3_quote_keyword', true );

    $people = get_posts( array(
        'post_type'      => 'people',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ) );

    $employees = get_posts( array(
        'post_type'      => 'employees',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ) );
    ?>
    <table class="form-table">
        <tr>
            <th scope="row"><label for="e3_quote_person_id"><?= __( 'Person / Employee', 'e3es' ) ?></label></th>
            <td>
                <select id="e3_quote_person_id" name="e3_quote_person_id" class="postform">
                    <option value=""><?= __( '— Select a Person or Employee —', 'e3es' ) ?></option>
                    <optgroup label="<?= esc_attr__( 'People', 'e3es' ) ?>">
                        <?php foreach ( $people as $p ) : ?>
                            <option value="<?= esc_attr( $p->ID ) ?>" <?php selected( $person_id, $p->ID ); ?>><?= esc_html( $p->post_title ) ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                    <optgroup label="<?= esc_attr__( 'Employees', 'e3es' ) ?>">
                        <?php foreach ( $employees as $e ) : ?>
                            <option value="<?= esc_attr( $e->ID ) ?>" <?php selected( $person_id, $e->ID ); ?>><?= esc_html( $e->post_title ) ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                </select>
                <p class="description"><?= __( 'Connect this quote to a person from the People post type or an employee from the Employees post type.', 'e3es' ) ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="e3_quote_quote"><?= __( 'Quote', 'e3es' ) ?></label></th>
            <td>
                <textarea id="e3_quote_quote" name="e3_quote_quote" rows="4" class="large-text"><?= esc_textarea( $quote ) ?></textarea>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="e3_quote_score"><?= __( 'Score', 'e3es' ) ?></label></th>
            <td>
                <input type="number" id="e3_quote_score" name="e3_quote_score" value="<?= esc_attr( $score ) ?>" class="small-text" min="0" max="100" step="1" />
                <p class="description"><?= __( 'Quality score for this quote (0–100).', 'e3es' ) ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="e3_quote_video_title"><?= __( 'Video Title', 'e3es' ) ?></label></th>
            <td>
                <input type="text" id="e3_quote_video_title" name="e3_quote_video_title" value="<?= esc_attr( $video_title ) ?>" class="regular-text" placeholder="e.g. ARCIT 2022 Breakout Session" />
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="e3_quote_video_link"><?= __( 'Link to Video', 'e3es' ) ?></label></th>
            <td>
                <input type="url" id="e3_quote_video_link" name="e3_quote_video_link" value="<?= esc_attr( $video_link ) ?>" class="large-text" placeholder="https://vimeo.com/..." />
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="e3_quote_timestamp"><?= __( 'Timestamp', 'e3es' ) ?></label></th>
            <td>
                <input type="text" id="e3_quote_timestamp" name="e3_quote_timestamp" value="<?= esc_attr( $timestamp ) ?>" class="regular-text" placeholder="e.g. 1:23" />
                <p class="description"><?= __( 'Timestamp in the video where this quote appears.', 'e3es' ) ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="e3_quote_service"><?= __( 'Service', 'e3es' ) ?></label></th>
            <td>
                <input type="text" id="e3_quote_service" name="e3_quote_service" value="<?= esc_attr( $service ) ?>" class="regular-text" placeholder="e.g. HVAC" />
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="e3_quote_industry"><?= __( 'Industry', 'e3es' ) ?></label></th>
            <td>
                <input type="text" id="e3_quote_industry" name="e3_quote_industry" value="<?= esc_attr( $industry ) ?>" class="regular-text" placeholder="e.g. K-12 Schools" />
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="e3_quote_region"><?= __( 'Region', 'e3es' ) ?></label></th>
            <td>
                <input type="text" id="e3_quote_region" name="e3_quote_region" value="<?= esc_attr( $region ) ?>" class="regular-text" placeholder="e.g. Central Texas" />
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="e3_quote_keyword"><?= __( 'Keyword', 'e3es' ) ?></label></th>
            <td>
                <input type="text" id="e3_quote_keyword" name="e3_quote_keyword" value="<?= esc_attr( $keyword ) ?>" class="regular-text" placeholder="e.g. energy savings, air quality" />
            </td>
        </tr>
    </table>
    <?php
}

// Save meta boxes
add_action( 'save_post_people', 'e3_save_person_meta', 10, 2 );
function e3_save_person_meta( $post_id, $post ) {
    if ( ! isset( $_POST['e3_person_meta_nonce'] ) || ! wp_verify_nonce( $_POST['e3_person_meta_nonce'], 'e3_person_meta_save' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    if ( isset( $_POST['e3_person_title'] ) ) {
        update_post_meta( $post_id, '_e3_person_title', sanitize_text_field( $_POST['e3_person_title'] ) );
    }

    if ( isset( $_POST['e3_person_client_id'] ) ) {
        $client_id = (int) $_POST['e3_person_client_id'];
        if ( $client_id > 0 ) {
            update_post_meta( $post_id, '_e3_person_client_id', $client_id );
        } else {
            delete_post_meta( $post_id, '_e3_person_client_id' );
        }
    }
}

add_action( 'save_post_quotes', 'e3_save_quote_meta', 10, 2 );
function e3_save_quote_meta( $post_id, $post ) {
    if ( ! isset( $_POST['e3_quote_meta_nonce'] ) || ! wp_verify_nonce( $_POST['e3_quote_meta_nonce'], 'e3_quote_meta_save' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    // Integer fields
    if ( isset( $_POST['e3_quote_person_id'] ) ) {
        update_post_meta( $post_id, '_e3_quote_person_id', (int) $_POST['e3_quote_person_id'] );
    }
    if ( isset( $_POST['e3_quote_score'] ) ) {
        update_post_meta( $post_id, '_e3_quote_score', (int) $_POST['e3_quote_score'] );
    }

    // URL field
    if ( isset( $_POST['e3_quote_video_link'] ) ) {
        update_post_meta( $post_id, '_e3_quote_video_link', esc_url_raw( $_POST['e3_quote_video_link'] ) );
    }

    // Textarea field
    if ( isset( $_POST['e3_quote_quote'] ) ) {
        update_post_meta( $post_id, '_e3_quote_quote', sanitize_textarea_field( $_POST['e3_quote_quote'] ) );
    }

    // Text fields
    $text_fields = array(
        'e3_quote_video_title' => '_e3_quote_video_title',
        'e3_quote_timestamp'   => '_e3_quote_timestamp',
        'e3_quote_service'     => '_e3_quote_service',
        'e3_quote_industry'    => '_e3_quote_industry',
        'e3_quote_region'      => '_e3_quote_region',
        'e3_quote_keyword'     => '_e3_quote_keyword',
    );

    foreach ( $text_fields as $field_name => $meta_key ) {
        if ( isset( $_POST[ $field_name ] ) ) {
            update_post_meta( $post_id, $meta_key, sanitize_text_field( $_POST[ $field_name ] ) );
        }
    }
}

// Automatically synchronize E3 Intro Banner background image and post featured image (thumbnail)
add_action( 'save_post_clients', 'e3_save_client_banner_sync', 10, 2 );
add_action( 'save_post_page', 'e3_save_client_banner_sync', 10, 2 );
add_action( 'save_post_services', 'e3_save_client_banner_sync', 10, 2 );
function e3_save_client_banner_sync( $post_id, $post ) {
    // Prevent infinite loops or autosaves
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( wp_is_post_revision( $post_id ) ) {
        return;
    }

    $featured_image_id = get_post_thumbnail_id( $post_id );
    
    // Special check: if no featured image is set, but the block has one, set it first
    if ( ! $featured_image_id ) {
        $blocks = parse_blocks( $post->post_content );
        foreach ( $blocks as $block ) {
            if ( 'e3es/intro-banner' === $block['blockName'] ) {
                $block_bg_url = $block['attrs']['bgImageUrl'] ?? '';
                if ( ! empty( $block_bg_url ) ) {
                    $attachment_id = attachment_url_to_postid( $block_bg_url );
                    if ( $attachment_id ) {
                        // Temporarily remove metadata sync to prevent infinite loop
                        remove_action( 'added_post_meta', 'e3_sync_banner_on_thumbnail_meta_change', 10 );
                        remove_action( 'updated_post_meta', 'e3_sync_banner_on_thumbnail_meta_change', 10 );
                        remove_action( 'deleted_post_meta', 'e3_sync_banner_on_thumbnail_meta_delete', 10 );
                        
                        set_post_thumbnail( $post_id, $attachment_id );
                        $featured_image_id = $attachment_id;
                        
                        add_action( 'added_post_meta', 'e3_sync_banner_on_thumbnail_meta_change', 10, 4 );
                        add_action( 'updated_post_meta', 'e3_sync_banner_on_thumbnail_meta_change', 10, 4 );
                        add_action( 'deleted_post_meta', 'e3_sync_banner_on_thumbnail_meta_delete', 10, 4 );
                    }
                }
                break;
            }
        }
    }

    // Now run the banner style synchronization
    e3_perform_post_banner_sync( $post_id, $post, $featured_image_id );
}

// Sync banner background when post thumbnail meta is updated or deleted (e.g. from Admin Columns Pro inline edit)
add_action( 'added_post_meta', 'e3_sync_banner_on_thumbnail_meta_change', 10, 4 );
add_action( 'updated_post_meta', 'e3_sync_banner_on_thumbnail_meta_change', 10, 4 );
function e3_sync_banner_on_thumbnail_meta_change( $meta_id, $post_id, $meta_key, $meta_value ) {
    if ( '_thumbnail_id' !== $meta_key ) {
        return;
    }
    
    $post = get_post( $post_id );
    if ( ! $post || ! in_array( $post->post_type, array( 'clients', 'page', 'services' ), true ) ) {
        return;
    }
    
    e3_perform_post_banner_sync( $post_id, $post, $meta_value );
}

add_action( 'deleted_post_meta', 'e3_sync_banner_on_thumbnail_meta_delete', 10, 4 );
function e3_sync_banner_on_thumbnail_meta_delete( $meta_ids, $post_id, $meta_key, $meta_value ) {
    if ( '_thumbnail_id' !== $meta_key ) {
        return;
    }
    
    $post = get_post( $post_id );
    if ( ! $post || ! in_array( $post->post_type, array( 'clients', 'page', 'services' ), true ) ) {
        return;
    }
    
    e3_perform_post_banner_sync( $post_id, $post, 0 );
}

function e3_perform_post_banner_sync( $post_id, $post, $thumbnail_id ) {
    // Prevent infinite loop by unhooking everything
    remove_action( 'save_post_clients', 'e3_save_client_banner_sync', 10 );
    remove_action( 'save_post_page', 'e3_save_client_banner_sync', 10 );
    remove_action( 'save_post_services', 'e3_save_client_banner_sync', 10 );
    remove_action( 'added_post_meta', 'e3_sync_banner_on_thumbnail_meta_change', 10 );
    remove_action( 'updated_post_meta', 'e3_sync_banner_on_thumbnail_meta_change', 10 );
    remove_action( 'deleted_post_meta', 'e3_sync_banner_on_thumbnail_meta_delete', 10 );

    $post_content = $post->post_content;
    $blocks = parse_blocks( $post_content );
    
    $featured_image_url = $thumbnail_id ? wp_get_attachment_url( $thumbnail_id ) : '';
    $content_changed = false;

    foreach ( $blocks as &$block ) {
        if ( 'e3es/intro-banner' === $block['blockName'] ) {
            $block_bg_url = $block['attrs']['bgImageUrl'] ?? '';

            if ( $block_bg_url !== $featured_image_url ) {
                $block['attrs']['bgImageUrl'] = $featured_image_url;
                $content_changed = true;
                
                // Regenerate inline styles and HTML content
                $bgImageUrl = $featured_image_url;
                $bgOpacity = $block['attrs']['bgOpacity'] ?? 0.85;
                $bgOverlayColor = $block['attrs']['bgOverlayColor'] ?? 'green';
                $bgFadeType = $block['attrs']['bgFadeType'] ?? 'flat';
                $focalPointX = $block['attrs']['focalPointX'] ?? 0.5;
                $focalPointY = $block['attrs']['focalPointY'] ?? 0.5;

                $rgbMap = array(
                    'green' => '33, 87, 52',
                    'sage'  => '125, 160, 68',
                    'black' => '0, 0, 0',
                    'blue'  => '16, 44, 87'
                );
                $rgb = $rgbMap[ $bgOverlayColor ] ?? $rgbMap['green'];

                switch ( $bgFadeType ) {
                    case 'vertical':
                        $gradient = 'linear-gradient(to bottom, rgba(' . $rgb . ',' . ($bgOpacity * 0.4) . '), rgba(' . $rgb . ',' . $bgOpacity . '))';
                        break;
                    case 'horizontal':
                        $gradient = 'linear-gradient(to right, rgba(' . $rgb . ',' . $bgOpacity . '), rgba(' . $rgb . ',' . ($bgOpacity * 0.3) . '))';
                        break;
                    case 'vignette':
                        $gradient = 'radial-gradient(circle, rgba(' . $rgb . ',' . ($bgOpacity * 0.4) . ') 0%, rgba(' . $rgb . ',' . $bgOpacity . ') 100%)';
                        break;
                    case 'vignette-center':
                        $gradient = 'radial-gradient(circle, rgba(' . $rgb . ',' . $bgOpacity . ') 0%, rgba(' . $rgb . ',' . ($bgOpacity * 0.4) . ') 100%)';
                        break;
                    case 'flat':
                    default:
                        $gradient = 'linear-gradient(rgba(' . $rgb . ',' . $bgOpacity . '), rgba(' . $rgb . ',' . $bgOpacity . '))';
                        break;
                }

                $style = 'background-size:cover;background-repeat:no-repeat;';
                if ( $bgImageUrl ) {
                    $style .= 'background-image:' . $gradient . ', url(' . esc_url( $bgImageUrl ) . ');';
                    $style .= 'background-position:' . ($focalPointX * 100) . '% ' . ($focalPointY * 100) . '%;';
                } else {
                    $style .= 'background-color:rgba(' . $rgb . ', 1);';
                }

                // Update HTML innerHTML and innerContent style attribute
                if ( strpos( $block['innerHTML'], 'style="' ) !== false ) {
                    $block['innerHTML'] = preg_replace( '/style="[^"]*"/', 'style="' . esc_attr( $style ) . '"', $block['innerHTML'], 1 );
                } else {
                    $block['innerHTML'] = preg_replace( '/class="([^"]*)"/', 'class="$1" style="' . esc_attr( $style ) . '"', $block['innerHTML'], 1 );
                }

                foreach ( $block['innerContent'] as &$inner_content ) {
                    if ( is_string( $inner_content ) ) {
                        if ( strpos( $inner_content, 'style="' ) !== false ) {
                            $inner_content = preg_replace( '/style="[^"]*"/', 'style="' . esc_attr( $style ) . '"', $inner_content, 1 );
                        } else {
                            $inner_content = preg_replace( '/class="([^"]*)"/', 'class="$1" style="' . esc_attr( $style ) . '"', $inner_content, 1 );
                        }
                    }
                }
            }
            break; // We only process the first banner
        }
    }

    if ( $content_changed ) {
        $new_content = serialize_blocks( $blocks );
        wp_update_post( array(
            'ID'           => $post_id,
            'post_content' => wp_slash( $new_content ),
        ) );
    }

    // Re-hook everything
    add_action( 'save_post_clients', 'e3_save_client_banner_sync', 10, 2 );
    add_action( 'save_post_page', 'e3_save_client_banner_sync', 10, 2 );
    add_action( 'save_post_services', 'e3_save_client_banner_sync', 10, 2 );
    add_action( 'added_post_meta', 'e3_sync_banner_on_thumbnail_meta_change', 10, 4 );
    add_action( 'updated_post_meta', 'e3_sync_banner_on_thumbnail_meta_change', 10, 4 );
    add_action( 'deleted_post_meta', 'e3_sync_banner_on_thumbnail_meta_delete', 10, 4 );
}

// Add admin columns for Testimonials list view
add_filter( 'manage_testimonials_posts_columns', 'e3_testimonial_admin_columns' );
function e3_testimonial_admin_columns( $columns ) {
    $new_columns = array();
    foreach ( $columns as $key => $val ) {
        $new_columns[ $key ] = $val;
        if ( $key === 'title' ) {
            $new_columns['e3_person']   = __( 'Person', 'e3es' );
            $new_columns['e3_service']  = __( 'Service', 'e3es' );
            $new_columns['e3_industry'] = __( 'Industry', 'e3es' );
            $new_columns['e3_region']   = __( 'Region', 'e3es' );
        }
    }
    return $new_columns;
}

add_action( 'manage_testimonials_posts_custom_column', 'e3_testimonial_admin_column_content', 10, 2 );
function e3_testimonial_admin_column_content( $column, $post_id ) {
    switch ( $column ) {
        case 'e3_person':
            $pid = (int) get_post_meta( $post_id, '_e3_testimonial_person_id', true );
            if ( $pid ) {
                $person = get_post( $pid );
                echo $person ? esc_html( $person->post_title ) : '—';
            } else {
                echo '—';
            }
            break;
        case 'e3_service':
            echo esc_html( get_post_meta( $post_id, '_e3_testimonial_service', true ) ?: '—' );
            break;
        case 'e3_industry':
            echo esc_html( get_post_meta( $post_id, '_e3_testimonial_industry', true ) ?: '—' );
            break;
        case 'e3_region':
            echo esc_html( get_post_meta( $post_id, '_e3_testimonial_region', true ) ?: '—' );
            break;
    }
}

/**
 * FA6 Free Solid icon data — mirrors fa-icons.js, zero CDN dependency.
 * Each icon: [ 'w' => viewBox width, 'h' => height, 'paths' => [ 'd string', ... ] ]
 */
function e3_fa_icons() {
    return array(
        'clock'               => array('w'=>512,'h'=>512,'paths'=>array('M256 0a256 256 0 1 1 0 512A256 256 0 1 1 256 0zM232 120v136c0 8 4 15.5 10.7 20l96 64c11 7.4 25.9 4.5 33.3-6.5s4.5-25.9-6.5-33.3L280 243.2V120c0-13.3-10.7-24-24-24s-24 10.7-24 24z')),
        'hourglass-half'      => array('w'=>384,'h'=>512,'paths'=>array('M32 0C14.3 0 0 14.3 0 32S14.3 64 32 64v11c0 42.4 16.9 83.1 46.9 113.1L192 301.3l113.1-113.1C335.1 158.1 352 117.4 352 75V64c17.7 0 32-14.3 32-32S369.7 0 352 0H32zM352 448v-11c0-42.4-16.9-83.1-46.9-113.1L192 210.7 78.9 323.9C48.9 353.9 32 394.6 32 437v11c-17.7 0-32 14.3-32 32s14.3 32 32 32h320c17.7 0 32-14.3 32-32s-14.3-32-32-32z')),
        'shield-halved'       => array('w'=>512,'h'=>512,'paths'=>array('M256 0c4.6 0 9.2 1 13.4 2.9L457.7 82.8c22 9.3 38.4 31 38.3 57.2-.5 99.2-41.3 280.7-213.7 363.2-16.7 8-36.1 8-52.8 0C57.3 420.7 16.5 239.2 16 140c-.1-26.2 16.3-47.9 38.3-57.2L242.7 2.9C246.8 1 251.4 0 256 0z')),
        'lock'                => array('w'=>448,'h'=>512,'paths'=>array('M144 144v-32C144 50.1 194.1 0 256 0s112 50.1 112 112v32h32c26.5 0 48 21.5 48 48v256c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V192c0-26.5 21.5-48 48-48h96zm48-32v32h128v-32c0-35.3-28.7-64-64-64s-64 28.7-64 64zm80 256c0-17.7-14.3-32-32-32s-32 14.3-32 32v40c0 17.7 14.3 32 32 32s32-14.3 32-32v-40z')),
        'dollar-sign'         => array('w'=>320,'h'=>512,'paths'=>array('M160 0c17.7 0 32 14.3 32 32v35.7l4.7.7 1.1.2 48 8.8c17.4 3.2 28.9 19.9 25.7 37.2s-19.9 28.9-37.2 25.7l-47.5-8.7c-31.3-4.6-58.9-1.5-78.3 6.2S81.3 118.5 79.5 128.3c-2 10.7-.5 16.7 1.2 20.4 1.8 3.9 5.5 8.3 12.8 13.2 16.3 10.7 41.3 17.9 73.7 26.3l2.9.8c28.6 7.6 63.6 16.8 89.6 33.8 14.2 9.3 27.6 21.9 35.9 39.5 8.5 17.9 10.3 37.9 6.4 59.2-6.9 38-33.1 63.4-65.6 76.7-13.7 5.6-28.6 9.2-44.4 11V448c0 17.7-14.3 32-32 32s-32-14.3-32-32v-34.9l-1.3-.2C104.2 409 64.1 398.5 37.1 386.5c-16.1-7.2-23.4-26.1-16.2-42.2s26.1-23.4 42.2-16.2c20.9 9.3 55.3 18.5 75.2 21.6 31.9 4.7 58.2 2 76-5.3 16.9-6.9 24.6-16.9 26.8-28.9 1.9-10.6.4-16.7-1.3-20.4-1.9-4-5.6-8.4-13-13.3-16.4-10.7-41.5-17.9-74-26.3l-2.8-.7C121.7 247 86.7 237.8 60.9 221.1c-14.2-9.3-27.8-21.9-36.2-39.7-8.5-17.9-10.3-37.9-6.2-59.2C24.3 84 55.1 56.8 92.8 44.9 106.6 40.5 121 37.9 128 37v-5C128 14.3 142.3 0 160 0z')),
        'sack-dollar'         => array('w'=>448,'h'=>512,'paths'=>array('M224 0c17.7 0 32 14.3 32 32 0 59.8 1.8 120 48 168 10 10.3 19.4 19.6 28.6 28.7C360 254.4 384 281.5 384 320c0 17.7-14.3 32-32 32H96c-17.7 0-32-14.3-32-32 0-38.5 24-65.6 51.4-91.3 9.2-9.1 18.6-18.4 28.6-28.7C192 152 176 91.8 176 32c0-17.7 14.3-32 32-32h16zm-90.8 368L314.8 368c7.9 0 14.6 5.5 16.5 13.2L352 464c2.4 9.7-.8 19.9-8.4 26.6S326 502.5 316.8 500 L224 476.6 131.2 500c-9.1 2.5-18.9 0-25.9-6.5S97.6 473.7 100 464l21.7-82.8c1.9-7.7 8.6-13.2 16.5-13.2z')),
        'location-dot'        => array('w'=>384,'h'=>512,'paths'=>array('M215.7 499.2C267 435 384 279.4 384 192 384 86 298 0 192 0S0 86 0 192c0 87.4 117 243 168.3 307.2 12.3 15.3 35.1 15.3 47.4 0zM192 128a64 64 0 1 1 0 128 64 64 0 1 1 0-128z')),
        'compass'             => array('w'=>512,'h'=>512,'paths'=>array('M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zm50.7-186.9L162.4 380.6c-19.4 7.5-38.5-11.6-31-31l55.5-144.3c3.3-8.5 9.9-15.1 18.4-18.4l144.3-55.5c19.4-7.5 38.5 11.6 31 31L325.1 306.7c-3.2 8.5-9.9 15.1-18.4 18.4zM288 256a32 32 0 1 0 -64 0 32 32 0 1 0 64 0z')),
        'globe'               => array('w'=>512,'h'=>512,'paths'=>array('M352 256c0 22.2-1.2 43.6-3.3 64H163.3c-2.2-20.4-3.3-41.8-3.3-64s1.2-43.6 3.3-64H348.7c2.2 20.4 3.3 41.8 3.3 64zm28.8-64h123.1c5.3 20.5 8.1 41.9 8.1 64s-2.8 43.5-8.1 64H380.8c2.1-20.6 3.2-42 3.2-64s-1.1-43.4-3.2-64zm112.6-32H376.7c-10-63.9-29.8-117.4-55.3-151.6 78.3 20.7 142 77.5 171.9 151.6zM192 32c16.6 3.2 27.8 13.8 39.1 24.7C241.6 67 251.2 83.2 259.5 101c11.8 26.2 21.3 59.3 27.2 95H225.3c-6-35.7-15.4-68.8-27.2-95C190.7 84.7 181.7 67 172.9 56.7 167.8 50 163.4 44.4 160 40.3c10.6-3.7 21.3-6.4 32-8.3zm-64 0c-10.6 3.7-21.3 6.4-32 8.3C79.4 58 63.8 82.8 53.4 115.7 47 136 42.2 159.7 40.3 184H163.7c5.9-35.7 15.4-68.8 27.2-95 5.2-12.7 11.4-24.8 18.7-35.7C208.8 34.3 200 32 192 32zm128 448c-16.6-3.2-27.8-13.8-39.1-24.7C270.4 444 260.8 428 252.5 410c-11.8-26.2-21.3-59.3-27.2-95h61.3c6 35.7 15.4 68.8 27.2 95 7.4 17.3 16.4 35 25.2 45.3 5.1 6.7 9.5 12.3 12.9 16.4-10.6 3.7-21.3 6.4-32 8.3zM64 256s.6 17.1 1.7 25.4H8.1C2.8 299.5 0 278.1 0 256s2.8-43.5 8.1-64H65.7C64.6 213 64 230.9 64 256zm384 0c0 25.1-.6 43-1.7 64h57.6c5.3-20.5 8.1-41.9 8.1-64s-2.8-43.5-8.1-64h-57.6c1.1 21 1.7 38.9 1.7 64z')),
        'flag'                => array('w'=>448,'h'=>512,'paths'=>array('M64 32C64 14.3 49.7 0 32 0S0 14.3 0 32v448c0 17.7 14.3 32 32 32s32-14.3 32-32V317.6l94.5-24.6c43.6-11.3 89.7-4.4 128.1 18.9 40.5 24.3 89 30.6 134.3 17.4l5.3-1.5c12.9-3.7 21.8-15.5 21.8-28.9V91.8c0-20.8-20.4-35.5-40.1-29.5-31.2 9.7-64.7 5.2-92.4-12.3C276.5 27.3 227.7 20.1 181.4 31.6L64 65.9V32z')),
        'users'               => array('w'=>640,'h'=>512,'paths'=>array('M96 128a128 128 0 1 1 256 0A128 128 0 1 1 96 128zM0 482.3C0 383.8 79.8 304 178.3 304h91.4C368.2 304 448 383.8 448 482.3c0 16.4-13.3 29.7-29.7 29.7H29.7C13.3 512 0 498.7 0 482.3zM609.3 512H471.5c5.4-9.4 8.6-20.3 8.6-32v-8c0-60.7-27.1-115.2-69.8-152.8 6.7-1.3 13.6-2 20.7-2h80.7C567.8 318 640 390.2 640 479.5c0 18-14.6 32.5-32.5 32.5zM432 256c-31 0-59-12.6-79.3-32.9C372.4 196.5 384 163.6 384 128c0-26.8-6.6-52.1-18.3-74.3C384.3 40.1 407.2 32 432 32c61.9 0 112 50.1 112 112s-50.1 112-112 112z')),
        'handshake'           => array('w'=>640,'h'=>512,'paths'=>array('M323.4 85.2l-96.8 78.4c-16.1 13-19.2 36.4-7 53.1 12.9 17.8 38 21.3 55.3 7.8l99.3-77.2c7-5.4 17-4.2 22.5 2.8s4.2 17-2.8 22.5l-20.9 16.2L550.2 352l-215 52.3L59.7 352l83.9-122.4L158 217.3C86.8 245.8 40.3 308.5 38.8 379l-.8 36.4c-1 44.2 32.9 81.1 77.1 82.1l429.3 8.9c44.2 1 81.1-32.9 82.1-77.1l.8-36.4c2.1-90.6-56.1-170.9-140.8-208.9l-4.8-2.1c3.8-11 3.3-23.3-2.1-34.2C469.7 115.2 440.9 98 409.5 98c-7.7 0-14.9 1.2-21.6 3.4L323.4 85.2z')),
        'thumbs-up'           => array('w'=>512,'h'=>512,'paths'=>array('M313.4 32.9c26 5.2 42.9 30.5 37.7 56.5l-2.3 11.4c-5.3 26.7-15.1 52.1-28.8 75.2h144c26.5 0 48 21.5 48 48 0 18.5-10.5 34.6-25.9 42.6C497 275.4 504 288.9 504 304c0 23.4-16.8 42.9-38.9 47.1 4.4 7.3 6.9 15.8 6.9 24.9 0 21.3-13.9 39.4-33.1 45.6.7 3.3 1.1 6.8 1.1 10.4 0 26.5-21.5 48-48 48H294.5c-19 0-37.5-5.6-53.3-16.1L202.7 438.4C176 420.4 160 390.4 160 358.3V320 288v-8c0-29.9 10.9-58.7 30.7-81.2l4.4-5 57.7-64.1c14.2-15.8 22.2-36.4 22.2-57.8 0-23 18.7-41.7 41.7-41.7 8.3 0 16.3 2.5 23.1 7.1zM64 336V128c0-26.5 21.5-48 48-48s48 21.5 48 48v208c0 26.5-21.5 48-48 48s-48-21.5-48-48z')),
        'star'                => array('w'=>576,'h'=>512,'paths'=>array('M316.9 18C311.6 7 300.4 0 288.1 0s-23.4 7-28.8 18L195 150.3 51.4 171.5c-12 1.8-22 10.2-25.7 21.7s-.7 24.2 7.9 32.7L137.8 329 113.2 474.7c-2 12 3 24.2 12.9 31.3s23 8 33.8 2.3l128.3-68.5 128.3 68.5c10.8 5.7 23.9 4.4 33.8-2.3s14.9-19.3 12.9-31.3L438.5 329 542.7 225.9c8.6-8.5 11.7-21.2 7.9-32.7s-13.7-19.9-25.7-21.7L381.2 150.3 316.9 18z')),
        'trophy'              => array('w'=>576,'h'=>512,'paths'=>array('M400 0H176c-26.5 0-48.1 21.8-47.1 48.2.2 5.3.4 10.6.7 15.8H24C10.7 64 0 74.7 0 88c0 92.6 33.5 157 78.5 200.7 44.3 43.1 98.3 64.8 138.1 75.8 23.4 6.5 39.4 26 39.4 45.6 0 20.9-17 37.9-37.9 37.9H192c-17.7 0-32 14.3-32 32s14.3 32 32 32h192c17.7 0 32-14.3 32-32s-14.3-32-32-32h-26.1C337 448 320 431 320 410.1c0-19.6 15.9-39.2 39.4-45.6 39.9-11 93.9-32.7 138.2-75.8C542.5 245 576 180.6 576 88c0-13.3-10.7-24-24-24H446.4c.3-5.2.5-10.4.7-15.8C448.1 21.8 426.5 0 400 0zM48.9 112h84.4c9.1 90.1 29.2 150.3 51.9 190.6C133.5 281.3 65.5 240.5 48.9 112zm427.8 190.6c22.7-40.3 42.8-100.5 51.9-190.6h84.4c-16.6 128.5-84.6 169.3-136.3 190.6z')),
        'circle-check'        => array('w'=>512,'h'=>512,'paths'=>array('M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209L241 337c-9.4 9.4-24.6 9.4-33.9 0l-64-64c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47L335 175c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z')),
        'check'               => array('w'=>448,'h'=>512,'paths'=>array('M438.6 105.4c12.5 12.5 12.5 32.8 0 45.3l-256 256c-12.5 12.5-32.8 12.5-45.3 0l-128-128c-12.5-12.5-12.5-32.8 0-45.3s32.5-12.5 45.3 0L160 338.7 393.4 105.4c12.5-12.5 32.8-12.5 45.3 0z')),
        'hard-hat'            => array('w'=>512,'h'=>512,'paths'=>array('M256 16c-18.6 0-36.5 3.8-52.8 10.5C202.8 50.8 208 78.1 208 112h96c0-33.9 5.2-61.2 4.8-85.5C292.5 19.8 274.6 16 256 16zm-48 96c-13.3 0-26.5 1.1-39.3 3.2C102.7 133.7 48 195.3 48 272h16 384 16c0-76.7-54.7-138.3-120.7-156.8C329.5 113.1 316.3 112 303.1 112H208zM16 368c0 8.8 7.2 16 16 16h448c8.8 0 16-7.2 16-16v-48H16v48zm0 96c0 26.5 21.5 48 48 48h384c26.5 0 48-21.5 48-48v-48H16v48z')),
        'wrench'              => array('w'=>512,'h'=>512,'paths'=>array('M352 320c88.4 0 160-71.6 160-160 0-15.3-2.2-30.1-6.2-44.2-3.1-10.8-16.4-13.2-24.3-5.3l-76.8 76.8c-3 3-7.1 4.7-11.3 4.7H336c-8.8 0-16-7.2-16-16v-57.4c0-4.2 1.7-8.3 4.7-11.3l76.8-76.8c7.9-7.9 5.4-21.2-5.3-24.3C382.1 2.2 367.3 0 352 0 263.6 0 192 71.6 192 160c0 19.1 3.4 37.5 9.5 54.5L19.9 396.1C7.2 408.8 0 426.1 0 444.1 0 481.6 30.4 512 67.9 512c18 0 35.3-7.2 48-19.9L297.5 310.5c17 6.2 35.4 9.5 54.5 9.5z')),
        'gear'                => array('w'=>512,'h'=>512,'paths'=>array('M495.9 166.6c3.2 8.7.5 18.4-6.4 24.6l-43.3 39.4c1.1 8.3 1.7 16.8 1.7 25.4s-.6 17.1-1.7 25.4l43.3 39.4c6.9 6.3 9.6 15.9 6.4 24.6-4.4 11.9-9.7 23.3-15.8 34.3l-4.7 8.1c-6.6 11-14 21.4-22.1 31.2-5.9 7.2-15.7 9.6-24.5 6.8l-55.7-17.7c-13.4 10.3-28.2 18.9-44 25.4L317 490.7c-2 9.1-9 16.3-18.2 17.8C284.5 510.8 270.2 512 256 512s-28.5-1.2-42.8-3.5c-9.2-1.5-16.2-8.7-18.2-17.8l-12.5-57.1c-15.8-6.5-30.6-15.1-44-25.4l-55.7 17.7c-8.8 2.8-18.6.3-24.5-6.8-8.1-9.8-15.5-20.2-22.1-31.2l-4.7-8.1c-6.1-11-11.4-22.4-15.8-34.3-3.2-8.7-.5-18.4 6.4-24.6l43.3-39.4C64.6 273.1 64 264.6 64 256s.6-17.1 1.7-25.4L22.4 191.2c-6.9-6.3-9.6-15.9-6.4-24.6 4.4-11.9 9.7-23.3 15.8-34.3l4.7-8.1c6.6-11 14-21.4 22.1-31.2 5.9-7.2 15.7-9.6 24.5-6.8l55.7 17.7c13.4-10.3 28.2-18.9 44-25.4l12.5-57.1c2-9.1 9-16.3 18.2-17.8C227.5 1.2 241.8 0 256 0s28.5 1.2 42.8 3.5c9.2 1.5 16.2 8.7 18.2 17.8l12.5 57.1c15.8 6.5 30.6 15.1 44 25.4l55.7-17.7c8.8-2.8 18.6-.3 24.5 6.8 8.1 9.8 15.5 20.2 22.1 31.2l4.7 8.1c6.1 11 11.4 22.4 15.8 34.3zM256 336a80 80 0 1 0 0-160 80 80 0 1 0 0 160z')),
        'bolt'                => array('w'=>448,'h'=>512,'paths'=>array('M349.4 44.6c5.9-13.7 1.5-29.7-10.6-38.5s-28.6-8-39.9 1.8l-256 224c-10 8.8-13.6 22.9-8.9 35.3S50.7 288 64 288h111.5L98.6 467.4c-5.9 13.7-1.5 29.7 10.6 38.5s28.6 8 39.9-1.8l256-224c10-8.8 13.6-22.9 8.9-35.3s-16.6-20.7-30-20.7H272.5L349.4 44.6z')),
        'lightbulb'           => array('w'=>384,'h'=>512,'paths'=>array('M272 384c9.6-31.9 29.5-59.1 49.2-86.2 5.2-7.1 10.4-14.2 15.4-21.4 19.8-28.5 31.4-63 31.4-100.3C368 78.8 289.2 0 192 0S16 78.8 16 176c0 37.3 11.6 71.9 31.4 100.3 5 7.2 10.2 14.3 15.4 21.4 19.8 27.1 39.7 54.4 49.2 86.2h160zM192 512c44.2 0 80-35.8 80-80v-16H112v16c0 44.2 35.8 80 80 80zM112 176c0 8.8-7.2 16-16 16s-16-7.2-16-16c0-61.9 50.1-112 112-112 8.8 0 16 7.2 16 16s-7.2 16-16 16c-44.2 0-80 35.8-80 80z')),
        'fire'                => array('w'=>448,'h'=>512,'paths'=>array('M159.3 5.4c7.8-7.3 19.9-7.2 27.7.1 27.6 25.9 53.5 53.8 79.8 81.3C298.6 59 299.4 30.9 288 3.4c-.5-1.3.1-2.8 1.5-3.4 7-3 15.3-.2 19.7 6.3 52.5 78 52.8 150.2 52.8 171.7 0 78.4-73.5 123-159.5 123-85.9 0-159.5-44.6-159.5-123 0-53.1 16.9-107.4 57.4-142.1.8-.7 2-.3 2.4.7 1.9 5.7 3.3 13.3 3.3 23.1 0 68.4-69.2 129.6-69.2 194.3 0 79.4 64.4 143.8 143.8 143.8 12.4 0 24.4-1.7 35.9-4.8 16-4.4 30.9-11.5 44.3-20.9 7.4-5.1 9.4-15.4 4.4-22.8-5-7.4-15.4-9.4-22.8-4.4-17.6 12.2-39.6 19.4-63.3 19.4-56.9 0-103.2-46.3-103.2-103.2 0-23.4 5.2-47.3 15.4-69.8 3.6-8 13-11.7 21-8.2 8 3.6 11.7 13 8.2 21-7.8 17.3-11.9 35.9-11.9 54.7 0 46.5 37.6 84.1 84.1 84.1')),
        'wind'                => array('w'=>512,'h'=>512,'paths'=>array('M288 32c0 17.7 14.3 32 32 32h32c17.7 0 32 14.3 32 32s-14.3 32-32 32H32c-17.7 0-32 14.3-32 32s14.3 32 32 32h320c53 0 96-43 96-96s-43-96-96-96h-32c-17.7 0-32 14.3-32 32zm64 352c0 17.7 14.3 32 32 32h32c53 0 96-43 96-96s-43-96-96-96H128c-17.7 0-32 14.3-32 32s14.3 32 32 32h288c17.7 0 32 14.3 32 32s-14.3 32-32 32h-32c-17.7 0-32 14.3-32 32zM128 512h32c53 0 96-43 96-96s-43-96-96-96H32c-17.7 0-32 14.3-32 32s14.3 32 32 32h128c17.7 0 32 14.3 32 32s-14.3 32-32 32h-32c-17.7 0-32 14.3-32 32s14.3 32 32 32z')),
        'droplet'             => array('w'=>384,'h'=>512,'paths'=>array('M192 512C86 512 0 426 0 320 0 228.8 130.2 112.4 166.3 80.6c9-8.1 22.4-8.1 31.4 0C233.8 112.4 384 228.8 384 320c0 106-86 192-192 192zM96 336c0-8.8-7.2-16-16-16s-16 7.2-16 16c0 61.9 50.1 112 112 112 8.8 0 16-7.2 16-16s-7.2-16-16-16c-44.2 0-80-35.8-80-80z')),
        'building'            => array('w'=>384,'h'=>512,'paths'=>array('M48 0C21.5 0 0 21.5 0 48v416c0 26.5 21.5 48 48 48h96v-80c0-26.5 21.5-48 48-48s48 21.5 48 48v80h96c26.5 0 48-21.5 48-48V48c0-26.5-21.5-48-48-48H48zM64 240c0-8.8 7.2-16 16-16h32c8.8 0 16 7.2 16 16v32c0 8.8-7.2 16-16 16H80c-8.8 0-16-7.2-16-16v-32zm112-16h32c8.8 0 16 7.2 16 16v32c0 8.8-7.2 16-16 16h-32c-8.8 0-16-7.2-16-16v-32c0-8.8 7.2-16 16-16zm80 16c0-8.8 7.2-16 16-16h32c8.8 0 16 7.2 16 16v32c0 8.8-7.2 16-16 16h-32c-8.8 0-16-7.2-16-16v-32zM64 96c0-8.8 7.2-16 16-16h32c8.8 0 16 7.2 16 16v32c0 8.8-7.2 16-16 16H80c-8.8 0-16-7.2-16-16V96zm112-16h32c8.8 0 16 7.2 16 16v32c0 8.8-7.2 16-16 16h-32c-8.8 0-16-7.2-16-16V96c0-8.8 7.2-16 16-16zm80 16c0-8.8 7.2-16 16-16h32c8.8 0 16 7.2 16 16v32c0 8.8-7.2 16-16 16h-32c-8.8 0-16-7.2-16-16V96z')),
        'house'               => array('w'=>576,'h'=>512,'paths'=>array('M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1v16.2c0 22.1-17.9 40-40 40h-16c-1.1 0-2.2 0-3.3-.1-1.4.1-2.8.1-4.2.1H416l-24 0c-22.1 0-40-17.9-40-40v-24 -64c0-17.7-14.3-32-32-32h-64c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40l-24 0-31.9 0c-1.5 0-3-.1-4.5-.2-1.2.1-2.4.2-3.6.2h-16c-22.1 0-40-17.9-40-40V368 256l-32 0c-18 0-32-14-32-32.1 0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z')),
        'building-columns'    => array('w'=>512,'h'=>512,'paths'=>array('M243.4 2.6l-224 96c-14 6-21.8 21-18.7 35.8S16.8 160 32 160v8c0 13.3 10.7 24 24 24h400c13.3 0 24-10.7 24-24v-8c15.2 0 28.3-10.7 31.3-25.6s-4.6-29.8-18.7-35.8l-224-96c-8.1-3.4-17.2-3.4-25.3 0zM128 224H64v196.3c-.6.3-1.2.7-1.8 1.1l-48 32c-11.7 7.8-17 22.4-12.9 35.9S17.9 512 32 512h448c14.1 0 26.5-9.2 30.6-22.7s-1.1-28.1-12.9-35.9l-48-32c-.6-.4-1.2-.7-1.8-1.1V224h-64v192h-40V224h-64v192h-48V224h-64v192h-40V224z')),
        'school'              => array('w'=>640,'h'=>512,'paths'=>array('M320 32c-8.1 0-16.1 1.4-23.7 4.1L15.8 137.4C6.3 140.9 0 149.9 0 160s6.3 19.1 15.8 22.6l280.6 101.3c7.6 2.7 15.6 4.1 23.7 4.1s16.1-1.4 23.7-4.1L624.2 182.6c9.5-3.4 15.8-12.5 15.8-22.6s-6.3-19.1-15.8-22.6L343.7 36.1C336.1 33.4 328.1 32 320 32zM128 408c0 35.3 86 72 192 72s192-36.7 192-72V262.9l-161.7 58.4c-10.1 3.6-20.8 5.5-31.8 5.7h-1.4c-11-.2-21.7-2.1-31.8-5.7L128 262.9V408zM576 227.2V416c0 11-7.4 20.8-18.1 23.7-16.6 4.5-40.6 9.3-80.6 12.1C491.7 445.5 496 438.1 496 430v-60.7l80-142.1z')),
        'graduation-cap'      => array('w'=>640,'h'=>512,'paths'=>array('M320 32c-8.1 0-16.1 1.4-23.7 4.1L15.8 137.4C6.3 140.9 0 149.9 0 160s6.3 19.1 15.8 22.6l280.6 101.3c7.6 2.7 15.6 4.1 23.7 4.1s16.1-1.4 23.7-4.1L624.2 182.6c9.5-3.4 15.8-12.5 15.8-22.6s-6.3-19.1-15.8-22.6L343.7 36.1C336.1 33.4 328.1 32 320 32zM128 408c0 35.3 86 72 192 72s192-36.7 192-72V262.9l-161.7 58.4c-10.1 3.6-20.8 5.5-31.8 5.7h-1.4c-11-.2-21.7-2.1-31.8-5.7L128 262.9V408z')),
        'hospital'            => array('w'=>512,'h'=>512,'paths'=>array('M192 0c-17.7 0-32 14.3-32 32v32H96C60.7 64 32 92.7 32 128v320c0 35.3 28.7 64 64 64h320c35.3 0 64-28.7 64-64V128c0-35.3-28.7-64-64-64h-64V32c0-17.7-14.3-32-32-32H192zM96 224c0-17.7 14.3-32 32-32h64v-64c0-17.7 14.3-32 32-32h64c17.7 0 32 14.3 32 32v64h64c17.7 0 32 14.3 32 32v64c0 17.7-14.3 32-32 32h-64v64c0 17.7-14.3 32-32 32h-64c-17.7 0-32-14.3-32-32v-64H128c-17.7 0-32-14.3-32-32v-64z')),
        'city'                => array('w'=>640,'h'=>512,'paths'=>array('M0 48C0 21.5 21.5 0 48 0h320c26.5 0 48 21.5 48 48v464H48 0V48zM64 240c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16v-32c0-8.8-7.2-16-16-16H80c-8.8 0-16 7.2-16 16v32zm112-16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16v-32c0-8.8-7.2-16-16-16h-32c-8.8 0-16 7.2-16 16zm80 16c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16v-32c0-8.8-7.2-16-16-16h-32c-8.8 0-16 7.2-16 16v32zM64 112v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16v-32c0-8.8-7.2-16-16-16H80c-8.8 0-16 7.2-16 16zm112-16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16v-32c0-8.8-7.2-16-16-16h-32c-8.8 0-16 7.2-16 16zm80 16c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16v-32c0-8.8-7.2-16-16-16h-32c-8.8 0-16 7.2-16 16v32zM416 96v320h96v-48h-32c-17.7 0-32-14.3-32-32v-32c0-17.7 14.3-32 32-32h32v-48h-32c-17.7 0-32-14.3-32-32v-32c0-17.7 14.3-32 32-32h32V96h-96zm160 224v32h32v-32h-32zm0-96v32h32v-32h-32zm-64 256h32 64c26.5 0 48-21.5 48-48V256c0-26.5-21.5-48-48-48h-96v288z')),
        'chart-bar'           => array('w'=>448,'h'=>512,'paths'=>array('M160 80c0-26.5 21.5-48 48-48h32c26.5 0 48 21.5 48 48v352c0 26.5-21.5 48-48 48h-32c-26.5 0-48-21.5-48-48V80zM0 272c0-26.5 21.5-48 48-48h32c26.5 0 48 21.5 48 48v160c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V272zm368-176h32c26.5 0 48 21.5 48 48v288c0 26.5-21.5 48-48 48h-32c-26.5 0-48-21.5-48-48V144c0-26.5 21.5-48 48-48z')),
        'magnifying-glass'    => array('w'=>512,'h'=>512,'paths'=>array('M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0s208 93.1 208 208zm-208 144a144 144 0 1 0 0-288 144 144 0 1 0 0 288z')),
        'phone'               => array('w'=>512,'h'=>512,'paths'=>array('M164.9 24.6c-7.7-18.6-28-28.5-47.4-23.2l-88 24C12.1 30.2 0 46 0 64 0 311.4 200.6 512 448 512c18 0 33.8-12.1 38.6-29.5l24-88c5.3-19.4-4.6-39.7-23.2-47.4l-96-40c-16.3-6.8-35.2-2.1-46.3 11.6L304.7 368C234.3 334.7 177.3 277.7 144 207.3L193.3 167c13.7-11.2 18.4-30 11.6-46.3l-40-96z')),
        'envelope'            => array('w'=>512,'h'=>512,'paths'=>array('M48 64C21.5 64 0 85.5 0 112c0 15.1 7.1 29.3 19.2 38.4L236.8 313.6c11.4 8.5 27 8.5 38.4 0L492.8 150.4c12.1-9.1 19.2-23.3 19.2-38.4 0-26.5-21.5-48-48-48H48zM0 176v208c0 35.3 28.7 64 64 64h384c35.3 0 64-28.7 64-64V176L294.4 339.2c-22.8 17.1-54 17.1-76.8 0L0 176z')),
        'bullhorn'            => array('w'=>512,'h'=>512,'paths'=>array('M480 32c0-12.9-7.8-24.6-19.8-29.6S434.5 0 425.3 9.2L381.7 53c-48 48-113.1 75-181 75H112 88C39.4 128 0 167.4 0 216v48c0 48.6 39.4 88 88 88h0 16 8v96c0 17.7 14.3 32 32 32h64c17.7 0 32-14.3 32-32V352h.3c68 0 133 27 181 75l43.7 43.7c9.2 9.2 22.9 11.9 34.9 6.9S480 460.9 480 448V32z')),
        'leaf'                => array('w'=>512,'h'=>512,'paths'=>array('M272 96c-78.6 0-145.1 51.5-167.7 122.5 33.6-17 71.5-26.5 111.7-26.5h88c8.8 0 16 7.2 16 16s-7.2 16-16 16H216c-16.6 0-32.7 1.9-48.3 5.4-25.9 5.9-49.9 16.4-71.4 30.7v6.3c0 70.7 57.3 128 128 128h176v-96c0-35.3-28.7-64-64-64h-8c-8.8 0-16-7.2-16-16s7.2-16 16-16h8c53 0 96 43 96 96v96h32 32V224c0-88.4-71.6-160-160-160h-32z')),
        'sun'                 => array('w'=>512,'h'=>512,'paths'=>array('M361.5 1.2c5 2.1 8.6 6.6 9.6 11.9L391 121l107.9 19.8c5.3 1 9.8 4.6 11.9 9.6s1.5 10.7-1.6 15.2L446.9 256l62.3 90.3c3.1 4.5 3.7 10.2 1.6 15.2s-6.6 8.6-11.9 9.6L391 391 371.1 498.9c-1 5.3-4.6 9.8-9.6 11.9s-10.7 1.5-15.2-1.6L256 446.9l-90.3 62.3c-4.5 3.1-10.2 3.7-15.2 1.6s-8.6-6.6-9.6-11.9L121 391 13.1 371.1c-5.3-1-9.8-4.6-11.9-9.6s-1.5-10.7 1.6-15.2L65.1 256 2.8 165.7c-3.1-4.5-3.7-10.2-1.6-15.2s6.6-8.6 11.9-9.6L121 121 140.9 13.1c1-5.3 4.6-9.8 9.6-11.9s10.7-1.5 15.2 1.6L256 65.1 346.3 2.8c4.5-3.1 10.2-3.7 15.2-1.6zM160 256a96 96 0 1 1 192 0 96 96 0 1 1 -192 0z')),
        'truck'               => array('w'=>640,'h'=>512,'paths'=>array('M48 0C21.5 0 0 21.5 0 48v320c0 26.5 21.5 48 48 48h16c0 53 43 96 96 96s96-43 96-96h128c0 53 43 96 96 96s96-43 96-96h32c17.7 0 32-14.3 32-32s-14.3-32-32-32v-64 -32 -18.7c0-17-6.7-33.3-18.7-45.3L512 114.7c-12-12-28.3-18.7-45.3-18.7H416V48c0-26.5-21.5-48-48-48H48zM416 160h50.7L544 237.3v18.7H416V160zm-208 96a48 48 0 1 1 0 96 48 48 0 1 1 0-96zm272 48a48 48 0 1 1 96 0 48 48 0 1 1 -96 0z')),
        'calendar'            => array('w'=>448,'h'=>512,'paths'=>array('M152 24c0-13.3-10.7-24-24-24s-24 10.7-24 24v40H64C28.7 64 0 92.7 0 128v16 48V448c0 35.3 28.7 64 64 64h320c35.3 0 64-28.7 64-64V192 144 128c0-35.3-28.7-64-64-64h-40V24c0-13.3-10.7-24-24-24s-24 10.7-24 24v40H152V24zM48 192h352v256c0 8.8-7.2 16-16 16H64c-8.8 0-16-7.2-16-16V192z')),
        'clipboard-list'      => array('w'=>384,'h'=>512,'paths'=>array('M192 0c-41.8 0-77.4 26.7-90.5 64H64C28.7 64 0 92.7 0 128v320c0 35.3 28.7 64 64 64h256c35.3 0 64-28.7 64-64V128c0-35.3-28.7-64-64-64H282.5C269.4 26.7 233.8 0 192 0zm0 64a32 32 0 1 1 0 64 32 32 0 1 1 0-64zm-32 160h128c8.8 0 16 7.2 16 16s-7.2 16-16 16H160c-8.8 0-16-7.2-16-16s7.2-16 16-16zm0 64h128c8.8 0 16 7.2 16 16s-7.2 16-16 16H160c-8.8 0-16-7.2-16-16s7.2-16 16-16zm0 64h128c8.8 0 16 7.2 16 16s-7.2 16-16 16H160c-8.8 0-16-7.2-16-16s7.2-16 16-16zM96 256a32 32 0 1 1 64 0 32 32 0 1 1 -64 0zm32 64a32 32 0 1 1 0 64 32 32 0 1 1 0-64zm0 96a32 32 0 1 1 0 64 32 32 0 1 1 0-64z')),
        'layer-group'         => array('w'=>576,'h'=>512,'paths'=>array('M264.5 5.2c14.9-6.9 32.1-6.9 47 0l218.6 101c8.5 3.9 13.9 12.4 13.9 21.8s-5.4 17.9-13.9 21.8L311.5 250.8c-14.9 6.9-32.1 6.9-47 0L45.9 149.8C37.4 145.8 32 137.3 32 128s5.4-17.9 13.9-21.8L264.5 5.2zm212.4 204.4l53.2 24.6c8.5 3.9 13.9 12.4 13.9 21.8s-5.4 17.9-13.9 21.8L311.5 378.8c-14.9 6.9-32.1 6.9-47 0L45.9 277.8C37.4 273.8 32 265.3 32 256s5.4-17.9 13.9-21.8l53.2-24.6 152 70.2c23.4 10.8 50.4 10.8 73.8 0l152-70.2zm0 96l53.2 24.6c8.5 3.9 13.9 12.4 13.9 21.8s-5.4 17.9-13.9 21.8L311.5 474.8c-14.9 6.9-32.1 6.9-47 0L45.9 373.8C37.4 369.8 32 361.3 32 352s5.4-17.9 13.9-21.8l53.2-24.6 152 70.2c23.4 10.8 50.4 10.8 73.8 0l152-70.2z')),
        'circle-info'         => array('w'=>512,'h'=>512,'paths'=>array('M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zm16-352c0-8.8-7.2-16-16-16s-16 7.2-16 16v112h-16c-8.8 0-16 7.2-16 16s7.2 16 16 16h48 16c8.8 0 16-7.2 16-16s-7.2-16-16-16h-16V160zm-16-48a32 32 0 1 1 0-64 32 32 0 1 1 0 64z')),
        'triangle-exclamation'=> array('w'=>512,'h'=>512,'paths'=>array('M256 32c14.2 0 27.3 7.5 34.5 19.8l216 368c7.3 12.4 7.3 27.7.2 40.1S486.3 480 472 480H40c-14.3 0-27.6-7.7-34.7-20.1s-7-27.8.2-40.1l216-368C228.7 39.5 241.8 32 256 32zm0 128c-13.3 0-24 10.7-24 24v112c0 13.3 10.7 24 24 24s24-10.7 24-24V184c0-13.3-10.7-24-24-24zm32 224a32 32 0 1 0 -64 0 32 32 0 1 0 64 0z')),
        'arrow-right'         => array('w'=>448,'h'=>512,'paths'=>array('M438.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L338.8 224 32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32h306.7L233.4 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z')),
        'rocket'              => array('w'=>512,'h'=>512,'paths'=>array('M156.6 384.9L125.7 353.1c-10.9-10.9-28.7-10.9-39.6 0l-31.1 31.1c-4.6 4.6-5.3 12.5-1.5 17.8l33.6 40.4c7.7 9.2 20.8 11.6 31.1 5.7l42.8-24.7c11.1-6.4 13.9-21.8 3.6-33.6-11.2-12.8-11-30.3 0-41.3zM233.7 17.2c-8.3-8.3-21.3-10.5-32-5.4L103 68.9c-13.8 6.5-20.9 21.9-16.7 36.6L119.8 216l35.4-35.4c31.5 31.5 47.5 73.1 47.5 114.7H16c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h32v96c0 17.7 14.3 32 32 32s32-14.3 32-32v-96h32v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16v-48c0-44.2 15.3-87.4 43.7-121.7l50.7-60.8c5.4-6.5 5.6-16 .5-22.7L233.7 17.2z')),
        'person-digging'      => array('w'=>512,'h'=>512,'paths'=>array('M192 96a48 48 0 1 0 0-96 48 48 0 1 0 0 96zm-8 352v-128c0-17.7 14.3-32 32-32h96c17.7 0 32 14.3 32 32v128c0 17.7 14.3 32 32 32s32-14.3 32-32V339.9l45.3 45.3c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L375 197.5C363.2 185.6 346.9 178 329 178h-97.9c-16.8 0-33 6.7-44.9 18.6L80.7 302.2c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L152 321.5V448c0 17.7 14.3 32 32 32s32-14.3 32-32z')),
        'plug'                => array('w'=>384,'h'=>512,'paths'=>array('M96 0C78.3 0 64 14.3 64 32v96h256V32c0-17.7-14.3-32-32-32H96zM64 192c-35.3 0-64 28.7-64 64v16c0 61.9 40 114.9 96 133.4V448c0 17.7 14.3 32 32 32h128c17.7 0 32-14.3 32-32v-42.6c56-18.6 96-71.5 96-133.4v-16c0-35.3-28.7-64-64-64H64z')),
        'seedling'            => array('w'=>512,'h'=>512,'paths'=>array('M512 32c0 113.6-84.6 207.9-194.2 222.9L477.4 415c8.4 10 7.1 25.1-2.9 33.5s-25.1 7.1-33.5-2.9L320 301.7V448c0 17.7-14.3 32-32 32s-32-14.3-32-32V269.7L132.7 385.5c-8.4 10-23.5 11.4-33.5 2.9S92 355.9 100.5 345.9L244.2 192H216c-17.7 0-32-14.3-32-32V64C184 28.7 212.7 0 248 0h232c17.7 0 32 14.3 32 32z')),
        'tag'                 => array('w'=>448,'h'=>512,'paths'=>array('M0 80v149.5c0 17 6.7 33.3 18.7 45.3l176 176c25 25 65.5 25 90.5 0l109.3-109.3c25-25 25-65.5 0-90.5l-176-176c-12-12-28.3-18.7-45.3-18.7L64 56c0-26.5 21.5-48 48-48H64C28.7 8 0 36.7 0 72v8zm112 84a28 28 0 1 1 56 0 28 28 0 1 1 -56 0z')),
    );
}


// Register custom image crop sizes for optimized source sets
add_action( 'after_setup_theme', 'e3es_register_custom_image_sizes' );
function e3es_register_custom_image_sizes() {
    add_image_size( 'e3-logo', 300, 115, false );
    add_image_size( 'e3-feature', 600, 400, true );
    add_image_size( 'e3-case-study', 800, 600, true );
    add_image_size( 'e3-card', 400, 300, true );
    add_image_size( 'e3-portrait', 400, 400, true );
    add_image_size( 'e3-service', 500, 220, true );
}

// Enable SVG uploads in Media Library
add_filter( 'upload_mimes', 'e3es_enable_svg_uploads' );
function e3es_enable_svg_uploads( $mimes ) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}


function e3_render_texas_map( $attributes, $content ) {
    // Region configuration
    $regions = array(
        array( 'key' => 'panhandle',    'attr' => 'panhandle',   'label' => 'Far West Texas' ),
        array( 'key' => 'west',         'attr' => 'west',        'label' => 'West Texas' ),
        array( 'key' => 'north',        'attr' => 'north',       'label' => 'North Texas' ),
        array( 'key' => 'northeast',    'attr' => 'northeast',   'label' => 'North East Texas' ),
        array( 'key' => 'southeast',    'attr' => 'southeast',   'label' => 'South East Texas' ),
        array( 'key' => 'central',      'attr' => 'central',     'label' => 'Central Texas' ),
        array( 'key' => 'hill-country', 'attr' => 'hillCountry', 'label' => 'Hill Country' ),
        array( 'key' => 'south',        'attr' => 'south',       'label' => 'South Texas' ),
    );

    // Extract default content
    $default_headline = ! empty( $attributes['defaultHeadline'] ) ? $attributes['defaultHeadline'] : 'Texas Educational Facility Upgrades';
    $default_text     = ! empty( $attributes['defaultText'] )     ? $attributes['defaultText']     : 'E3 Entegral Solutions is a premier Texas-based energy services company, partnering with public school districts statewide to modernize classrooms, upgrade mechanical systems, improve indoor air quality, and secure funding—all while delivering guaranteed utility savings. Click a region on the map or use the buttons below to explore our local project success stories.';
    $default_photo    = ! empty( $attributes['defaultPhoto'] )    ? $attributes['defaultPhoto']    : 'http://e3es2026.local/wp-content/uploads/2026/06/Texas-Funding-Solutions-600x400-2.jpg';

    // Build region data for JSON output
    // Fallback relative slugs mapping
    $slug_map = array(
        'panhandle'    => '/k12/far-west-texas',
        'west'         => '/k12/west-texas',
        'north'        => '/k12/north-texas',
        'northeast'    => '/k12/north-east-texas',
        'southeast'    => '/k12/south-east-texas',
        'central'      => '/k12/central-texas',
        'hill-country' => '/k12/hill-country',
        'south'        => '/k12/south-texas',
    );

    // Build region data for JSON output
    $region_data = array();
    foreach ( $regions as $r ) {
        $p       = $r['attr'];
        $page_id = ! empty( $attributes[ $p . 'LinkPageId' ] ) ? (int) $attributes[ $p . 'LinkPageId' ] : 0;
        $link    = $page_id ? get_permalink( $page_id ) : '';

        if ( $link ) {
            $link = wp_make_link_relative( $link );
            $link = str_replace( '/home/industries/', '/', $link );
            $link = str_replace( '/home/our-approach/', '/', $link );
            $link = str_replace( '/home/about-us/', '/', $link );
            $link = str_replace( '/home/', '/', $link );
            
            $clean_path = trim( $link, '/' );
            $regions_list = array( 'panhandle', 'east-texas', 'west-texas', 'north-texas', 'south-texas', 'central-texas', 'hill-country', 'south-east-texas', 'north-east-texas', 'far-west-texas' );
            if ( in_array( $clean_path, $regions_list ) ) {
                if ( $clean_path === 'panhandle' ) {
                    $clean_path = 'far-west-texas';
                }
                $link = '/k12/' . $clean_path;
            }
        }

        if ( ! $link && isset( $slug_map[ $r['key'] ] ) ) {
            $link = $slug_map[ $r['key'] ];
        }

        $region_data[ $r['key'] ] = array(
            'headline' => ! empty( $attributes[ $p . 'Headline' ] ) ? $attributes[ $p . 'Headline' ] : $r['label'],
            'text'     => ! empty( $attributes[ $p . 'Text' ] )     ? $attributes[ $p . 'Text' ]     : '',
            'photo'    => ! empty( $attributes[ $p . 'Photo' ] )    ? $attributes[ $p . 'Photo' ]    : '',
            'linkUrl'  => $link ? $link : '',
        );
    }

    $json_data = wp_json_encode( array(
        'default' => array(
            'headline' => $default_headline,
            'text'     => $default_text,
            'photo'    => $default_photo,
        ),
        'regions' => $region_data,
    ) );

    ob_start();
    ?>
    <section class="map-section">
        <div class="map-container">
            <div class="map-left">
                <svg id="texas-map-svg" viewBox="0 0 941.76 907.17" class="texas-svg-map" xmlns="http://www.w3.org/2000/svg">
                  <defs>
                    <style>
                      .texas-svg-map { width: 100%; max-width: 650px; height: auto; filter: drop-shadow(0 10px 20px rgba(0,0,0,0.15)); pointer-events: none; }
                      .texas-region { cursor: pointer; pointer-events: all; }
                      .texas-region path, .texas-region polygon, .texas-region rect { transition: all 0.3s ease; }
                      .texas-svg-map.has-active .texas-region:not(.active) path, .texas-svg-map.has-active .texas-region:not(.active) polygon, .texas-svg-map.has-active .texas-region:not(.active) rect { fill: #d3dbd5 !important; transition: fill 0.3s ease; }
                      .texas-region:hover, .texas-region.active { filter: brightness(1.2) drop-shadow(0 8px 16px rgba(0,0,0,0.5)); stroke: #ffffff; stroke-width: 4px; }
                      .cls-1 { fill: #2b5434; }
                      .cls-2 { fill: #598e64; }
                      .cls-3 { fill: #31623d; }
                      .cls-4 { fill: #034411; }
                      .cls-5 { fill: #5b8764; }
                      .cls-6 { fill: #65b776; }
                      .cls-7 { fill: #54725a; }
                      .cls-8 { fill: #115620; }
                      .cls-9 { fill: none; }
                    </style>
                  </defs>
                  <g id="Layer_1-2" data-name="Layer 1">
                    <g class="texas-region" data-region="panhandle">
                      <path class="cls-1" d="M625.37,271.22c-3.01-.1-4.59-.27-6.39-3.06-6.58-9.19-16.48,14.39-19.8,2.98.36-12.2-8.33-1.94-8.68-9.28-.05-4.19-1.51-9.2-6.52-5.59-3.56.59-9.11-1.64-12.88-1.13-2.48-.02-1.4,6.05-4.73,4.49-6.32-7.95-15.5-1.81-23.12-6.03-3.75-3.62-10.11-1.56-14.61-3.1-2.33-1.43-.54-4.57-2.19-6.73-2.42-2.19-3.04-7.27-6.69-7.09-4.38-1.98-5.68-3.6-8.22,1.67-2.51.26-2.98-2.32-6.84-.25-10.36,5.8-11.52-9.11-19.29-11.6-1.46-1.04-4.6,0-5.36-1.51-1.15-34.93,1.6-104.83-1.86-133.08-55.21.18-122.84-1.67-173.01-2.19-1.76,103.62-6.17,210.23-9.82,313.06-27.93-.6-84.62-3.23-108.69-3.72-1.97,27.93-1.31,53.4-3.71,83.08-.94,7.07,1.81,8.26-6.01,10.84-4.13,6.35,5.25.95,6.4,18.25,1.89,5.53,5.99,9.43,8.57,14.47-.31,16.55-4.5,15.32,6.36,30.43.88,10.24,10.27,16.23,18.87,20.16,5.92,9.06,14.63,16.01,25.45,18.46,4.36,4.92,10.16,9.4,16.94,9.86,6.97,7.77,25.03,19.67,31.44,5.88,2.02-1.99,3.99-5.7,6.61-7.36,1.98-1,4.46-1.67,5.74-3.68,1.35-2.35-.84-5.87,1.16-8.11,3.61-4.48,3.61-10.47,6.72-15.26,1.02-4.57,4.97-7.46,6.35-11.46,4.79-2.35,10.8-1.55,15.86-2.66,3.34-1.38,3.24-8.25,8.02-4.82,9.84,5.84,10.08,2.93,19.54,5.2,3.29-.09,11.61.56,11.61.56,0,0,.22-29.67,1.17-33.49-.73-5.57,3.61-4.21,5.18-7.68-3.27-7.86,7.46-18.36-1.92-24.22-1.19-.7-3.29.1-3.66-1.63-2.85-13.26.88-20.67-11.7-20.97-2.88-.07-20.83-4.72-20.83-4.72l66.15,1.83s-.1-49.4.39-67.86c34.3-.18,102.16-2.52,134.48-.3,2.9,4.56,12.03,20.41,14.34,24.02,2.55,2.23,6.92-.89,10.06,0,2.14.45,2.61,2.32,4.1,1.84,4.84-3.34,15.89-8.44,19.17-12.65-11.29-23.53-5.92,5.32-22.26-27.27-.57-1.71,1.94-2.31,2.6-4.28.67-4.47.02-10.01.06-14.42-.17-2.93-4-1.41-5.52-2.68-.96-5.37-.47-20.63-.68-25.25.81-3.39,7.13.29,8.44-3.22.39-1.12.68-2.24,2.09-2.13,5.28-.02,20.58.06,26.87-.04.85-.06.81-.77.82-1.45.15-4.35-.61-21.34-.12-28.22,6.3-1.31,17.85-.4,24.51-1.2,1.75-4.97-.61-28.35-.03-34.32.1-.71-.37-1.25-.94-1.39Z" />
                    </g>
                    <g class="texas-region" data-region="north">
                      <path class="cls-2" d="M715.7,340.75c.36-4.12,1.69-5.37,5.57-5.84,1.48-6.23-.62-23.26-.3-32.15-.08-4.83-.6-20.36-.71-25.83-.19-.88.44-1.53.93-2.02.42-.5-1.48-2.05-2.34-2.56-3.61-3.41-7.48.07-11.45.93-2.91.23-6.38.94-7,4.18-.35,4.14-4.79,1.38-6.9,3.58-1.35,1.52-2.62,4.39-4.28,1.37-2.65-4.05-8.26-4.21-12.26-7.03-1.19-.77-.43-2.2-.76-3.28-.35-1.12-3.93-1.63-5.18-.86-1.54,1.03-1.65,4.85-4.26,3.87-4.66-3.08-5.59.66-8.74-4.9-1.3-2.17-4.08-4.23-5.05-.8-.78,7.99-5.71,3.47-5.98,10.3-.51,3.84-1.69,4.56-4.04.73-.97-2.78.24-6.15-2.12-8.64-1.38-1.95-2.89.78-4.37,1.39-5.14.03-3.57,1.94-7.2,3.64-3.07.59-2.74-4.3-4.53-5.68-3.29-4.21-.01,31.64-.89,32.94-4.75,1.59-14.4.41-19.96,1.01-2.16.37-5.44-.84-5.23,1.43.02,4.36.31,24.01.27,27.87-.44,1.76-3.29.82-4.72,1.04-7.37.39-18.56-.78-24.44.55-.85,6.49-9.09.95-9.18,4.97.06,5.04.25,25.19.33,29.5.4,2.79,6.1-.27,6.56,2.86.12,3.23.41,8.39,0,11.7-.36,2.24-4.94,2.61-3.5,5.11,2.29,3.81,10.39,17.25,12.58,20.86,1.14,2.01,2.94-.7,4.94-.33,1.83.12,2.32,3.51,4.23,2.51,7.58-4.22,20.47-12.87,28.81-18.41,5.53-4.12,4.21.37,8.82,1.79,2.51.79,3.45-1.93,5.27-2.82,5.23-1.76,14.99-4.82,21.11-5.91,2.25,1,8.57,16.85,10.76,13.78,5.57-3.47,23.67-15,30.18-18.8,2.24-1.08,10.23-2.89,18.79-3.7,3.34-.31-.53-29.18.22-32.59.69-1.72,4.3-.7,6.05-1.19,5.1-.98-.59-2.28-.03-4.56Z" />
                    </g>
                    <g class="texas-region" data-region="central">
                      <path class="cls-3" d="M575.28,479.41c-2.46-3.6.38-12.63-5.57-12.29.67-6.15-3.17-3.02-4.04-11.94-1.13-3.45-4.09-4.67-6.79-5.52-1.36-7.24-3.16-.15-15.86-5.89-2.51-1.35.68-2.94,1.79-3.98,2.93-2.33,11.53-9.07,14.14-11.15,1.97-1.18.24-2.41-.48-3.94-3.57-5.98-12.26-20.64-14.07-23.62.39-2.01-16.92-.35-19.02-.83-38.39,1.28-84.34-2.33-121.68.2-.26,15.98-1.57,69.14-1.57,69.14,0,0-63.94-.62-69.97-1.48-2.05-.82,9.92,4.29,12.94,6.06,4.5,2.43,10.53.8,14.71,3.81,9.41,5.29,4.36,21.47,16.09,23.54,3.01,2.32,1.14,7.24-1.25,9.24-5.01,2.98,1.04,7.64.08,9.37-2.01,1.64-4.75,3.91-4.26,6.82.09,7.57-.04,35.79-.04,35.79,0,0,9.87.53,12.21.68s8.67,1.12,11.69,5.77c1.07,1.65,24.36,23.13,30.23,27.38,9.21,6.17,8.1,2.37,10.55-5.77,2.47-4.74.24-12.11,1.79-17.36,59.39-2.71,33.63,11.21,43.45-28.74.64-2.82,2.45-3.46,4.96-3.03,14.01,1.72,6.67-5.56,9.09-14.73,5.17-1.16,21.01.45,26.7-.46,2.19-19.29-6.57-13.59,19.23-14.68,2.94.13.98-3.89,1.46-7.8-.03-5.38-.18-13.45-.22-18.52.15-1.57-.55-2.76,1.05-2.8,4.21-.17,27.12-.92,32.11-1.14,1.31-.03,1.01-1.42.56-2.16Z" />
                    </g>
                    <g class="texas-region" data-region="south">
                      <path class="cls-4" d="M647.17,675.13c-3.84-3.51-8.6-1.3-13.52-5.17-.64-3.21,11.36-13.54,5.16-14.76-5.92-2.82-7.06-2.7-9.95-8.46-1.53-.9-5.35-.21-6.96-1.65-3.36-5.91-4.28-8.89-11.58-10.33-4.08-2.99-5.49.53-9.22-.48-3.11-1.92-4.74-5.01-7.34-7.59-.85-1.29-2.97-2.62-2.89-4.17,3.56-3.62,17.42-15.05,24.68-21.5,1.06-1.04,3.06-2.03,1.92-3.11-13.11-13.04.39-10.79-20.74-10.16-5.74.7-1.62-5.45-4.56-7.71-5.22-4.16-14.85-7.17-12.95-15.46-1.08-3.99-6.74-1.39-9.72-2.33-7.95-2.26-19.97,5.75-24.96-3.67-.66-2.61.93-23.35-1.4-23.41-4.08-.15-16.36.36-20.47.29-3.27-.17-1.08-6.99-1.85-9.16-.13-1.1-.5-1.32-1.46-1.36-4.98.27-21.82-.67-27.1.05-2.73,12.22,5.29,16.59-11.8,14.57-3.62,1.78-2.77,7.37-3.81,11.13-2.27,4.63-.43,9.82-2.35,14.53-.62,2.91,3.42,5.87-2.28,5.9-6.86-.38-29.14.08-36.3-.09-1.67-.03-1.57.57-1.66,2.69-.56,5.94.63,13.24-2.06,18.24-6.37,10.99-2.59,9.64,3.17,18.2,1.12,3.04,1.07,8.07,2.74,11.21,4.04,5.2,6.61,11.03,9.32,16.96,6.01,9.96,12.05,21.58,16.4,32.43,4.99,6.46,13.47,12.45,17.27,18.73,4.23,4.66,9.63,12.73,13.18,17.95,2.26,3.72,8.22,2.64,10.77,6.03,1.39,1.71.81,4.65,2.3,6.39,3.72,2.48.5,9.53-.61,12.14,2.23,6.04,7.54,4.47,5.03,13.64.06,3.95,2.2,8.53,4.43,11.78,2.68,5.88,11.54,21.04,13.65,29.09.97,1.58,2.82,2.79,3.04,4.74-.03,3.69,1.72,6.21,5.82,5.73,4.98.14,6.94,2.77,11.64,2.43,4.65.87,7.15,5.8,11.36,7.79,10.91,1.04,20.21,6.74,29.29,12.48,2.32,1.37,5.13.21,7.31.83,1.53.65,3.17.84,4.73.09,6.18-1.97,12.22.96,18.55,1.44,3.63-.43,5.5,2.54,7.56,4.96,2.96,2.34,6.65,4.61,10.04,6.56,9.62,3.46,4.32-4.05,10.09-5.34,2.11-.72,4.6-1.95,6.71-2.01.97-.09,2.17.13,2.78-.8,1.46-4.71.23-9.71-.57-14.45-2.49-10.35-4.7-20.92-8.11-30.49-1.25-12.11-3.94-36.61-4.87-45.59-.45-9.16,6.36-17.22,7.64-26.17,5.29-12.59,10.33-20.51,19.82-30.46,2.22-1.81-16.28-2.66-25.31-3.08Z" />
                    </g>
                    <g class="texas-region" data-region="west">
                      <path class="cls-5" d="M188.46,399.11c-26.63-2.3-76.43-6.08-101.72-6.87,8.76,16.64,30.03,43.31,42.69,59.33,1.33,2.31,2.06,5.46,4.72,6.55,8.11,2.95,12.22,11.85,15.64,19.25,5.72,7.78,10.3,6.35,17.52,10.85,1.13,1.09,1.05,2.5,2.72,3.27,3.04,1.39,5.39,4.39,8.43,6.01,2.24-.24,7.81-1.26,7.91-4.6,2.35-26.69,2.65-67.01,4.22-93.74,0,0-1.35,0-2.13-.05Z" />
                    </g>
                    <g class="texas-region" data-region="hill-country">
                      <path class="cls-6" d="M672.48,678.22c13.46-9.72,28.22-22.41,41.81-32.02,10.15-6.35,22.56-10.98,32.23-18.06,1.68-1.69,14.33-7.97,5.07-7.47-22.72-13.6-6.74-7.08-17.69-18.17-1.13-3.88-.64-9.52-3.97-12.4-21.04-24.33.09-18.88-13.11-35.67-1.66-1.15-1.12-3.13-1.56-4.87-.92-2.86,1.16-5.4.25-8.49-.45-3.71-5.51-5.65-4.33-9.5.36-2.47,2.4-3.8,3.63-5.69,2.72-5.75-4.93-6.5-1.26-10.09,2.01-2.17,1.43-4.46-.63-6.23-3.33-6.42-2.97-13.01-3.37-20.23,1.21-7.68-5.92-13.11-5.26-20.51-2.89-6.36-10.24-21.38-.26-24.47,4.48-3.03,21.76-11.36,26.07-15.63.6-4.69-2-4.55-4.66-7.07-1.45-3.45-6.14-3.72-7.33-7.27-.43-3.09-11.6-27.06-14.35-27.57-3.94-1.98-7.56-8.14-12.72-6.39-7.76,4.01-23.92,16.72-31.24,18.24-3.72-2.32-5.95-11.46-10.08-13.29-6.87,1.37-17.13,4.25-23.43,6.63-1.98,2.95-4.16.87-6.28-.66-1.44-.97-2.12-.81-3.79.22-9.86,6.32-21.29,12.68-30.92,18.6-3.84,1.99,2.92,6.2-1.39,7.97-3.45,2.12-9.84,6.26-13.26,8.12-1.57.82-2.43-1.24-3.81-1.26-7.19,3.36-22.72,12.62-28.37,15.52-.83.43-1.68.97-1.74,1.96.02,3.79,4.01,3.65,6.81,4.11,3.1,1.01,5.91,3.71,9.19,2.82.44-.09.93-.09,1.1.36,1.21,5.87,3.71-.65,7.35,8.87,1.16,1.94,2.24,3.98,3.04,6.04.16,6.64,4.18,2.63,5,8.08.05,1.93,2.64,8.31-.49,7.89-37.61,2.53-28.68-10.1-29.35,28.7-.42,2.85-22.53-1.53-20.54,2.93,2,30.29-7.7,24.81,21.44,24.61,1.08-.08,1.93.15,1.89,1.36.12,3.37.31,17.01.42,21.23-.04,3.57,7.2,4.59,9.95,6.22,5.7.68,11.39-3.54,17.12-1.09,1.65.72,3.66.44,5.17.58,1.98,8.38,9.51,12.7,15.7,17.56.74,2.1-.04,6.67,2.93,7.51,14.07.29,10.85-5.19,20.74,7.03.66.89.99,1.24.37,1.94-3.22,2.88-22.52,19.45-26.17,22.66-1.02.7-.28,1.6.38,2.32,18.43,22.01,6.86,4.99,26.07,15.38,2,1.75,2.19,5.26,3.91,6.96,2.75,2.24,8.45.82,9.53,5.51,1.26,3.74,10.09,1.9,8.92,6.63-.7,2.77-9.16,13.12-6.96,14.55,2.63-.09,4.53,1.56,6.65,2.71,3.92.03,7.06.99,10.59,2.63,6.62.15,17.87,0,25-.32ZM577.95,564.78h0s.03-.04.03-.04l.02.02-.04.02h0ZM719.36,577.92h.08v-.17h.27l-.35.36v-.18h0ZM714.27,553.28l-.07.02-.02-.02h.1ZM709,500.18h.12l-.1.1-.02-.1h0ZM700.44,656.41l-.16-.66h.16v.66h0Z" />
                    </g>
                    <g class="texas-region" data-region="southeast">
                      <path class="cls-7" d="M867.07,461.62c-.95-2.6-1.22-6.24-2.56-8.76-2.65-1.95-3.51-5.24-5.69-7.88-2.35-6.24-3.33-14.65-10.32-17.95-7.47-8.02-20.65-4.73-31.29-5.5-9.1.86-3.53-14.17-12.97-13.24-18.92,1.96-37.87-5.22-24.74,22.06,1.62,2.68.6,5.49-2.43,6.62-5.57,1.62-6.98-6.92-11.43-8.18-10.08-1.8-21.42,1.52-31.33,4.72-2.85.36-.07-5.92-3.61-6.93-8.81,2.9-25.99,14.79-33.39,18.4-7.06,7.2,5.14,29.6,7.35,38.51,2.51,8.35-.21,17.76,4.38,25.73,2.59,6.5-2.34,5.75,2.13,13.86,0,3.67-4.32,6.72-3.76,10.14,1.53,4.71,5.21,8.55,3.57,13.95-1.15,7.4,7.8,9.61,5.22,16.63.06,1.71.77,3.45-.23,5.04-1.13,1.92-.15,3.78-.06,6.25-.46,3.45,1.67,5.88,4.25,7.9,2.26,2.36,3.49,5.41,5.88,7.62,3.28,3.28,2.57,8.31,4.34,12.35.74,1.76,2.05,3.11,2.76,4.53,1.03,1.87,1,4.35,2.77,5.76,11.1,2.99,10.16,12.85,17.29,9.77,11.7-3.13,18-14.61,27.23-21.54,5.9-4.12,13.81-9.49,19.93-13.84,3.82-2.78-.78-5.11,3.19-7.64,13.79-10.38,28.55-17.36,45.98-20.03,1.86-.14,4.1.13,3.81-2.16-.3-3.17-3.69-4.69-5.02-7.19-2.56-10.79,12.74-15.25,11.89-25.78-.48-3.85-.63-8.08-3.14-11.25-2.73-4.82,3.4-6.5.74-15.31,1.1-7.95,7.51-14.57,8.11-22.83.89-4.78-.16-9.24,1.16-13.83ZM808.34,410.84v.17l-.16-.16h.16ZM702.22,474.17c-.22-.03-1.55.68-.57-.2.16-.11.22-.32.3-.03.09.05.25.07.3.17l-.02.06Z" />
                    </g>
                    <g class="texas-region" data-region="northeast">
                      <path class="cls-8" d="M848.18,422.34c.89-12.68-9.57-17.6-15.25-26.33-3.7-36.34-2.49-70.91-6.57-107.61-6.78-6.62-18.34,4.41-24.48-3.92-4.17-2.02-8.95-3.94-13.61-4.88-2.88-1.94-5.54-3.83-9.19-3.64-3-.71-4.01-4.43-6.75-5.74-4.97-2.43-10.17-7.53-16.13-6.62-13.45,11.6-14.44,1.53-23.84,2.67-5.57,2.63-15.4,4.2-14.62,12.31.69,14.3.16,30.45.97,45.01-.29,2.87,1.29,6.88-.97,9.08-4.02,1.34-5.29,5.52-5.6,9.35-.72,3.07-5.34.93-4.85,5.33-.45,33.97,9.27,30.01-18.7,30.68-1.83.07-3.32.34-2.88,1.55.74,1.69,3.78,2.66,5.62,3.27,19.24,11.06,21.91,32.11,24.93,35.85s13.6,15.96,14.1,17.77c1.76,4.41,7.87-1.1,10.9-1.38,7.93-1.28,16.56-5.46,24.17-1.68,3.79,1.88,7.51,10.12,12.06,7.73,14.19-7.46-.43-19.42,2.17-25.66s19.6-3.2,24.2-4.14c8.26.8,1.61,14.68,11.76,13.37,5.88.31,15.17.85,21.64,1.2,4.42.16,10.05,3.08,10.91-3.57ZM806.42,289.91l.03-.12.08.68-.5-.17.39-.39h0ZM805.84,290.46l-.04.04h-.05s.09-.04.09-.04ZM735.58,267.46l-.14-.1.14-.55v.65h0Z" />
                    </g>
                    <rect class="cls-9" width="941.76" height="907.17" />
                  </g>
                </svg>
            </div>
            <div class="map-right">
                <div style="min-height: 400px; display: flex; flex-direction: column; justify-content: flex-start;">
                    <h2 id="content-title" class="map-content-title"><?= esc_html( $default_headline ) ?></h2>
                    <?php if ( $default_photo ) : ?>
                        <img id="content-img" src="<?= esc_url( $default_photo ) ?>" alt="Region" class="map-content-img">
                    <?php else : ?>
                        <img id="content-img" src="" alt="Region" class="map-content-img" style="display: none;">
                    <?php endif; ?>
                    <p id="content-text" class="map-content-text"><?= esc_html( $default_text ) ?></p>
                    <a href="#" id="content-btn" class="btn btn--primary btn-hidden" style="display: none; align-self: flex-start; margin-top: 1.5rem;">View Regional Case Studies</a>
                </div>
            </div>
        </div>
        <div id="region-links-list" style="margin-top: 2rem; margin-bottom: 1rem; text-align: center;">
            <ul style="list-style: none; padding: 0; display: flex; flex-direction: row; justify-content: center; flex-wrap: wrap; gap: 1rem;">
                <?php foreach ( $regions as $r ) : ?>
                    <li><a href="<?= esc_url( $region_data[ $r['key'] ]['linkUrl'] ) ?>" class="btn btn--outline region-link" data-region="<?= esc_attr( $r['key'] ) ?>"><?= esc_html( $r['label'] ) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <script type="application/json" id="texas-map-region-data"><?= $json_data ?></script>
        <script>
        (function() {
            var svgMap = document.getElementById('texas-map-svg');
            if (!svgMap) return;
            var paths = svgMap.querySelectorAll('.texas-region');

            paths.forEach(function(region, index) {
                region.setAttribute('data-original-order', index);
            });

            var contentTitle = document.getElementById('content-title');
            var contentText  = document.getElementById('content-text');
            var contentImg   = document.getElementById('content-img');
            var contentBtn   = document.getElementById('content-btn');

            var jsonDataEl = document.getElementById('texas-map-region-data');
            if (!jsonDataEl) return;
            var mapData = JSON.parse(jsonDataEl.textContent);

            var currentHover  = null;
            var lockedRegion  = null;
            var hoverTimeout  = null;
            var switchTimeout = null;

            function updateContent(regionId) {
                if (!regionId) {
                    if (lockedRegion) {
                        regionId = lockedRegion;
                    } else {
                        var def = mapData.default || {};
                        contentTitle.textContent      = def.headline || '';
                        contentTitle.style.display    = def.headline ? 'block' : 'none';
                        contentText.innerHTML         = def.text || '';
                        contentText.style.display     = def.text ? 'block' : 'none';
                        if (def.photo) {
                            contentImg.src            = def.photo;
                            contentImg.style.display  = 'block';
                        } else {
                            contentImg.style.display  = 'none';
                        }
                        contentBtn.style.display      = 'none';
                        return;
                    }
                }

                var data = (mapData.regions || {})[regionId];
                if (!data) return;

                contentTitle.textContent      = data.headline;
                contentTitle.style.display    = 'block';
                contentText.innerHTML         = data.text;
                contentText.style.display     = 'block';
                if (data.photo) {
                    contentImg.src            = data.photo;
                    contentImg.style.display  = 'block';
                } else {
                    contentImg.style.display  = 'none';
                }
                if (data.linkUrl) {
                    contentBtn.href           = data.linkUrl;
                    contentBtn.style.display  = 'inline-block';
                } else {
                    contentBtn.style.display  = 'none';
                }
            }

            function updateHighlights() {
                var activeRegion = currentHover || lockedRegion;

                if (!activeRegion) {
                    svgMap.classList.remove('has-active');
                    paths.forEach(function(p) { p.classList.remove('active'); });

                    var parent = svgMap.querySelector('#Layer_1-2');
                    if (parent) {
                        var regionsArray = Array.from(parent.querySelectorAll('.texas-region'));
                        regionsArray.sort(function(a, b) {
                            return parseInt(a.getAttribute('data-original-order'), 10) - parseInt(b.getAttribute('data-original-order'), 10);
                        });
                        regionsArray.forEach(function(reg) { parent.appendChild(reg); });
                    }

                    document.querySelectorAll('.region-link').forEach(function(btn) {
                        btn.classList.remove('active');
                    });
                    return;
                }

                svgMap.classList.add('has-active');
                paths.forEach(function(p) {
                    if (p.getAttribute('data-region') === activeRegion) {
                        p.classList.add('active');
                        p.parentNode.appendChild(p);
                    } else {
                        p.classList.remove('active');
                    }
                });

                document.querySelectorAll('.region-link').forEach(function(btn) {
                    if (btn.getAttribute('data-region') === activeRegion) {
                        btn.classList.add('active');
                    } else {
                        btn.classList.remove('active');
                    }
                });
            }

            /* ── map-right hover: keep locked state alive while reading panel ── */
            var mapRight = document.querySelector('.map-right');
            if (mapRight) {
                mapRight.addEventListener('mouseover', function() {
                    clearTimeout(hoverTimeout);
                    clearTimeout(switchTimeout);
                });
                mapRight.addEventListener('mouseout', function() {
                    hoverTimeout = setTimeout(function() {
                        currentHover = null;
                        updateHighlights();
                        if (!lockedRegion) updateContent(null);
                    }, 250);
                });
            }

            /* ── region text-link buttons ── */
            function bindLinkEvents() {
                document.querySelectorAll('.region-link').forEach(function(link) {
                    link.addEventListener('mouseover', function(e) {
                        var regionId = e.currentTarget.getAttribute('data-region');
                        paths.forEach(function(p) {
                            if (p.getAttribute('data-region') === regionId && p !== p.parentNode.lastElementChild) {
                                p.parentNode.appendChild(p);
                            }
                        });
                        clearTimeout(hoverTimeout);
                        clearTimeout(switchTimeout);
                        currentHover = regionId;
                        updateHighlights();
                        if (!lockedRegion) updateContent(currentHover);
                    });

                    link.addEventListener('mouseout', function() {
                        clearTimeout(switchTimeout);
                        clearTimeout(hoverTimeout);
                        currentHover = null;
                        updateHighlights();
                        if (!lockedRegion) updateContent(null);
                    });

                    /* click → navigate directly to region page */
                    link.addEventListener('click', function(e) {
                        var regionId = e.currentTarget.getAttribute('data-region');
                        var url = ((mapData.regions || {})[regionId] || {}).linkUrl;
                        if (url && url !== '#') {
                            window.location.href = url;
                        } else {
                            e.preventDefault();
                        }
                    });
                });
            }

            bindLinkEvents();

            /* ── SVG region paths ── */
            paths.forEach(function(path) {
                path.style.pointerEvents = 'all';
                path.style.cursor        = 'pointer';

                path.addEventListener('mouseover', function(e) {
                    var p = e.currentTarget;
                    if (p !== p.parentNode.lastElementChild) {
                        p.parentNode.appendChild(p);
                    }
                    clearTimeout(hoverTimeout);
                    clearTimeout(switchTimeout);
                    switchTimeout = setTimeout(function() {
                        currentHover = p.getAttribute('data-region');
                        updateHighlights();
                        if (!lockedRegion) updateContent(currentHover);
                    }, 250);
                });

                path.addEventListener('mouseout', function() {
                    clearTimeout(switchTimeout);
                    hoverTimeout = setTimeout(function() {
                        currentHover = null;
                        updateHighlights();
                        if (!lockedRegion) updateContent(null);
                    }, 250);
                });

                /* click → lock the region; second click on same → navigate */
                path.addEventListener('click', function(e) {
                    var regionId = e.currentTarget.getAttribute('data-region');
                    if (lockedRegion === regionId) {
                        var url = ((mapData.regions || {})[regionId] || {}).linkUrl;
                        if (url && url !== '#') { window.location.href = url; }
                    } else {
                        lockedRegion = regionId;
                        updateHighlights();
                        updateContent(regionId);
                    }
                });
            });

            updateContent(null);
        })();
        </script>
    </section>
    <?php
    return ob_get_clean();
}

// Headless Redirect Settings & Logic
// Headless Redirect Settings & Logic
add_action( 'admin_menu', 'e3es_register_headless_settings_menu' );
function e3es_register_headless_settings_menu() {
    add_options_page(
        'E3 Headless Settings',
        'E3 Headless',
        'manage_options',
        'e3es-headless',
        'e3es_render_headless_settings_page'
    );
}

add_action( 'admin_init', 'e3es_register_headless_settings' );
function e3es_register_headless_settings() {
    register_setting( 'e3es-headless-group', 'e3_headless_redirect_enabled' );
    register_setting( 'e3es-headless-group', 'e3_headless_frontend_url' );    // legacy — kept for compat
    register_setting( 'e3es-headless-group', 'e3_headless_local_wp_url' );
    register_setting( 'e3es-headless-group', 'e3_headless_local_astro_url' );
    register_setting( 'e3es-headless-group', 'e3_headless_local_astro_path' );
    register_setting( 'e3es-headless-group', 'e3_headless_staging_astro_url' );

    // Combined deploy settings (using same names)
    register_setting( 'e3es-headless-group', 'cf_deploy_github_owner', array( 'sanitize_callback' => 'sanitize_text_field' ) );
    register_setting( 'e3es-headless-group', 'cf_deploy_github_repo',  array( 'sanitize_callback' => 'sanitize_text_field' ) );
    register_setting( 'e3es-headless-group', 'cf_deploy_github_token', array( 'sanitize_callback' => 'sanitize_text_field' ) );
    register_setting( 'e3es-headless-group', 'cf_deploy_webhook_url', array( 'sanitize_callback' => 'sanitize_text_field' ) );
}

function e3es_render_headless_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Process manual trigger from settings page
    $manual_trigger_success = false;
    $manual_trigger_error = '';
    if ( isset( $_POST['e3es_manual_trigger'] ) && check_admin_referer( 'e3es_manual_trigger_nonce' ) ) {
        $ok = e3es_dispatch_github_rebuild();
        if ( $ok ) {
            $manual_trigger_success = true;
        } else {
            $manual_trigger_error = 'Build dispatch failed — check credentials, webhook URL, or PHP error log.';
        }
    }

    $enabled       = get_option( 'e3_headless_redirect_enabled', '0' );
    $local_wp      = get_option( 'e3_headless_local_wp_url',       'http://e3es2026.local' );
    $local_astro   = get_option( 'e3_headless_local_astro_url',    'http://localhost:4383' );
    $local_path    = get_option( 'e3_headless_local_astro_path',   '/Users/bryanpaul/Local Sites/astro-e3es' );
    $staging_astro = get_option( 'e3_headless_staging_astro_url',  'https://astro-e3es.paulbryanvisual.workers.dev' );
    
    $owner   = get_option( 'cf_deploy_github_owner', '' );
    $repo    = get_option( 'cf_deploy_github_repo',  '' );
    $token   = get_option( 'cf_deploy_github_token', '' );
    $webhook = get_option( 'cf_deploy_webhook_url', '' );

    $is_local     = e3es_is_local_env();
    $env_label    = $is_local ? '🟢 LOCAL' : '🔵 STAGING';
    $env_color    = $is_local ? '#1e7e34' : '#0056b3';
    $site_url     = site_url();
    $has_deploy   = ! empty( $webhook ) || ( ! empty( $owner ) && ! empty( $repo ) && ! empty( $token ) );

    // Check query args to show save updates
    $settings_updated = isset( $_GET['settings-updated'] ) && 'true' === $_GET['settings-updated'];
    ?>
    <div class="wrap e3-settings-wrap">
        <style>
            .e3-settings-wrap h1 { margin-bottom: 20px; }
            .e3-settings-section { background: #fff; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04); padding: 20px 24px; margin-bottom: 24px; }
            .e3-settings-section h2 { margin-top: 0; padding-bottom: 12px; border-bottom: 1px solid #f0f0f1; font-size: 1.3em; }
            .e3-env-badge { display: inline-block; margin-left: 12px; font-size: 13px; font-weight: 600; padding: 4px 12px; border-radius: 12px; color: #fff; vertical-align: middle; }
        </style>

        <h1>E3 Headless Configuration
            <span class="e3-env-badge" style="background: <?= esc_attr( $env_color ) ?>;">
                <?= esc_html( $env_label ) ?> — <?= esc_html( $site_url ) ?>
            </span>
        </h1>
        <p>Manage headless environment routing and front-end deployment configurations for the Astro site.</p>

        <?php if ( $settings_updated ) : ?>
            <div class="notice notice-success is-dismissible"><p>Settings saved successfully.</p></div>
        <?php endif; ?>

        <?php if ( $manual_trigger_success ) : ?>
            <div class="notice notice-success is-dismissible"><p>✅ Build and deployment successfully triggered!</p></div>
        <?php elseif ( ! empty( $manual_trigger_error ) ) : ?>
            <div class="notice notice-error is-dismissible"><p>❌ <?= esc_html( $manual_trigger_error ) ?></p></div>
        <?php endif; ?>

        <form method="post" action="options.php">
            <?php settings_fields( 'e3es-headless-group' ); ?>

            <div class="e3-settings-section">
                <h2>Redirect Behaviour</h2>
                <table class="form-table" role="presentation">
                    <tr valign="top">
                        <th scope="row">Enable Frontend Redirect</th>
                        <td>
                            <label>
                                <input type="checkbox" name="e3_headless_redirect_enabled" value="1" <?php checked( '1', $enabled ); ?> />
                                <span class="description">Redirect WordPress front-end visitors to the Astro site. Always leave enabled.</span>
                            </label>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="e3-settings-section">
                <h2>Environment URLs</h2>
                <p class="description">The plugin detects the environment by comparing <code>site_url()</code> to the <strong>Local WordPress URL</strong> below. Set the correct Astro URLs for each environment; the active instance uses the matching set automatically.</p>
                <table class="form-table" role="presentation">
                    <tr valign="top">
                        <th scope="row">Local WordPress URL</th>
                        <td>
                            <input type="url" name="e3_headless_local_wp_url" value="<?= esc_attr( $local_wp ) ?>" class="regular-text" placeholder="http://e3es2026.local" />
                            <p class="description">Used to detect if this is the local instance. Must match exactly.</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Local Astro URL</th>
                        <td>
                            <input type="url" name="e3_headless_local_astro_url" value="<?= esc_attr( $local_astro ) ?>" class="regular-text" placeholder="http://localhost:4383" />
                            <p class="description">Astro dev server (<code>npm run dev</code>). The interstitial will check if it's running and show a warning with startup instructions if not.</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Local Astro Project Path</th>
                        <td>
                            <input type="text" name="e3_headless_local_astro_path" value="<?= esc_attr( $local_path ) ?>" class="regular-text" placeholder="/Users/you/Local Sites/astro-e3es" />
                            <p class="description">Absolute path to the Astro project folder. Used to run local background builds. Optional.</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Staging / Production Astro URL</th>
                        <td>
                            <input type="url" name="e3_headless_staging_astro_url" value="<?= esc_attr( $staging_astro ) ?>" class="regular-text" placeholder="https://astro-e3es.paulbryanvisual.workers.dev" />
                            <p class="description">Cloudflare Workers URL. Used on the staging instance. Saves trigger rebuilds; the interstitial polls until the build completes.</p>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="e3-settings-section">
                <h2>Deployment & Build Triggers</h2>
                <p class="description">Configure either a Cloudflare Pages Deploy Hook URL or GitHub credentials to automatically rebuild the static Astro frontend on content updates.</p>
                
                <table class="form-table" role="presentation">
                    <tr valign="top">
                        <th scope="row">Cloudflare Pages Deploy Hook URL</th>
                        <td>
                            <input type="url" name="cf_deploy_webhook_url" value="<?= esc_attr( $webhook ) ?>" class="large-text" placeholder="https://api.cloudflare.com/client/v4/pages/projects/..." />
                            <p class="description">Recommended if deploying directly to Cloudflare Pages via build hooks.</p>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><hr /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">GitHub Owner / Org</th>
                        <td>
                            <input type="text" name="cf_deploy_github_owner" value="<?= esc_attr( $owner ) ?>" class="regular-text" placeholder="e.g. my-org" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Repository Name</th>
                        <td>
                            <input type="text" name="cf_deploy_github_repo" value="<?= esc_attr( $repo ) ?>" class="regular-text" placeholder="e.g. astro-e3es" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">GitHub Personal Access Token</th>
                        <td>
                            <input type="password" name="cf_deploy_github_token" value="<?= esc_attr( $token ) ?>" class="regular-text" placeholder="ghp_..." />
                            <p class="description">Needs <code>repo</code> or <code>workflow</code> permissions. <a href="https://github.com/settings/tokens" target="_blank">Create one here</a>.</p>
                        </td>
                    </tr>
                </table>
            </div>

            <?php submit_button( 'Save All Settings' ); ?>
        </form>

        <?php if ( $has_deploy ) : ?>
            <div class="e3-settings-section" style="margin-top: 2rem;">
                <h2>Manual Rebuild</h2>
                <p>Trigger an immediate rebuild and deploy of the front-end site manually without modifying any content.</p>
                <form method="post">
                    <?php wp_nonce_field( 'e3es_manual_trigger_nonce' ); ?>
                    <input type="hidden" name="e3es_manual_trigger" value="1" />
                    <?php submit_button( '🚀 Rebuild & Deploy Live Site Now', 'secondary' ); ?>
                </form>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

// ─────────────────────────────────────────────────────────────────────────────
// ENVIRONMENT HELPERS — detect local vs. staging and resolve the correct Astro URL
// ─────────────────────────────────────────────────────────────────────────────

function e3es_is_local_env() {
    $site_host = parse_url( site_url(), PHP_URL_HOST );
    if ( empty( $site_host ) ) {
        return false;
    }
    // Flywheel staging and production environments are never local
    if ( strpos( $site_host, 'flywheelstaging.com' ) !== false || $site_host === 'e3es.com' || $site_host === 'www.e3es.com' ) {
        return false;
    }
    $local_wp   = get_option( 'e3_headless_local_wp_url', 'http://e3es2026.local' );
    $local_host = parse_url( $local_wp,  PHP_URL_HOST );
    return ! empty( $local_host ) && $site_host === $local_host;
}

function e3es_get_frontend_url() {
    // If we're on the local instance, prefer the local Astro URL
    if ( e3es_is_local_env() ) {
        $local = get_option( 'e3_headless_local_astro_url', 'http://localhost:4383' );
        if ( ! empty( $local ) ) {
            return rtrim( $local, '/' );
        }
    }

    // Prefer new explicit staging option; fall back to legacy single-URL option
    $staging = get_option( 'e3_headless_staging_astro_url', '' );
    if ( empty( $staging ) ) {
        $staging = get_option( 'e3_headless_frontend_url', 'https://astro-e3es.paulbryanvisual.workers.dev' );
    }
    return rtrim( $staging, '/' );
}

// ─────────────────────────────────────────────────────────────────────────────
// ADMIN LINK REWRITE — make "View Page" and "Preview" go directly to Astro
// ─────────────────────────────────────────────────────────────────────────────

function e3es_rewrite_admin_link( $url ) {
    $frontend = e3es_get_frontend_url();
    if ( empty( $frontend ) ) {
        return $url;
    }
    // Replace the WordPress origin with the Astro origin, preserve path + query
    $wp_origin = rtrim( site_url(), '/' );
    if ( strpos( $url, $wp_origin ) === 0 ) {
        $url = $frontend . substr( $url, strlen( $wp_origin ) );
    }

    // Normalize path by stripping parent prefixes to match Astro's clean flat routing
    $frontend_esc = preg_quote( rtrim( $frontend, '/' ), '/' );
    $url = preg_replace( '/^' . $frontend_esc . '\/home\/our-approach\//', rtrim( $frontend, '/' ) . '/', $url );
    $url = preg_replace( '/^' . $frontend_esc . '\/home\/about-us\//', rtrim( $frontend, '/' ) . '/', $url );
    $url = preg_replace( '/^' . $frontend_esc . '\/home\/industries\//', rtrim( $frontend, '/' ) . '/', $url );
    $url = preg_replace( '/^' . $frontend_esc . '\/home\//', rtrim( $frontend, '/' ) . '/', $url );

    return $url;
}
add_filter( 'page_link',      'e3es_rewrite_admin_link', 20 );
add_filter( 'post_link',      'e3es_rewrite_admin_link', 20 );
add_filter( 'post_type_link', 'e3es_rewrite_admin_link', 20 );
add_filter( 'preview_post_link', 'e3es_rewrite_admin_link', 20 );

// Redirect Hook
add_action( 'template_redirect', 'e3es_headless_redirect_handler' );
function e3es_headless_redirect_handler() {
    // Check if enabled
    $enabled = get_option( 'e3_headless_redirect_enabled', '0' );
    if ( $enabled !== '1' ) {
        return;
    }

    // Do not redirect for Admin, AJAX, REST, WP Cron, or Previews
    if ( is_admin() || wp_doing_ajax() || wp_is_json_request() || is_preview() || isset( $_GET['preview'] ) || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
        return;
    }

    $frontend_url = e3es_get_frontend_url();
    if ( empty( $frontend_url ) ) {
        return;
    }

    // Also avoid redirect loops/issues for common query parameters if needed, or if URL is exactly /wp-json
    $request_uri = $_SERVER['REQUEST_URI'];
    if ( strpos( $request_uri, '/wp-admin' ) !== false || 
         strpos( $request_uri, '/wp-login' ) !== false || 
         strpos( $request_uri, '/wp-json' ) !== false ||
         strpos( $request_uri, '/wp-content/' ) !== false ||
         strpos( $request_uri, '/wp-includes/' ) !== false ||
         strpos( $request_uri, 'favicon.ico' ) !== false ||
         strpos( $request_uri, 'robots.txt' ) !== false ) {
        return;
    }

    // Clean parent prefixes from the redirect path to match Astro's clean flat routing
    $path = parse_url( $request_uri, PHP_URL_PATH );
    $query = parse_url( $request_uri, PHP_URL_QUERY );
    
    $path = preg_replace( '/^\/home\/our-approach\//', '/', $path );
    $path = preg_replace( '/^\/home\/about-us\//', '/', $path );
    $path = preg_replace( '/^\/home\/industries\//', '/', $path );
    $path = preg_replace( '/^\/home\//', '/', $path );
    
    $redirect_path = ltrim( $path, '/' );
    if ( ! empty( $query ) ) {
        $redirect_path .= '?' . $query;
    }

    $redirect_url = rtrim( $frontend_url, '/' ) . '/' . $redirect_path;
    if ( current_user_can( 'edit_posts' ) ) {
        // Generate secure random token
        $token = wp_generate_password( 32, false );
        $post_id = get_the_ID();
        if ( $post_id ) {
            set_transient( 'e3es_edit_token_' . $token, $post_id, 300 ); // 5 minutes
            $redirect_url = add_query_arg( 'wp_edit_token', $token, $redirect_url );
        }
        
        // Output Interstitial Page for editors
        e3es_render_build_interstitial( $redirect_url, e3es_is_local_env() );
        exit;
    }
    
    // Regular users
    wp_redirect( $redirect_url, 302 );
    exit;
}

// Register custom REST API routes
add_action( 'rest_api_init', 'e3es_register_headless_routes' );
function e3es_register_headless_routes() {
    register_rest_route( 'e3es/v1', '/verify-token', array(
        'methods'             => 'GET',
        'callback'            => 'e3es_verify_headless_token',
        'permission_callback' => '__return_true', // Secure via transient lookup
    ) );
    
    register_rest_route( 'e3es/v1', '/build-status', array(
        'methods'             => 'GET',
        'callback'            => 'e3es_api_build_status',
        'permission_callback' => function() { return current_user_can( 'edit_posts' ); },
    ) );
}

function e3es_api_build_status() {
    $owner = get_option('cf_deploy_github_owner');
    $repo = get_option('cf_deploy_github_repo');
    $token = get_option('cf_deploy_github_token');

    if (empty($owner) || empty($repo) || empty($token)) {
        return rest_ensure_response(array('status' => 'completed', 'note' => 'No GitHub config'));
    }

    $api_url = "https://api.github.com/repos/{$owner}/{$repo}/actions/runs?per_page=1";
    $response = wp_remote_get($api_url, array(
        'headers' => array(
            'Authorization' => 'token ' . $token,
            'Accept' => 'application/vnd.github.v3+json',
            'User-Agent' => 'WordPress-E3ES-Headless-Helper'
        ),
        'timeout' => 5,
    ));

    if (is_wp_error($response)) {
        return rest_ensure_response(array('status' => 'completed', 'note' => 'API Error'));
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    if (isset($body['workflow_runs']) && !empty($body['workflow_runs'])) {
        $run = $body['workflow_runs'][0];
        $status = $run['status']; // "queued", "in_progress", or "completed"
        return rest_ensure_response(array('status' => $status));
    }

    return rest_ensure_response(array('status' => 'completed', 'note' => 'No runs found'));
}

function e3es_render_build_interstitial( $target_url, $is_local = false ) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Preparing Preview...</title>
        <style>
            body { margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, "Open Sans", "Helvetica Neue", sans-serif; background: #1a1a1a; color: #fff; display: flex; align-items: center; justify-content: center; height: 100vh; overflow: hidden; }
            .loader-container { text-align: center; max-width: 440px; padding: 2rem; }
            .loader-spinner { border: 4px solid rgba(255,255,255,0.1); border-left-color: #fff; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto 1.5rem auto; }
            @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
            h2 { font-size: 1.25rem; margin-bottom: 0.5rem; font-weight: 600; }
            p { font-size: 0.95rem; color: #aaa; margin-bottom: 1rem; line-height: 1.5; }
            .progress-bar { width: 100%; background: #333; height: 6px; border-radius: 3px; overflow: hidden; position: relative; }
            .progress-fill { background: #fff; height: 100%; width: 0%; transition: width 0.3s ease; }
            .progress-fill.indeterminate { width: 30%; animation: pulse 1.5s ease-in-out infinite alternate; }
            @keyframes pulse { 0% { transform: translateX(-100%); } 100% { transform: translateX(333%); } }
            .hidden { display: none !important; }
            /* Dev server warning */
            .warn-icon { font-size: 2.5rem; margin-bottom: 0.5rem; }
            .cmd-block { background: #0d1117; border: 1px solid #30363d; border-radius: 8px; padding: 10px 16px; font-family: "SFMono-Regular", Consolas, monospace; font-size: 13px; color: #7eff6a; text-align: left; margin: 10px 0 16px; user-select: all; cursor: text; white-space: pre-wrap; word-break: break-all; }
            .retry-btn { background: transparent; border: 2px solid rgba(255,255,255,0.5); color: #fff; padding: 8px 20px; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; transition: border-color .2s, background .2s; }
            .retry-btn:hover { border-color: #fff; background: rgba(255,255,255,0.08); }
            .auto-note { font-size: 0.8rem; color: #666; margin-top: 8px; margin-bottom: 0; }
        </style>
    </head>
    <body>
        <div class="loader-container">
            <div class="warn-icon hidden" id="warn-icon">⚠️</div>
            <div class="loader-spinner" id="spinner"></div>
            <h2 id="status-title">Checking Dev Server…</h2>
            <p id="status-desc">Verifying the local Astro dev server is running.</p>
            <div class="cmd-block hidden" id="cmd-block"><?php
                $astro_path = get_option( 'e3_headless_local_astro_path', '/Users/bryanpaul/Local Sites/astro-e3es' );
                echo esc_html( 'cd "' . $astro_path . '" && npm run dev' );
            ?></div>
            <div class="hidden" id="retry-container">
                <button class="retry-btn" id="retry-btn">↺ Check Again</button>
                <p class="auto-note" id="auto-note">Checking again automatically…</p>
            </div>
            <div class="progress-bar hidden" id="progress-container">
                <div class="progress-fill indeterminate"></div>
            </div>
        </div>

        <script>
            const targetUrl = <?php echo wp_json_encode( $target_url ); ?>;
            const isLocal   = <?php echo $is_local ? 'true' : 'false'; ?>;
            const apiUrl    = '/wp-json/e3es/v1/build-status';

            // ── Helpers ──────────────────────────────────────────────────────
            const el = id => document.getElementById(id);

            if ( isLocal ) {
                // Check if the local Astro dev server is reachable via no-cors ping
                var retryTimer = null;

                function showOffline() {
                    el('spinner').classList.add('hidden');
                    el('warn-icon').classList.remove('hidden');
                    el('status-title').innerText = 'Dev Server Offline';
                    el('status-desc').innerText  = 'Start the Astro dev server in a new Terminal window, then come back here:';
                    el('cmd-block').classList.remove('hidden');
                    el('retry-container').classList.remove('hidden');
                    // Auto-retry every 5 seconds
                    if ( !retryTimer ) {
                        retryTimer = setInterval( checkDevServer, 5000 );
                    }
                }

                function showReady() {
                    clearInterval( retryTimer );
                    retryTimer = null;
                    el('warn-icon').classList.add('hidden');
                    el('spinner').classList.remove('hidden');
                    el('cmd-block').classList.add('hidden');
                    el('retry-container').classList.add('hidden');
                    el('status-title').innerText = 'Dev Server Ready!';
                    el('status-desc').innerText  = 'Opening local Astro preview…';
                    setTimeout( function() { window.location.replace( targetUrl ); }, 600 );
                }

                async function checkDevServer() {
                    try {
                        // no-cors: resolves as opaque 200 if server is up, throws on connection refused
                        await fetch( targetUrl, {
                            mode: 'no-cors',
                            signal: AbortSignal.timeout( 3000 )
                        });
                        showReady();
                    } catch (e) {
                        showOffline();
                    }
                }

                el('retry-btn').addEventListener( 'click', function() {
                    clearInterval( retryTimer );
                    retryTimer = null;
                    el('status-title').innerText = 'Checking…';
                    el('status-desc').innerText  = '';
                    el('cmd-block').classList.add('hidden');
                    el('retry-container').classList.add('hidden');
                    el('warn-icon').classList.add('hidden');
                    el('spinner').classList.remove('hidden');
                    checkDevServer();
                });

                checkDevServer();

            } else {
                async function checkStatus() {
                    try {
                        const res = await fetch(apiUrl);
                        if (!res.ok) throw new Error('API Error');
                        const data = await res.json();

                        if (data.status === 'in_progress' || data.status === 'queued') {
                            el('status-title').innerText = 'Astro is building…';
                            el('status-desc').innerText  = 'Your changes are rendering on Cloudflare. This takes about 2 minutes.';
                            el('progress-container').classList.remove('hidden');
                            setTimeout(checkStatus, 5000);
                        } else {
                            el('status-title').innerText = 'Ready!';
                            el('status-desc').innerText  = 'Redirecting to the frontend…';
                            window.location.replace(targetUrl);
                        }
                    } catch (e) {
                        window.location.replace(targetUrl);
                    }
                }
                el('status-title').innerText = 'Checking Build Status…';
                el('status-desc').innerText  = 'Verifying your frontend is up to date.';
                checkStatus();
            }
        </script>
    </body>
    </html>
    <?php
}

function e3es_verify_headless_token( $request ) {
    $token = $request->get_param( 'token' );
    if ( empty( $token ) ) {
        return new WP_Error( 'missing_token', 'Token is required', array( 'status' => 400 ) );
    }

    $post_id = get_transient( 'e3es_edit_token_' . $token );
    if ( $post_id ) {
        return rest_ensure_response( array(
            'valid'   => true,
            'post_id' => (int) $post_id,
        ) );
    }

    return rest_ensure_response( array(
        'valid' => false,
    ) );
}

// Register E3 Custom Patterns and Categories
add_action( 'init', 'e3es_register_layout_patterns', 100 );
function e3es_register_layout_patterns() {
    register_block_pattern_category(
        'e3-patterns',
        array( 'label' => __( 'E3 Page Layouts', 'e3es' ) )
    );

    // Unregister core categories to push E3 Patterns category to the top
    $core_categories = array(
        'buttons',
        'columns',
        'gallery',
        'header',
        'text',
        'query',
        'featured',
        'call-to-action',
        'services',
        'contact',
        'about',
        'portfolio',
        'banner',
        'team',
        'testimonial'
    );
    foreach ( $core_categories as $cat ) {
        unregister_block_pattern_category( $cat );
    }

    // Pattern 1: E3 Service Page Layout
    register_block_pattern(
        'e3es/service-page-layout',
        array(
            'title'       => __( 'E3 Service Page Layout', 'e3es' ),
            'categories'  => array( 'e3-patterns' ),
            'description' => __( 'Full service detail page layout.', 'e3es' ),
            'content'     => '<!-- wp:e3es/intro-banner {"title":"HVAC System Upgrades and Replacements","bgImageUrl":"/images/hvac.jpg","bgOpacity":0.85,"bgOverlayColor":"green","bgFadeType":"flat"} -->
<section class="wp-block-e3es-intro-banner db-page-hero" style="background-size:cover;background-position:center;background-repeat:no-repeat"><div class="db-page-hero__container"><h1 class="db-page-hero__title" style="margin-bottom:0;text-align:center;text-transform:uppercase">HVAC System Upgrades and Replacements</h1></div></section>
<!-- /wp:e3es/intro-banner -->

<!-- wp:e3es/two-column {"imageUrl":"/images/hvac.jpg","imageAlt":"","reverse":false,"bgStyle":"white","icon":"clock"} -->
<section class="db-feature db-feature--white"><div class="db-feature__container"><div class="db-feature__content"><div class="db-feature__icon"></div><!-- wp:heading {"level":2} -->
<h2>High-Efficiency HVAC Design &amp; Installation</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>E3 designs, replaces, and upgrades outdated heating, ventilation, and air conditioning systems. We specialize in high-efficiency chillers, boiler plants, and rooftop DX systems tailored for schools, offices, and public facilities.</p>
<!-- /wp:paragraph --></div><div class="db-feature__image-wrapper"><img src="/images/hvac.jpg" alt="" class="db-feature__image" /></div></div></section>
<!-- /wp:e3es/two-column -->

<!-- wp:e3es/two-column {"imageUrl":"/images/hvac.jpg","imageAlt":"","reverse":true,"bgStyle":"grey","icon":"dollar"} -->
<section class="db-feature db-feature--grey"><div class="db-feature__container db-feature__container--reverse"><div class="db-feature__content"><div class="db-feature__icon"></div><!-- wp:heading {"level":2} -->
<h2>Reducing Maintenance &amp; Lowering Utility Bills</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Our team focuses on replacing aging equipment with modern, energy-efficient solutions. By reducing frequency of repairs and lowering energy use, we free up critical budget space for your organization.</p>
<!-- /wp:paragraph --></div><div class="db-feature__image-wrapper"><img src="/images/hvac.jpg" alt="" class="db-feature__image" /></div></div></section>
<!-- /wp:e3es/two-column -->

<!-- wp:e3es/mini-testimonial {"quote":"The new HVAC plant has cut our electrical usage by 24% and solved our classroom temperature complaints. We couldn\'t be happier with E3\'s design-build execution.","cite":"School District Superintendent"} -->
<div class="mini-testimonial"><blockquote>The new HVAC plant has cut our electrical usage by 24% and solved our classroom temperature complaints. We couldn\'t be happier with E3\'s design-build execution.</blockquote><cite>School District Superintendent</cite></div>
<!-- /wp:e3es/mini-testimonial -->

<!-- wp:e3es/cta-banner {"title":"Upgrade Your Facility\'s HVAC System","text":"Learn how modern cooling technology reduces your overhead and improves environment control.","btnText":"Explore Funding Options","btnUrl":"/our-approach/funding"} -->
<section class="cta-banner"><div class="cta-banner__container"><h2 class="cta-banner__title">Upgrade Your Facility\'s HVAC System</h2><p class="cta-banner__text">Learn how modern cooling technology reduces your overhead and improves environment control.</p><a href="/our-approach/funding" class="btn btn--primary cta-banner__btn">Explore Funding Options</a></div></section>
<!-- /wp:e3es/cta-banner -->',
        )
    );

    // Pattern 2: E3 Design-Build Advantage Layout
    register_block_pattern(
        'e3es/design-build-layout',
        array(
            'title'       => __( 'E3 Design-Build Advantage Layout', 'e3es' ),
            'categories'  => array( 'e3-patterns' ),
            'description' => __( 'Design-build features and benefit cards grid.', 'e3es' ),
            'content'     => '<!-- wp:e3es/intro-banner {"title":"Design-Build Advantage","bgImageUrl":"/images/dl_caldwell-dark-1-600x400-600x400-600x400-600x400-600x400-600x400.jpg","bgOpacity":0.85,"bgOverlayColor":"green","bgFadeType":"flat"} -->
<section class="wp-block-e3es-intro-banner db-page-hero" style="background-size:cover;background-position:center;background-repeat:no-repeat"><div class="db-page-hero__container"><h1 class="db-page-hero__title" style="margin-bottom:0;text-align:center;text-transform:uppercase">Design-Build Advantage</h1></div></section>
<!-- /wp:e3es/intro-banner -->

<!-- wp:e3es/two-column {"imageUrl":"/images/dl_caldwell-dark-1-600x400-600x400-600x400-600x400-600x400-600x400.jpg","imageAlt":"Speed","reverse":false,"bgStyle":"white","icon":"clock"} -->
<section class="wp-block-e3es-two-column db-feature db-feature--white"><div class="db-feature__container"><div class="db-feature__content"><div class="db-feature__icon"></div><!-- wp:heading {"level":2} -->
<h2>Up to 2x Faster Timelines</h2>
<!-- /wp:heading --><!-- wp:paragraph -->
<p>Because we serve as both your designer and your contractor, our in-house engineers and project managers work hand-in-hand with you from day one. We design, audit, and secure funding simultaneously, making your project delivery up to twice as fast as traditional, linear bidding methods.</p>
<!-- /wp:paragraph --></div><div class="db-feature__image-wrapper"><img src="/images/dl_caldwell-dark-1-600x400-600x400-600x400-600x400-600x400-600x400.jpg" alt="Speed" class="db-feature__image"/></div></div></section>
<!-- /wp:e3es/two-column -->

<!-- wp:e3es/design-build-advantage -->
<section class="design-build"><div class="design-build__container"><div class="design-build__grid">
    <!-- wp:e3es/design-build-card {"title":"Taxpayer-Friendly Funding","text":"We utilize SECO grants and performance contracting to fund upgrades directly out of existing utility budgets.","icon":"dollar"} -->
    <div class="design-build__card"><div class="design-build__icon" style="margin-bottom: 1rem"></div><h3 class="design-build__card-title">Taxpayer-Friendly Funding</h3><p class="design-build__card-text">We utilize SECO grants and performance contracting to fund upgrades directly out of existing utility budgets.</p></div>
    <!-- /wp:e3es/design-build-card -->
    <!-- wp:e3es/design-build-card {"title":"Cooperative Purchasing","text":"Skip long bidding delays with pre-negotiated contracts through BuyBoard and Choice Partners.","icon":"shield"} -->
    <div class="design-build__card"><div class="design-build__icon" style="margin-bottom: 1rem"></div><h3 class="design-build__card-title">Cooperative Purchasing</h3><p class="design-build__card-text">Skip long bidding delays with pre-negotiated contracts through BuyBoard and Choice Partners.</p></div>
    <!-- /wp:e3es/design-build-card -->
    <!-- wp:e3es/design-build-card {"title":"Turnkey Execution","text":"Single-source engineering, construction management, and verified savings reports under one roof.","icon":"clock"} -->
    <div class="design-build__card"><div class="design-build__icon" style="margin-bottom: 1rem"></div><h3 class="design-build__card-title">Turnkey Execution</h3><p class="design-build__card-text">Single-source engineering, construction management, and verified savings reports under one roof.</p></div>
    <!-- /wp:e3es/design-build-card -->
</div></div></section>
<!-- /wp:e3es/design-build-advantage -->

<!-- wp:e3es/cta-banner {"title":"Ready to Learn More?","text":"Connect with our design-build experts to discuss your facility needs.","btnText":"Get in Touch","btnUrl":"/about-us/contact"} -->
<section class="cta-banner"><div class="cta-banner__container"><h2 class="cta-banner__title">Ready to Learn More?</h2><p class="cta-banner__text">Connect with our design-build experts to discuss your facility needs.</p><a href="/about-us/contact" class="btn btn--primary cta-banner__btn">Get in Touch</a></div></section>
<!-- /wp:e3es/cta-banner -->',
        )
    );

    // Pattern 3: E3 Solutions Layout
    register_block_pattern(
        'e3es/solutions-layout',
        array(
            'title'       => __( 'E3 Solutions Layout', 'e3es' ),
            'categories'  => array( 'e3-patterns' ),
            'description' => __( 'Solutions page layout with pillars.', 'e3es' ),
            'content'     => '<!-- wp:e3es/intro-banner {"title":"K-12 Schools","bgImageUrl":"/wp-content/uploads/2026/06/54401120128_a10df8e7eb_o-scaled.jpg","bgOpacity":0.85,"bgOverlayColor":"green","bgFadeType":"flat"} -->
<section class="wp-block-e3es-intro-banner db-page-hero" style="background-size:cover;background-position:center;background-repeat:no-repeat"><div class="db-page-hero__container"><h1 class="db-page-hero__title" style="margin-bottom:0;text-align:center;text-transform:uppercase">K-12 Schools</h1></div></section>
<!-- /wp:e3es/intro-banner -->

<!-- wp:e3es/two-column {"imageUrl":"/wp-content/uploads/2026/06/54401120128_a10df8e7eb_o-scaled.jpg","imageAlt":"","reverse":false,"bgStyle":"white","icon":"shield"} -->
<section class="wp-block-e3es-two-column db-feature db-feature--white"><div class="db-feature__container"><div class="db-feature__content"><div class="db-feature__icon"></div><!-- wp:heading {"level":2} -->
<h2>Modern Classroom Environments</h2>
<!-- /wp:heading --><!-- wp:paragraph -->
<p>We work closely with school boards and superintendents to deliver modern, efficient, and healthy learning environments. Upgrading aging facility infrastructure improves student and staff comfort while protecting tax dollars.</p>
<!-- /wp:paragraph --></div><div class="db-feature__image-wrapper"><img src="/wp-content/uploads/2026/06/54401120128_a10df8e7eb_o-scaled.jpg" alt="" class="db-feature__image" /></div></div></section>
<!-- /wp:e3es/two-column -->

<!-- wp:e3es/core-pillars -->
<section class="db-pillars" style="background-color:var(--color-bg-light);padding:5rem 2rem"><div style="max-width:1200px;margin:0 auto;display:grid;grid-template-columns:repeat(auto-fit, minmax(300px, 1fr));gap:3rem">
    <!-- wp:e3es/core-pillar {"title":"Energy Savings","text":"Lower utility bills redirecting crucial capital straight back into the classroom."} -->
    <div style="background:white;padding:2.5rem;box-shadow:0 10px 30px rgba(0,0,0,0.05);border-top:4px solid var(--color-primary-green)"><h3 style="color:var(--color-primary-green);font-size:1.25rem;margin-bottom:1rem;text-transform:uppercase;letter-spacing:1px;line-height:1.3">Energy Savings</h3><p style="margin-bottom:0">Lower utility bills redirecting crucial capital straight back into the classroom.</p></div>
    <!-- /wp:e3es/core-pillar -->
    <!-- wp:e3es/core-pillar {"title":"Healthy Facilities","text":"State-of-the-art HVAC filtration mitigates pathogens and improves indoor air quality."} -->
    <div style="background:white;padding:2.5rem;box-shadow:0 10px 30px rgba(0,0,0,0.05);border-top:4px solid var(--color-primary-green)"><h3 style="color:var(--color-primary-green);font-size:1.25rem;margin-bottom:1rem;text-transform:uppercase;letter-spacing:1px;line-height:1.3">Healthy Facilities</h3><p style="margin-bottom:0">State-of-the-art HVAC filtration mitigates pathogens and improves indoor air quality.</p></div>
    <!-- /wp:e3es/core-pillar -->
    <!-- wp:e3es/core-pillar {"title":"Turnkey Execution","text":"E3 handles all aspects of design, engineering, construction management, and validation."} -->
    <div style="background:white;padding:2.5rem;box-shadow:0 10px 30px rgba(0,0,0,0.05);border-top:4px solid var(--color-primary-green)"><h3 style="color:var(--color-primary-green);font-size:1.25rem;margin-bottom:1rem;text-transform:uppercase;letter-spacing:1px;line-height:1.3">Turnkey Execution</h3><p style="margin-bottom:0">E3 handles all aspects of design, engineering, construction management, and validation.</p></div>
    <!-- /wp:e3es/core-pillar -->
</div></section>
<!-- /wp:e3es/core-pillars -->

<!-- wp:e3es/cta-banner {"title":"Ready to Modernize Your Schools?","text":"Learn how E3 can help your district improve campuses out of utility budgets.","btnText":"Request School Audit","btnUrl":"/about-us/contact"} -->
<section class="cta-banner"><div class="cta-banner__container"><h2 class="cta-banner__title">Ready to Modernize Your Schools?</h2><p class="cta-banner__text">Learn how E3 can help your district improve campuses out of utility budgets.</p><a href="/about-us/contact" class="btn btn--primary cta-banner__btn">Request School Audit</a></div></section>
<!-- /wp:e3es/cta-banner -->',
        )
    );

    // Pattern 4: E3 Partners in Funding Layout
    register_block_pattern(
        'e3es/funding-layout',
        array(
            'title'       => __( 'E3 Partners in Funding Layout', 'e3es' ),
            'categories'  => array( 'e3-patterns' ),
            'description' => __( 'Funding page layout with mapSpill overhang column.', 'e3es' ),
            'content'     => '<!-- wp:e3es/intro-banner {"title":"Partners in Funding","bgImageUrl":"/wp-content/uploads/2026/06/Texas-Funding-Solutions-scaled.jpg","bgOpacity":0.85,"bgOverlayColor":"green","bgFadeType":"flat"} -->
<section class="wp-block-e3es-intro-banner db-page-hero" style="background-size:cover;background-position:center;background-repeat:no-repeat"><div class="db-page-hero__container"><h1 class="db-page-hero__title" style="margin-bottom:0;text-align:center;text-transform:uppercase">Partners in Funding</h1></div></section>
<!-- /wp:e3es/intro-banner -->

<!-- wp:e3es/two-column {"imageUrl":"/wp-content/uploads/2026/06/Texas-Funding-Solutions-scaled.jpg","imageAlt":"","reverse":false,"bgStyle":"white","mapSpill":true,"icon":"dollar"} -->
<section class="wp-block-e3es-two-column db-feature db-feature--white db-feature--map-spill"><div class="db-feature__container"><div class="db-feature__content"><div class="db-feature__icon"></div><!-- wp:heading {"level":2} -->
<h2>Funding Upgrades with Utility Savings</h2>
<!-- /wp:heading --><!-- wp:paragraph -->
<p>E3 specializes in identifying and securing state-wide grants, SECO funding, and cooperative contracts to make infrastructure upgrades zero-out-of-pocket for tax districts and institutions.</p>
<!-- /wp:paragraph --></div><div class="db-feature__image-wrapper db-feature__image-wrapper--spill"><img src="/wp-content/uploads/2026/06/Texas-Funding-Solutions-scaled.jpg" alt="" class="db-feature__image" /></div></div></section>
<!-- /wp:e3es/two-column -->

<!-- wp:e3es/cta-banner {"title":"Explore Funding Options","text":"Request a preliminary financial audit to find out how much you can save and fund.","btnText":"Request Audit","btnUrl":"/about-us/contact"} -->
<section class="cta-banner"><div class="cta-banner__container"><h2 class="cta-banner__title">Explore Funding Options</h2><p class="cta-banner__text">Request a preliminary financial audit to find out how much you can save and fund.</p><a href="/about-us/contact" class="btn btn--primary cta-banner__btn">Request Audit</a></div></section>
<!-- /wp:e3es/cta-banner -->',
        )
    );

    // Pattern 5: E3 Texas Interactive Map Layout
    register_block_pattern(
        'e3es/texas-map-layout',
        array(
            'title'       => __( 'E3 Texas Interactive Map Layout', 'e3es' ),
            'categories'  => array( 'e3-patterns' ),
            'description' => __( 'Texas map showcase page layout.', 'e3es' ),
            'content'     => '<!-- wp:e3es/intro-banner {"title":"State-Wide Impact","bgImageUrl":"/wp-content/uploads/2026/06/Texas-Funding-Solutions-scaled.jpg","bgOpacity":0.85,"bgOverlayColor":"green","bgFadeType":"flat"} -->
<section class="wp-block-e3es-intro-banner db-page-hero" style="background-size:cover;background-position:center;background-repeat:no-repeat"><div class="db-page-hero__container"><h1 class="db-page-hero__title" style="margin-bottom:0;text-align:center;text-transform:uppercase">State-Wide Impact</h1></div></section>
<!-- /wp:e3es/intro-banner -->

<!-- wp:e3es/texas-interactive-map /-->

<!-- wp:e3es/services-grid {"mode":"auto","limit":4} /-->',
        )
    );
}

// Disable default core pattern layout categories and remote patterns
add_action( 'after_setup_theme', 'e3es_disable_default_patterns', 99 );
function e3es_disable_default_patterns() {
    remove_theme_support( 'core-block-patterns' );
}
add_filter( 'should_load_remote_block_patterns', '__return_false' );

// Custom admin menu order
add_filter( 'custom_menu_order', '__return_true' );
add_filter( 'menu_order', 'e3es_custom_menu_order' );
function e3es_custom_menu_order( $menu_ord ) {
    if ( ! $menu_ord ) {
        return true;
    }

    $new_menu = array();
    $target_order = array(
        'index.php',                       // Dashboard
        'separator1',                      // Separator 1
        'edit.php',                        // Posts
        'edit.php?post_type=page',         // Pages
        'edit.php?post_type=clients',      // Clients
        'edit.php?post_type=services',     // Services
        'edit.php?post_type=employees',    // Employees
        'edit.php?post_type=people',       // People
        'edit.php?post_type=testimonials', // Testimonials
        'upload.php',                      // Media
    );

    // Build the new menu order based on targets
    foreach ( $target_order as $item ) {
        if ( in_array( $item, $menu_ord ) ) {
            $new_menu[] = $item;
        }
    }

    // Add remaining items
    foreach ( $menu_ord as $item ) {
        if ( ! in_array( $item, $new_menu ) ) {
            $new_menu[] = $item;
        }
    }

    return $new_menu;
}

// Disable comments post type support
add_action( 'admin_init', 'e3es_disable_comments_post_types_support' );
function e3es_disable_comments_post_types_support() {
    $post_types = get_post_types();
    foreach ( $post_types as $post_type ) {
        if ( post_type_supports( $post_type, 'comments' ) ) {
            remove_post_type_support( $post_type, 'comments' );
            remove_post_type_support( $post_type, 'trackbacks' );
        }
    }
}

// Close comments on the front-end
add_filter( 'comments_open', '__return_false', 20, 2 );
add_filter( 'pings_open', '__return_false', 20, 2 );

// Hide existing comments
add_filter( 'comments_array', '__return_empty_array', 10, 2 );

// Remove comments page in menu
add_action( 'admin_menu', 'e3es_disable_comments_admin_menu' );
function e3es_disable_comments_admin_menu() {
    remove_menu_page( 'edit-comments.php' );
}

// Remove comments links from admin bar
add_action( 'wp_before_admin_bar_render', 'e3es_disable_comments_admin_bar' );
function e3es_disable_comments_admin_bar() {
    global $wp_admin_bar;
    $wp_admin_bar->remove_menu( 'comments' );
}

// Disable wpautop for clients and services post types
add_filter( 'the_content', 'e3es_disable_wpautop_for_custom_post_types', 9 );
function e3es_disable_wpautop_for_custom_post_types( $content ) {
    if ( get_post_type() === 'clients' || get_post_type() === 'services' ) {
        remove_filter( 'the_content', 'wpautop' );
    } else {
        if ( ! has_filter( 'the_content', 'wpautop' ) ) {
            add_filter( 'the_content', 'wpautop' );
        }
    }
    return $content;
}

// Form fields for client-services Add screen
add_action( 'client-services_add_form_fields', 'e3_add_client_services_meta_fields', 10, 2 );
function e3_add_client_services_meta_fields($taxonomy) {
    ?>
    <div class="form-field term-group">
        <label for="e3_service_page_id"><?php _e( 'Matching Service Page', 'e3es' ); ?></label>
        <select name="e3_service_page_id" id="e3_service_page_id" class="postform">
            <option value=""><?php _e( 'None', 'e3es' ); ?></option>
            <?php
            $posts = get_posts( array(
                'post_type' => 'services',
                'numberposts' => -1,
                'orderby' => 'title',
                'order' => 'ASC'
            ) );
            foreach ( $posts as $p ) {
                echo '<option value="' . esc_attr($p->ID) . '">' . esc_html($p->post_title) . '</option>';
            }
            ?>
        </select>
        <p><?php _e( 'Link this service term to a specific Services post page.', 'e3es' ); ?></p>
    </div>
    <?php
}

// Form fields for client-services Edit screen
add_action( 'client-services_edit_form_fields', 'e3_edit_client_services_meta_fields', 10, 2 );
function e3_edit_client_services_meta_fields($term, $taxonomy) {
    $service_page_id = get_term_meta( $term->term_id, '_e3_service_page_id', true );
    ?>
    <tr class="form-field term-group-wrap">
        <th scope="row"><label for="e3_service_page_id"><?php _e( 'Matching Service Page', 'e3es' ); ?></label></th>
        <td>
            <select name="e3_service_page_id" id="e3_service_page_id" class="postform">
                <option value=""><?php _e( 'None', 'e3es' ); ?></option>
                <?php
                $posts = get_posts( array(
                    'post_type' => 'services',
                    'numberposts' => -1,
                    'orderby' => 'title',
                    'order' => 'ASC'
                ) );
                foreach ( $posts as $p ) {
                    $selected = selected( $service_page_id, $p->ID, false );
                    echo '<option value="' . esc_attr($p->ID) . '"' . $selected . '>' . esc_html($p->post_title) . '</option>';
                }
                ?>
            </select>
            <p class="description"><?php _e( 'Link this service term to a specific Services post page.', 'e3es' ); ?></p>
        </td>
    </tr>
    <?php
}

// Save custom term metadata
add_action( 'created_client-services', 'e3_save_client_services_meta_fields', 10, 2 );
add_action( 'edited_client-services', 'e3_save_client_services_meta_fields', 10, 2 );
function e3_save_client_services_meta_fields($term_id, $tt_id) {
    if ( isset( $_POST['e3_service_page_id'] ) ) {
        $val = sanitize_text_field( $_POST['e3_service_page_id'] );
        update_term_meta( $term_id, '_e3_service_page_id', $val );
    }
}

// Expose matching service page slug to REST API response for terms
add_action( 'rest_api_init', 'e3_register_client_services_rest_fields' );
function e3_register_client_services_rest_fields() {
    register_rest_field( 'client-services', 'service_page_slug', array(
        'get_callback' => function( $term_array ) {
            $term_id = $term_array['id'];
            $post_id = get_term_meta( $term_id, '_e3_service_page_id', true );
            if ( $post_id ) {
                $post = get_post( $post_id );
                if ( $post ) {
                    return $post->post_name;
                }
            }
            return '';
        },
        'schema' => null,
    ) );
}

// Include seed/import utilities
require_once plugin_dir_path( __FILE__ ) . 'seed-media-import.php';
require_once plugin_dir_path( __FILE__ ) . 'seed-client-blocks.php';
require_once plugin_dir_path( __FILE__ ) . 'seed-team-import.php';
require_once plugin_dir_path( __FILE__ ) . 'seed-add-team-block.php';
if ( file_exists( plugin_dir_path( __FILE__ ) . 'seed-quotes-import.php' ) ) {
    require_once plugin_dir_path( __FILE__ ) . 'seed-quotes-import.php';
}
require_once plugin_dir_path( __FILE__ ) . 'seed-connect-people-clients.php';

// ─── Employees Drag & Drop Reorder Admin Page ────────────────────────────────

add_action( 'admin_menu', 'e3_add_employee_reorder_page' );
function e3_add_employee_reorder_page() {
    add_submenu_page(
        'edit.php?post_type=employees',
        'Reorder Team',
        'Reorder Team',
        'edit_posts',
        'e3-reorder-team',
        'e3_render_employee_reorder_page'
    );
}

// Enqueue jQuery UI Sortable on the reorder page
add_action( 'admin_enqueue_scripts', 'e3_enqueue_reorder_assets' );
function e3_enqueue_reorder_assets( $hook ) {
    if ( $hook !== 'employees_page_e3-reorder-team' ) {
        return;
    }
    wp_enqueue_script( 'jquery-ui-sortable' );
    wp_enqueue_style( 'e3-reorder-style', false );
    wp_add_inline_style( 'e3-reorder-style', e3_get_reorder_css() );
}

function e3_get_reorder_css() {
    return '
    .e3-reorder-wrap { max-width: 900px; margin: 20px auto; }
    .e3-reorder-wrap h1 { margin-bottom: 4px; }
    .e3-reorder-subtitle { color: #646970; margin-bottom: 12px; font-size: 13px; }
    .e3-reorder-status {
        position: sticky; top: 32px; z-index: 100;
        padding: 6px 12px; margin-bottom: 10px;
        border-radius: 4px; font-weight: 500; font-size: 13px;
        display: none; transition: opacity 0.3s;
    }
    .e3-reorder-status--saving { background: #fff3cd; color: #856404; display: block; }
    .e3-reorder-status--saved  { background: #d4edda; color: #155724; display: block; }
    .e3-reorder-status--error  { background: #f8d7da; color: #721c24; display: block; }

    #e3-sortable-list { list-style: none; padding: 0; margin: 0; }
    #e3-sortable-list li {
        display: flex; align-items: center; gap: 10px;
        padding: 4px 10px; margin-bottom: 2px;
        background: #fff; border: 1px solid #dcdcde; border-radius: 4px;
        cursor: grab; user-select: none;
        transition: box-shadow 0.15s, border-color 0.15s;
        line-height: 1.2;
    }
    #e3-sortable-list li:hover { border-color: #2271b1; }
    #e3-sortable-list li.ui-sortable-helper {
        box-shadow: 0 2px 8px rgba(0,0,0,0.12);
        border-color: #2271b1; cursor: grabbing;
    }
    #e3-sortable-list li.ui-sortable-placeholder {
        visibility: visible !important;
        background: #f0f6fc; border: 2px dashed #2271b1;
        min-height: 32px; border-radius: 4px;
    }
    .e3-reorder-drag-handle {
        color: #a7aaad; font-size: 14px; flex-shrink: 0;
        display: flex; align-items: center;
    }
    .e3-reorder-order-num {
        background: #f0f0f1; border-radius: 3px; padding: 1px 6px;
        font-size: 11px; color: #646970; font-weight: 600;
        min-width: 22px; text-align: center; flex-shrink: 0;
    }
    .e3-reorder-thumb {
        width: 28px; height: 28px; border-radius: 50%;
        object-fit: cover; flex-shrink: 0; background: #f0f0f1;
    }
    .e3-reorder-thumb-placeholder {
        width: 28px; height: 28px; border-radius: 50%;
        background: #f0f0f1; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        color: #a7aaad; font-size: 13px;
    }
    .e3-reorder-info { flex: 1; min-width: 0; display: flex; align-items: center; gap: 8px; }
    .e3-reorder-name { font-weight: 600; font-size: 12px; color: #1d2327; white-space: nowrap; }
    .e3-reorder-role { font-size: 11px; color: #888; }
    .e3-reorder-division {
        font-size: 10px; color: #2271b1; background: #f0f6fc;
        padding: 0 6px; border-radius: 3px; flex-shrink: 0;
        white-space: nowrap;
    }
    .e3-reorder-status-badge {
        font-size: 10px; padding: 0 6px; border-radius: 3px; flex-shrink: 0;
    }
    .e3-reorder-status-badge--draft { background: #fff3cd; color: #856404; }
    .e3-reorder-status-badge--private { background: #f8d7da; color: #721c24; }
    ';
}

function e3_render_employee_reorder_page() {
    $employees = get_posts( array(
        'post_type'      => 'employees',
        'post_status'    => array( 'publish', 'draft', 'private' ),
        'posts_per_page' => -1,
        'meta_key'       => '_e3_employee_order',
        'orderby'        => 'meta_value_num',
        'order'          => 'ASC',
    ) );

    $nonce = wp_create_nonce( 'e3_reorder_team' );
    ?>
    <div class="wrap e3-reorder-wrap">
        <h1>Reorder Team Members</h1>
        <p class="e3-reorder-subtitle">Drag and drop to reorder. Changes save automatically.</p>

        <div id="e3-reorder-status" class="e3-reorder-status"></div>

        <ul id="e3-sortable-list">
            <?php foreach ( $employees as $i => $emp ) :
                $role     = get_post_meta( $emp->ID, '_e3_employee_role', true );
                $division = get_post_meta( $emp->ID, '_e3_employee_division', true );
                $thumb_id = get_post_thumbnail_id( $emp->ID );
                $thumb    = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'thumbnail' ) : '';
                $status_class = '';
                $status_label = '';
                if ( $emp->post_status === 'draft' ) {
                    $status_class = 'e3-reorder-status-badge--draft';
                    $status_label = 'Draft';
                } elseif ( $emp->post_status === 'private' ) {
                    $status_class = 'e3-reorder-status-badge--private';
                    $status_label = 'Private';
                }
            ?>
            <li data-id="<?= esc_attr( $emp->ID ) ?>">
                <span class="e3-reorder-drag-handle">⠿</span>
                <span class="e3-reorder-order-num"><?= esc_html( $i + 1 ) ?></span>
                <?php if ( $thumb ) : ?>
                    <img class="e3-reorder-thumb" src="<?= esc_url( $thumb ) ?>" alt="">
                <?php else : ?>
                    <span class="e3-reorder-thumb-placeholder">👤</span>
                <?php endif; ?>
                <div class="e3-reorder-info">
                    <div class="e3-reorder-name"><?= esc_html( $emp->post_title ) ?></div>
                    <?php if ( $role ) : ?>
                        <div class="e3-reorder-role"><?= esc_html( $role ) ?></div>
                    <?php endif; ?>
                </div>
                <?php if ( $division ) : ?>
                    <span class="e3-reorder-division"><?= esc_html( $division ) ?></span>
                <?php endif; ?>
                <?php if ( $status_label ) : ?>
                    <span class="e3-reorder-status-badge <?= $status_class ?>"><?= $status_label ?></span>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <script>
    jQuery(function($) {
        var $list   = $('#e3-sortable-list');
        var $status = $('#e3-reorder-status');
        var saveTimer;

        function updateNumbers() {
            $list.find('li').each(function(i) {
                $(this).find('.e3-reorder-order-num').text(i + 1);
            });
        }

        function showStatus(type, msg) {
            $status.removeClass('e3-reorder-status--saving e3-reorder-status--saved e3-reorder-status--error')
                   .addClass('e3-reorder-status--' + type)
                   .text(msg)
                   .stop(true, true).fadeIn(200);
            if (type === 'saved') {
                setTimeout(function() { $status.fadeOut(400); }, 2000);
            }
        }

        function saveOrder() {
            var order = [];
            $list.find('li').each(function(i) {
                order.push({ id: $(this).data('id'), order: i });
            });

            showStatus('saving', 'Saving order…');

            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'e3_save_team_order',
                    nonce: '<?= $nonce ?>',
                    order: JSON.stringify(order)
                },
                success: function(res) {
                    if (res.success) {
                        showStatus('saved', '✓ Order saved');
                    } else {
                        showStatus('error', 'Error: ' + (res.data || 'Unknown'));
                    }
                },
                error: function() {
                    showStatus('error', 'Network error — order not saved');
                }
            });
        }

        $list.sortable({
            handle: '.e3-reorder-drag-handle, .e3-reorder-thumb, .e3-reorder-thumb-placeholder',
            placeholder: 'ui-sortable-placeholder',
            tolerance: 'pointer',
            cursor: 'grabbing',
            opacity: 0.9,
            update: function() {
                updateNumbers();
                clearTimeout(saveTimer);
                saveTimer = setTimeout(saveOrder, 400);
            }
        });
    });
    </script>
    <?php
}

// AJAX handler for saving team order
add_action( 'wp_ajax_e3_save_team_order', 'e3_save_team_order_ajax' );
function e3_save_team_order_ajax() {
    check_ajax_referer( 'e3_reorder_team', 'nonce' );

    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( 'Insufficient permissions' );
    }

    $order = json_decode( stripslashes( $_POST['order'] ), true );
    if ( ! is_array( $order ) ) {
        wp_send_json_error( 'Invalid data' );
    }

    foreach ( $order as $item ) {
        $post_id = intval( $item['id'] );
        $position = intval( $item['order'] );
        if ( $post_id > 0 ) {
            update_post_meta( $post_id, '_e3_employee_order', $position );
        }
    }

    wp_send_json_success();
}

// Bookmarklet Documentation and Help Menu
add_action('admin_menu', 'e3es_register_help_menu');
function e3es_register_help_menu() {
    add_menu_page(
        'Documentation & Help',
        'Docs & Help',
        'edit_posts',
        'e3es-help',
        'e3es_render_help_page',
        'dashicons-editor-help',
        99
    );
}

function e3es_render_help_page() {
    ?>
    <div class="wrap">
        <h1>Documentation & Help</h1>
        <div class="card" style="max-width: 800px; padding: 20px; margin-top: 20px;">
            <h2>Edit in WordPress Bookmarklet</h2>
            <p>To quickly edit a page you're viewing on the frontend website, you can use our custom Bookmarklet.</p>
            <p><strong>Step 1:</strong> Make sure your browser's bookmarks bar is visible (usually <code>Ctrl+Shift+B</code> or <code>Cmd+Shift+B</code>).</p>
            <p><strong>Step 2:</strong> Drag the blue button below into your bookmarks bar.</p>
            <p style="margin: 20px 0;">
                <a class="button button-primary button-hero" href="javascript:(function(){var meta=document.querySelector('meta[name=\'wp-post-id\']');if(meta&&meta.content){window.open('<?php echo esc_url(admin_url('post.php')); ?>?post='+meta.content+'&action=edit','_blank');}else{alert('No WordPress Post ID found on this page.');}})();" onclick="event.preventDefault(); alert('Drag this button to your bookmarks bar! Do not click it here.');">
                    Edit in WP
                </a>
            </p>
            <p><strong>Step 3:</strong> While browsing the front-end website, if you see a page you want to edit, simply click the <strong>Edit in WP</strong> bookmark and you will be taken directly to the editor for that specific page.</p>
        </div>
    </div>
    <?php
}

// Add a dismissible admin notice (Pop-up style) for the Bookmarklet
add_action('admin_notices', 'e3es_bookmarklet_admin_notice');
function e3es_bookmarklet_admin_notice() {
    $user_id = get_current_user_id();
    if (!get_user_meta($user_id, 'e3es_bookmarklet_notice_dismissed', true) && current_user_can('edit_posts')) {
        ?>
        <div class="notice notice-info is-dismissible" id="e3es-bookmarklet-notice">
            <p><strong>New Feature:</strong> Quickly jump to the editor from the frontend site using our new Bookmarklet!</p>
            <p>Drag this button to your bookmarks bar: 
                <a class="button button-secondary" href="javascript:(function(){var meta=document.querySelector('meta[name=\'wp-post-id\']');if(meta&&meta.content){window.open('<?php echo esc_url(admin_url('post.php')); ?>?post='+meta.content+'&action=edit','_blank');}else{alert('No WordPress Post ID found on this page.');}})();" onclick="event.preventDefault(); alert('Drag this button to your bookmarks bar! Do not click it here.');">
                    Edit in WP
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=e3es-help')); ?>" style="margin-left: 10px;">View Full Instructions</a>
            </p>
        </div>
        <script>
        jQuery(document).ready(function($) {
            $(document).on('click', '#e3es-bookmarklet-notice .notice-dismiss', function() {
                $.ajax({
                    url: ajaxurl,
                    data: {
                        action: 'e3es_dismiss_bookmarklet_notice'
                    }
                });
            });
        });
        </script>
        <?php
    }
}

add_action('wp_ajax_e3es_dismiss_bookmarklet_notice', 'e3es_dismiss_bookmarklet_notice_handler');
function e3es_dismiss_bookmarklet_notice_handler() {
    $user_id = get_current_user_id();
    update_user_meta($user_id, 'e3es_bookmarklet_notice_dismissed', true);
    wp_die();
}

// ─────────────────────────────────────────────────────────────────────────────
// REBUILD ON SAVE & TRANSITION — Dispatch rebuilding when content changes
// ─────────────────────────────────────────────────────────────────────────────

// Hook into post publishing/status transitions
add_action( 'transition_post_status', 'e3es_trigger_rebuild_on_transition', 10, 3 );
function e3es_trigger_rebuild_on_transition( $new_status, $old_status, $post ) {
    // Only trigger for public posts, pages, or custom post types
    $allowed_types = apply_filters( 'e3es_rebuild_post_types', array( 'page', 'post', 'services', 'clients', 'employees', 'people', 'quotes' ) );
    if ( ! in_array( $post->post_type, $allowed_types, true ) ) {
        return;
    }

    // Check if the post status transition indicates content updates (publishing, updating, trashing, etc.)
    if ( $new_status === 'publish' || $old_status === 'publish' ) {
        // Prevent duplicate pings during a single request session
        static $already_triggered = false;
        if ( ! $already_triggered ) {
            $already_triggered = true;
            
            // Debounce across separate requests: only trigger once per 10 seconds to prevent double triggers on fast saves
            if ( ! get_transient( 'e3es_rebuild_dispatched' ) ) {
                set_transient( 'e3es_rebuild_dispatched', true, 10 );
                e3es_dispatch_github_rebuild( $post->ID );
            }
            
            // Schedule a secondary build 15 minutes later to ensure all dependent
            // breadcrumbs and cross-post-type relationships are fully updated.
            if ( ! wp_next_scheduled( 'cf_deploy_delayed_build_event' ) ) {
                wp_schedule_single_event( time() + 15 * 60, 'cf_deploy_delayed_build_event' );
            }
        }
    }
}

// Cron event handler for delayed secondary builds
add_action( 'cf_deploy_delayed_build_event', 'e3es_delayed_build_cron_handler' );
function e3es_delayed_build_cron_handler() {
    e3es_dispatch_github_rebuild();
}

function e3es_dispatch_github_rebuild( $post_id = null ) {
    // 1. Local background build: if on local environment and local project path is configured
    if ( e3es_is_local_env() ) {
        $local_path = get_option( 'e3_headless_local_astro_path', '' );
        if ( ! empty( $local_path ) ) {
            // Write a timestamp to cache.ts to force Vite HMR to invalidate the API cache
            $cmd = 'cd ' . escapeshellarg( $local_path ) . ' && echo "export const cacheBuster = ' . time() . ';" > src/lib/cache.ts 2>&1 &';
            shell_exec( $cmd );
            return true;
        }
    }

    $owner   = get_option( 'cf_deploy_github_owner', '' );
    $repo    = get_option( 'cf_deploy_github_repo',  '' );
    $token   = get_option( 'cf_deploy_github_token', '' );
    $webhook = get_option( 'cf_deploy_webhook_url', '' );

    // Option A: Direct Webhook URL (Pages/Netlify/etc.)
    if ( ! empty( $webhook ) ) {
        $response = wp_remote_post( $webhook, array(
            'timeout'  => 15,
            'blocking' => true,
        ) );
        if ( is_wp_error( $response ) ) {
            error_log( '[E3ES] Webhook dispatch error: ' . $response->get_error_message() );
            return false;
        }
        $code = wp_remote_retrieve_response_code( $response );
        if ( $code < 200 || $code >= 300 ) {
            error_log( '[E3ES] Webhook dispatch unexpected status: ' . $code );
            return false;
        }
        return true;
    }

    // Option B: GitHub API Repository Dispatch
    if ( ! empty( $owner ) && ! empty( $repo ) && ! empty( $token ) ) {
        $url  = "https://api.github.com/repos/{$owner}/{$repo}/dispatches";
        $body = array(
            'event_type'     => 'build-and-deploy',
            'client_payload' => array(
                'post_id'    => $post_id,
                'triggered'  => current_time( 'c' ),
            ),
        );

        $response = wp_remote_post( $url, array(
            'method'  => 'POST',
            'timeout' => 10,
            'headers' => array(
                'Authorization' => 'token ' . $token,
                'Accept'        => 'application/vnd.github.v3+json',
                'User-Agent'    => 'WordPress-E3ES-Headless-Helper',
                'Content-Type'  => 'application/json',
            ),
            'body'    => wp_json_encode( $body ),
        ) );

        if ( is_wp_error( $response ) ) {
            error_log( '[E3ES] GitHub dispatch error: ' . $response->get_error_message() );
            return false;
        }

        $code = wp_remote_retrieve_response_code( $response );
        if ( $code !== 204 ) {
            error_log( '[E3ES] GitHub dispatch unexpected status: ' . $code . ' — ' . wp_remote_retrieve_body( $response ) );
            return false;
        }

        return true;
    }

    return false;
}

// ─────────────────────────────────────────────────────────────────────────────
// REST endpoint: manually trigger a rebuild from the editor toolbar
// ─────────────────────────────────────────────────────────────────────────────
add_action( 'rest_api_init', function () {
    register_rest_route( 'e3es/v1', '/trigger-rebuild', array(
        'methods'             => 'POST',
        'callback'            => function ( $request ) {
            if ( ! current_user_can( 'edit_posts' ) ) {
                return new WP_Error( 'forbidden', 'Insufficient permissions', array( 'status' => 403 ) );
            }
            $post_id = (int) $request->get_param( 'post_id' );
            $ok      = e3es_dispatch_github_rebuild( $post_id ?: null );

            if ( $ok ) {
                return rest_ensure_response( array( 'dispatched' => true, 'message' => 'Build triggered.' ) );
            }
            // Check if credentials are missing
            $has_creds = ( get_option( 'cf_deploy_github_owner' ) && get_option( 'cf_deploy_github_token' ) ) || get_option( 'cf_deploy_webhook_url' );
            if ( ! $has_creds ) {
                return rest_ensure_response( array( 'dispatched' => false, 'message' => 'GitHub/Webhook credentials not configured. Visit Settings → E3 Headless.' ) );
            }
            return rest_ensure_response( array( 'dispatched' => false, 'message' => 'Build trigger failed — check error log.' ) );
        },
        'permission_callback' => function () { return current_user_can( 'edit_posts' ); },
    ) );
} );

/**
 * Customize Admin Columns: Display video title as the link label for the video link column in Quotes CPT.
 */
add_filter( 'ac/column/render', function( $value, $context, $id ) {
    if ( is_a( $context, 'AC\Column\CustomFieldContext' ) ) {
        if ( 'quotes' === $context->get_post_type() && '_e3_quote_video_link' === $context->get_meta_key() ) {
            $url   = get_post_meta( $id, '_e3_quote_video_link', true );
            $title = get_post_meta( $id, '_e3_quote_video_title', true );
            if ( $url ) {
                $label = ! empty( $title ) ? $title : $url;
                $value = '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $label ) . '</a>';
            }
        }
    }
    return $value;
}, 10, 3 );

/**
 * Reorder the WordPress Admin Menu: Move Media above Posts
 */
add_filter( 'custom_menu_order', '__return_true' );
add_filter( 'menu_order', function( $menu_order ) {
    $media_key = array_search( 'upload.php', $menu_order );
    $posts_key = array_search( 'edit.php', $menu_order );
    if ( false !== $media_key && false !== $posts_key ) {
        unset( $menu_order[$media_key] );
        
        $new_order = array();
        foreach ( $menu_order as $item ) {
            if ( $item === 'edit.php' ) {
                $new_order[] = 'upload.php';
            }
            $new_order[] = $item;
        }
        $menu_order = $new_order;
    }
    return $menu_order;
} );

/**
 * Filter standard REST API response for clients custom post type to expose clean meta fields.
 */
add_filter( 'rest_prepare_clients', 'e3_rest_prepare_clients', 10, 3 );
function e3_rest_prepare_clients( $response, $post, $request ) {
    $data = $response->get_data();
    
    if ( ! isset( $data['meta'] ) || ! is_array( $data['meta'] ) ) {
        $data['meta'] = array();
    }
    
    $data['meta']['region']         = get_post_meta( $post->ID, '_e3_client_region', true ) ?: 'central';
    $data['meta']['year_completed'] = get_post_meta( $post->ID, '_e3_client_year', true ) ?: 'Active';
    $data['meta']['location']       = get_post_meta( $post->ID, '_e3_client_location', true ) ?: '';
    $data['meta']['scope']          = get_post_meta( $post->ID, '_e3_client_scope', true ) ?: '';
    $data['meta']['contract_type']  = get_post_meta( $post->ID, '_e3_client_contract', true ) ?: '';
    $data['meta']['client_logo']    = get_post_meta( $post->ID, '_e3_client_logo', true ) ?: '';
    
    $response->set_data( $data );
    return $response;
}

/**
 * Replicate e3es/intro-banner styling logic in PHP for precise, validation-error-free seeding
 */
function e3es_get_banner_styles( $attr ) {
    $rgbMap = array(
        'green' => '33, 87, 52',
        'sage'  => '125, 160, 68',
        'black' => '0, 0, 0',
        'blue'  => '16, 44, 87'
    );
    $bgOverlayColor = $attr['bgOverlayColor'] ?? 'green';
    $rgb = $rgbMap[$bgOverlayColor] ?? $rgbMap['green'];
    $bgOpacity = isset($attr['bgOpacity']) ? (float)$attr['bgOpacity'] : 0.85;
    
    $bgFadeType = $attr['bgFadeType'] ?? 'flat';
    $gradient = '';
    switch ($bgFadeType) {
        case 'vertical':
            $gradient = 'linear-gradient(to bottom, rgba(' . $rgb . ',' . ($bgOpacity * 0.4) . '), rgba(' . $rgb . ',' . $bgOpacity . '))';
            break;
        case 'horizontal':
            $gradient = 'linear-gradient(to right, rgba(' . $rgb . ',' . $bgOpacity . '), rgba(' . $rgb . ',' . ($bgOpacity * 0.3) . '))';
            break;
        case 'vignette':
            $gradient = 'radial-gradient(circle, rgba(' . $rgb . ',' . ($bgOpacity * 0.4) . ') 0%, rgba(' . $rgb . ',' . $bgOpacity . ') 100%)';
            break;
        case 'vignette-center':
            $gradient = 'radial-gradient(circle, rgba(' . $rgb . ',' . $bgOpacity . ') 0%, rgba(' . $rgb . ',' . ($bgOpacity * 0.4) . ') 100%)';
            break;
        case 'flat':
        default:
            $gradient = 'linear-gradient(rgba(' . $rgb . ',' . $bgOpacity . '), rgba(' . $rgb . ',' . $bgOpacity . '))';
            break;
    }

    $heroStyle = array();
    if (!empty($attr['bgImageUrl'])) {
        $heroStyle['background-image'] = $gradient . ', url(' . $attr['bgImageUrl'] . ')';
        $heroStyle['background-size'] = 'cover';
        $fx = isset($attr['focalPointX']) ? (float)$attr['focalPointX'] : 0.5;
        $fy = isset($attr['focalPointY']) ? (float)$attr['focalPointY'] : 0.5;
        $heroStyle['background-position'] = ($fx * 100) . '% ' . ($fy * 100) . '%';
        $heroStyle['background-repeat'] = 'no-repeat';
    } else {
        $heroStyle['background-color'] = 'rgba(' . $rgb . ', 1)';
    }

    $parts = array();
    foreach ($heroStyle as $k => $v) {
        $parts[] = $k . ':' . $v;
    }
    return implode(';', $parts);
}

function e3es_get_title_styles( $attr ) {
    $shadowMap = array(
        'none' => 'none',
        'subtle' => '0 2px 4px rgba(0,0,0,0.3)',
        'strong' => '0 4px 15px rgba(0,0,0,0.8), 0 2px 4px rgba(0,0,0,0.5)'
    );
    
    $textShadowKey = $attr['textShadow'] ?? 'subtle';
    $textShadow = $shadowMap[$textShadowKey] ?? $shadowMap['subtle'];
    
    $titleStyle = array(
        'margin-bottom' => '0',
        'text-align' => $attr['textAlignment'] ?? 'center',
        'text-transform' => $attr['textCase'] ?? 'uppercase',
        'text-shadow' => $textShadow
    );
    
    if (!empty($attr['textSkew'])) {
        $titleStyle['transform'] = 'skewX(-5deg)';
        $titleStyle['display'] = 'inline-block';
    }

    $parts = array();
    foreach ($titleStyle as $k => $v) {
        $parts[] = $k . ':' . $v;
    }
    return implode(';', $parts);
}

/**
 * Generate standard, validation-compliant e3es/intro-banner block markup
 */
function e3es_make_intro_banner_markup( $args ) {
    $defaults = array(
        'title'           => '',
        'bgImageUrl'      => '',
        'bgOpacity'       => 0.85,
        'bgOverlayColor'  => 'green',
        'bgFadeType'      => 'flat',
        'textShadow'      => 'subtle',
        'textAlignment'   => 'center',
        'textCase'        => 'uppercase',
        'textSkew'        => false,
        'focalPointX'     => 0.5,
        'focalPointY'     => 0.5,
        'clientLogoUrl'   => '',
        'logoHasCircle'   => true,
        'region'          => '',
        'industry'        => '',
        'subtitle'        => '',
    );
    $attr = array_merge( $defaults, $args );
    
    // RichText title needs to have double quotes escaped in JSON, e.g. \"
    // json_encode will handle this automatically.
    $attrs_json = json_encode( $attr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
    
    $style = e3es_get_banner_styles( $attr );
    $title_style = e3es_get_title_styles( $attr );
    
    $logo_html = '';
    if ( ! empty( $attr['clientLogoUrl'] ) ) {
        $has_circle = ! isset( $attr['logoHasCircle'] ) || $attr['logoHasCircle'] !== false;
        $logo_class = 'db-page-hero__logo-wrapper ' . ( $has_circle ? 'db-page-hero__logo-wrapper--circle' : 'db-page-hero__logo-wrapper--no-circle' );
        $logo_html = '<div class="' . $logo_class . '"><img src="' . esc_url( $attr['clientLogoUrl'] ) . '" alt="Client Logo" class="db-page-hero__logo-img"/></div>';
    }
    
    $intro_html = '';
    if ( ! empty( $attr['subtitle'] ) ) {
        $intro_html = '<div class="db-page-hero__intro"><p>' . $attr['subtitle'] . '</p></div>';
    } elseif ( ! empty( $attr['region'] ) || ! empty( $attr['industry'] ) ) {
        $region_label = $attr['region'] ? esc_html( $attr['region'] ) : '';
        $industry_label = $attr['industry'] ? esc_html( $attr['industry'] ) : '';
        $intro_text_parts = array();
        if ( $industry_label ) $intro_text_parts[] = $industry_label;
        if ( $region_label ) $intro_text_parts[] = $region_label;
        $intro_text = implode( ' | ', $intro_text_parts );
        $intro_html = '<div class="db-page-hero__intro"><p>' . $intro_text . '</p></div>';
    }
    
    return "<!-- wp:e3es/intro-banner " . $attrs_json . " -->\n" .
           "<section class=\"wp-block-e3es-intro-banner db-page-hero\" style=\"" . $style . "\"><div class=\"db-page-hero__container\">" . $logo_html . "<div><h1 class=\"db-page-hero__title\" style=\"" . $title_style . "\">" . $attr['title'] . "</h1>" . $intro_html . "</div></div></section>\n" .
           "<!-- /wp:e3es/intro-banner -->";
}

add_action( 'init', 'e3_clean_duplicate_faqs_handler' );
function e3_clean_duplicate_faqs_handler() {
    if ( ! isset( $_GET['e3_clean_faqs'] ) || $_GET['e3_clean_faqs'] !== '1' ) {
        return;
    }

    $is_local = ( strpos( $_SERVER['HTTP_HOST'] ?? '', '.local' ) !== false || $_SERVER['HTTP_HOST'] === 'localhost' );
    if ( ! $is_local && ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Unauthorized.' );
    }

    header( 'Content-Type: text/plain; charset=utf-8' );
    echo "Starting FAQ Section cleanup and relocation...\n\n";

    $posts = get_posts( array(
        'post_type'      => 'any',
        'posts_per_page' => -1,
        'post_status'    => 'any',
    ) );

    $updated_count = 0;

    foreach ( $posts as $post ) {
        if ( strpos( $post->post_content, 'wp:e3es/faq-section' ) === false ) {
            continue;
        }

        $blocks = parse_blocks( $post->post_content );
        $modified = false;
        $faq_block = null;

        // Recursive function to locate, clean, and extract the FAQ block
        $extract_and_clean_faq = function( &$blocksList ) use ( &$faq_block, &$modified, $post, &$extract_and_clean_faq ) {
            foreach ( $blocksList as $index => &$block ) {
                if ( $block['blockName'] === 'e3es/faq-section' ) {
                    $new_inner_blocks = array();
                    $removed_redundant_heading = false;
                    $question_texts = array();

                    foreach ( $block['innerBlocks'] as $inner ) {
                        if ( $inner['blockName'] === 'core/heading' ) {
                            $heading_text = strip_tags( $inner['innerHTML'] ?? '' );
                            $heading_text = trim( html_entity_decode( $heading_text ) );

                            if ( $heading_text === 'Frequently Asked Questions (FAQ)' || $heading_text === 'Frequently Asked Questions' ) {
                                $removed_redundant_heading = true;
                                $modified = true;
                                continue;
                            }
                            if ( ! empty( $heading_text ) ) {
                                $question_texts[] = strtolower( $heading_text );
                            }
                        }
                        $new_inner_blocks[] = $inner;
                    }

                    if ( $removed_redundant_heading ) {
                        $block['innerBlocks'] = $new_inner_blocks;
                    }

                    $block['attrs']['title'] = 'Frequently Asked Questions';

                    // Determine topics based on content questions
                    $topics = array();
                    $has_financing = false;
                    $has_hvac = false;
                    $has_lighting = false;
                    $has_water = false;
                    $has_controls = false;

                    foreach ( $question_texts as $qt ) {
                        if ( strpos( $qt, 'maintenance tax' ) !== false || strpos( $qt, 'financing' ) !== false || strpos( $qt, 'espc' ) !== false || strpos( $qt, 'guarantee' ) !== false || strpos( $qt, 'funding' ) !== false ) {
                            $has_financing = true;
                        }
                        if ( strpos( $qt, 'hvac' ) !== false || strpos( $qt, 'heating' ) !== false || strpos( $qt, 'air conditioning' ) !== false || strpos( $qt, 'cooling' ) !== false || strpos( $qt, 'ventilation' ) !== false ) {
                            $has_hvac = true;
                        }
                        if ( strpos( $qt, 'lighting' ) !== false || strpos( $qt, 'led' ) !== false || strpos( $qt, 'fixtures' ) !== false ) {
                            $has_lighting = true;
                        }
                        if ( strpos( $qt, 'water' ) !== false || strpos( $qt, 'wastewater' ) !== false || strpos( $qt, 'sewer' ) !== false ) {
                            $has_water = true;
                        }
                        if ( strpos( $qt, 'controls' ) !== false || strpos( $qt, 'automation' ) !== false || strpos( $qt, 'bas' ) !== false ) {
                            $has_controls = true;
                        }
                    }

                    if ( $has_financing ) $topics[] = 'Project Financing';
                    if ( $has_hvac ) $topics[] = 'HVAC Upgrades';
                    if ( $has_lighting ) $topics[] = 'LED Lighting';
                    if ( $has_water ) $topics[] = 'Water Conservation';
                    if ( $has_controls ) $topics[] = 'Smart Controls';

                    $desc = implode( ', ', $topics );
                    
                    $block['attrs']['description'] = $desc;
                    $faq_block = $block;

                    unset( $blocksList[$index] );
                    $blocksList = array_values( $blocksList );
                    $modified = true;
                    return true;
                }

                if ( ! empty( $block['innerBlocks'] ) ) {
                    if ( $extract_and_clean_faq( $block['innerBlocks'] ) ) {
                        return true;
                    }
                }
            }
            return false;
        };

        // Run the extraction and cleanup
        $extract_and_clean_faq( $blocks );

        // Append to the root level
        if ( $faq_block !== null ) {
            $blocks[] = $faq_block;
            $modified = true;
        }

        if ( $modified ) {
            $new_content = serialize_blocks( $blocks );
            wp_update_post( array(
                'ID'           => $post->ID,
                'post_content' => wp_slash( $new_content ),
            ) );
            echo "Updated & Relocated FAQ for post ID {$post->ID}: \"{$post->post_title}\" (Type: {$post->post_type})\n";
            $updated_count++;
        }
    }

    echo "\nFAQ Cleanup and Relocation completed. Updated {$updated_count} posts.\n";
    exit;
}

/**
 * Disable Gutenberg duotone color filters globally.
 */
function e3es_disable_duotone_filters( $theme_json ) {
    $new_data = array(
        'version'  => 3,
        'settings' => array(
            'color' => array(
                'duotone' => null,
                'customDuotone' => false,
                'defaultDuotone' => false,
            ),
        ),
    );
    return $theme_json->update_with( $new_data );
}
add_filter( 'wp_theme_json_data_theme', 'e3es_disable_duotone_filters' );

// Set Admin Columns Pro storage directory to plugin folder /acp-settings (Theme-Agnostic File Storage)
add_filter( 'acp/storage/file/directory', function() {
	return dirname( __FILE__ ) . '/acp-settings';
} );

// Add a "Featured Clients" view link above the clients list table in wp-admin
add_filter( 'views_edit-clients', 'e3_add_featured_clients_view_link' );
function e3_add_featured_clients_view_link( $views ) {
	global $wpdb;

	// Count featured clients
	$featured_count = $wpdb->get_var( "
		SELECT COUNT(DISTINCT p.ID) 
		FROM {$wpdb->posts} p
		INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
		WHERE p.post_type = 'clients' 
		  AND p.post_status IN ('publish', 'draft', 'pending', 'private')
		  AND pm.meta_key = '_e3_client_show_in_index' 
		  AND pm.meta_value = '1'
	" );

	$is_current = ( isset( $_GET['acp_filter']['45c6ebdb445a1c'] ) && $_GET['acp_filter']['45c6ebdb445a1c'] === '1' );
	$class = $is_current ? 'class="current"' : '';

	$url = admin_url( 'edit.php?ac-actions-form=1&orderby=title&order=asc&s&post_status=all&post_type=clients&m=0&layout=49dae1f7860c1&acp_filter%5B45c6ebdb445a1c%5D=1&filter_action=Filter&action=-1&paged=1&action2=-1&ac-rules' );

	$views['featured'] = sprintf(
		'<a href="%s" %s>%s <span class="count">(%d)</span></a>',
		esc_url( $url ),
		$class,
		__( 'Featured Clients', 'e3es-headless-helper' ),
		$featured_count
	);

	// If we are currently viewing featured clients, the 'all' view should NOT have the 'current' class
	if ( $is_current && isset( $views['all'] ) ) {
		$views['all'] = preg_replace( '/class="current"(\s*)/i', '', $views['all'] );
	}

	return $views;
}




