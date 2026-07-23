<?php
require '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';

$p = get_page_by_path('anderson-shiro-cisd', OBJECT, 'clients');
if (!$p) {
    die("Post not found\n");
}

$blocks = parse_blocks($p->post_content);

function clean_block_entities(&$blocks) {
    foreach ($blocks as &$b) {
        // Clean double-escaped or raw entity strings in block attributes
        if (!empty($b['attrs'])) {
            foreach ($b['attrs'] as $key => &$val) {
                if (is_string($val)) {
                    // Replace literal u0026amp; or u0026 with raw ampersand
                    $val = str_replace(['u0026amp;', 'u0026', 'amp;'], '&', $val);
                    $val = str_replace('u0027', "'", $val);
                    $val = html_entity_decode($val, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                }
            }
        }
        
        // Clean double-escaped or raw entity strings in block innerHTML
        if (!empty($b['innerHTML'])) {
            // Replace literal u0026amp; or u0026 with raw ampersand or html entity &amp;
            $b['innerHTML'] = str_replace(['u0026amp;', 'u0026'], '&amp;', $b['innerHTML']);
            $b['innerHTML'] = str_replace('u0027', "'", $b['innerHTML']);
            $b['innerContent'] = [ $b['innerHTML'] ];
        }
        
        if (!empty($b['innerBlocks'])) {
            clean_block_entities($b['innerBlocks']);
        }
    }
}

clean_block_entities($blocks);
$new_content = serialize_blocks($blocks);

// Clean up any remaining literal u0026 in the whole content string (just in case)
$new_content = str_replace('u0026amp;', '&amp;', $new_content);

echo "--- BEFORE UPDATE ---\n";
echo "Slashed check: Does it contain \\\\u0026? " . (strpos($new_content, '\\u0026') !== false ? "Yes" : "No") . "\n";

// Update post with wp_slash!
wp_update_post(wp_slash([
    'ID' => $p->ID,
    'post_content' => $new_content
]));

// Fetch it back to see how it looks in database
$updated_post = get_post($p->ID);
echo "--- AFTER FETCH FROM DB ---\n";
echo "Slashed check in DB: Does it contain \\\\u0026? " . (strpos($updated_post->post_content, '\\u0026') !== false ? "Yes" : "No") . "\n";
echo "Full DB Content:\n" . $updated_post->post_content . "\n";
