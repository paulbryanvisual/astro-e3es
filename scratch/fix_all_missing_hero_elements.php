<?php
require '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';

$posts = get_posts(['post_type' => 'clients', 'posts_per_page' => -1]);
$updated_count = 0;

function reconstruct_project_hero_html($b) {
    if (empty($b['attrs']['heroImageUrl'])) {
        return $b['innerHTML'];
    }
    
    $hero_url = $b['attrs']['heroImageUrl'];
    $title = !empty($b['attrs']['title']) ? esc_attr($b['attrs']['title']) : '';
    
    $html = $b['innerHTML'];
    
    // 1. Replace style="--hero-img:none" or similar with style="--hero-img:url(URL)"
    $html = preg_replace('/style=["\']--hero-img:[^"\']*["\']/i', 'style="--hero-img:url(' . $hero_url . ')"', $html);
    
    // 2. Prepend the hero div inside project-section__header if not present
    if (strpos($html, 'project-section__hero') === false) {
        $hero_div = '<div class="project-section__hero"><img src="' . $hero_url . '" alt="' . $title . '" class="project-section__hero-img" style="object-position:50% 50%"/><div class="project-section__mask project-section__mask--left"></div><div class="project-section__mask project-section__mask--right"></div></div>';
        
        // Find the project-section__header tag
        $html = preg_replace(
            '/(<div[^>]*class=["\'][^"\']*project-section__header[^"\']*["\'][^>]*>)/i',
            '$1' . $hero_div,
            $html
        );
    }
    
    return $html;
}

function process_blocks(&$blocks, &$changed) {
    foreach ($blocks as &$b) {
        if ($b['blockName'] === 'e3es/project') {
            if (!empty($b['attrs']['heroImageUrl'])) {
                if (strpos($b['innerHTML'], 'project-section__hero') === false || strpos($b['innerHTML'], '--hero-img:none') !== false) {
                    $new_html = reconstruct_project_hero_html($b);
                    if ($new_html !== $b['innerHTML']) {
                        $b['innerHTML'] = $new_html;
                        $b['innerContent'] = [ $new_html ];
                        $changed = true;
                    }
                }
            }
        }
        if (!empty($b['innerBlocks'])) {
            process_blocks($b['innerBlocks'], $changed);
        }
    }
}

foreach ($posts as $p) {
    $blocks = parse_blocks($p->post_content);
    $changed = false;
    process_blocks($blocks, $changed);
    
    if ($changed) {
        $new_content = serialize_blocks($blocks);
        wp_update_post([
            'ID' => $p->ID,
            'post_content' => $new_content
        ]);
        echo "Successfully repaired project hero HTML layout for: {$p->post_name}\n";
        $updated_count++;
    }
}

echo "Done! Restructured project hero HTML on {$updated_count} client posts.\n";
