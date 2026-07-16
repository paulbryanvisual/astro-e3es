<?php
require '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';

$posts = get_posts([
    'post_type' => ['page', 'post', 'clients', 'services'],
    'posts_per_page' => -1,
    'post_status' => 'any'
]);

echo "Auditing all testimonial-related Gutenberg blocks...\n\n";

$blocks_to_search = [
    'e3es/mini-testimonial',
    'e3es/testimonial-picker',
    'e3es/full-width-testimonial'
];

foreach ($posts as $p) {
    $content = $p->post_content;
    $found_in_post = false;
    
    // Simple search for block names
    foreach ($blocks_to_search as $block_name) {
        if (strpos($content, $block_name) !== false) {
            if (!$found_in_post) {
                echo "📄 Post: '{$p->post_title}' (ID: {$p->ID}, Type: {$p->post_type}, Status: {$p->post_status})\n";
                $found_in_post = true;
            }
            
            // Extract block markup using regex
            // e.g. <!-- wp:e3es/mini-testimonial {"mode":"linked","testimonialId":11070} /-->
            // or <!-- wp:e3es/mini-testimonial ... --> ... <!-- /wp:e3es/mini-testimonial -->
            $esc_name = preg_quote($block_name, '/');
            $pattern_self_closing = '/<!-- wp:' . $esc_name . ' ({.*?}) \/-->/';
            $pattern_enclosing = '/<!-- wp:' . $esc_name . ' ({.*?}) -->([\s\S]*?)<!-- \/wp:' . $esc_name . ' -->/';
            
            if (preg_match_all($pattern_self_closing, $content, $matches)) {
                foreach ($matches[1] as $attrs_json) {
                    echo "   [{$block_name}] Self-closing: {$attrs_json}\n";
                }
            }
            
            if (preg_match_all($pattern_enclosing, $content, $matches)) {
                foreach ($matches[1] as $attrs_json) {
                    echo "   [{$block_name}] Enclosing: {$attrs_json}\n";
                }
            }
        }
    }
}
echo "\nAudit complete.\n";
