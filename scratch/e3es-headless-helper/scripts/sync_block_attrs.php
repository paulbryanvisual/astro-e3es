<?php
define('WP_USE_THEMES', false);
require_once('/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php');

// Disable KSES filtering to preserve style attributes (like background-image: url)
kses_remove_filters();

function getBannerStyles($attr) {
    $rgbMap = [
        'green' => '33, 87, 52',
        'sage' => '125, 160, 68',
        'black' => '0, 0, 0',
        'blue' => '16, 44, 87'
    ];
    $rgb = isset($attr['bgOverlayColor']) ? $rgbMap[$attr['bgOverlayColor']] : $rgbMap['green'];
    if (!$rgb) $rgb = $rgbMap['green'];
    $opacity = isset($attr['bgOpacity']) ? (float)$attr['bgOpacity'] : 0.85;
    
    $gradient = '';
    $fadeType = isset($attr['bgFadeType']) ? $attr['bgFadeType'] : 'flat';
    switch ($fadeType) {
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

    $heroStyle = '';
    if (!empty($attr['bgImageUrl'])) {
        $heroStyle .= 'background-image:' . $gradient . ', url(' . $attr['bgImageUrl'] . ');';
        $heroStyle .= 'background-size:cover;';
        $fx = isset($attr['focalPointX']) ? (float)$attr['focalPointX'] : 0.5;
        $fy = isset($attr['focalPointY']) ? (float)$attr['focalPointY'] : 0.5;
        $heroStyle .= 'background-position:' . ($fx * 100) . '% ' . ($fy * 100) . '%;';
        $heroStyle .= 'background-repeat:no-repeat';
    } else {
        $heroStyle .= 'background-color:rgba(' . $rgb . ', 1)';
    }

    return $heroStyle;
}

function getTitleStyles($attr) {
    $shadowMap = [
        'none' => 'none',
        'subtle' => '0 2px 4px rgba(0,0,0,0.3)',
        'strong' => '0 4px 15px rgba(0,0,0,0.8), 0 2px 4px rgba(0,0,0,0.5)'
    ];
    $shadow = isset($attr['textShadow']) ? $attr['textShadow'] : 'subtle';
    if (!$shadow) $shadow = 'subtle';
    
    $titleStyle = '';
    $titleStyle .= 'margin-bottom:0;';
    
    $align = isset($attr['textAlignment']) ? $attr['textAlignment'] : 'center';
    if (!$align) $align = 'center';
    $titleStyle .= 'text-align:' . $align . ';';
    
    $case = isset($attr['textCase']) ? $attr['textCase'] : 'uppercase';
    if (!$case) $case = 'uppercase';
    if ($case === 'uppercase') {
        $titleStyle .= 'text-transform:uppercase;';
    } else {
        $titleStyle .= 'text-transform:none;';
    }
    
    if ($shadow !== 'none') {
        $titleStyle .= 'text-shadow:' . $shadowMap[$shadow] . ';';
    }
    if (!empty($attr['textSkew'])) {
        $titleStyle .= 'transform:skewX(-5deg);display:inline-block;';
    }
    return rtrim($titleStyle, ';');
}

function render_intro_banner_html($attr) {
    $logo_wrapper = '';
    if (!empty($attr['clientLogoUrl'])) {
        $logo_wrapper = '<div class="db-page-hero__logo-wrapper"><img src="' . esc_url($attr['clientLogoUrl']) . '" alt="Client Logo" class="db-page-hero__logo-img"/></div>';
    }

    $hero_intro = '';
    if (!empty($attr['region']) || !empty($attr['industry'])) {
        $regionLabel = !empty($attr['region']) ? $attr['region'] : '';
        $industryLabel = !empty($attr['industry']) ? $attr['industry'] : '';
        $introTextParts = [];
        if ($industryLabel) $introTextParts[] = $industryLabel;
        if ($regionLabel) $introTextParts[] = $regionLabel;
        $introText = implode(' | ', $introTextParts);
        $hero_intro = '<div class="db-page-hero__intro"><p>' . esc_html($introText) . '</p></div>';
    } else if (!empty($attr['subtitle'])) {
        $hero_intro = '<div class="db-page-hero__intro"><p>' . esc_html($attr['subtitle']) . '</p></div>';
    }

    $banner_style = getBannerStyles($attr);
    $title_style = getTitleStyles($attr);
    $title_style_attr = $title_style ? ' style="' . $title_style . '"' : '';
    
    $title = isset($attr['title']) ? $attr['title'] : '';

    return '<section class="wp-block-e3es-intro-banner db-page-hero" style="' . $banner_style . '"><div class="db-page-hero__container">' . $logo_wrapper . '<div><h1 class="db-page-hero__title"' . $title_style_attr . '>' . $title . '</h1>' . $hero_intro . '</div></div></section>';
}

function render_project_start_html($attr) {
    $hero_html = '';
    if (!empty($attr['heroImageUrl'])) {
        $px = isset($attr['focalPointX']) ? (float)$attr['focalPointX'] * 100 : 50;
        $py = isset($attr['focalPointY']) ? (float)$attr['focalPointY'] * 100 : 50;
        $hero_html = '<div class="project-section__hero">' .
            '<img src="' . esc_url($attr['heroImageUrl']) . '" alt="' . esc_attr($attr['title']) . '" class="project-section__hero-img" style="object-position:' . $px . '% ' . $py . '%"/>' .
            '<div class="project-section__mask project-section__mask--left"></div>' .
            '<div class="project-section__mask project-section__mask--right"></div>' .
            '</div>';
    }
    
    $eyebrow = isset($attr['eyebrow']) ? $attr['eyebrow'] : 'Project 1';
    $title = isset($attr['title']) ? $attr['title'] : '';
    $id_attr = !empty($attr['sectionId']) ? ' id="' . esc_attr($attr['sectionId']) . '"' : '';

    return '<div class="wp-block-e3es-project project-section"' . $id_attr . ' style="--hero-img:' . (!empty($attr['heroImageUrl']) ? 'url(' . esc_url($attr['heroImageUrl']) . ')' : 'none') . '"><div class="project-section__header">' . $hero_html . '<div class="project-section__info"><span class="project-section__eyebrow">' . esc_html($eyebrow) . '</span><h2 class="project-section__title">' . $title . '</h2></div></div><div class="project-section__content">';
}

function render_project_details_html($attr) {
    $items = [];
    for ($i = 1; $i <= 4; $i++) {
        if (!empty($attr['label' . $i])) {
            $items[] = '<div class="project-details__item"><span class="project-details__label">' . esc_html($attr['label' . $i]) . '</span><span class="project-details__value">' . esc_html($attr['value' . $i]) . '</span></div>';
        }
    }
    return '<div class="wp-block-e3es-project-details project-details">' . implode('', $items) . '</div>';
}

function render_video_embed_html($attr) {
    $title = isset($attr['title']) ? $attr['title'] : 'Case Study Video';
    $intro = isset($attr['intro']) ? $attr['intro'] : 'This video highlights the energy efficiency improvements and facility upgrades implemented across the district. Watch the case study to see the impact of single-source accountability.';
    
    $iframe = '';
    if (!empty($attr['videoUrl'])) {
        $clean_url = str_replace('&#038;', '&amp;', esc_url($attr['videoUrl']));
        $iframe = '<iframe src="' . $clean_url . '" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen title="' . esc_attr($title) . '"></iframe>';
    }
    
    return '<section class="wp-block-e3es-video-embed db-video-section"><h3 class="db-video-section__title">' . esc_html($title) . '</h3><p class="db-video-section__intro">' . esc_html($intro) . '</p><div class="db-video-wrapper">' . $iframe . '</div></section>';
}

function render_project_toc_html($attr) {
    $children = ['<span class="db-toc__label">Jump to project:</span>'];
    $added = 0;
    for ($i = 1; $i <= 4; $i++) {
        if (!empty($attr['link' . $i . 'Label'])) {
            if ($added > 0) {
                $children[] = '<span class="db-toc__divider">|</span>';
            }
            $href = isset($attr['link' . $i . 'Href']) ? $attr['link' . $i . 'Href'] : '';
            $children[] = '<a href="' . esc_attr($href) . '" class="db-toc__link">' . esc_html($attr['link' . $i . 'Label']) . '</a>';
            $added++;
        }
    }
    return '<nav class="wp-block-e3es-project-toc db-toc" aria-label="Table of Contents">' . implode('', $children) . '</nav>';
}

function clean_attributes(&$attrs) {
    if (is_array($attrs)) {
        foreach ($attrs as $key => &$val) {
            if (is_array($val)) {
                clean_attributes($val);
            } elseif (is_string($val)) {
                $val = str_replace('u0026', '&', $val);
                $val = html_entity_decode($val, ENT_QUOTES, 'UTF-8');
            }
        }
    }
}

function process_blocks(&$blocks, $thumb_url) {
    $modified = false;
    foreach ($blocks as &$block) {
        if (!empty($block['attrs'])) {
            $old_attrs = $block['attrs'];
            clean_attributes($block['attrs']);
            if ($old_attrs !== $block['attrs']) {
                $modified = true;
            }
        }
        
        if ($block['blockName'] === 'e3es/intro-banner') {
            if ($thumb_url && (!isset($block['attrs']['bgImageUrl']) || $block['attrs']['bgImageUrl'] !== $thumb_url)) {
                $block['attrs']['bgImageUrl'] = $thumb_url;
                $modified = true;
            }
            $old_html = $block['innerHTML'];
            $block['innerHTML'] = render_intro_banner_html($block['attrs']);
            $block['innerContent'] = [$block['innerHTML']];
            if ($old_html !== $block['innerHTML']) {
                $modified = true;
            }
        } elseif ($block['blockName'] === 'e3es/project-details') {
            $old_html = $block['innerHTML'];
            $block['innerHTML'] = render_project_details_html($block['attrs']);
            $block['innerContent'] = [$block['innerHTML']];
            if ($old_html !== $block['innerHTML']) {
                $modified = true;
            }
        } elseif ($block['blockName'] === 'e3es/project') {
            if (!empty($block['innerBlocks'])) {
                if (process_blocks($block['innerBlocks'], $thumb_url)) {
                    $modified = true;
                }
            }
            $old_html = $block['innerHTML'];
            $start_html = render_project_start_html($block['attrs']);
            $block['innerHTML'] = $start_html . '</div></div>';
            $block['innerContent'] = [
                $start_html,
                null,
                '</div></div>'
            ];
            if ($old_html !== $block['innerHTML']) {
                $modified = true;
            }
        } elseif ($block['blockName'] === 'e3es/video-embed') {
            $old_html = $block['innerHTML'];
            $block['innerHTML'] = render_video_embed_html($block['attrs']);
            $block['innerContent'] = [$block['innerHTML']];
            if ($old_html !== $block['innerHTML']) {
                $modified = true;
            }
        } elseif ($block['blockName'] === 'e3es/project-toc') {
            $old_html = $block['innerHTML'];
            $block['innerHTML'] = render_project_toc_html($block['attrs']);
            $block['innerContent'] = [$block['innerHTML']];
            if ($old_html !== $block['innerHTML']) {
                $modified = true;
            }
        } else {
            if (!empty($block['innerBlocks'])) {
                if (process_blocks($block['innerBlocks'], $thumb_url)) {
                    $modified = true;
                }
            }
        }
    }
    return $modified;
}

// Query all posts
$query = new WP_Query(array(
    'post_type'      => 'any',
    'posts_per_page' => -1,
    'post_status'    => 'any',
));

echo "Found " . $query->post_count . " posts total in database.\n";

$updated_count = 0;

if ($query->have_posts()) {
    while ($query->have_posts()) {
        $query->the_post();
        $post_obj = $query->post;
        $post_id = $post_obj->ID;
        $post_title = $post_obj->post_title;
        $post_content = $post_obj->post_content;
        
        // Simple fast check if the post contains E3 blocks
        if (strpos($post_content, 'wp:e3es/') === false) {
            continue;
        }
        
        $blocks = parse_blocks($post_content);
        $thumb_id = get_post_thumbnail_id($post_id);
        $thumb_url = $thumb_id ? wp_get_attachment_url($thumb_id) : '';
        
        $modified = process_blocks($blocks, $thumb_url);
        
        if ($modified) {
            $new_content = serialize_blocks($blocks);
            
            if ($new_content !== $post_content) {
                $updated_post = array(
                    'ID'           => $post_id,
                    'post_content' => wp_slash($new_content),
                );
                
                $res = wp_update_post($updated_post);
                if ($res) {
                    echo "Updated Post ID {$post_id}: \"{$post_title}\"\n";
                    $updated_count++;
                } else {
                    echo "Failed to update Post ID {$post_id}: \"{$post_title}\"\n";
                }
            }
        }
    }
    wp_reset_postdata();
}

echo "Successfully updated {$updated_count} posts.\n";
