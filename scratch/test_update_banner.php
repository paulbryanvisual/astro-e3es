<?php
require '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';

$p = get_page_by_path('anderson-shiro-cisd', OBJECT, 'clients');
if (!$p) {
    die("Post not found\n");
}

$photo_url = "http://e3es2026.local/wp-content/uploads/2026/07/anderson-shiro-cisd-cropped-layout-photo.jpg";
$blocks = parse_blocks($p->post_content);
$changed = false;

foreach ($blocks as &$b) {
    if ($b['blockName'] === 'e3es/intro-banner') {
        if (!empty($b['attrs']['bgImageUrl'])) {
            $url = $b['attrs']['bgImageUrl'];
            echo "Found intro-banner: bgImageUrl=$url\n";
            $b['attrs']['bgImageUrl'] = $photo_url;
            $b['innerHTML'] = str_replace($url, $photo_url, $b['innerHTML']);
            $b['innerContent'] = [ $b['innerHTML'] ];
            $changed = true;
        }
    }
}

if ($changed) {
    $new_content = serialize_blocks($blocks);
    echo "--- NEW CONTENT ---\n";
    echo $new_content . "\n";
    $res = wp_update_post([
        'ID' => $p->ID,
        'post_content' => $new_content
    ]);
    if (is_wp_error($res)) {
        echo "Error updating post: " . $res->get_error_message() . "\n";
    } else {
        echo "Post updated successfully! Return ID: $res\n";
    }
} else {
    echo "No changes made.\n";
}
