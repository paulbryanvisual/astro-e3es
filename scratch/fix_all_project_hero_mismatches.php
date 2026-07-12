<?php
require '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';

$posts = get_posts(['post_type' => 'clients', 'posts_per_page' => -1]);
$updated_count = 0;

function align_hero_images(&$blocks) {
    $changed = false;
    foreach ($blocks as &$b) {
        if ($b['blockName'] === 'e3es/project') {
            if (!empty($b['attrs']['heroImageUrl'])) {
                $hero_url = $b['attrs']['heroImageUrl'];
                
                // Align src in img tag
                $new_html = preg_replace('/src=["\'][^"\']*["\']/i', 'src="' . $hero_url . '"', $b['innerHTML']);
                // Align url(...) in style attribute (remove any escaped single quotes or quotes inside)
                $new_html = preg_replace('/url\([^)]*\)/i', 'url(' . $hero_url . ')', $new_html);
                
                if ($new_html !== $b['innerHTML']) {
                    $b['innerHTML'] = $new_html;
                    $b['innerContent'] = [ $new_html ];
                    $changed = true;
                }
            }
        }
        if ($b['blockName'] === 'e3es/intro-banner') {
            if (!empty($b['attrs']['bgImageUrl'])) {
                $bg_url = $b['attrs']['bgImageUrl'];
                
                // Align url(...) in style attribute
                $new_html = preg_replace('/url\([^)]*\)/i', 'url(' . $bg_url . ')', $b['innerHTML']);
                
                if ($new_html !== $b['innerHTML']) {
                    $b['innerHTML'] = $new_html;
                    $b['innerContent'] = [ $new_html ];
                    $changed = true;
                }
            }
        }
        if (!empty($b['innerBlocks'])) {
            if (align_hero_images($b['innerBlocks'])) {
                $changed = true;
            }
        }
    }
    return $changed;
}

foreach ($posts as $p) {
    $blocks = parse_blocks($p->post_content);
    if (align_hero_images($blocks)) {
        $new_content = serialize_blocks($blocks);
        wp_update_post([
            'ID' => $p->ID,
            'post_content' => $new_content
        ]);
        echo "Updated blocks alignment for: {$p->post_name}\n";
        $updated_count++;
    }
}

echo "Done! Updated {$updated_count} client posts.\n";
