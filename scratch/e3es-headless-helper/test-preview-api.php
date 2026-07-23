<?php
require_once('../../../wp-load.php');
$post_id = 12; // Boyd ISD
$autosaves = wp_get_post_autosave( $post_id );
if ( $autosaves ) {
    echo "Found autosave ID: " . $autosaves->ID . "\n";
    echo "Content: " . substr($autosaves->post_content, 0, 100) . "...\n";
} else {
    echo "No autosave found.\n";
    $latest_revision = wp_get_post_revisions( $post_id, array('posts_per_page' => 1) );
    if ( !empty($latest_revision) ) {
        $rev = array_shift($latest_revision);
        echo "Found revision ID: " . $rev->ID . "\n";
        echo "Content: " . substr($rev->post_content, 0, 100) . "...\n";
    }
}
