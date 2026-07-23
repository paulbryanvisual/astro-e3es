<?php
/**
 * One-time script to add the team-directory block to the Our Team page.
 * Trigger: wp-admin/admin.php?e3_add_team_block=1
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_init', function() {
    if ( empty( $_GET['e3_add_team_block'] ) || $_GET['e3_add_team_block'] !== '1' ) {
        return;
    }
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Unauthorized' );
    }

    $post = get_post( 24 );
    if ( ! $post ) {
        wp_die( 'Post 24 not found' );
    }

    // Check if the block already exists
    if ( strpos( $post->post_content, 'e3es/team-directory' ) !== false ) {
        wp_die( 'Team directory block already exists in this page.' );
    }

    // Append the team-directory block to the existing content
    $new_content = $post->post_content . "\n\n" . '<!-- wp:e3es/team-directory /-->' . "\n";

    wp_update_post( array(
        'ID'           => 24,
        'post_content' => $new_content,
    ) );

    wp_die(
        '<h2>Done!</h2>' .
        '<p>Added <code>&lt;!-- wp:e3es/team-directory /--&gt;</code> to the "Our Team" page.</p>' .
        '<p><a href="' . admin_url( 'post.php?post=24&action=edit' ) . '">Open in Editor →</a></p>',
        'Block Added',
        array( 'back_link' => true )
    );
});
