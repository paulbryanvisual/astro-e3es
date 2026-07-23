<?php
require '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';

$post = get_page_by_path('bishop-cisd', OBJECT, 'clients');
if (!$post) {
    die("Post not found\n");
}

$blocks = parse_blocks($post->post_content);

function align_hero_images(&$blocks) {
    foreach ($blocks as &$b) {
        if ($b['blockName'] === 'e3es/project') {
            if (!empty($b['attrs']['heroImageUrl'])) {
                $hero_url = $b['attrs']['heroImageUrl'];
                // Update src in img tag
                $b['innerHTML'] = preg_replace('/src=["\'][^"\']*["\']/i', 'src="' . $hero_url . '"', $b['innerHTML']);
                // Update url(...) in style attribute
                $b['innerHTML'] = preg_replace('/url\([^\)]*\)/i', 'url(' . $hero_url . ')', $b['innerHTML']);
                // Update innerContent
                $b['innerContent'] = [ $b['innerHTML'] ];
            }
        }
        if ($b['blockName'] === 'e3es/intro-banner') {
            if (!empty($b['attrs']['bgImageUrl'])) {
                $bg_url = $b['attrs']['bgImageUrl'];
                // Update url(...) in style attribute
                $b['innerHTML'] = preg_replace('/url\([^\)]*\)/i', 'url(' . $bg_url . ')', $b['innerHTML']);
                $b['innerContent'] = [ $b['innerHTML'] ];
            }
        }
        if (!empty($b['innerBlocks'])) {
            align_hero_images($b['innerBlocks']);
        }
    }
}

align_hero_images($blocks);
$new_content = serialize_blocks($blocks);
echo "--- BEFORE ---\n";
echo $post->post_content . "\n";
echo "--- AFTER ---\n";
echo $new_content . "\n";
