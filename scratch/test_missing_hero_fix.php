<?php
require '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';

$post = get_page_by_path('anderson-shiro-cisd', OBJECT, 'clients');
if (!$post) {
    die("Post not found\n");
}

$blocks = parse_blocks($post->post_content);

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

function process_blocks(&$blocks) {
    foreach ($blocks as &$b) {
        if ($b['blockName'] === 'e3es/project') {
            if (!empty($b['attrs']['heroImageUrl'])) {
                $new_html = reconstruct_project_hero_html($b);
                $b['innerHTML'] = $new_html;
                $b['innerContent'] = [ $new_html ];
            }
        }
        if (!empty($b['innerBlocks'])) {
            process_blocks($b['innerBlocks']);
        }
    }
}

process_blocks($blocks);
$new_content = serialize_blocks($blocks);
echo "--- BEFORE ---\n";
echo $post->post_content . "\n";
echo "--- AFTER ---\n";
echo $new_content . "\n";
