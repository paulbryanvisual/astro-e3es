<?php
// PHP Script to generate and prepend a relationship description paragraph for all client pages missing one.

$posts = get_posts(array(
    'post_type' => 'clients',
    'posts_per_page' => -1,
    'post_status' => 'publish'
));

echo "Found " . count($posts) . " clients.\n";

$updated = 0;

foreach ($posts as $post) {
    $content = $post->post_content;
    
    // Check if there is already a relationship description paragraph before the project block
    $intro_banner_end = '<!-- /wp:e3es/intro-banner -->';
    $pos_banner = strpos($content, $intro_banner_end);
    if ($pos_banner !== false) {
        $after_banner = substr($content, $pos_banner + strlen($intro_banner_end));
        $project_start = '<!-- wp:e3es/project';
        $pos_project = strpos($after_banner, $project_start);
        
        $segment_before_project = ($pos_project !== false) ? substr($after_banner, 0, $pos_project) : $after_banner;
        
        if (strpos($segment_before_project, '<!-- wp:paragraph -->') !== false || strpos($segment_before_project, '<p') !== false) {
            // Already has a paragraph before the project block! Skip.
            continue;
        }
    } else {
        // If there's no intro banner end, skip for safety
        continue;
    }
    
    // Parse attributes from e3es/intro-banner to get title, region, and industry
    $title = $post->post_title;
    $region = 'Texas';
    $industry = 'K-12';
    
    if (preg_match('/<!-- wp:e3es\/intro-banner (\{.*?\}) -->/', $content, $matches)) {
        $attrs = json_decode($matches[1], true);
        if ($attrs) {
            if (!empty($attrs['title'])) $title = $attrs['title'];
            if (!empty($attrs['region'])) $region = $attrs['region'];
            if (!empty($attrs['industry'])) $industry = $attrs['industry'];
        }
    }
    
    // Clean up title
    $clean_title = html_entity_decode($title, ENT_QUOTES, 'UTF-8');
    
    // Construct default paragraph using professional variations
    $desc = "";
    if (strpos(strtolower($industry), 'school') !== false || strpos(strtolower($industry), 'k-12') !== false || strpos(strtolower($industry), 'education') !== false) {
        $desc = "{$clean_title} is a valued educational partner in {$region}. The district partnered with E3 to implement comprehensive facility upgrades, modernizing campus infrastructure and enhancing energy efficiency across their schools.";
    } else if (strpos(strtolower($industry), 'healthcare') !== false || strpos(strtolower($industry), 'hospital') !== false || strpos(strtolower($industry), 'medical') !== false) {
        $desc = "{$clean_title} is a valued healthcare partner in {$region}. The facility partnered with E3 to address aging building infrastructure, improve energy efficiency, and enhance the indoor environment for patients and staff.";
    } else {
        $desc = "{$clean_title} is a valued municipal partner in {$region}. The community partnered with E3 to address facility challenges, upgrading building systems and improving operational efficiency across public properties.";
    }
    
    $paragraph_block = "\n<!-- wp:paragraph -->\n<p>{$desc}</p>\n<!-- /wp:paragraph -->\n";
    
    // Insert paragraph after the intro banner
    $insert_pos = $pos_banner + strlen($intro_banner_end);
    $new_content = substr_replace($content, $paragraph_block, $insert_pos, 0);
    
    $res = wp_update_post(array(
        'ID' => $post->ID,
        'post_content' => $new_content
    ));
    
    if (!is_wp_error($res)) {
        echo "Updated client {$post->post_name} (ID: {$post->ID}) with relationship paragraph.\n";
        $updated++;
    } else {
        echo "Error updating client {$post->post_name} (ID: {$post->ID}): " . $res->get_error_message() . "\n";
    }
}

echo "Done. Updated {$updated} client posts.\n";
?>
