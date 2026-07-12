<?php
require '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';

$posts = get_posts(['post_type' => 'clients', 'posts_per_page' => -1]);
foreach ($posts as $p) {
    $blocks = parse_blocks($p->post_content);
    
    $check = function($blocks) use (&$check, $p) {
        foreach ($blocks as $b) {
            if ($b['blockName'] === 'e3es/project') {
                if (!empty($b['attrs']['heroImageUrl'])) {
                    if (strpos($b['innerHTML'], 'project-section__hero') === false) {
                        echo "MISSING_HERO_HTML in {$p->post_name}: heroImageUrl is set but HTML has no hero div\n";
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
