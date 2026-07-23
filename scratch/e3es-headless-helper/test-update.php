<?php
define('WP_USE_THEMES', false);
require_once('../../../wp-load.php');
$post = get_post(12);
echo "Original title: " . $post->post_title . "\n";
