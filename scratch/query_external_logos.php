<?php
require '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';
$posts = get_posts(['post_type' => 'clients', 'posts_per_page' => -1]);
$res = [];
foreach ($posts as $p) {
    if (preg_match_all('/https?:\/\/(?:www\.)?txhslogoproject\.com\/[^\s"\'>]+/i', $p->post_content, $matches)) {
        foreach (array_unique($matches[0]) as $url) {
            $clean_url = strtok($url, '"');
            $clean_url = strtok($clean_url, "'");
            $res[] = ['id' => $p->ID, 'slug' => $p->post_name, 'url' => $clean_url];
        }
    }
}
echo json_encode($res);
