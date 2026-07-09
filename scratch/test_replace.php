<?php
$post_id = 3875;
$post = get_post($post_id);
if ($post) {
    echo "Content Start:\n";
    echo substr($post->post_content, 0, 1000) . "\n";
} else {
    echo "Post not found.\n";
}
?>
