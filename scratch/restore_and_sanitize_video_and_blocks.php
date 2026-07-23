<?php
require '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';

// Disable KSES filters to prevent iframe stripping
wp_set_current_user(1);
if (function_exists('kses_remove_filters')) {
    kses_remove_filters();
}

$posts = get_posts(['post_type' => 'clients', 'posts_per_page' => -1]);
$updated_count = 0;

$expected_videos = [
    'granbury-isd' => ['id' => '227283498', 'title' => 'Granbury ISD Case Study Video'],
    'little-elm-isd' => ['id' => '946653874', 'title' => 'Lessons In Learning - Mike Lamb'],
    'keene-isd' => ['id' => '1176712805', 'title' => 'Keene ISD, Sports Lighting'],
    'plano-isd' => ['id' => '1007829512', 'title' => 'Lessons in Learning - Dr. Theresa Williams'],
    'city-of-stockdale' => ['id' => '1171901749', 'title' => 'Lessons in Learning - Stephen Mayfield'],
    'boyd-isd' => ['id' => '1179578579', 'title' => 'Boyd ISD Case Study Video']
];

function parse_styles($style_str) {
    $styles = [];
    if (empty($style_str)) return $styles;
    $parts = explode(';', $style_str);
    foreach ($parts as $p) {
        $p = trim($p);
        if (empty($p)) continue;
        $kv = explode(':', $p, 2);
        if (count($kv) === 2) {
            $styles[trim($kv[0])] = trim($kv[1]);
        }
    }
    return $styles;
}

function extract_url_from_css($css_val) {
    if (preg_match('/url\([\'"]?([^)]*?)[\'"]?\)/i', $css_val, $matches)) {
        return $matches[1];
    }
    return '';
}

function get_banner_styles_php($attrs) {
    $rgbMap = [
        'green' => '33, 87, 52',
        'sage' => '125, 160, 68',
        'black' => '0, 0, 0',
        'blue' => '16, 44, 87'
    ];
    $bgOverlayColor = isset($attrs['bgOverlayColor']) ? $attrs['bgOverlayColor'] : 'green';
    $rgb = isset($rgbMap[$bgOverlayColor]) ? $rgbMap[$bgOverlayColor] : $rgbMap['green'];
    $opacity = isset($attrs['bgOpacity']) ? floatval($attrs['bgOpacity']) : 0.85;
    
    $bgFadeType = isset($attrs['bgFadeType']) ? $attrs['bgFadeType'] : 'flat';
    $gradient = '';
    switch ($bgFadeType) {
        case 'vertical':
            $gradient = 'linear-gradient(to bottom, rgba(' . $rgb . ',' . ($opacity * 0.4) . '), rgba(' . $rgb . ',' . $opacity . '))';
            break;
        case 'horizontal':
            $gradient = 'linear-gradient(to right, rgba(' . $rgb . ',' . $opacity . '), rgba(' . $rgb . ',' . ($opacity * 0.3) . '))';
            break;
        case 'vignette':
            $gradient = 'radial-gradient(circle, rgba(' . $rgb . ',' . ($opacity * 0.4) . ') 0%, rgba(' . $rgb . ',' . $opacity . ') 100%)';
            break;
        case 'vignette-center':
            $gradient = 'radial-gradient(circle, rgba(' . $rgb . ',' . $opacity . ') 0%, rgba(' . $rgb . ',' . ($opacity * 0.4) . ') 100%)';
            break;
        case 'flat':
        default:
            $gradient = 'linear-gradient(rgba(' . $rgb . ',' . $opacity . '), rgba(' . $rgb . ',' . $opacity . '))';
            break;
    }
    
    $styles = [];
    $bgImageUrl = isset($attrs['bgImageUrl']) ? $attrs['bgImageUrl'] : '';
    if ($bgImageUrl) {
        $styles[] = 'background-image:' . $gradient . ', url(' . esc_url($bgImageUrl) . ')';
        $styles[] = 'background-size:cover';
        $fpx = isset($attrs['focalPointX']) ? floatval($attrs['focalPointX']) : 0.5;
        $fpy = isset($attrs['focalPointY']) ? floatval($attrs['focalPointY']) : 0.5;
        $styles[] = 'background-position:' . ($fpx * 100) . '% ' . ($fpy * 100) . '%';
        $styles[] = 'background-repeat:no-repeat';
    } else {
        $styles[] = 'background-color:rgba(' . $rgb . ', 1)';
    }
    
    return implode(';', $styles);
}

function get_title_styles_php($attrs) {
    $shadowMap = [
        'none' => 'none',
        'subtle' => '0 2px 4px rgba(0,0,0,0.3)',
        'strong' => '0 4px 15px rgba(0,0,0,0.8), 0 2px 4px rgba(0,0,0,0.5)'
    ];
    $textAlignment = isset($attrs['textAlignment']) ? $attrs['textAlignment'] : 'center';
    $textCase = isset($attrs['textCase']) ? $attrs['textCase'] : 'uppercase';
    $textShadowKey = isset($attrs['textShadow']) ? $attrs['textShadow'] : 'subtle';
    $textShadow = isset($shadowMap[$textShadowKey]) ? $shadowMap[$textShadowKey] : $shadowMap['subtle'];
    
    $styles = [];
    $styles[] = 'margin-bottom:0';
    $styles[] = 'text-align:' . $textAlignment;
    $styles[] = 'text-transform:' . $textCase;
    $styles[] = 'text-shadow:' . $textShadow;
    
    if (!empty($attrs['textSkew'])) {
        $styles[] = 'transform:skewX(-5deg)';
        $styles[] = 'display:inline-block';
    }
    
    return implode(';', $styles);
}

function restore_block_attributes(&$blocks, $post_slug, $expected_videos) {
    $changed = false;
    foreach ($blocks as &$b) {
        $orig_attrs = $b['attrs'];
        
        // 1. Restore e3es/project attributes & Reconstruct block HTML
        if ($b['blockName'] === 'e3es/project') {
            if (empty($b['attrs'])) {
                $b['attrs'] = [];
            }
            
            $html = $b['innerHTML'];
            
            // sectionId
            if (preg_match('/id=["\']([^"\']+)["\']/i', $html, $m)) {
                $b['attrs']['sectionId'] = $m[1];
            }
            
            // className
            if (preg_match('/class=["\']([^"\']+)["\']/i', $html, $m)) {
                $classes = explode(' ', $m[1]);
                $custom_classes = array_filter($classes, function($c) {
                    return !in_array($c, ['wp-block-e3es-project', 'project-section']);
                });
                if (!empty($custom_classes)) {
                    $b['attrs']['className'] = implode(' ', $custom_classes);
                }
            }
            
            // style / heroImageUrl
            if (preg_match('/style=["\']([^"\']+)["\']/i', $html, $m)) {
                $styles = parse_styles($m[1]);
                if (isset($styles['--hero-img'])) {
                    $url = extract_url_from_css($styles['--hero-img']);
                    if ($url) {
                        $b['attrs']['heroImageUrl'] = $url;
                    }
                }
            }
            
            // fallback heroImageUrl from img src
            if (empty($b['attrs']['heroImageUrl'])) {
                if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $m)) {
                    $b['attrs']['heroImageUrl'] = $m[1];
                }
            }
            
            // title
            if (preg_match('/<h2[^>]+class=["\'][^"\']*project-section__title[^"\']*["\'][^>]*>(.*?)<\/h2>/is', $html, $m)) {
                $b['attrs']['title'] = html_entity_decode(trim($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
            
            // eyebrow
            if (preg_match('/<span[^>]+class=["\'][^"\']*project-section__eyebrow[^"\']*["\'][^>]*>(.*?)<\/span>/is', $html, $m)) {
                $b['attrs']['eyebrow'] = html_entity_decode(trim($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
            
            // focalPointX & Y from image style
            if (preg_match('/<img[^>]+style=["\']([^"\']+)["\']/i', $html, $m)) {
                $img_styles = parse_styles($m[1]);
                if (isset($img_styles['object-position'])) {
                    $pos = explode(' ', $img_styles['object-position']);
                    if (count($pos) === 2) {
                        $x = floatval(rtrim($pos[0], '%')) / 100.0;
                        $y = floatval(rtrim($pos[1], '%')) / 100.0;
                        $b['attrs']['focalPointX'] = round($x, 2);
                        $b['attrs']['focalPointY'] = round($y, 2);
                    }
                }
            }
            
            // Clean attributes (decode HTML entities completely)
            foreach ($b['attrs'] as $key => &$val) {
                if (is_string($val)) {
                    $val = str_replace(['u0026amp;', 'u0026', 'amp;'], '&', $val);
                    $val = str_replace('u0027', "'", $val);
                    $val = html_entity_decode($val, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $val = html_entity_decode($val, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                }
            }
            
            // RECONSTRUCT MARKUP TO PRESERVE INNER BLOCKS AND PREVENT FLOAT PRECISION MISMATCH
            $hero_url = isset($b['attrs']['heroImageUrl']) ? $b['attrs']['heroImageUrl'] : '';
            $title = isset($b['attrs']['title']) ? $b['attrs']['title'] : '';
            $eyebrow = isset($b['attrs']['eyebrow']) ? $b['attrs']['eyebrow'] : 'Project 1';
            $section_id = isset($b['attrs']['sectionId']) ? $b['attrs']['sectionId'] : '';
            $class_name = isset($b['attrs']['className']) ? $b['attrs']['className'] : '';
            
            $fpx = isset($b['attrs']['focalPointX']) ? $b['attrs']['focalPointX'] : 0.5;
            $fpy = isset($b['attrs']['focalPointY']) ? $b['attrs']['focalPointY'] : 0.5;
            
            $class_html = 'wp-block-e3es-project project-section' . ($class_name ? ' ' . $class_name : '');
            $id_html = $section_id ? ' id="' . esc_attr($section_id) . '"' : '';
            $style_html = $hero_url ? ' style="--hero-img:url(' . esc_url($hero_url) . ')"' : ' style="--hero-img:none"';
            
            $hero_html = '';
            if ($hero_url) {
                $img_style = ' style="object-position:' . ($fpx * 100) . '% ' . ($fpy * 100) . '%"';
                $hero_html = '<div class="project-section__hero"><img src="' . esc_url($hero_url) . '" alt="' . esc_attr($title) . '" class="project-section__hero-img"' . $img_style . '/><div class="project-section__mask project-section__mask--left"></div><div class="project-section__mask project-section__mask--right"></div></div>';
            }
            
            $clean_title = esc_html($title);
            $clean_eyebrow = esc_html($eyebrow);
            
            $header_html = '<div class="project-section__header">' . $hero_html . '<div class="project-section__info"><span class="project-section__eyebrow">' . $clean_eyebrow . '</span><h2 class="project-section__title">' . $clean_title . '</h2></div></div>';
            
            $before_content = '<div class="' . $class_html . '"' . $id_html . $style_html . '>' . $header_html . '<div class="project-section__content">';
            $after_content = '</div></div>';
            
            $inner_blocks_count = count($b['innerBlocks']);
            
            $b['innerContent'] = [];
            $b['innerContent'][] = $before_content;
            for ($i = 0; $i < $inner_blocks_count; $i++) {
                $b['innerContent'][] = null;
            }
            $b['innerContent'][] = $after_content;
            
            $inner_html = $before_content;
            foreach ($b['innerBlocks'] as $ib) {
                $inner_html .= serialize_block($ib);
            }
            $inner_html .= $after_content;
            $b['innerHTML'] = $inner_html;
            
            $changed = true;
        }
        
        // 2. Restore e3es/intro-banner attributes & Reconstruct HTML
        if ($b['blockName'] === 'e3es/intro-banner') {
            if (empty($b['attrs'])) {
                $b['attrs'] = [];
            }
            
            $html = $b['innerHTML'];
            
            // title
            if (preg_match('/<h1[^>]+class=["\'][^"\']*db-page-hero__title[^"\']*["\'][^>]*>(.*?)<\/h1>/is', $html, $m)) {
                $b['attrs']['title'] = html_entity_decode(trim($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
            
            // clientLogoUrl
            if (preg_match('/<img[^>]+class=["\'][^"\']*db-page-hero__logo-img[^"\']*["\'][^>]+src=["\']([^"\']+)["\']/i', $html, $m)) {
                $b['attrs']['clientLogoUrl'] = $m[1];
            }
            
            // bgImageUrl
            if (preg_match('/<section[^>]+style=["\']([^"\']+)["\']/i', $html, $m)) {
                $styles = parse_styles($m[1]);
                if (isset($styles['background-image'])) {
                    $url = extract_url_from_css($styles['background-image']);
                    if ($url) {
                        $b['attrs']['bgImageUrl'] = $url;
                    }
                }
            }
            
            // fallback bgImageUrl
            if (empty($b['attrs']['bgImageUrl'])) {
                if (preg_match('/background-image:[^;]*url\([\'"]?([^)]*?)[\'"]?\)/i', $html, $m)) {
                    $b['attrs']['bgImageUrl'] = $m[1];
                }
            }
            
            // region and industry
            if (preg_match('/<div[^>]+class=["\'][^"\']*db-page-hero__intro[^"\']*["\'][^>]*>\s*<p>(.*?)<\/p>/is', $html, $m)) {
                $parts = explode('|', $m[1]);
                if (count($parts) === 2) {
                    $b['attrs']['industry'] = trim($parts[0]);
                    $b['attrs']['region'] = trim($parts[1]);
                }
            }
            
            // Clean attributes
            foreach ($b['attrs'] as $key => &$val) {
                if (is_string($val)) {
                    $val = str_replace(['u0026amp;', 'u0026', 'amp;'], '&', $val);
                    $val = str_replace('u0027', "'", $val);
                    $val = html_entity_decode($val, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $val = html_entity_decode($val, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                }
            }
            
            // RECONSTRUCT INTRO BANNER MARKUP
            $title = isset($b['attrs']['title']) ? $b['attrs']['title'] : '';
            $bg_image_url = isset($b['attrs']['bgImageUrl']) ? $b['attrs']['bgImageUrl'] : '';
            $client_logo_url = isset($b['attrs']['clientLogoUrl']) ? $b['attrs']['clientLogoUrl'] : '';
            $industry = isset($b['attrs']['industry']) ? $b['attrs']['industry'] : '';
            $region = isset($b['attrs']['region']) ? $b['attrs']['region'] : '';
            
            $banner_styles = get_banner_styles_php($b['attrs']);
            $title_styles = get_title_styles_php($b['attrs']);
            
            $logo_html = '';
            if ($client_logo_url) {
                $logo_html = '<div class="db-page-hero__logo-wrapper"><img src="' . esc_url($client_logo_url) . '" alt="Client Logo" class="db-page-hero__logo-img"/></div>';
            }
            
            $intro_html = '';
            if ($region || $industry) {
                $parts = [];
                if ($industry) $parts[] = $industry;
                if ($region) $parts[] = $region;
                $intro_text = implode(' | ', $parts);
                $intro_html = '<div class="db-page-hero__intro"><p>' . esc_html($intro_text) . '</p></div>';
            }
            
            $title_html = '<h1 class="db-page-hero__title" style="' . $title_styles . '">' . esc_html($title) . '</h1>';
            
            $inner_html = '<section class="wp-block-e3es-intro-banner db-page-hero" style="' . $banner_styles . '"><div class="db-page-hero__container">' . $logo_html . '<div>' . $title_html . $intro_html . '</div></div></section>';
            
            $b['innerHTML'] = $inner_html;
            $b['innerContent'] = [ $inner_html ];
            $changed = true;
        }
        
        // 3. Restore e3es/project-toc attributes
        if ($b['blockName'] === 'e3es/project-toc') {
            if (empty($b['attrs'])) {
                $b['attrs'] = [];
            }
            
            $html = $b['innerHTML'];
            if (preg_match_all('/<a[^>]+href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/is', $html, $m, PREG_SET_ORDER)) {
                for ($i = 0; $i < 4; $i++) {
                    $idx = $i + 1;
                    if (isset($m[$i])) {
                        $b['attrs']["link{$idx}Href"] = $m[$i][1];
                        $b['attrs']["link{$idx}Label"] = html_entity_decode(trim($m[$i][2]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    } else {
                        $b['attrs']["link{$idx}Href"] = '';
                        $b['attrs']["link{$idx}Label"] = '';
                    }
                }
            }
            
            // Clean attributes
            foreach ($b['attrs'] as $key => &$val) {
                if (is_string($val)) {
                    $val = str_replace(['u0026amp;', 'u0026', 'amp;'], '&', $val);
                    $val = str_replace('u0027', "'", $val);
                    $val = html_entity_decode($val, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $val = html_entity_decode($val, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                }
            }
            
            if ($b['attrs'] !== $orig_attrs) {
                $changed = true;
            }
        }
        
        // 4. Restore and reconstruct e3es/video-embed attributes & iframe HTML markup
        if ($b['blockName'] === 'e3es/video-embed') {
            if (empty($b['attrs'])) {
                $b['attrs'] = [];
            }
            
            $video_title = 'Case Study Video';
            if (preg_match('/<h3[^>]*>(.*?)<\/h3>/is', $b['innerHTML'], $m)) {
                $video_title = html_entity_decode(trim($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
            
            if (isset($expected_videos[$post_slug])) {
                $vid_info = $expected_videos[$post_slug];
                $vid_id = $vid_info['id'];
                $video_title = $vid_info['title'];
                
                $b['attrs']['title'] = $video_title;
                $b['attrs']['videoUrl'] = "https://player.vimeo.com/video/{$vid_id}?badge=0&autopause=0&player_id=0&app_id=58479";
                $b['attrs']['intro'] = 'This video highlights the energy efficiency improvements and facility upgrades implemented across the district. Watch the case study to see the impact of single-source accountability.';
                
                $iframe_html = '<iframe src="https://player.vimeo.com/video/' . $vid_id . '?badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen title="' . esc_attr($video_title) . '"></iframe>';
                
                $b['innerHTML'] = '<section class="wp-block-e3es-video-embed db-video-section"><h3 class="db-video-section__title">' . esc_html($video_title) . '</h3><p class="db-video-section__intro">This video highlights the energy efficiency improvements and facility upgrades implemented across the district. Watch the case study to see the impact of single-source accountability.</p><div class="db-video-wrapper">' . $iframe_html . '</div></section>';
                $b['innerContent'] = [ $b['innerHTML'] ];
                $changed = true;
            } else {
                if (preg_match('/<iframe[^>]+src=["\']([^"\']+)["\']/i', $b['innerHTML'], $m)) {
                    $b['attrs']['videoUrl'] = str_replace('&amp;', '&', $m[1]);
                }
                $b['attrs']['title'] = $video_title;
                
                foreach ($b['attrs'] as $key => &$val) {
                    if (is_string($val)) {
                        $val = str_replace(['u0026amp;', 'u0026', 'amp;'], '&', $val);
                        $val = str_replace('u0027', "'", $val);
                        $val = html_entity_decode($val, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        $val = html_entity_decode($val, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    }
                }
                if ($b['attrs'] !== $orig_attrs) {
                    $changed = true;
                }
            }
        }
        
        // 5. Restore e3es/faq-section attributes & Reconstruct HTML
        if ($b['blockName'] === 'e3es/faq-section') {
            if (empty($b['attrs'])) {
                $b['attrs'] = [];
            }
            
            $title = isset($b['attrs']['title']) ? $b['attrs']['title'] : 'Frequently Asked Questions';
            
            $before_content = '<section class="wp-block-e3es-faq-section faq-section"><div class="faq-section__container"><h2 class="faq-section__title">' . esc_html($title) . '</h2>';
            $after_content = '</div></section>';
            
            $inner_blocks_count = count($b['innerBlocks']);
            
            $b['innerContent'] = [];
            $b['innerContent'][] = $before_content;
            for ($i = 0; $i < $inner_blocks_count; $i++) {
                $b['innerContent'][] = null;
            }
            $b['innerContent'][] = $after_content;
            
            $inner_html = $before_content;
            foreach ($b['innerBlocks'] as $ib) {
                $inner_html .= serialize_block($ib);
            }
            $inner_html .= $after_content;
            $b['innerHTML'] = $inner_html;
            
            $changed = true;
        }
        
        if (!empty($b['innerBlocks'])) {
            if (restore_block_attributes($b['innerBlocks'], $post_slug, $expected_videos)) {
                $changed = true;
            }
        }
    }
    return $changed;
}

foreach ($posts as $p) {
    $blocks = parse_blocks($p->post_content);
    $changed = restore_block_attributes($blocks, $p->post_name, $expected_videos);
    
    if ($changed) {
        $new_content = serialize_blocks($blocks);
        
        // SAVE USING WP_SLASH TO PRESERVE BACKSLASHES
        wp_update_post(wp_slash([
            'ID' => $p->ID,
            'post_content' => $new_content
        ]));
        
        echo "Restored and slashed blocks for post: {$p->post_name}\n";
        $updated_count++;
    }
}

echo "Done! Restored block attributes on $updated_count client posts.\n";
