<?php
/**
 * Programmatic Gutenberg Block Formatter & Validator in PHP
 * Directly parses and serializes WordPress post content to fix block validation warnings.
 */

$wp_load = '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';
if (!file_exists($wp_load)) {
    die("Cannot find wp-load.php at: $wp_load\n");
}
require_once $wp_load;

wp_set_current_user(1);
if (function_exists('kses_remove_filters')) {
    kses_remove_filters();
}

echo "\n🛠️ Starting Database block formatter...\n\n";

function getBannerStylesPhp($attr) {
    $rgbMap = [
        'green' => '33, 87, 52',
        'sage' => '125, 160, 68',
        'black' => '0, 0, 0',
        'blue' => '16, 44, 87'
    ];
    $color = isset($attr['bgOverlayColor']) ? $attr['bgOverlayColor'] : 'green';
    $rgb = isset($rgbMap[$color]) ? $rgbMap[$color] : $rgbMap['green'];
    $opacity = isset($attr['bgOpacity']) ? floatval($attr['bgOpacity']) : 0.85;
    
    $fade = isset($attr['bgFadeType']) ? $attr['bgFadeType'] : 'flat';
    switch ($fade) {
        case 'vertical':
            $gradient = "linear-gradient(to bottom, rgba($rgb," . ($opacity * 0.4) . "), rgba($rgb,$opacity))";
            break;
        case 'horizontal':
            $gradient = "linear-gradient(to right, rgba($rgb,$opacity), rgba($rgb," . ($opacity * 0.3) . "))";
            break;
        case 'vignette':
            $gradient = "radial-gradient(circle, rgba($rgb," . ($opacity * 0.4) . ") 0%, rgba($rgb,$opacity) 100%)";
            break;
        case 'vignette-center':
            $gradient = "radial-gradient(circle, rgba($rgb,$opacity) 0%, rgba($rgb," . ($opacity * 0.4) . ") 100%)";
            break;
        case 'flat':
        default:
            $gradient = "linear-gradient(rgba($rgb,$opacity), rgba($rgb,$opacity))";
            break;
    }
    
    $styles = [];
    if (!empty($attr['bgImageUrl'])) {
        $styles[] = "background-image:$gradient, url(" . $attr['bgImageUrl'] . ")";
        $styles[] = "background-size:cover";
        $fx = isset($attr['focalPointX']) ? floatval($attr['focalPointX']) : 0.5;
        $fy = isset($attr['focalPointY']) ? floatval($attr['focalPointY']) : 0.5;
        $styles[] = "background-position:" . ($fx * 100) . "% " . ($fy * 100) . "%";
        $styles[] = "background-repeat:no-repeat";
    } else {
        $styles[] = "background-color:rgba($rgb, 1)";
    }
    
    return implode(';', $styles);
}

function getTitleStylesPhp($attr) {
    $shadowMap = [
        'none' => 'none',
        'subtle' => '0 2px 4px rgba(0,0,0,0.3)',
        'strong' => '0 4px 15px rgba(0,0,0,0.8), 0 2px 4px rgba(0,0,0,0.5)'
    ];
    $shadow = isset($attr['textShadow']) ? $attr['textShadow'] : 'subtle';
    $shadowVal = isset($shadowMap[$shadow]) ? $shadowMap[$shadow] : $shadowMap['subtle'];
    
    $align = isset($attr['textAlignment']) ? $attr['textAlignment'] : 'center';
    $case = isset($attr['textCase']) ? $attr['textCase'] : 'uppercase';
    
    $styles = [
        'margin-bottom:0',
        "text-align:$align",
        "text-transform:$case",
        "text-shadow:$shadowVal"
    ];
    
    if (!empty($attr['textSkew'])) {
        $styles[] = 'transform:skewX(-5deg)';
        $styles[] = 'display:inline-block';
    }
    
    return implode(';', $styles);
}

// Function to recursively clean and format parsed Gutenberg blocks
function format_gutenberg_blocks(&$blocks, $post_type = '') {
    foreach ($blocks as &$block) {
        if (empty($block['blockName'])) {
            continue;
        }

        // Recursively format inner blocks first
        if (!empty($block['innerBlocks'])) {
            format_gutenberg_blocks($block['innerBlocks'], $post_type);
        }

        if ($block['blockName'] === 'e3es/cta-banner') {
            $attr = $block['attrs'];
            $title = isset($attr['title']) ? $attr['title'] : '';
            $text = isset($attr['text']) ? $attr['text'] : '';
            $btnText = isset($attr['btnText']) ? $attr['btnText'] : '';
            $btnUrl = isset($attr['btnUrl']) ? $attr['btnUrl'] : '#';
            
            $btnHtml = '';
            if ($btnText) {
                $btnHtml = '<a href="' . esc_url($btnUrl) . '" class="btn btn--primary cta-banner__btn">' . esc_html($btnText) . '</a>';
            }
            
            $block['innerHTML'] = '<section class="cta-banner"><div class="cta-banner__container"><h2 class="cta-banner__title">' . esc_html($title) . '</h2><p class="cta-banner__text">' . esc_html($text) . '</p>' . $btnHtml . '</div></section>';
            $block['innerContent'] = [ $block['innerHTML'] ];
        }
        
        elseif ($block['blockName'] === 'e3es/intro-banner') {
            $attr = $block['attrs'];
            $title = isset($attr['title']) ? $attr['title'] : '';
            $subtitle = isset($attr['subtitle']) ? $attr['subtitle'] : '';
            
            $logoHtml = '';
            if (!empty($attr['clientLogoUrl'])) {
                $logoHtml = '<div class="db-page-hero__logo-wrapper"><img src="' . esc_url($attr['clientLogoUrl']) . '" alt="Client Logo" class="db-page-hero__logo-img"/></div>';
            }
            
            $introHtml = '';
            if ($post_type === 'clients' || !empty($attr['region']) || !empty($attr['industry'])) {
                $parts = [];
                if (!empty($attr['industry'])) $parts[] = $attr['industry'];
                if (!empty($attr['region'])) $parts[] = $attr['region'];
                $introText = implode(' | ', $parts);
                $introHtml = '<div class="db-page-hero__intro"><p>' . esc_html($introText) . '</p></div>';
            } else if ($subtitle) {
                $introHtml = '<div class="db-page-hero__intro"><p>' . esc_html($subtitle) . '</p></div>';
            }
            
            $bannerStyles = getBannerStylesPhp($attr);
            $titleStyles = getTitleStylesPhp($attr);
            
            $block['innerHTML'] = '<section class="wp-block-e3es-intro-banner db-page-hero" style="' . $bannerStyles . '"><div class="db-page-hero__container">' . $logoHtml . '<div><h1 class="db-page-hero__title" style="' . $titleStyles . '">' . esc_html($title) . '</h1>' . $introHtml . '</div></div></section>';
            $block['innerContent'] = [ $block['innerHTML'] ];
        }
        
        elseif ($block['blockName'] === 'e3es/video-embed') {
            $attr = $block['attrs'];
            $title = isset($attr['title']) ? $attr['title'] : 'Case Study Video';
            $intro = isset($attr['intro']) ? $attr['intro'] : 'This video highlights the energy efficiency improvements and facility upgrades implemented across the district. Watch the case study to see the impact of single-source accountability.';
            
            $iframe = '';
            if (!empty($attr['videoUrl'])) {
                $video_url = trim($attr['videoUrl']);
                if (!empty($video_url) && strpos($video_url, 'player.vimeo.com/video/') === false) {
                    if (preg_match('/(?:vimeo\.com\/)(?:channels\/[^\/]+\/|groups\/[^\/]+\/videos\/|manage\/videos\/|showcase\/[^\/]+\/video\/|)?([0-9]+)/i', $video_url, $matches)) {
                        $video_url = 'https://player.vimeo.com/video/' . $matches[1] . '?badge=0&autopause=0&player_id=0&app_id=58479';
                        $block['attrs']['videoUrl'] = $video_url;
                    }
                }
                $clean_url = str_replace('&#038;', '&amp;', esc_url($video_url));
                $iframe = '<iframe src="' . $clean_url . '" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen title="' . esc_attr($title) . '"></iframe>';
            }
            
            // Add video-embed class name to support stylesheet constraints
            $block['attrs']['className'] = 'video-embed';
            
            $block['innerHTML'] = '<section class="wp-block-e3es-video-embed db-video-section video-embed"><h3 class="db-video-section__title">' . esc_html($title) . '</h3><p class="db-video-section__intro">' . esc_html($intro) . '</p><div class="db-video-wrapper">' . $iframe . '</div></section>';
            $block['innerContent'] = [ $block['innerHTML'] ];
        }
        
        elseif ($block['blockName'] === 'e3es/faq-section') {
            $attr = $block['attrs'];
            $title = isset($attr['title']) ? $attr['title'] : 'Frequently Asked Questions';
            
            // Keep keywords wrapper if it was present inside the original block content
            $keywordsHtml = '';
            if (preg_match('/<div class="faq-section__keywords">.*?<\/div>/is', $block['innerHTML'], $matches)) {
                $keywordsHtml = $matches[0];
            }
            
            $block['innerHTML'] = '<section class="wp-block-e3es-faq-section faq-section"><div class="faq-section__container"><h2 class="faq-section__title">' . esc_html($title) . '</h2>' . $keywordsHtml . '</div></section>';
            
            $innerContent = [
                '<section class="wp-block-e3es-faq-section faq-section"><div class="faq-section__container"><h2 class="faq-section__title">' . esc_html($title) . '</h2>' . $keywordsHtml
            ];
            foreach ($block['innerBlocks'] as $ib) {
                $innerContent[] = null;
            }
            $innerContent[] = '</div></section>';
            
            $block['innerContent'] = $innerContent;
        }
    }
}

function run_formatter($target_id = 0) {
    wp_set_current_user(1);
    if (function_exists('kses_remove_filters')) {
        kses_remove_filters();
    }

    echo "\n🛠️ Starting Database block formatter...\n\n";

    if ($target_id) {
        $post = get_post($target_id);
        if (!$post) {
            die("Post ID $target_id not found.\n");
        }
        $posts_to_process = [$post];
    } else {
        // Process all Pages, Clients, and Services
        $query = new WP_Query([
            'post_type' => ['page', 'clients', 'services'],
            'post_status' => 'publish',
            'posts_per_page' => -1,
        ]);
        $posts_to_process = $query->posts;
    }

    $updated_count = 0;
    foreach ($posts_to_process as $post) {
        if (strpos($post->post_content, '<!-- wp:') === false) {
            continue;
        }

        $blocks = parse_blocks($post->post_content);
        
        format_gutenberg_blocks($blocks, $post->post_type);
        
        $new_content = serialize_blocks($blocks);
        
        // Normalize newlines and whitespaces for comparison
        $clean_old = trim(preg_replace('/\s+/', ' ', $post->post_content));
        $clean_new = trim(preg_replace('/\s+/', ' ', $new_content));
        
        if ($clean_old !== $clean_new || $post->post_content !== $new_content) {
            echo "Updating Post ID {$post->ID} ({$post->post_type}): \"{$post->post_title}\"...\n";
            
            $res = wp_update_post([
                'ID' => $post->ID,
                'post_content' => $new_content
            ]);
            
            if (is_wp_error($res)) {
                echo "  Error: " . $res->get_error_message() . "\n";
            } else {
                echo "  Successfully updated!\n";
                $updated_count++;
            }
        }
    }

    echo "\n🏁 Database block formatting complete! Updated $updated_count posts.\n\n";
}

// Only run if executed directly
if (basename(__FILE__) === basename($_SERVER['PHP_SELF']) || (isset($argv[0]) && basename($argv[0]) === basename(__FILE__))) {
    $target_id = isset($argv[1]) ? intval($argv[1]) : 0;
    run_formatter($target_id);
}
