<?php
// PHP Script to restructure legacy client posts (ID 13, 14, 15, 16)

$wp_dir = '/Users/bryanpaul/Local Sites/e3es2026/app/public';
require_once($wp_dir . '/wp-load.php');

// 1. Process Bryan ISD (ID 13)
$bryan_id = 13;
$bryan_post = get_post($bryan_id, ARRAY_A);

if ($bryan_post) {
    echo "Processing Bryan ISD (ID 13)...\n";
    $content = $bryan_post['post_content'];
    
    // We want to insert the project block under the relationship paragraph.
    // The relationship paragraph is:
    // <!-- wp:paragraph --><p>Bryan ISD engaged E3 Entegral Solutions...</p><!-- /wp:paragraph -->
    // We insert it before the mini-testimonial:
    // <!-- wp:e3es/mini-testimonial ... /-->
    
    $project_block = '
<!-- wp:e3es/project {"sectionId":"project-details","title":"Energy Efficiency \u0026 Facility Upgrades","heroImageUrl":""} -->
<div class="wp-block-e3es-project project-section" id="project-details" style="--hero-img:none">
    <div class="project-section__header">
        <div class="project-section__info">
            <span class="project-section__eyebrow">Project 1</span>
            <h3 class="project-section__title">Energy Efficiency &amp; Facility Upgrades</h3>
        </div>
    </div>
    <div class="project-section__content">
        <!-- wp:e3es/project-details {"label1":"Market","value1":"K-12","label2":"Project Scope","value2":"LED, HVAC, Indoor Air Quality","label3":"Contract Amount","value3":"$6,421,852","label4":"Annual Savings","value4":"$763,908"} -->
        <div class="wp-block-e3es-project-details project-details">
            <div class="project-details__item"><span class="project-details__label">Market</span><span class="project-details__value">K-12</span></div>
            <div class="project-details__item"><span class="project-details__label">Project Scope</span><span class="project-details__value">LED, HVAC, Indoor Air Quality</span></div>
            <div class="project-details__item"><span class="project-details__label">Contract Amount</span><span class="project-details__value">$6,421,852</span></div>
            <div class="project-details__item"><span class="project-details__label">Annual Savings</span><span class="project-details__value">$763,908</span></div>
        </div>
        <!-- /wp:e3es/project-details -->
        
        <!-- wp:paragraph -->
        <p>Bryan ISD partnered with E3 to implement a comprehensive facility modernization project. The program included installing high-efficiency LED lighting systems, replacing outdated HVAC units, and adding indoor air quality controls across all campuses to enhance the learning environment.</p>
        <!-- /wp:paragraph -->
        
        <!-- wp:paragraph -->
        <p>The project was financed through the Texas SECO LoanSTAR program, which provides low-interest loans for public energy efficiency projects. These upgrades are paid for entirely by the annual operational and utility savings, ensuring a budget-neutral solution for the district.</p>
        <!-- /wp:paragraph -->
    </div>
</div>
<!-- /wp:e3es/project -->
';
    
    // Find the end of the relationship paragraph
    $para_end = '<!-- /wp:paragraph -->';
    $pos = strpos($content, $para_end);
    if ($pos !== false) {
        $insert_pos = $pos + strlen($para_end);
        $new_content = substr_replace($content, $project_block, $insert_pos, 0);
        
        $res = wp_update_post(array(
            'ID'           => $bryan_id,
            'post_content' => $new_content
        ));
        if (is_wp_error($res)) {
            echo "Error updating Bryan ISD: " . $res->get_error_message() . "\n";
        } else {
            echo "Success: Bryan ISD restructured.\n";
        }
    } else {
        echo "Error: Could not find paragraph block end in Bryan ISD post content.\n";
    }
} else {
    echo "Error: Bryan ISD post not found.\n";
}

// 2. Process Caldwell ISD, Carrizo Springs CISD, Donna ISD from clients_dump.json
$dump_path = '/Users/bryanpaul/Local Sites/astro-e3es/clients_dump.json';
if (file_exists($dump_path)) {
    echo "Reading clients_dump.json...\n";
    $dump_data = json_decode(file_get_contents($dump_path), true);
    
    $targets = array(
        14 => array(
            'slug' => 'caldwell-isd',
            'hero_img' => 'http://e3es2026.local/wp-content/uploads/2026/06/Jason Flowers - Caldwell ISD_img_161_0.jpeg'
        ),
        15 => array(
            'slug' => 'carrizo-springs-cisd',
            'hero_img' => 'http://e3es2026.local/wp-content/uploads/2026/06/Jason Flowers - Carrizo Springs CISD.png'
        ),
        16 => array(
            'slug' => 'donna-isd',
            'hero_img' => 'http://e3es2026.local/wp-content/uploads/2026/06/Jason Flowers - Donna ISD for TFC.jpg'
        ),
    );
    
    foreach ($targets as $id => $info) {
        $slug = $info['slug'];
        $hero_img = $info['hero_img'];
        
        echo "Processing $slug (ID $id)...\n";
        
        // Find item in dump data
        $dump_item = null;
        foreach ($dump_data as $item) {
            if ($item['id'] == $id || $item['slug'] == $slug) {
                $dump_item = $item;
                break;
            }
        }
        
        if (!$dump_item) {
            echo "Error: Could not find $slug in clients_dump.json\n";
            continue;
        }
        
        $rendered_html = $dump_item['content']['rendered'];
        
        // Extract the project section block starting at '<div class="wp-block-e3es-project'
        $project_pos = strpos($rendered_html, '<div class="wp-block-e3es-project');
        if ($project_pos === false) {
            echo "Error: Could not find project section in dump for $slug.\n";
            continue;
        }
        
        $project_html = substr($rendered_html, $project_pos);
        // Clean up any trailing space or newlines
        $project_html = trim($project_html);
        
        // Wrap it in Gutenberg block comments
        $wrapped_block = "\n<!-- wp:e3es/project {\"sectionId\":\"project-details\",\"title\":\"Energy Efficiency \\u0026 Facility Upgrades\",\"heroImageUrl\":\"" . $hero_img . "\"} -->\n" . $project_html . "\n<!-- /wp:e3es/project -->\n";
        
        // Get the current local post
        $local_post = get_post($id, ARRAY_A);
        if (!$local_post) {
            echo "Error: Local post $id for $slug not found.\n";
            continue;
        }
        
        $local_content = $local_post['post_content'];
        
        // Find the end of the relationship paragraph
        $para_end = '<!-- /wp:paragraph -->';
        $pos = strpos($local_content, $para_end);
        if ($pos !== false) {
            $insert_pos = $pos + strlen($para_end);
            $new_content = substr_replace($local_content, $wrapped_block, $insert_pos, 0);
            
            $res = wp_update_post(array(
                'ID'           => $id,
                'post_content' => $new_content
            ));
            if (is_wp_error($res)) {
                echo "Error updating $slug: " . $res->get_error_message() . "\n";
            } else {
                echo "Success: $slug (ID $id) restructured.\n";
            }
        } else {
            echo "Error: Could not find paragraph block end in local post $slug.\n";
        }
    }
} else {
    echo "Error: clients_dump.json not found.\n";
}
?>
