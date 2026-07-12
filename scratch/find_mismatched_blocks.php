<?php
require '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';

$posts = get_posts(['post_type' => 'clients', 'posts_per_page' => -1]);
foreach ($posts as $p) {
    $blocks = parse_blocks($p->post_content);
    $mismatch = false;
    
    $check_mismatch = function($blocks) use (&$check_mismatch, &$mismatch, $p) {
        foreach ($blocks as $b) {
            if ($b['blockName'] === 'e3es/project') {
                if (!empty($b['attrs']['heroImageUrl'])) {
                    $hero_url = $b['attrs']['heroImageUrl'];
                    // Find URL in src="..."
                    if (preg_match('/src=["\']([^"\']+)["\']/i', $b['innerHTML'], $m)) {
                        $src_url = $m[1];
                        if (urldecode($src_url) !== urldecode($hero_url)) {
                            echo "MISMATCH [project src] in {$p->post_name}: heroImageUrl={$hero_url} vs src={$src_url}\n";
                            $mismatch = true;
                        }
                    }
                    // Find URL in url(...)
                    if (preg_match('/url\(([^)]+)\)/i', $b['innerHTML'], $m)) {
                        $bg_url = trim($m[1], "'\" ");
                        if (urldecode($bg_url) !== urldecode($hero_url)) {
                            echo "MISMATCH [project bg] in {$p->post_name}: heroImageUrl={$hero_url} vs bg={$bg_url}\n";
                            $mismatch = true;
                        }
                    }
                }
            }
            if ($b['blockName'] === 'e3es/intro-banner') {
                if (!empty($b['attrs']['bgImageUrl'])) {
                    $bg_url_attr = $b['attrs']['bgImageUrl'];
                    if (preg_match('/url\(([^)]+)\)/i', $b['innerHTML'], $m)) {
                        $bg_url = trim($m[1], "'\" ");
                        if (urldecode($bg_url) !== urldecode($bg_url_attr)) {
                            echo "MISMATCH [banner bg] in {$p->post_name}: bgImageUrl={$bg_url_attr} vs bg={$bg_url}\n";
                            $mismatch = true;
                        }
                    }
                }
            }
            if (!empty($b['innerBlocks'])) {
                $check_mismatch($b['innerBlocks']);
            }
        }
    };
    
    $check_mismatch($blocks);
}
