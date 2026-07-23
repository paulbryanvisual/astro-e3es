<?php
require_once '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';
$p = get_post(1452);
echo "Post Title: " . $p->post_title . "\n";
echo "Post Modified: " . $p->post_modified . "\n";
