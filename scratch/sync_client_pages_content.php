<?php
require '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';

$json_file = '/Users/bryanpaul/Local Sites/astro-e3es/scratch/matched_flickr_folders.json';
$matched_flickr = [];
if (file_exists($json_file)) {
    $matched_flickr = json_decode(file_get_contents($json_file), true);
}

$flickr_base = '/Users/bryanpaul/Dropbox/PaulDropbox/E3/flickr_downloads/';

function e3_get_high_res_hero_image_from_html($html, $slug) {
    if (preg_match('/<img[^>]*class="[^"]*s-img-switch[^"]*"[^>]*src="(https:\/\/www\.e3es\.com\/wp-content\/uploads\/[^"]+)"/i', $html, $m)) {
        return $m[1];
    }
    
    preg_match_all('/src="(https:\/\/www\.e3es\.com\/wp-content\/uploads\/[^\s"\'>]+)"/i', $html, $matches);
    foreach ($matches[1] as $url) {
        $lower_url = strtolower($url);
        if (strpos($lower_url, 'logo') !== false ||
            strpos($lower_url, 'icon') !== false ||
            strpos($lower_url, 'footer') !== false ||
            strpos($lower_url, 'map') !== false ||
            preg_match('/-\d+x\d+\.(jpg|jpeg|png)$/i', $url)) {
            continue;
        }
        return $url;
    }
    
    foreach ($matches[1] as $url) {
        $lower_url = strtolower($url);
        if (strpos($lower_url, 'logo') !== false ||
            strpos($lower_url, 'icon') !== false ||
            strpos($lower_url, 'footer') !== false ||
            strpos($lower_url, 'map') !== false) {
            continue;
        }
        $clean_url = preg_replace('/-\d+x\d+(-\d+)?\.(jpg|jpeg|png)$/i', '.$2', $url);
        return $clean_url;
    }
    
    return '';
}

function e3_sideload_image($url, $post_id) {
    if (empty($url)) return null;
    
    global $wpdb;
    $filename = basename($url);
    $filename = sanitize_file_name($filename);
    
    $attachment_id = $wpdb->get_var($wpdb->prepare(
        "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s",
        '%' . $filename
    ));
    
    if ($attachment_id) {
        return wp_get_attachment_url($attachment_id);
    }
    
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    
    $tmp_file = download_url($url);
    if (is_wp_error($tmp_file)) {
        return null;
    }
    
    $file_array = [
        'name'     => $filename,
        'tmp_name' => $tmp_file,
    ];
    
    $id = media_handle_sideload($file_array, $post_id, "High resolution image from live site");
    if (is_wp_error($id)) {
        @unlink($tmp_file);
        return null;
    }
    
    return wp_get_attachment_url($id);
}

function e3_get_client_logo_url($post_id) {
    global $wpdb;
    $logo_id = $wpdb->get_var($wpdb->prepare(
        "SELECT ID FROM $wpdb->posts WHERE post_type = 'attachment' AND post_parent = %d AND (post_title LIKE '%logo%' OR post_name LIKE '%logo%') LIMIT 1",
        $post_id
    ));
    if ($logo_id) {
        return wp_get_attachment_url($logo_id);
    }
    return '';
}

function e3_generate_intro_banner_block($title, $bg_image_url, $client_logo_url = '', $region = 'Central Texas', $industry = 'K-12 Schools') {
    $attrs = [
        'title' => $title,
        'bgImageUrl' => $bg_image_url,
        'bgOpacity' => 0.85,
        'bgOverlayColor' => 'green',
        'bgFadeType' => 'flat',
        'textShadow' => 'subtle',
        'textAlignment' => 'center',
        'textCase' => 'uppercase',
        'textSkew' => false,
        'focalPointX' => 0.5,
        'focalPointY' => 0.5,
        'clientLogoUrl' => $client_logo_url,
        'logoHasCircle' => true,
        'region' => $region,
        'industry' => $industry,
        'subtitle' => ''
    ];
    
    $attrs_json = json_encode($attrs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    
    $style = "";
    if ($bg_image_url) {
        $style = "background-image:linear-gradient(rgba(33, 87, 52,0.85), rgba(33, 87, 52,0.85)), url(" . esc_url($bg_image_url) . ");background-size:cover;background-position:50% 50%;background-repeat:no-repeat";
    }
    
    $logo_html = '';
    if (!empty($client_logo_url)) {
        $logo_html = '<div class="db-page-hero__logo-wrapper db-page-hero__logo-wrapper--circle"><img src="' . esc_url($client_logo_url) . '" alt="Client Logo" class="db-page-hero__logo-img"/></div>';
    }
    
    $intro_html = '';
    $intro_text_parts = [];
    if ($industry) $intro_text_parts[] = esc_html($industry);
    if ($region) $intro_text_parts[] = esc_html($region);
    if (!empty($intro_text_parts)) {
        $intro_text = implode(' | ', $intro_text_parts);
        $intro_html = '<div class="db-page-hero__intro"><p>' . $intro_text . '</p></div>';
    }
    
    $html = "<!-- wp:e3es/intro-banner " . $attrs_json . " -->\n" .
            "<section class=\"wp-block-e3es-intro-banner db-page-hero\" style=\"" . $style . "\"><div class=\"db-page-hero__container\">" . $logo_html . "<div><h1 class=\"db-page-hero__title\" style=\"margin-bottom:0;text-align:center;text-transform:uppercase;text-shadow:0 2px 4px rgba(0,0,0,0.3)\">" . $title . "</h1>" . $intro_html . "</div></div></section>\n" .
            "<!-- /wp:e3es/intro-banner -->\n\n";
            
    return $html;
}

function e3_generate_video_embed_block($title, $video_url, $intro = '') {
    if (empty($intro)) {
        $intro = 'This video highlights the energy efficiency improvements and facility upgrades implemented across the district. Watch the case study to see the impact of single-source accountability.';
    }
    $attrs = [
        'title' => $title,
        'videoUrl' => $video_url,
        'intro' => $intro
    ];
    $attrs_json = json_encode($attrs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    
    $html = "<!-- wp:e3es/video-embed " . $attrs_json . " -->\n" .
            "<section class=\"wp-block-e3es-video-embed db-video-section\"><h3 class=\"db-video-section__title\">" . $title . "</h3><p class=\"db-video-section__intro\">" . $intro . "</p><div class=\"db-video-wrapper\"><iframe src=\"" . esc_url($video_url) . "\" frameborder=\"0\" allow=\"autoplay; fullscreen; picture-in-picture\" allowfullscreen title=\"" . esc_attr($title) . "\"></iframe></div></section>\n" .
            "<!-- /wp:e3es/video-embed -->\n\n";
            
    return $html;
}

function e3_parse_content_html_sequentially($html, $client_title) {
    if (preg_match('/<div class="wpb_text_column wpb_content_element[^"]*"[^>]*>\s*<div class="wpb_wrapper">\s*([\s\S]*?)\s*<\/div>\s*<\/div>/i', $html, $m)) {
        $content_html = trim($m[1]);
    } else {
        return ['rel_p' => '', 'project_content' => ''];
    }
    
    if (empty($content_html)) {
        return ['rel_p' => '', 'project_content' => ''];
    }
    
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="utf-8" ?><div>' . $content_html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    
    $root = $dom->documentElement;
    $blocks = [];
    $first_heading = true;
    
    foreach ($root->childNodes as $node) {
        if ($node->nodeType !== XML_ELEMENT_NODE) {
            continue;
        }
        
        $tag = strtolower($node->nodeName);
        
        if ($tag === 'p') {
            $inner_html = '';
            foreach ($node->childNodes as $child) {
                $inner_html .= $dom->saveHTML($child);
            }
            $inner_html = trim($inner_html);
            
            if (preg_match('/^<strong[^>]*>([\s\S]*?)<\/strong>$/i', $inner_html, $strong_match)) {
                $heading_text = strip_tags($strong_match[1]);
                $heading_text = html_entity_decode($heading_text, ENT_QUOTES, 'UTF-8');
                $heading_text = preg_replace('/\s+/', ' ', trim($heading_text));
                
                if (!empty($heading_text)) {
                    $blocks[] = [
                        'type' => 'heading',
                        'html' => "<!-- wp:heading {\"level\":3} -->\n<h3 class=\"wp-block-heading\">" . esc_html($heading_text) . "</h3>\n<!-- /wp:heading -->\n\n"
                    ];
                }
            } else {
                $p_text = strip_tags($inner_html);
                $p_text = html_entity_decode($p_text, ENT_QUOTES, 'UTF-8');
                $p_text = preg_replace('/\s+/', ' ', trim($p_text));
                
                if (!empty($p_text)) {
                    $blocks[] = [
                        'type' => 'paragraph',
                        'text' => $p_text,
                        'html' => "<!-- wp:paragraph -->\n<p>" . esc_html($p_text) . "</p>\n<!-- /wp:paragraph -->\n\n"
                    ];
                }
            }
        } elseif ($tag === 'ul') {
            $list_html = "<!-- wp:list -->\n<ul>\n";
            $has_items = false;
            foreach ($node->childNodes as $li) {
                if ($li->nodeType === XML_ELEMENT_NODE && strtolower($li->nodeName) === 'li') {
                    $li_text = strip_tags($dom->saveHTML($li));
                    $li_text = html_entity_decode($li_text, ENT_QUOTES, 'UTF-8');
                    $li_text = preg_replace('/\s+/', ' ', trim($li_text));
                    if (!empty($li_text)) {
                        $list_html .= "<!-- wp:list-item -->\n<li>" . esc_html($li_text) . "</li>\n<!-- /wp:list-item -->\n";
                        $has_items = true;
                    }
                }
            }
            $list_html .= "</ul>\n<!-- /wp:list -->\n\n";
            if ($has_items) {
                $blocks[] = [
                    'type' => 'list',
                    'html' => $list_html
                ];
            }
        } elseif (in_array($tag, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'])) {
            $heading_text = strip_tags($dom->saveHTML($node));
            $heading_text = html_entity_decode($heading_text, ENT_QUOTES, 'UTF-8');
            $heading_text = preg_replace('/\s+/', ' ', trim($heading_text));
            
            if ($tag === 'h2' && $first_heading) {
                $first_heading = false;
                continue;
            }
            
            $level = (int)substr($tag, 1);
            $blocks[] = [
                'type' => 'heading',
                'html' => "<!-- wp:heading {\"level\":$level} -->\n<$tag class=\"wp-block-heading\">" . esc_html($heading_text) . "</$tag>\n<!-- /wp:heading -->\n\n"
            ];
        }
    }
    
    $rel_p = '';
    $project_blocks = [];
    $extracted_rel = false;
    
    foreach ($blocks as $b) {
        if (!$extracted_rel && $b['type'] === 'paragraph') {
            $rel_p = $b['text'];
            $extracted_rel = true;
        } else {
            $project_blocks[] = $b['html'];
        }
    }
    
    return [
        'rel_p' => $rel_p,
        'project_content' => implode('', $project_blocks)
    ];
}

function e3_split_gallery_images($gallery_images) {
    $categories = [
        'construction' => [
            'title' => 'Installation & Construction Progress',
            'desc' => 'A look at the mechanical modernization and installation process, showcasing the equipment upgrades and on-site construction work in progress across the facilities.',
            'images' => []
        ],
        'completed' => [
            'title' => 'Completed Improvements & Retrofits',
            'desc' => 'The finalized upgrades, featuring high-efficiency systems and optimized building environments designed to deliver maximum energy performance and operational savings.',
            'images' => []
        ],
        'general' => [
            'title' => 'Additional Project Details',
            'desc' => 'Visual details and close-up views showing the technical scope and facility components addressed during the project execution.',
            'images' => []
        ]
    ];
    
    foreach ($gallery_images as $img) {
        $path = strtolower($img['url']);
        $alt = strtolower($img['alt']);
        
        $is_construction = (
            strpos($path, 'before') !== false ||
            strpos($path, 'construction') !== false ||
            strpos($path, 'failed') !== false ||
            strpos($path, 'lift') !== false ||
            strpos($path, 'crane') !== false ||
            strpos($path, 'work') !== false ||
            strpos($alt, 'before') !== false ||
            strpos($alt, 'construction') !== false ||
            strpos($alt, 'lift') !== false ||
            strpos($alt, 'crane') !== false ||
            strpos($alt, 'work') !== false
        );
        
        $is_completed = (
            strpos($path, 'after') !== false ||
            strpos($path, 'complete') !== false ||
            strpos($path, 'new') !== false ||
            strpos($path, 'retrofitted') !== false ||
            strpos($alt, 'after') !== false ||
            strpos($alt, 'complete') !== false ||
            strpos($alt, 'new') !== false ||
            strpos($alt, 'retrofitted') !== false
        );
        
        if ($is_construction) {
            $categories['construction']['images'][] = $img;
        } elseif ($is_completed) {
            $categories['completed']['images'][] = $img;
        } else {
            $categories['general']['images'][] = $img;
        }
    }
    
    $result = [];
    foreach ($categories as $key => $cat) {
        if (!empty($cat['images'])) {
            $result[] = $cat;
        }
    }
    
    if (count($result) <= 1 || count($gallery_images) < 4) {
        return [
            [
                'title' => 'Project Showcase Gallery',
                'desc' => 'Visual documentation of the facility upgrades, mechanical systems modernization, and energy conservation improvements executed during the project.',
                'images' => $gallery_images
            ]
        ];
    }
    
    return $result;
}

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

$protected_file = '/Users/bryanpaul/Local Sites/astro-e3es/scratch/protected_slugs.json';
$protected_slugs = [];
if (file_exists($protected_file)) {
    $protected_slugs = json_decode(file_get_contents($protected_file), true);
}

foreach ($clients as $c) {
    $slug = $c->post_name;
    
    $cache_file = "/Users/bryanpaul/Local Sites/astro-e3es/scratch/live_project_pages_cache/{$slug}.html";
    if (!file_exists($cache_file)) {
        echo "[SKIP] No live cache for: $slug\n";
        continue;
    }
    $html = file_get_contents($cache_file);

    // Resolve industry taxonomy
    $industry = 'K-12 Schools';
    if (strpos($slug, 'hospital') !== false || strpos($slug, 'medical') !== false) {
        $industry = 'Healthcare';
    } elseif (strpos($slug, 'city') !== false || strpos($slug, 'county') !== false || strpos($slug, 'commission') !== false) {
        $industry = 'Municipalities';
    } elseif (strpos($slug, 'college') !== false || strpos($slug, 'university') !== false) {
        $industry = 'Higher Education';
    }

    // Resolve region
    $region = 'Central Texas';
    if (preg_match('/<strong>MARKET<\/strong>\s*:\s*([^<]+)/i', $html, $m)) {
        $market_val = strtolower(trim($m[1]));
        if (strpos($market_val, 'south') !== false) {
            $region = 'South Texas';
        } elseif (strpos($market_val, 'north') !== false) {
            $region = 'North Texas';
        } elseif (strpos($market_val, 'west') !== false) {
            $region = 'West Texas';
        } elseif (strpos($market_val, 'east') !== false) {
            $region = 'East Texas';
        }
    }

    // Safe Banner Prepending for protected manual client pages
    if (in_array($slug, $protected_slugs)) {
        if (strpos($c->post_content, 'wp:e3es/intro-banner') === false) {
            $hero_id = get_post_thumbnail_id($c->ID);
            $hero_url = $hero_id ? wp_get_attachment_url($hero_id) : '';
            $logo_url = e3_get_client_logo_url($c->ID);
            
            if (function_exists('e3es_make_intro_banner_markup')) {
                $banner_block = e3es_make_intro_banner_markup([
                    'title' => $c->post_title,
                    'bgImageUrl' => $hero_url,
                    'clientLogoUrl' => $logo_url,
                    'region' => $region,
                    'industry' => $industry
                ]) . "\n\n";
            } else {
                $banner_block = e3_generate_intro_banner_block($c->post_title, $hero_url, $logo_url, $region, $industry);
            }
            $new_content = $banner_block . $c->post_content;
            
            wp_update_post([
                'ID' => $c->ID,
                'post_content' => wp_slash($new_content)
            ]);
            echo "[OK] Prepended missing intro-banner block to protected client page: $slug\n";
        } else {
            echo "[SKIP] Protected manually edited client (already has intro-banner): $slug\n";
        }
        continue;
    }

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
    
    // 4. Resolve high-resolution hero image from the live site
    $hero_url = '';
    $live_hero = e3_get_high_res_hero_image_from_html($html, $slug);
    if (!empty($live_hero)) {
        $sideloaded = e3_sideload_image($live_hero, $c->ID);
        if (!empty($sideloaded)) {
            $hero_url = $sideloaded;
            $hero_att_id = attachment_url_to_postid($hero_url);
            if ($hero_att_id) {
                set_post_thumbnail($c->ID, $hero_att_id);
            }
        }
    }
    
    // Fallback: get post attachments for gallery and hero if high-res failed
    $attachments = get_posts([
        'post_type'      => 'attachment',
        'posts_per_page' => -1,
        'post_parent'    => $c->ID,
        'post_mime_type' => 'image',
        'orderby'        => 'title',
        'order'          => 'ASC'
    ]);
    
    $gallery_images = [];
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
        
        if (empty($hero_url) && $att->ID == $featured_id) {
            $hero_url = $url;
        }
    }
    
    if (empty($hero_url) && !empty($gallery_images)) {
        $hero_url = $gallery_images[0]['url'];
        foreach ($attachments as $att) {
            if (wp_get_attachment_url($att->ID) == $hero_url) {
                set_post_thumbnail($c->ID, $att->ID);
                break;
            }
        }
    }
    
    // Get client logo
    $logo_url = e3_get_client_logo_url($c->ID);
    
    // 5. Parse content sequentially (all-bold paragraphs -> H3, lists broken up correctly)
    $parsed_content = e3_parse_content_html_sequentially($html, $c->post_title);
    $rel_p = $parsed_content['rel_p'];
    $project_inner_content = $parsed_content['project_content'];
    
    if (empty($rel_p)) {
        $rel_p = esc_html($c->post_title) . " partnered with E3 Entegral Solutions to implement a comprehensive series of energy efficiency improvements and facility upgrades.";
    }
    
    // 6. Compile Gutenberg post content
    if (function_exists('e3es_make_intro_banner_markup')) {
        $content = e3es_make_intro_banner_markup([
            'title' => $c->post_title,
            'bgImageUrl' => $hero_url,
            'clientLogoUrl' => $logo_url,
            'region' => $region,
            'industry' => $industry
        ]) . "\n\n";
    } else {
        $content = e3_generate_intro_banner_block($c->post_title, $hero_url, $logo_url, $region, $industry);
    }
    
    if (!empty($vimeo_id)) {
        $vimeo_url = "https://vimeo.com/$vimeo_id";
        
        $v_title = $c->post_title . " Case Study Video";
        $v_intro = "Watch the video overview of E3's project work and facility improvements at " . $c->post_title . ".";
        
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
                'title' => 'Goodall Witcher Hospital Case Study Video',
                'intro' => "Watch the Goodall Witcher Hospital case study video to learn about the district's mechanical modernization, HVAC improvements, and LED lighting retrofits."
            ],
            'greenville-isd' => [
                'title' => 'Greenville ISD Case Study Video',
                'intro' => 'This video showcase details the district-wide energy conservation program, mechanical modernizations, and controls upgrades executed at Greenville ISD.'
            ],
            'hondo-isd' => [
                'title' => 'Hondo ISD Case Study Video',
                'intro' => "A video walkthrough of Hondo ISD's facility modernization, detailing mechanical replacements, LED upgrades, and automation systems implemented with E3."
            ],
            'houston-community-college' => [
                'title' => 'Houston Community College Case Study Video',
                'intro' => 'Watch E3’s project execution showcase at Houston Community College, outlining the high-efficiency chilled water system and building controls upgrades.'
            ],
            'kountze-isd' => [
                'title' => 'Kountze ISD Case Study Video',
                'intro' => 'This case study video highlights the energy infrastructure modernizations, LED lighting retrofits, and building controls upgrades implemented at Kountze ISD.'
            ],
            'lake-worth-isd' => [
                'title' => 'Lake Worth ISD Case Study Video',
                'intro' => 'A visual presentation detailing Lake Worth ISD’s districtwide energy conservation project, featuring lighting, HVAC, and building automation upgrades.'
            ],
            'manor-isd' => [
                'title' => 'Manor ISD Case Study Video',
                'intro' => 'Watch a case study overview of the energy performance contracting project at Manor ISD, showcasing comprehensive mechanical and lighting upgrades.'
            ],
            'mercedes-isd' => [
                'title' => 'Mercedes ISD Case Study Video',
                'intro' => 'Explore the energy efficiency project at Mercedes ISD, highlighting district-wide lighting, controls modernization, and chiller plant replacements.'
            ],
            'needville-isd' => [
                'title' => 'Needville ISD Case Study Video',
                'intro' => 'This video details E3’s partnership with Needville ISD, illustrating the LED retrofits and mechanical modernization project completed across the district.'
            ],
            'port-neches-groves-isd' => [
                'title' => 'Port Neches-Groves ISD Case Study Video',
                'intro' => 'Watch the video overview of E3’s mechanical modernization, HVAC systems replacement, and LED lighting upgrades at Port Neches-Groves ISD.'
            ],
            'prosper-isd' => [
                'title' => 'Prosper ISD Case Study Video',
                'intro' => 'A visual walkthrough of the district-wide energy conservation program and controls upgrades implemented in partnership with Prosper ISD.'
            ],
            'raymondville-isd' => [
                'title' => 'Raymondville ISD Case Study Video',
                'intro' => 'This video documents the comprehensive energy efficiency improvements, LED retrofits, and mechanical modernizations completed at Raymondville ISD.'
            ],
            'ricardo-isd' => [
                'title' => 'Ricardo ISD Case Study Video',
                'intro' => 'Watch the Ricardo ISD project video highlighting classrooms comfort improvements, LED upgrades, and HVAC modernizations.'
            ],
            'royal-isd' => [
                'title' => 'Royal ISD Case Study Video',
                'intro' => 'Watch this video to learn about the comprehensive facility improvements, LED lighting, and mechanical upgrades completed at Royal ISD.'
            ],
            'sanger-isd' => [
                'title' => 'Sanger ISD Case Study Video',
                'intro' => 'Explore the energy conservation program, LED lighting retrofits, and HVAC systems upgrades implemented across Sanger ISD campuses.'
            ]
        ];
        
        if (isset($video_copy[$slug])) {
            $v_title = $video_copy[$slug]['title'];
            $v_intro = $video_copy[$slug]['intro'];
        }
        
        $content .= e3_generate_video_embed_block($v_title, $vimeo_url, $v_intro);
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
    $content .= "<div class=\"project-details__item\"><span class=\"project-details__label\">Project Scope</span><span class=\"project-details__value\">" . esc_html($scope) . "</span></div>";
    $content .= "<div class=\"project-details__item\"><span class=\"project-details__label\">Contract Amount</span><span class=\"project-details__value\">" . esc_html($contract_amount) . "</span></div>";
    $content .= "<div class=\"project-details__item\"><span class=\"project-details__label\">Annual Savings</span><span class=\"project-details__value\">" . esc_html($annual_savings) . "</span></div>";
    $content .= "<div class=\"project-details__item\"><span class=\"project-details__label\">Market</span><span class=\"project-details__value\">" . esc_html($market) . "</span></div>";
    $content .= "</div>\n<!-- /wp:e3es/project-details -->\n\n";
    
    // Inline description content
    $content .= $project_inner_content;
    
    // Split galleries
    if (count($gallery_images) > 0) {
        $split_galleries = e3_split_gallery_images($gallery_images);
        foreach ($split_galleries as $gallery) {
            $content .= "<!-- wp:heading {\"level\":3} -->\n<h3 class=\"wp-block-heading\">" . esc_html($gallery['title']) . "</h3>\n<!-- /wp:heading -->\n\n";
            $content .= "<!-- wp:paragraph -->\n<p>" . esc_html($gallery['desc']) . "</p>\n<!-- /wp:paragraph -->\n\n";
            $content .= "<!-- wp:gallery {\"linkTo\":\"none\",\"columns\":4} -->\n";
            $content .= "<figure class=\"wp-block-gallery has-nested-images columns-4 is-cropped\">\n";
            foreach ($gallery['images'] as $g) {
                $att_id = attachment_url_to_postid($g['url']);
                $img_attrs = [
                    'url' => $g['url'],
                    'alt' => $g['alt']
                ];
                if ($att_id) {
                    $img_attrs['id'] = $att_id;
                    $img_attrs['sizeSlug'] = 'large';
                    $img_attrs['linkDestination'] = 'none';
                }
                $img_attrs_json = json_encode($img_attrs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                
                $content .= "<!-- wp:image " . $img_attrs_json . " -->\n";
                $content .= "<figure class=\"wp-block-image size-large\"><img src=\"" . esc_url($g['url']) . "\" alt=\"" . esc_attr($g['alt']) . "\"" . ($att_id ? " class=\"wp-image-$att_id\"" : "") . "/></figure>\n";
                $content .= "<!-- /wp:image -->\n\n";
            }
            $content .= "</figure>\n<!-- /wp:gallery -->\n\n";
        }
    }
    
    $content .= "</div></div>\n<!-- /wp:e3es/project -->\n";
    
    $result = wp_update_post([
        'ID' => $c->ID,
        'post_content' => wp_slash($content)
    ], true);
    
    if (is_wp_error($result)) {
        echo "[ERROR] Failed to update: $slug - " . $result->get_error_message() . "\n";
    } else {
        echo "[OK] Restored content with intro-banner, sequential parser, high-res image, and split galleries for: $slug\n";
    }
}

kses_init_filters();
echo "Restoration script execution complete.\n";
