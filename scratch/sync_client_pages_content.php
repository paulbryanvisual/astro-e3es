<?php
require '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';

$json_file = '/Users/bryanpaul/Local Sites/astro-e3es/scratch/matched_flickr_folders.json';
$matched_flickr = [];
if (file_exists($json_file)) {
    $matched_flickr = json_decode(file_get_contents($json_file), true);
}

$flickr_base = '/Users/bryanpaul/Dropbox/PaulDropbox/E3/flickr_downloads/';

function get_or_upload_attachment($file_path, $post_id) {
    if (!file_exists($file_path)) {
        return null;
    }
    
    global $wpdb;
    $filename = basename($file_path);
    
    $attachment_id = $wpdb->get_var($wpdb->prepare(
        "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s",
        '%' . $filename
    ));
    
    if ($attachment_id) {
        $post = get_post($attachment_id);
        if ($post && $post->post_parent != $post_id) {
            wp_update_post([
                'ID' => $attachment_id,
                'post_parent' => $post_id
            ]);
        }
        return $attachment_id;
    }
    
    $uploads = wp_upload_dir();
    $new_file = $uploads['path'] . '/' . $filename;
    
    if (!file_exists($new_file)) {
        copy($file_path, $new_file);
    }
    
    $attachment = [
        'guid'           => $uploads['url'] . '/' . $filename, 
        'post_mime_type' => wp_check_filetype($new_file)['type'],
        'post_title'     => preg_replace( '/\.[^.]+$/', '', $filename ),
        'post_content'   => '',
        'post_status'    => 'inherit'
    ];
    
    $attach_id = wp_insert_attachment($attachment, $new_file, $post_id);
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $attach_data = wp_generate_attachment_metadata($attach_id, $new_file);
    wp_update_attachment_metadata($attach_id, $attach_data);
    
    return $attach_id;
}

// Loop through all clients
$clients = get_posts([
    'post_type' => 'clients',
    'posts_per_page' => -1,
    'post_status' => 'any'
]);

kses_remove_filters();

echo "Starting synchronization for " . count($clients) . " clients...\n";

foreach ($clients as $c) {
    $slug = $c->post_name;
    if (in_array($slug, ['boyd-isd', 'bishop-cisd', 'granbury-isd', 'keene-isd', 'little-elm-isd', 'plano-isd', 'city-of-stockdale'])) {
        echo "[SKIP] Manually seeded client: $slug\n";
        continue;
    }

    // Only update if cache file exists
    $cache_file = "/Users/bryanpaul/Local Sites/astro-e3es/scratch/live_project_pages_cache/{$slug}.html";
    if (!file_exists($cache_file)) {
        echo "[SKIP] No live cache for: $slug\n";
        continue;
    }
    
    $html = file_get_contents($cache_file);
    
    // 1. Sideload Flickr images first if mapped
    if (isset($matched_flickr[$slug]) && !empty($matched_flickr[$slug]['folders'])) {
        foreach ($matched_flickr[$slug]['folders'] as $folder) {
            $folder_path = $flickr_base . $folder;
            if (is_dir($folder_path)) {
                $files = scandir($folder_path);
                foreach ($files as $file) {
                    if (in_array($file, ['.', '..', '.DS_Store'])) continue;
                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                        get_or_upload_attachment($folder_path . '/' . $file, $c->ID);
                    }
                }
            }
        }
    }
    
    // 2. Extract sidebar details
    $market = '';
    $scope = '';
    $contract_amount = '';
    $annual_savings = '';
    
    if (preg_match('/<strong>MARKET<\/strong>\s*:\s*([^<]+)/i', $html, $m)) {
        $market = trim($m[1]);
    }
    if (preg_match('/<strong>PROJECT SCOPE<\/strong>\s*:\s*([^<]+)/i', $html, $m)) {
        $scope = trim($m[1]);
    }
    if (preg_match('/<strong>CONTRACT AMOUNT<\/strong>\s*:\s*([^<]+)/i', $html, $m)) {
        $contract_amount = trim($m[1]);
    }
    if (preg_match('/<strong>ANNUAL SAVINGS<\/strong>\s*:\s*([^<]+)/i', $html, $m)) {
        $annual_savings = trim($m[1]);
    }
    
    // 3. Extract Vimeo video ID
    $vimeo_id = '';
    if (preg_match('/src="[^"]*player\.vimeo\.com\/video\/([0-9]+)[^"]*"/i', $html, $m)) {
        $vimeo_id = $m[1];
    }
    
    // 4. Extract description paragraphs
    preg_match_all('/<p[^>]*>([\s\S]*?)<\/p>/i', $html, $matches);
    $paragraphs = [];
    foreach ($matches[1] as $p) {
        $pText = trim(strip_tags($p));
        $pText = html_entity_decode($pText, ENT_QUOTES, 'UTF-8');
        $pText = preg_replace('/\s+/', ' ', $pText);
        
        if (strlen($pText) < 50) continue;
        if (strpos($pText, 'Join Our Team') !== false ||
            strpos($pText, 'prague-architects') !== false ||
            strpos($pText, 'ALL RIGHTS RESERVED') !== false ||
            strpos($pText, 'Tel:') !== false ||
            strpos($pText, '+7 (885)') !== false ||
            strpos($pText, 'Jungmannova') !== false ||
            strpos($pText, 'Czech Republic') !== false ||
            strpos($pText, 'PROJECT SCOPE') !== false ||
            strpos($pText, 'CONTRACT AMOUNT') !== false ||
            strpos($pText, 'ANNUAL SAVINGS') !== false ||
            strpos($pText, 'Office Locations') !== false ||
            $pText === 'K-12' ||
            $pText === 'Municipal Water Quality' ||
            $pText === 'Higher Education' ||
            $pText === 'Healthcare') {
            continue;
        }
        $paragraphs[] = $pText;
    }
    
    // 5. Extract deliverables
    preg_match_all('/<ul([^>]*?)>([\s\S]*?)<\/ul>/i', $html, $ulMatches);
    $deliverables = [];
    foreach ($ulMatches[0] as $ulFull) {
        if (preg_match('/(class|id)=["\'][^"\']*(menu|nav|widget|social|pixfield|meta)[^"\']*/i', $ulFull)) {
            continue;
        }
        preg_match_all('/<li[^>]*>([\s\S]*?)<\/li>/i', $ulFull, $liMatches);
        foreach ($liMatches[1] as $li) {
            $liText = trim(strip_tags($li));
            $liText = html_entity_decode($liText, ENT_QUOTES, 'UTF-8');
            $liText = preg_replace('/\s+/', ' ', $liText);
            if (strlen($liText) > 5) {
                $deliverables[] = $liText;
            }
        }
    }
    
    // 6. Get post attachments for hero and gallery
    $attachments = get_posts([
        'post_type'      => 'attachment',
        'posts_per_page' => -1,
        'post_parent'    => $c->ID,
        'post_mime_type' => 'image',
        'orderby'        => 'title',
        'order'          => 'ASC'
    ]);
    
    $gallery_images = [];
    $hero_url = '';
    $featured_id = get_post_thumbnail_id($c->ID);
    
    foreach ($attachments as $att) {
        $url = wp_get_attachment_url($att->ID);
        $lower_url = strtolower($url);
        
        // Skip logos in project block and gallery
        if (strpos($lower_url, 'logo') !== false || 
            strpos($lower_url, 'client_logo') !== false || 
            strpos($lower_url, '150x150') !== false ||
            strpos($lower_url, '300x115') !== false) {
            continue;
        }
        
        $alt = get_post_meta($att->ID, '_wp_attachment_image_alt', true);
        if (!$alt) {
            $alt = $att->post_title;
        }
        
        $gallery_images[] = [
            'url' => $url,
            'alt' => $alt
        ];
        
        if ($att->ID == $featured_id) {
            $hero_url = $url;
        }
    }
    
    // Set default hero image if none set
    if (empty($hero_url) && !empty($gallery_images)) {
        $hero_url = $gallery_images[0]['url'];
        // Also set as featured image in DB
        foreach ($attachments as $att) {
            if (wp_get_attachment_url($att->ID) == $hero_url) {
                set_post_thumbnail($c->ID, $att->ID);
                break;
            }
        }
    }
    
    // 7. Compile post content using Gutenberg blocks
    $content = '';
    
    if (!empty($vimeo_id)) {
        $vimeo_url = "https://vimeo.com/$vimeo_id";
        
        $v_title = esc_html($c->post_title) . " Case Study Video";
        $v_intro = "Watch the video overview of E3's project work and facility improvements at " . esc_html($c->post_title) . ".";
        
        $video_copy = [
            'bryan-isd' => [
                'title' => 'Bryan ISD Case Study Video',
                'intro' => "This video documents E3's partnership with Bryan ISD, highlighting over $6M in facility retrofits, HVAC enhancements, and LED lighting upgrades funded through the SECO LoanSTAR program."
            ],
            'caldwell-isd' => [
                'title' => 'Caldwell ISD Case Study Video',
                'intro' => "Watch a detailed showcase of Caldwell ISD's facility upgrades, featuring the complete replacement of high school HVAC and lighting systems implemented in partnership with TASB and E3."
            ],
            'carrizo-springs-cisd' => [
                'title' => 'Carrizo Springs CISD Case Study Video',
                'intro' => 'A visual walkthrough of the comprehensive mechanical, indoor air quality, and roofing restoration improvements executed across Carrizo Springs CISD campuses.'
            ],
            'edcouch-elsa-isd' => [
                'title' => 'Edcouch-Elsa ISD Case Study Video',
                'intro' => "Explore Edcouch-Elsa ISD's district-wide facility modernization program, highlighting HVAC lifecycle replacements and new energy management system controls."
            ],
            'ferris-isd' => [
                'title' => 'Ferris ISD Case Study Video',
                'intro' => 'Watch the video case study detailing the mechanical retrofits, energy conservation measures, and utility cost-saving upgrades implemented at Ferris ISD.'
            ],
            'glen-rose-medical-center' => [
                'title' => 'Glen Rose Medical Center Case Study Video',
                'intro' => 'This video case study features the critical infrastructure upgrades, HVAC plant replacements, and facility optimization works performed at Glen Rose Medical Center.'
            ],
            'goodall-witcher-hospital' => [
                'title' => 'Goodall-Witcher Healthcare Case Study Video',
                'intro' => "A visual case study documenting the hospital facility renovations, HVAC plant upgrades, and lighting modernizations that optimized Goodall-Witcher's clinical environment."
            ],
            'lake-worth-isd' => [
                'title' => 'Lake Worth ISD Case Study Video',
                'intro' => "This video tours Lake Worth ISD's campuses to review the mechanical replacements, indoor air quality improvements, and campus-wide LED lighting retrofits."
            ],
            'port-neches-groves-isd' => [
                'title' => 'Port Neches-Groves ISD Case Study Video',
                'intro' => 'An overview of the district-wide energy conservation program, mechanical systems upgrades, and building envelope sealing at Port Neches-Groves ISD.'
            ],
            'prosper-isd' => [
                'title' => 'Prosper ISD Case Study Video',
                'intro' => "Watch the highlights of Prosper ISD's high school athletic facilities LED sports lighting installation and auxiliary upgrades."
            ],
            'royal-isd' => [
                'title' => 'Royal ISD Case Study Video',
                'intro' => 'Explore the district-wide energy efficiency and HVAC upgrades that resolved widespread comfort complaints and modernized facility management at Royal ISD.'
            ]
        ];
        
        if (isset($video_copy[$slug])) {
            $v_title = $video_copy[$slug]['title'];
            $v_intro = $video_copy[$slug]['intro'];
        }
        
        $embed_attrs = json_encode([
            'title' => $v_title,
            'videoUrl' => $vimeo_url,
            'intro' => $v_intro
        ], JSON_UNESCAPED_SLASHES);
        
        $iframe_src = "https://player.vimeo.com/video/$vimeo_id?badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479";
        
        $content .= "<!-- wp:e3es/video-embed $embed_attrs -->\n";
        $content .= "<div class=\"wp-block-e3es-video-embed video-embed\">";
        $content .= "<h3 class=\"video-embed__title\">" . esc_html($v_title) . "</h3>";
        $content .= "<p class=\"video-embed__intro\">" . esc_html($v_intro) . "</p>";
        $content .= "<div class=\"video-embed__wrapper\"><iframe src=\"" . esc_url($iframe_src) . "\" frameborder=\"0\" allow=\"autoplay; fullscreen; picture-in-picture; clipboard-write\" style=\"position:absolute;top:0;left:0;width:100%;height:100%;\" title=\"" . esc_attr($v_title) . "\"></iframe></div></div>\n";
        $content .= "<!-- /wp:e3es/video-embed -->\n\n";
    }
    
    // Add first paragraph above the project block
    $rel_p = '';
    $has_relationship = false;
    foreach ($paragraphs as $idx => $p) {
        $lower = strtolower($p);
        if (strpos($lower, 'partner') !== false || strpos($lower, 'collaborat') !== false || strpos($lower, 'cooperat') !== false) {
            $rel_p = $p;
            unset($paragraphs[$idx]);
            $paragraphs = array_values($paragraphs);
            $has_relationship = true;
            break;
        }
    }
    
    // Fallback if no relationship paragraph was found
    if (!$has_relationship) {
        if (count($paragraphs) > 0) {
            // Use the first paragraph as relationship paragraph if it contains the client name
            $first_p = $paragraphs[0];
            $name_words = explode(' ', strtolower($c->post_title));
            $client_keyword = $name_words[0];
            if (strpos(strtolower($first_p), $client_keyword) !== false) {
                $rel_p = array_shift($paragraphs);
            }
        }
    }
    
    if (empty($rel_p)) {
        // Prepend a default professional relationship paragraph
        $rel_p = esc_html($c->post_title) . " partnered with E3 Entegral Solutions to implement a comprehensive series of energy efficiency improvements and facility upgrades.";
    }
    
    $content .= "<!-- wp:paragraph -->\n<p>" . esc_html($rel_p) . "</p>\n<!-- /wp:paragraph -->\n\n";
    
    // Build project block
    $project_attrs = json_encode([
        'sectionId' => 'project-1',
        'eyebrow' => 'Project 1',
        'title' => esc_html($c->post_title),
        'heroImageUrl' => esc_url($hero_url),
        'focalPointX' => 0.5,
        'focalPointY' => 0.5,
        'className' => 'is-style-green-texture-behind'
    ], JSON_UNESCAPED_SLASHES);
    
    $content .= "<!-- wp:e3es/project $project_attrs -->\n";
    $content .= "<div class=\"wp-block-e3es-project project-section is-style-green-texture-behind\" id=\"project-1\" style=\"--hero-img:url(" . esc_url($hero_url) . ")\">";
    $content .= "<div class=\"project-section__header\">";
    if ($hero_url) {
        $content .= "<div class=\"project-section__hero\"><img src=\"" . esc_url($hero_url) . "\" alt=\"" . esc_attr($c->post_title) . "\" class=\"project-section__hero-img\" style=\"object-position:50% 50%\"/><div class=\"project-section__mask project-section__mask--left\"></div><div class=\"project-section__mask project-section__mask--right\"></div></div>";
    }
    $content .= "<div class=\"project-section__info\"><span class=\"project-section__eyebrow\">Project 1</span><h2 class=\"project-section__title\">" . esc_html($c->post_title) . "</h2></div></div>";
    $content .= "<div class=\"project-section__content\">\n\n";
    
    // Details grid
    $details_attrs = json_encode([
        'label1' => 'Project Scope',
        'value1' => $scope,
        'label2' => 'Contract Amount',
        'value2' => $contract_amount,
        'label3' => 'Annual Savings',
        'value3' => $annual_savings,
        'label4' => 'Market',
        'value4' => $market
    ], JSON_UNESCAPED_SLASHES);
    
    $content .= "<!-- wp:e3es/project-details $details_attrs -->\n";
    $content .= "<div class=\"wp-block-e3es-project-details project-details\">";
    if ($scope) $content .= "<div class=\"project-details__item\"><span class=\"project-details__label\">Project Scope</span><span class=\"project-details__value\">" . esc_html($scope) . "</span></div>";
    if ($contract_amount) $content .= "<div class=\"project-details__item\"><span class=\"project-details__label\">Contract Amount</span><span class=\"project-details__value\">" . esc_html($contract_amount) . "</span></div>";
    if ($annual_savings) $content .= "<div class=\"project-details__item\"><span class=\"project-details__label\">Annual Savings</span><span class=\"project-details__value\">" . esc_html($annual_savings) . "</span></div>";
    if ($market) $content .= "<div class=\"project-details__item\"><span class=\"project-details__label\">Market</span><span class=\"project-details__value\">" . esc_html($market) . "</span></div>";
    $content .= "</div>\n<!-- /wp:e3es/project-details -->\n\n";
    
    // Remaining paragraphs
    foreach ($paragraphs as $p) {
        $content .= "<!-- wp:paragraph -->\n<p>" . esc_html($p) . "</p>\n<!-- /wp:paragraph -->\n\n";
    }
    
    // Deliverables
    if (count($deliverables) > 0) {
        $content .= "<!-- wp:heading {\"level\":3} -->\n<h3 class=\"wp-block-heading\">Key Project Deliverables</h3>\n<!-- /wp:heading -->\n\n";
        $content .= "<!-- wp:list -->\n<ul>\n";
        foreach ($deliverables as $d) {
            $content .= "<!-- wp:list-item -->\n<li>" . esc_html($d) . "</li>\n<!-- /wp:list-item -->\n";
        }
        $content .= "</ul>\n<!-- /wp:list -->\n\n";
    }
    
    // Gallery
    if (count($gallery_images) > 0) {
        $content .= "<!-- wp:heading {\"level\":3} -->\n<h3 class=\"wp-block-heading\">Project Gallery</h3>\n<!-- /wp:heading -->\n\n";
        $content .= "<!-- wp:gallery {\"columns\":4,\"linkTo\":\"none\"} -->\n";
        $content .= "<figure class=\"wp-block-gallery has-nested-images columns-4 is-cropped\">";
        foreach ($gallery_images as $g) {
            $content .= "<!-- wp:image -->\n<figure class=\"wp-block-image\"><img src=\"" . esc_url($g['url']) . "\" alt=\"" . esc_attr($g['alt']) . "\"/></figure>\n<!-- /wp:image -->\n";
        }
        $content .= "</figure>\n<!-- /wp:gallery -->\n\n";
    }
    
    $content .= "</div></div>\n<!-- /wp:e3es/project -->\n";
    
    $result = wp_update_post([
        'ID' => $c->ID,
        'post_content' => wp_slash($content)
    ], true);
    
    if (is_wp_error($result)) {
        echo "[ERROR] Failed to update: $slug - " . $result->get_error_message() . "\n";
    } else {
        echo "[OK] Restored content for: $slug\n";
    }
}

kses_init_filters();
echo "Restoration script execution complete.\n";
