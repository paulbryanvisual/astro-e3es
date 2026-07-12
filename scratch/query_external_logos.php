<?php
require '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';
$posts = get_posts(['post_type' => 'clients', 'posts_per_page' => -1]);
$res = [];
foreach ($posts as $p) {
    $urls = [];
    
    // Check post content
    if (preg_match_all('/https?:\/\/(?:www\.)?txhslogoproject\.com\/[^\s"\'>]+/i', $p->post_content, $matches)) {
        foreach ($matches[0] as $url) {
            $clean_url = strtok($url, '"');
            $clean_url = strtok($clean_url, "'");
            $clean_url = strtok($clean_url, "}");
            $urls[] = $clean_url;
        }
    }
    
    // Check metadata
    $logo_meta = get_post_meta($p->ID, '_e3_client_logo', true);
    if ($logo_meta && strpos($logo_meta, 'txhslogoproject.com') !== false) {
        $urls[] = $logo_meta;
    }
    
    foreach (array_unique($urls) as $clean_url) {
        $res[] = ['id' => $p->ID, 'slug' => $p->post_name, 'url' => $clean_url];
    }
}
echo json_encode($res);
