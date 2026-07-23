<?php
require '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';

$posts = get_posts(['post_type' => 'clients', 'posts_per_page' => -1]);
$updated_count = 0;

function clean_block_entities_and_align(&$blocks, $post_slug) {
    $changed = false;
    foreach ($blocks as &$b) {
        // 1. Clean attributes
        if (!empty($b['attrs'])) {
            foreach ($b['attrs'] as $key => &$val) {
                if (is_string($val)) {
                    $orig = $val;
                    // Replace literal u0026amp;, u0026, amp; with raw ampersand
                    $val = str_replace(['u0026amp;', 'u0026', 'amp;'], '&', $val);
                    $val = str_replace('u0027', "'", $val);
                    $val = html_entity_decode($val, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    
                    // Run a second pass of html_entity_decode to clean any double-encoded ampersands
                    $val = html_entity_decode($val, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    
                    if ($val !== $orig) {
                        $changed = true;
                    }
                }
            }
        }
        
        // 2. Clean innerHTML
        if (!empty($b['innerHTML'])) {
            $orig_html = $b['innerHTML'];
            
            // Reconstruct e3es/project hero HTML if heroImageUrl is present
            if ($b['blockName'] === 'e3es/project' && !empty($b['attrs']['heroImageUrl'])) {
                $hero_url = $b['attrs']['heroImageUrl'];
                $title = !empty($b['attrs']['title']) ? esc_attr($b['attrs']['title']) : '';
                
                // Align style attribute
                $b['innerHTML'] = preg_replace('/style=["\']--hero-img:[^"\']*["\']/i', 'style="--hero-img:url(' . $hero_url . ')"', $b['innerHTML']);
                
                // Prepend project-section__hero div if not present
                if (strpos($b['innerHTML'], 'project-section__hero') === false) {
                    $hero_div = '<div class="project-section__hero"><img src="' . $hero_url . '" alt="' . $title . '" class="project-section__hero-img" style="object-position:50% 50%"/><div class="project-section__mask project-section__mask--left"></div><div class="project-section__mask project-section__mask--right"></div></div>';
                    $b['innerHTML'] = preg_replace(
                        '/(<div[^>]*class=["\'][^"\']*project-section__header[^"\']*["\'][^>]*>)/i',
                        '$1' . $hero_div,
                        $b['innerHTML']
                    );
                } else {
                    // Update src in existing img tag
                    $b['innerHTML'] = preg_replace('/src=["\'][^"\']*["\']/i', 'src="' . $hero_url . '"', $b['innerHTML']);
                    $b['innerHTML'] = preg_replace('/url\([^)]*\)/i', 'url(' . $hero_url . ')', $b['innerHTML']);
                }
            }
            
            // Reconstruct e3es/intro-banner hero HTML if bgImageUrl is present
            if ($b['blockName'] === 'e3es/intro-banner' && !empty($b['attrs']['bgImageUrl'])) {
                $bg_url = $b['attrs']['bgImageUrl'];
                $b['innerHTML'] = preg_replace('/url\([^)]*\)/i', 'url(' . $bg_url . ')', $b['innerHTML']);
            }
            
            // Replace literal entity codes with correct HTML representation
            $b['innerHTML'] = str_replace(['u0026amp;', 'u0026'], '&amp;', $b['innerHTML']);
            $b['innerHTML'] = str_replace('u0027', "'", $b['innerHTML']);
            
            if ($b['innerHTML'] !== $orig_html) {
                $b['innerContent'] = [ $b['innerHTML'] ];
                $changed = true;
            }
        }
        
        if (!empty($b['innerBlocks'])) {
            if (clean_block_entities_and_align($b['innerBlocks'], $post_slug)) {
                $changed = true;
            }
        }
    }
    return $changed;
}

foreach ($posts as $p) {
    $blocks = parse_blocks($p->post_content);
    $changed = clean_block_entities_and_align($blocks, $p->post_name);
    
    if ($changed) {
        $new_content = serialize_blocks($blocks);
        
        // Clean remaining literal entity text in serialized strings
        $new_content = str_replace('u0026amp;', '&amp;', $new_content);
        $new_content = str_replace('u0026', '&amp;', $new_content);
        
        // UPDATE DB USING WP_SLASH TO PRESERVE BACKSLASHES
        wp_update_post(wp_slash([
            'ID' => $p->ID,
            'post_content' => $new_content
        ]));
        
        echo "Cleaned & aligned blocks for post: {$p->post_name}\n";
        $updated_count++;
    }
}

echo "Done! Repaired and slashed $updated_count client posts.\n";
