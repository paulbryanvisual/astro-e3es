<?php
require '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';

$posts = get_posts(['post_type' => 'clients', 'posts_per_page' => -1]);
foreach ($posts as $p) {
    $blocks = parse_blocks($p->post_content);
    
    $check = function($blocks) use (&$check, $p) {
        foreach ($blocks as $b) {
            if ($b['blockName'] === 'e3es/project') {
                if (!empty($b['attrs']['heroImageUrl'])) {
                    $url = $b['attrs']['heroImageUrl'];
                    if (strtolower(pathinfo($url, PATHINFO_EXTENSION)) === 'png') {
                        echo "PNG_HERO in {$p->post_name}: {$url}\n";
                    }
                }
            }
            if ($b['blockName'] === 'e3es/intro-banner') {
                if (!empty($b['attrs']['bgImageUrl'])) {
                    $url = $b['attrs']['bgImageUrl'];
                    if (strtolower(pathinfo($url, PATHINFO_EXTENSION)) === 'png') {
                        echo "PNG_BANNER in {$p->post_name}: {$url}\n";
                    }
                }
            }
            if (!empty($b['innerBlocks'])) {
                $check($b['innerBlocks']);
            }
        }
    };
    
    $check($blocks);
}
