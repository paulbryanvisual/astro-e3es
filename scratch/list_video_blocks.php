<?php
require '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';

$posts = get_posts(['post_type' => 'clients', 'posts_per_page' => -1]);
foreach ($posts as $p) {
    if (strpos($p->post_content, 'e3es/video-embed') !== false) {
        $blocks = parse_blocks($p->post_content);
        foreach ($blocks as $b) {
            if ($b['blockName'] === 'e3es/video-embed') {
                echo "Post: {$p->post_name}\n";
                echo "  Attrs: " . json_encode($b['attrs']) . "\n";
                echo "  HTML: " . substr(trim($b['innerHTML']), 0, 200) . "...\n";
            }
        }
    }
}
