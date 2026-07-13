<?php
/**
 * E3 Client Block Seeder — v2 (No Custom HTML)
 *
 * Generates post_content using ONLY native Gutenberg + registered
 * custom e3es/* blocks. Zero core/html blocks.
 *
 * Trigger: http://e3es2026.local/?e3_seed_blocks=1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'init', 'e3_seed_blocks_handler' );
function e3_seed_blocks_handler() {
    if ( ! isset( $_GET['e3_seed_blocks'] ) || $_GET['e3_seed_blocks'] !== '1' ) {
        return;
    }

    $is_local = ( strpos( $_SERVER['HTTP_HOST'] ?? '', '.local' ) !== false || $_SERVER['HTTP_HOST'] === 'localhost' );
    if ( ! $is_local && ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Unauthorized.' );
    }

    set_time_limit( 120 );
    
    // Set current user to admin and remove KSES filters to prevent escaping of HTML comments in block attributes
    wp_set_current_user( 1 );
    if ( function_exists( 'kses_remove_filters' ) ) {
        kses_remove_filters();
    }
    
    header( 'Content-Type: text/plain; charset=utf-8' );

    // Load the media URL map
    $url_map = get_option( 'e3_media_url_map', array() );
    if ( empty( $url_map ) ) {
        echo "ERROR: No media URL map found. Run ?e3_import_media=1 first.\n";
        exit;
    }
    echo "Loaded " . count( $url_map ) . " media URL mappings.\n\n";

    $placeholder = 'http://e3es2026.local/wp-content/uploads/2026/06/taj-mahal-placeholder.png';

    // Helper to check if a URL's file is missing on disk
    $is_url_missing = function( $url ) {
        if ( empty( $url ) ) return true;
        if ( strpos( $url, 'http' ) === 0 ) {
            $parts = parse_url( $url );
            $host = $_SERVER['HTTP_HOST'] ?? 'e3es2026.local';
            if ( isset( $parts['host'] ) && $parts['host'] === $host ) {
                $file_path = ABSPATH . ltrim( $parts['path'] ?? '', '/' );
            } else {
                // External remote URL - not missing
                return false;
            }
        } else {
            $file_path = ABSPATH . ltrim( $url, '/' );
        }
        return ! file_exists( $file_path );
    };

    // Helper to resolve filename to WP media URL
    $resolve = function( $filename ) use ( $url_map, $placeholder, $is_url_missing ) {
        $url = $url_map[ $filename ] ?? '';
        if ( $url && ! $is_url_missing( $url ) ) {
            return $url;
        }
        $local_path = ABSPATH . 'images/' . $filename;
        if ( file_exists( $local_path ) ) {
            return '/images/' . $filename;
        }
        return $placeholder;
    };

    // Helper to resolve flickr subdir images
    $resolve_flickr = function( $subdir, $filename ) use ( $url_map, $placeholder, $is_url_missing ) {
        $url = $url_map[ $filename ] ?? '';
        if ( $url && ! $is_url_missing( $url ) ) {
            return $url;
        }
        $local_path = ABSPATH . 'images/flickr/' . $subdir . '/' . $filename;
        if ( file_exists( $local_path ) ) {
            return '/images/flickr/' . $subdir . '/' . $filename;
        }
        return $placeholder;
    };

    // ─── Block Markup Builders ────────────────────────────────────

    // e3es/intro-banner
    $intro_banner = function( $title, $bgImageUrl, $bgOpacity = 0.85, $bgOverlayColor = 'green', $bgFadeType = 'flat', $focalPointX = 0.5, $focalPointY = 0.5, $clientLogoUrl = '', $region = '', $industry = '' ) {
        return e3es_make_intro_banner_markup([
            'title'          => $title,
            'bgImageUrl'     => $bgImageUrl,
            'bgOpacity'      => $bgOpacity,
            'bgOverlayColor' => $bgOverlayColor,
            'bgFadeType'     => $bgFadeType,
            'focalPointX'    => $focalPointX,
            'focalPointY'    => $focalPointY,
            'clientLogoUrl'  => $clientLogoUrl,
            'region'         => $region,
            'industry'       => $industry
        ]);
    };

    // e3es/video-embed
    $video_embed = function( $title, $videoUrl, $intro = '' ) {
        if ( empty( $intro ) ) {
            $intro = 'This video highlights the energy efficiency improvements and facility upgrades implemented across the district. Watch the case study to see the impact of single-source accountability.';
        }
        $attrs = json_encode( compact( 'title', 'videoUrl', 'intro' ), JSON_UNESCAPED_SLASHES );
        $t = esc_html( $title );
        $intro_esc = esc_html( $intro );
        $escaped_video_url = str_replace('&#038;', '&amp;', esc_url($videoUrl));
        return <<<BLOCK
<!-- wp:e3es/video-embed $attrs -->
<section class="wp-block-e3es-video-embed db-video-section"><h3 class="db-video-section__title">$t</h3><p class="db-video-section__intro">$intro_esc</p><div class="db-video-wrapper"><iframe src="$escaped_video_url" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen title="$t"></iframe></div></section>
<!-- /wp:e3es/video-embed -->
BLOCK;
    };

    // e3es/project-toc
    $project_toc = function( $links ) {
        $attrs = [];
        $inner_html_parts = [ '<span class="db-toc__label">Jump to project:</span>' ];
        $i = 0;
        foreach ( $links as $link ) {
            $idx = $i + 1;
            $attrs[ "link{$idx}Label" ] = $link['label'];
            $attrs[ "link{$idx}Href" ] = $link['href'];
            if ( $i > 0 ) {
                $inner_html_parts[] = '<span class="db-toc__divider">|</span>';
            }
            $inner_html_parts[] = '<a href="' . esc_attr( $link['href'] ) . '" class="db-toc__link">' . esc_html( $link['label'] ) . '</a>';
            $i++;
        }
        for ( $idx = $i + 1; $idx <= 4; $idx++ ) {
            $attrs[ "link{$idx}Label" ] = '';
            $attrs[ "link{$idx}Href" ] = '';
        }
        $json = json_encode( $attrs, JSON_UNESCAPED_SLASHES );
        $inner = implode( '', $inner_html_parts );
        return <<<BLOCK
<!-- wp:e3es/project-toc $json -->
<nav class="wp-block-e3es-project-toc db-toc" aria-label="Table of Contents">$inner</nav>
<!-- /wp:e3es/project-toc -->
BLOCK;
    };

    // e3es/project
    $project_start = function( $sectionId, $eyebrow, $title, $heroImageUrl, $focalPointX = 0.5, $focalPointY = 0.5 ) {
        $attrArr = compact( 'sectionId', 'eyebrow', 'title', 'heroImageUrl', 'focalPointX', 'focalPointY' );
        $attrs = json_encode( $attrArr, JSON_UNESCAPED_SLASHES );
        $e = esc_html( $eyebrow );
        $t = $title; // May contain &amp; entities
        $id_attr = $sectionId ? ' id="' . esc_attr( $sectionId ) . '"' : '';
        
        $hero_html = '';
        if ( $heroImageUrl ) {
            $px = $focalPointX * 100;
            $py = $focalPointY * 100;
            $hero_html = '<div class="project-section__hero">' .
                '<img src="' . esc_url($heroImageUrl) . '" alt="' . esc_attr($title) . '" class="project-section__hero-img" style="object-position:' . $px . '% ' . $py . '%"/>' .
                '<div class="project-section__mask project-section__mask--left"></div>' .
                '<div class="project-section__mask project-section__mask--right"></div>' .
                '</div>';
        }
        
        $style_attr = ' style="--hero-img:' . ($heroImageUrl ? 'url(' . esc_url($heroImageUrl) . ')' : 'none') . '"';
        return <<<BLOCK
<!-- wp:e3es/project $attrs -->
<div class="wp-block-e3es-project project-section"{$id_attr}{$style_attr}><div class="project-section__header">{$hero_html}<div class="project-section__info"><span class="project-section__eyebrow">{$e}</span><h2 class="project-section__title">{$t}</h2></div></div><div class="project-section__content">
BLOCK;
    };

    $project_end = function() {
        return <<<BLOCK
</div></div>
<!-- /wp:e3es/project -->
BLOCK;
    };

    // e3es/project-details
    $project_details = function( $items ) {
        $attrs = [];
        $inner_parts = [];
        for ( $i = 0; $i < 4; $i++ ) {
            $idx = $i + 1;
            if ( isset( $items[$i] ) ) {
                $attrs[ "label{$idx}" ] = $items[$i]['label'];
                $attrs[ "value{$idx}" ] = $items[$i]['value'];
                $inner_parts[] = '<div class="project-details__item"><span class="project-details__label">' . esc_html( $items[$i]['label'] ) . '</span><span class="project-details__value">' . esc_html( $items[$i]['value'] ) . '</span></div>';
            } else {
                $attrs[ "label{$idx}" ] = '';
                $attrs[ "value{$idx}" ] = '';
            }
        }
        $json = json_encode( $attrs, JSON_UNESCAPED_SLASHES );
        $inner = implode( '', $inner_parts );
        return <<<BLOCK
<!-- wp:e3es/project-details $json -->
<div class="wp-block-e3es-project-details project-details">$inner</div>
<!-- /wp:e3es/project-details -->
BLOCK;
    };

    // e3es/mini-testimonial
    $testimonial = function( $quote, $cite, $photoUrl = '' ) {
        $attrs_arr = [
            'mode'          => 'manual',
            'testimonialId' => 0,
            'quote'         => $quote,
            'cite'          => $cite,
            'photoUrl'      => $photoUrl
        ];
        $attrs = json_encode( $attrs_arr, JSON_UNESCAPED_SLASHES );
        return "<!-- wp:e3es/mini-testimonial " . $attrs . " /-->";
    };

    // e3es/cta-banner
    $cta = function( $title, $text, $btnText, $btnUrl ) {
        $attrs = json_encode( compact( 'title', 'text', 'btnText', 'btnUrl' ), JSON_UNESCAPED_SLASHES );
        $t = esc_html( $title );
        $tx = esc_html( $text );
        $bt = esc_html( $btnText );
        $bu = esc_url( $btnUrl );
        $btn_html = $bt ? '<a href="' . $bu . '" class="btn btn--primary cta-banner__btn">' . $bt . '</a>' : '';
        return <<<BLOCK
<!-- wp:e3es/cta-banner $attrs -->
<section class="wp-block-e3es-cta-banner cta-banner"><div class="cta-banner__container"><h2 class="cta-banner__title">$t</h2><p class="cta-banner__text">$tx</p>$btn_html</div></section>
<!-- /wp:e3es/cta-banner -->
BLOCK;
    };

    // e3es/before-after
    $before_after = function( $title, $beforeImageUrl, $beforeLabel, $afterImageUrl, $afterLabel ) {
        $attrs = json_encode( compact( 'title', 'beforeImageUrl', 'beforeLabel', 'afterImageUrl', 'afterLabel' ), JSON_UNESCAPED_SLASHES );
        $t = esc_html( $title );
        $bl = esc_html( $beforeLabel );
        $al = esc_html( $afterLabel );
        $before_img_html = $beforeImageUrl ? '<img src="' . esc_url($beforeImageUrl) . '" alt="' . $bl . '" class="db-comparison__img">' : '';
        $after_img_html = $afterImageUrl ? '<img src="' . esc_url($afterImageUrl) . '" alt="' . $al . '" class="db-comparison__img">' : '';
        return <<<BLOCK
<!-- wp:e3es/before-after $attrs -->
<div class="wp-block-e3es-before-after db-comparison"><h4 class="db-comparison__title">$t</h4><div class="db-comparison__grid"><div class="db-comparison__card"><span class="db-comparison__label">$bl</span>$before_img_html</div><div class="db-comparison__card"><span class="db-comparison__label">$al</span>$after_img_html</div></div></div>
<!-- /wp:e3es/before-after -->
BLOCK;
    };


    // Native core blocks
    $heading = function( $text, $level = 2 ) {
        $tag = 'h' . $level;
        $json = json_encode( [ 'level' => $level ], JSON_UNESCAPED_SLASHES );
        return <<<BLOCK
<!-- wp:heading $json -->
<$tag class="wp-block-heading">$text</$tag>
<!-- /wp:heading -->
BLOCK;
    };

    $para = function( $text ) {
        return "<!-- wp:paragraph -->\n<p>$text</p>\n<!-- /wp:paragraph -->";
    };

    $image = function( $url, $alt ) {
        $attrs = json_encode( [ 'url' => $url, 'alt' => $alt ], JSON_UNESCAPED_SLASHES );
        $a = esc_attr( $alt );
        return "<!-- wp:image $attrs -->\n<figure class=\"wp-block-image\"><img src=\"$url\" alt=\"$a\"></figure>\n<!-- /wp:image -->";
    };

    $list = function( $items ) {
        $inner = '';
        foreach ( $items as $item ) {
            $inner .= "<!-- wp:list-item -->\n<li>$item</li>\n<!-- /wp:list-item -->\n";
        }
        return "<!-- wp:list -->\n<ul class=\"wp-block-list\">\n$inner</ul>\n<!-- /wp:list -->";
    };

    $separator = function() {
        return "<!-- wp:separator -->\n<hr class=\"wp-block-separator has-alpha-channel-opacity\"/>\n<!-- /wp:separator -->";
    };

    // core/gallery (native) — replaces e3es/project-gallery
    $project_gallery = function( $title, $images, $level = 3 ) use ( $heading ) {
        $cols = min( count( $images ), 4 );
        $inner = '';
        foreach ( $images as $img ) {
            $iattrs = json_encode( [ 'url' => $img['url'], 'alt' => $img['alt'] ], JSON_UNESCAPED_SLASHES );
            $a = esc_attr( $img['alt'] );
            $inner .= "<!-- wp:image $iattrs -->\n<figure class=\"wp-block-image\"><img src=\"{$img['url']}\" alt=\"$a\"></figure>\n<!-- /wp:image -->\n\n";
        }
        $gattrs = json_encode( [ 'linkTo' => 'none', 'columns' => $cols ], JSON_UNESCAPED_SLASHES );
        $gallery = "<!-- wp:gallery $gattrs -->\n<figure class=\"wp-block-gallery has-nested-images columns-$cols is-cropped\">\n{$inner}</figure>\n<!-- /wp:gallery -->";
        return $heading( $title, $level ) . "\n\n" . $gallery;
    };

    // core/group wrapper (replaces core/html for divs)
    $group_start = function( $className = '', $anchor = '' ) {
        $attrs = [];
        if ( $className ) $attrs['className'] = $className;
        if ( $anchor ) $attrs['anchor'] = $anchor;
        $attrs['layout'] = [ 'type' => 'constrained', 'contentSize' => '1400px' ];
        $json = json_encode( $attrs, JSON_UNESCAPED_SLASHES );
        $cls = $className ? "wp-block-group $className" : 'wp-block-group';
        return "<!-- wp:group $json -->\n<div class=\"$cls\">";
    };

    $group_end = function() {
        return "</div>\n<!-- /wp:group -->";
    };

    // ─── Project Section Builder ──────────────────────────────────
    $build_project = function( $config ) use (
        $project_start, $project_end, $project_details, $para, $heading, $list,
        $image, $testimonial, $before_after, $project_gallery
    ) {
        $blocks = [];

        // Wrap in e3es/project instead of core/group + e3es/project-header
        $blocks[] = $project_start(
            $config['id'],
            $config['eyebrow'],
            $config['title'],
            $config['heroImg']
        );

        // Project Details Grid
        $blocks[] = $project_details( $config['details'] );

        // Description paragraphs
        foreach ( $config['description'] as $p ) {
            $blocks[] = $para( $p );
        }

        // Key deliverables list
        if ( ! empty( $config['deliverables_title'] ) ) {
            $blocks[] = $heading( $config['deliverables_title'], 3 );
            $blocks[] = $list( $config['deliverables'] );
        }

        // Sidebar image
        if ( ! empty( $config['sidebarImg'] ) ) {
            $blocks[] = $image( $config['sidebarImg'], $config['sidebarAlt'] ?? $config['title'] );
        }

        // Video embed
        if ( ! empty( $config['videoId'] ) ) {
            $blocks[] = $heading( $config['videoTitle'] ?? 'Project Video', 3 );
            $vimeo_url = 'https://player.vimeo.com/video/' . $config['videoId'] . '?badge=0&autopause=0&player_id=0&app_id=58479';
            // Use core/embed for the video
            $embed_url = 'https://vimeo.com/' . $config['videoId'];
            $embed_attrs = json_encode( [
                'url' => $embed_url,
                'type' => 'video',
                'providerNameSlug' => 'vimeo',
                'responsive' => true,
                'className' => 'wp-embed-aspect-16-9 wp-has-aspect-ratio'
            ], JSON_UNESCAPED_SLASHES );
            $blocks[] = "<!-- wp:embed $embed_attrs -->\n<figure class=\"wp-block-embed is-type-video is-provider-vimeo wp-block-embed-vimeo wp-embed-aspect-16-9 wp-has-aspect-ratio\"><div class=\"wp-block-embed__wrapper\">\n$embed_url\n</div></figure>\n<!-- /wp:embed -->";
        }

        // Testimonial
        if ( ! empty( $config['quote'] ) ) {
            $blocks[] = $testimonial( $config['quote'], $config['cite'] );
        }

        // Before/After comparison
        if ( ! empty( $config['beforeImg'] ) ) {
            $blocks[] = $before_after(
                'Before & After comparison',
                $config['beforeImg'], 'Before',
                $config['afterImg'], 'After'
            );
        }

        // Project gallery
        if ( ! empty( $config['gallery'] ) ) {
            $blocks[] = $project_gallery( 'Project Gallery', $config['gallery'] );
        }

        // Close e3es/project block
        $blocks[] = $project_end();

        return implode( "\n\n", $blocks );
    };

    // ─── Build Boyd ISD Content ───────────────────────────────────
    $boyd_content = implode( "\n\n", [

        // Case study video
        $video_embed( 'Boyd ISD Case Study Video', 'https://player.vimeo.com/video/1179578579?badge=0&autopause=0&player_id=0&app_id=58479' ),

        // Table of contents
        $project_toc([
            [ 'label' => 'HVAC & Controls Upgrades', 'href' => '#project-hvac' ],
            [ 'label' => 'LED Sports Lighting', 'href' => '#project-lighting' ],
        ]),

        // Project 1: HVAC
        $build_project([
            'id'          => 'project-hvac',
            'eyebrow'     => 'Project 1',
            'title'       => 'Districtwide HVAC &amp; Controls Upgrades',
            'heroImg'     => $resolve( 'hvac.png' ),
            'details'     => [
                [ 'label' => 'Project Scope', 'value' => 'HVAC, Controls, Commissioning' ],
                [ 'label' => 'Contract Amount', 'value' => '$3,156,127' ],
                [ 'label' => 'Funding Source', 'value' => 'Bond 2024' ],
                [ 'label' => 'Year', 'value' => 'Completed 2024' ],
            ],
            'description' => [
                'Boyd ISD selected E3 Entegral Solutions as its design-build partner for a comprehensive HVAC replacement program across all district campuses. Following a competitive qualifications-based selection process, E3 was awarded a $3,156,127 contract to address aging mechanical equipment and improve energy efficiency throughout the district’s facilities.',
                'E3 conducted a complete facilities assessment across Boyd ISD’s five campuses: Boyd Elementary, Boyd Intermediate, Boyd Middle School, Boyd High School, and district auxiliary facilities, evaluating equipment condition, life-cycle status, and energy management opportunities. The resulting design-build scope was developed to minimize academic disruption while delivering long-term operational savings to the district.'
            ],
            'deliverables_title' => 'The Comprehensive Project Included:',
            'deliverables' => [
                'Full HVAC life-cycle replacements districtwide',
                'Districtwide energy management system evaluation and optimization',
                'Turnkey engineering design and construction management',
                'Commissioning of all installed systems',
                'Performance management and M&V services'
            ],
            'sidebarImg'  => $resolve_flickr( 'e3_best_of', 'e3-best-of_rio-hondo-constructionrio-hondo-after-dsc05778_51983014624.jpg' ),
            'sidebarAlt'  => 'Boyd ISD School Facility HVAC Installation',
            'videoId'     => '1065940484',
            'videoTitle'  => 'Project Video: HVAC Upgrades in Action',
            'quote'       => "E3's phased mechanical upgrades were executed with minimal disruption to our classrooms. The energy management system gives us total control over our energy use.",
            'cite'        => '- Tom Woody, Superintendent',
            'gallery'     => [
                [ 'url' => $resolve( 'hvac.png' ), 'alt' => 'HVAC Equipment Replacement' ],
                [ 'url' => $resolve( '51551511268_f74e3b930e_k.jpg' ), 'alt' => 'Controls Panel Installation' ],
                [ 'url' => $resolve( '51554219241_702ee4717f_k.jpg' ), 'alt' => 'Completed mechanical room' ],
                [ 'url' => $resolve( '54845433725_c85916126a_k.jpg' ), 'alt' => 'Roof RTU placement' ],
            ],
        ]),

        $heading( 'Procurement &amp; Funding', 3 ),
        $para( 'The project was funded through Bond 2024 proceeds and procured through a competitive qualifications-based selection process. E3’s selection was based on demonstrated qualifications, relevant Texas K-12 project experience, and depth of technical team consistent with the district’s commitment to responsible stewardship of public funds.' ),

        // Project 2: LED
        $build_project([
            'id'          => 'project-lighting',
            'eyebrow'     => 'Project 2',
            'title'       => 'LED Sports Lighting &amp; Auxiliary Upgrades',
            'heroImg'     => $resolve( 'led.jpg' ),
            'details'     => [
                [ 'label' => 'Project Scope', 'value' => 'LED Sports Lighting, Auditing' ],
                [ 'label' => 'Contract Amount', 'value' => '$650,000 (Estimated)' ],
                [ 'label' => 'Funding Source', 'value' => 'ESPC' ],
                [ 'label' => 'Year', 'value' => 'Completed 2025' ],
            ],
            'description' => [
                'Following the success of the mechanical upgrades, Boyd ISD re-engaged E3 to install energy-efficient LED sports lighting at the high school football stadium, baseball field, and tennis courts. By switching to high-output, directional LED fixtures, the district dramatically improved spectator and player visibility while cutting athletic utility costs by 45%.',
            ],
            'quote'       => 'The new lighting not only improved visibility but dramatically reduced our utility bills. We realized our annual savings estimate in just over eight months of tracking.',
            'cite'        => '- Texas School District',
            'gallery'     => [
                [ 'url' => $resolve( 'led.jpg' ), 'alt' => 'Stadium LED Lighting' ],
                [ 'url' => $resolve( '51670228367_9daa14b611_k.jpg' ), 'alt' => 'LED Classroom Fixture' ],
                [ 'url' => $resolve( '51671231498_f84028afe5_k.jpg' ), 'alt' => 'Tennis Court Lighting' ],
                [ 'url' => $resolve( '53969622794_b49535a782_k.jpg' ), 'alt' => 'Hallway Lighting Retrofit' ],
            ],
        ]),

        // Project Documentation Section
        $separator(),
        $heading( 'Project Documentation', 3 ),
        $para( 'These archive photos document the progress of renovations at Boyd ISD, capturing the before conditions, the construction phase, and final completed systems.' ),

        // Boyd Before Album
        $project_gallery( 'Boyd ISD - Before Upgrades (Archive)',
            array_values( array_map( function( $f ) use ( $resolve_flickr ) {
                return [ 'url' => $resolve_flickr( 'boyd_before', $f ), 'alt' => 'Boyd ISD before upgrades' ];
            }, array_slice( array_filter( scandir( '/Users/bryanpaul/Local Sites/astro-e3es/public/images/flickr/boyd_before' ), function( $f ) { return pathinfo( $f, PATHINFO_EXTENSION ) === 'jpg'; } ), 0, 8 ) ) ),
            4
        ),

        // Boyd After Album
        $project_gallery( 'Boyd ISD - Completed Projects (Archive)',
            array_values( array_map( function( $f ) use ( $resolve_flickr ) {
                return [ 'url' => $resolve_flickr( 'boyd_after', $f ), 'alt' => 'Boyd ISD completed projects' ];
            }, array_slice( array_filter( scandir( '/Users/bryanpaul/Local Sites/astro-e3es/public/images/flickr/boyd_after' ), function( $f ) { return pathinfo( $f, PATHINFO_EXTENSION ) === 'jpg'; } ), 0, 8 ) ) ),
            4
        ),

        // Boyd Construction Album
        $project_gallery( 'Boyd ISD - Construction Phase (Archive)',
            array_values( array_map( function( $f ) use ( $resolve_flickr ) {
                return [ 'url' => $resolve_flickr( 'boyd_construction', $f ), 'alt' => 'Boyd ISD construction phase' ];
            }, array_slice( array_filter( scandir( '/Users/bryanpaul/Local Sites/astro-e3es/public/images/flickr/boyd_construction' ), function( $f ) { return pathinfo( $f, PATHINFO_EXTENSION ) === 'jpg'; } ), 0, 8 ) ) ),
            4
        ),

    ]);

    // ─── Build Bishop CISD Content ────────────────────────────────
    $bishop_content = implode( "\n\n", [

        $project_toc([
            [ 'label' => 'Mechanical Upgrades', 'href' => '#project-mechanical' ],
            [ 'label' => 'LED Lighting Retrofits', 'href' => '#project-lighting' ],
        ]),

        // Project 1: Mechanical
        $build_project([
            'id'          => 'project-mechanical',
            'eyebrow'     => 'Project 1',
            'title'       => 'Luehrs Junior High &amp; Badger Den Mechanical Upgrades',
            'heroImg'     => $resolve( '54474213788_147e72a636_k.jpg' ),
            'details'     => [
                [ 'label' => 'Project Scope', 'value' => 'Packaged RTUs, Natural Gas, Electric Upgrades' ],
                [ 'label' => 'Contract Value', 'value' => '$1,036,716 (Total Program)' ],
                [ 'label' => 'Contract Type', 'value' => 'Design-Build' ],
                [ 'label' => 'Year', 'value' => 'Completed 2018' ],
            ],
            'description' => [
                'E3 designed and installed 18 high-efficiency packaged rooftop units (RTUs) with natural gas furnaces at the Luehrs Junior High campus. The scope of work also included providing new electrical service, structural support, routing and insulating condensate drain lines, and integrating existing controls.',
                'In addition, E3 replaced two older RTUs on the Badger Den facilities, increasing cooling capacity from 5 to 6 tons to improve indoor climate control and ventilation, along with installing additional supply vents.',
            ],
            'sidebarImg'  => $resolve_flickr( 'e3_best_of', 'e3-best-of_rio-hondo-construction-dsc05586_52177149681.jpg' ),
            'sidebarAlt'  => 'Bishop CISD School Facility Construction',
            'quote'       => "Installing the packaged RTUs resolved our cooling issues at Luehrs Junior High. E3 managed the helicopter lifts and electrical setup seamlessly.",
            'cite'        => '- Manny Tamez, Director of Finance',
            'gallery'     => [
                [ 'url' => $resolve( '54474213788_147e72a636_k.jpg' ), 'alt' => 'Rooftop HVAC Installation' ],
                [ 'url' => $resolve( '54811959968_9493f28880_k.jpg' ), 'alt' => 'New Condensate Piping' ],
                [ 'url' => $resolve( 'hvac.png' ), 'alt' => 'Packaged Gas RTUs' ],
                [ 'url' => $resolve( '51551255896_f97e3d97b1_k.jpg' ), 'alt' => 'Helicopter Lift Prep' ],
            ],
        ]),

        // Project 2: LED Lighting
        $build_project([
            'id'          => 'project-lighting',
            'eyebrow'     => 'Project 2',
            'title'       => 'Districtwide LED Lighting Retrofits',
            'heroImg'     => $resolve( 'led.jpg' ),
            'details'     => [
                [ 'label' => 'Project Scope', 'value' => '5,212 LED Fixtures, Energy Audit' ],
                [ 'label' => 'Guaranteed Savings', 'value' => '$90,824 Annual Savings' ],
                [ 'label' => 'Contract Type', 'value' => 'Guaranteed Savings Performance Contract' ],
            ],
            'description' => [
                'E3 designed, supplied, and installed modern LED lamps and fixtures across all Bishop CISD buildings. Retrofitting a total of 5,212 interior and exterior fixtures resulted in improved lighting quality, lower heat output, reduced maintenance costs, and contractually guaranteed annual utility savings of $90,824.',
            ],
            'quote'       => 'The new lighting not only improved visibility but dramatically reduced our utility bills. We realized our annual savings estimate in just over eight months of tracking.',
            'cite'        => '- Texas School District',
            'gallery'     => [
                [ 'url' => $resolve( 'led.jpg' ), 'alt' => 'Gymnasium LED Fixtures' ],
                [ 'url' => $resolve( '51670228367_9daa14b611_k.jpg' ), 'alt' => 'Classroom LED Retrofit' ],
                [ 'url' => $resolve( '51671231498_f84028afe5_k.jpg' ), 'alt' => 'Exterior Canopy LED' ],
                [ 'url' => $resolve( '53969622794_b49535a782_k.jpg' ), 'alt' => 'Office Lighting Panel' ],
            ],
        ]),

    ]);

    // ─── Build Granbury ISD Content ───────────────────────────────
    $granbury_content = implode( "\n\n", [

        $intro_banner( 'Granbury ISD', $resolve( '54474213788_147e72a636_k.jpg' ), 0.85, 'green', 'flat', 0.5, 0.5, $resolve( 'Granbury_ISD.png' ), 'Hill Country', 'K-12 Schools' ),

        // Case study video
        $video_embed( 'Efficiency in Action: The Granbury ISD Transformation', 'https://player.vimeo.com/video/227283498?badge=0&autopause=0&player_id=0&app_id=58479', 'Through a comprehensive district-wide initiative, Granbury ISD transformed their campus facilities to optimize energy use and reduce operational costs. This case study video highlights the key upgrades, from modern lighting retrofits to advanced HVAC system replacements, that are driving long-term sustainability.' ),

        $project_toc([
            [ 'label' => 'HVAC Upgrades & Lighting Retrofits', 'href' => '#project-energy' ],
            [ 'label' => 'Water Conservation & Turf Upgrades', 'href' => '#project-water' ],
        ]),

        // Project 1: Energy
        $build_project([
            'id'          => 'project-energy',
            'eyebrow'     => 'Project 1',
            'title'       => 'HVAC Upgrades &amp; Lighting Retrofits',
            'heroImg'     => $resolve( '54474213788_147e72a636_k.jpg' ),
            'details'     => [
                [ 'label' => 'Project Scope', 'value' => '200+ HVAC replacements, T8 LED Retrofit' ],
                [ 'label' => 'Contract Value / Savings', 'value' => '$5,481,279 / $323,362 Actual Yr 1 Savings' ],
                [ 'label' => 'Contract Type', 'value' => 'Performance Contract' ],
                [ 'label' => 'Year', 'value' => 'Completed 2013' ],
            ],
            'description' => [
                'Granbury ISD was faced with many challenges as it related to deferred maintenance and energy efficiency needs and sought the partnership of E3 to craft a plan and financial strategy to address those needs. After exhaustive collaboration and planning, a project was designed to accomplish the following:',
                'E3 designed a program that replaced more than 200 HVAC units older than 20 years and completed a districtwide T8 lighting retrofit with controls.',
                'To minimize disruption to summer classes and normal district workflows, construction was tightly coordinated. This included a notable single-day helicopter lift to place 160 HVAC units across rooftops, allowing summer school and facility cleaning to proceed on schedule. Actual energy savings exceeded expectations, delivering $323,362 in Year 1 ($41,500 over the guaranteed target).',
            ],
            'sidebarImg'  => $resolve_flickr( 'e3_best_of', 'e3-best-of_rio-hondo-interior-lighting-before_51500902443.jpg' ),
            'sidebarAlt'  => 'Granbury ISD School Facility Chiller Installation',
            'quote'       => "E3 did everything they said they would. The audit was very thorough and the team walked us through every step and each recommendation. The installation was quick and seamless... E3 also worked well with our maintenance staff.",
            'cite'        => '- Dr. James Largent, Superintendent',
            'beforeImg'   => $resolve_flickr( 'e3_best_of', 'e3-best-of_rio-hondo-interior-lighting-before_51499860007.jpg' ),
            'afterImg'    => $resolve_flickr( 'e3_best_of', 'e3-best-of_rio-hondo-interior-lighting-after_51974381892.jpg' ),
            'gallery'     => [
                [ 'url' => $resolve( '54474213788_147e72a636_k.jpg' ), 'alt' => 'Helicopter HVAC Placement' ],
                [ 'url' => $resolve( 'hvac.png' ), 'alt' => 'Completed mechanical room' ],
                [ 'url' => $resolve( 'led.jpg' ), 'alt' => 'Stadium LED Retrofit' ],
                [ 'url' => $resolve( '51670228367_9daa14b611_k.jpg' ), 'alt' => 'Outdoor School Lighting' ],
            ],
        ]),

        // Project 2: Water
        $build_project([
            'id'          => 'project-water',
            'eyebrow'     => 'Project 2',
            'title'       => 'District-wide Water Conservation &amp; Turf Upgrades',
            'heroImg'     => $resolve( 'water.jpg' ),
            'details'     => [
                [ 'label' => 'Project Scope', 'value' => 'Water Conservation Retrofits, Field Turf' ],
                [ 'label' => 'Contract Value', 'value' => 'Included in Program' ],
                [ 'label' => 'Contract Type', 'value' => 'Performance Contract' ],
            ],
            'description' => [
                "To address the district's high water bills, E3 designed and performed a comprehensive water conservation retrofit, upgrading restroom plumbing and faucets across campuses. To deliver additional water savings and eliminate intensive maintenance requirements, E3 coordinated the installation of low-maintenance synthetic field turf on the varsity athletic fields.",
            ],
            'quote'       => 'The water conservation retrofits and new synthetic field turf significantly reduced our utility bills and field maintenance hours. A great investment for the district.',
            'cite'        => '- Randy Leach, Director of Facilities',
            'gallery'     => [
                [ 'url' => $resolve( 'water.jpg' ), 'alt' => 'Water Conservation Installation' ],
                [ 'url' => $resolve( '51551511268_f74e3b930e_k.jpg' ), 'alt' => 'Controls and Plumbing panel' ],
                [ 'url' => $resolve( '51554219241_702ee4717f_k.jpg' ), 'alt' => 'Pressure regulators' ],
                [ 'url' => $resolve( '53969622794_b49535a782_k.jpg' ), 'alt' => 'Newly installed field turf' ],
            ],
        ]),

    ]);

    // ─── Placeholder Content ──────────────────────────────────────
    $placeholder = function( $name, $region, $description ) use ( $para, $testimonial, $cta ) {
        return implode( "\n\n", [
            $para( $description ),
            $testimonial(
                "E3 has been an outstanding partner for $name. Their team delivered results that exceeded our expectations.",
                "- $name Administration"
            ),
            $cta( 'Ready to Transform Your Facilities?', "Contact E3 to learn how we can help $name achieve energy efficiency and operational savings.", 'Get Started', '/contact' ),
        ]);
    };

    $donna_content = $placeholder( 'Donna ISD', 'South Texas',
        'Donna ISD partnered with E3 Entegral Solutions for comprehensive facility improvements including HVAC upgrades, LED lighting retrofits, and energy management system integration across multiple campuses in South Texas.'
    );
    $bryan_content = $placeholder( 'Bryan ISD', 'Central Texas',
        'Bryan ISD engaged E3 Entegral Solutions for a districtwide performance contract encompassing mechanical systems upgrades, interior and exterior lighting retrofits, and building automation improvements serving the Central Texas community.'
    );
    $caldwell_content = $placeholder( 'Caldwell ISD', 'Central Texas',
        'Caldwell ISD collaborated with E3 Entegral Solutions on energy conservation measures including HVAC replacements, LED lighting installations, and water conservation retrofits to modernize district facilities.'
    );
    $carrizo_content = $placeholder( 'Carrizo Springs CISD', 'South Texas',
        'Carrizo Springs CISD partnered with E3 Entegral Solutions for comprehensive mechanical and lighting upgrades across the district, achieving significant energy savings and improved learning environments in South Texas.'
    );

    // ─── Build Keene ISD Content ──────────────────────────────────
    $keene_content = implode( "\n\n", [

        // Video embed
        $video_embed( 'Keene ISD, Sports Lighting', 'https://player.vimeo.com/video/1176712805?badge=0&autopause=0&player_id=0&app_id=58479', 'E3 partnered with Keene ISD to upgrade their athletic field lighting with a full RGB LED system, delivering dynamic lighting effects alongside improved visibility and safety.' ),

        // Project block
        $build_project([
            'id'          => 'project-lighting',
            'eyebrow'     => 'Project 1',
            'title'       => 'RGB LED Sports Lighting',
            'heroImg'     => '',
            'details'     => [
                [ 'label' => 'Project Scope', 'value' => 'LED Sports Lighting, Turnkey Installation' ],
                [ 'label' => 'Contract Type', 'value' => 'Design-Build' ],
                [ 'label' => 'Funding Source', 'value' => 'SECO Low-Interest Loan' ],
                [ 'label' => 'Year', 'value' => 'Completed 2026' ],
            ],
            'description' => [
                'E3 partnered with Keene ISD to upgrade their athletic field lighting with a full RGB LED system, delivering dynamic lighting effects alongside improved visibility and safety.',
                'The new system provides uniform field coverage while enabling custom colors and game-day experiences, all with reduced energy use and maintenance costs. From design through installation, E3 delivered a turnkey solution tailored to the district’s needs.',
            ]
        ]),

    ]);

    // ─── Build Plano ISD Content ──────────────────────────────────
    $plano_content = implode( "\n\n", [

        $video_embed( 'Lessons in Learning - Dr. Theresa Williams', 'https://player.vimeo.com/video/1007829512?badge=0&autopause=0&player_id=0&app_id=58479', 'Dr. Theresa Williams, Superintendent of Plano ISD, shares insights on leadership and learning to be comfortable with the uncomfortable.' ),

        $build_project([
            'id'          => 'project-leadership',
            'eyebrow'     => 'Case Study',
            'title'       => 'Lessons in Learning Interview',
            'heroImg'     => '',
            'details'     => [
                [ 'label' => 'Project Scope', 'value' => 'Leadership Interview, Video Case Study' ],
                [ 'label' => 'Speaker', 'value' => 'Dr. Theresa Williams' ],
                [ 'label' => 'Role', 'value' => 'Superintendent, Plano ISD' ],
                [ 'label' => 'Year', 'value' => '2024' ],
            ],
            'description' => [
                'Dr. Theresa Williams, Superintendent of Plano ISD, shares insights on leadership and learning to be comfortable with the uncomfortable. In this Lessons in Learning interview, she discusses setting goals, working through exhaustion to accomplish achievements, and continuous improvement in educational leadership.',
            ],
            'quote'       => "Learning to be comfortable with the uncomfortable is pretty much the norm... Leadership is like training for a marathon: you work really hard, it's exhausting, but you accomplish your goals and then ask, 'what is the next race?'",
            'cite'        => '- Dr. Theresa Williams, Superintendent'
        ]),

    ]);

    // ─── Build Little Elm ISD Content ──────────────────────────────
    $little_elm_content = implode( "\n\n", [

        $video_embed( 'Lessons In Learning - Mike Lamb', 'https://player.vimeo.com/video/946653874?badge=0&autopause=0&player_id=0&app_id=58479', 'Mike Lamb, Superintendent of Little Elm ISD, shares his journey of leadership and competition.' ),

        $build_project([
            'id'          => 'project-leadership',
            'eyebrow'     => 'Case Study',
            'title'       => 'Lessons in Learning Interview',
            'heroImg'     => '',
            'details'     => [
                [ 'label' => 'Project Scope', 'value' => 'Leadership Interview, Video Case Study' ],
                [ 'label' => 'Speaker', 'value' => 'Mike Lamb' ],
                [ 'label' => 'Role', 'value' => 'Superintendent, Little Elm ISD' ],
                [ 'label' => 'Year', 'value' => '2024' ],
            ],
            'description' => [
                'Mike Lamb, Superintendent of Little Elm ISD, shares his journey of leadership and competition. In this Lessons in Learning interview, he describes how facing and learning from failure shapes successful leadership, and how setting environments that challenge students promotes learning from both failure and success.',
            ],
            'quote'       => "You can't be afraid to fail; you learn so much from failure. We've got to create some uncomfortable scenarios where students and leaders learn as much from failure as they do from success.",
            'cite'        => '- Mike Lamb, Superintendent'
        ]),

    ]);

    // ─── Build City of Stockdale Content ──────────────────────────
    $stockdale_content = implode( "\n\n", [

        $video_embed( 'Stockdale Lagoon Restoration Case Study', 'https://player.vimeo.com/video/1171901749?badge=0&autopause=0&player_id=0&app_id=58479', "Stephen Mayfield, City Manager for the City of Stockdale, shares how the city partnered with E3 to restore their wastewater treatment lagoons." ),

        $build_project([
            'id'          => 'project-lagoon',
            'eyebrow'     => 'Project 1',
            'title'       => 'Lagoon Restoration &amp; Wastewater Treatment',
            'heroImg'     => '',
            'details'     => [
                [ 'label' => 'Project Scope', 'value' => 'Wastewater Treatment, Lagoon Restoration' ],
                [ 'label' => 'Contract Type', 'value' => 'Design-Build' ],
                [ 'label' => 'Speaker', 'value' => 'Stephen Mayfield' ],
                [ 'label' => 'Role', 'value' => 'City Manager, City of Stockdale' ],
            ],
            'description' => [
                "Stephen Mayfield, City Manager for the City of Stockdale, shares how E3 and Nano Gas partnered to restore the city's wastewater treatment lagoons. Facing severe sludge accumulation and capacity limits that threatened future residential development, Stockdale implemented a cost-effective alternative to mechanical dredging to restore lagoon capacity and efficiency.",
            ],
            'quote'       => "Working with the team has been great. They are professional and always come up with the right solutions to our problems. The lagoon restoration made a huge difference, returning the capacity we need for future development.",
            'cite'        => '- Stephen Mayfield, City Manager'
        ]),

    ]);

    // ─── Update Posts ─────────────────────────────────────────────
    $updates = [
        12   => [ 'Boyd ISD',              $boyd_content ],
        486  => [ 'Bishop CISD',           $bishop_content ],
        488  => [ 'Granbury ISD',          $granbury_content ],
        3874 => [ 'Keene ISD',            $keene_content ],
        3873 => [ 'Little Elm ISD',       $little_elm_content ],
        3875 => [ 'Plano ISD',            $plano_content ],
        3872 => [ 'City of Stockdale',    $stockdale_content ],
    ];

    // Disable kses filtering to preserve iframes and custom HTML
    kses_remove_filters();

    $get_dynamic_gallery_block = function( $post_id, $client_slug ) use ( $separator, $project_gallery ) {
        $attachments = get_posts([
            'post_type'      => 'attachment',
            'posts_per_page' => -1,
            'post_parent'    => $post_id,
            'post_mime_type' => 'image',
            'orderby'        => 'title',
            'order'          => 'ASC',
        ]);
        
        if ( empty( $attachments ) ) {
            return '';
        }
        
        $images = [];
        foreach ( $attachments as $att ) {
            $url = wp_get_attachment_url( $att->ID );
            $lower_url = strtolower( $url );
            if ( strpos( $lower_url, 'logo' ) !== false || 
                 strpos( $lower_url, 'client_logo' ) !== false || 
                 strpos( $lower_url, '150x150' ) !== false ||
                 strpos( $lower_url, '300x115' ) !== false ) {
                continue;
            }
            
            $alt = get_post_meta( $att->ID, '_wp_attachment_image_alt', true );
            if ( ! $alt ) {
                $alt = $att->post_title;
            }
            $images[] = [
                'url' => $url,
                'alt' => $alt,
            ];
        }
        
        if ( empty( $images ) ) {
            return '';
        }
        
        $title = "Project Documentation (Archive)";
        return "\n\n" . $separator() . "\n\n" . $project_gallery( $title, $images );
    };

    foreach ( $updates as $post_id => $data ) {
        list( $name, $content ) = $data;

        $post = get_post( $post_id );
        $slug = $post ? $post->post_name : '';

        if ( $slug !== 'boyd-isd' ) {
            $gallery = $get_dynamic_gallery_block( $post_id, $slug );
            if ( $gallery ) {
                $content .= $gallery;
            }
        }

        // Verify zero core/html blocks
        $html_count = substr_count( $content, '<!-- wp:html' );
        if ( $html_count > 0 ) {
            echo "[WARNING] $name contains $html_count core/html blocks!\n";
        }

        $result = wp_update_post( [
            'ID'           => $post_id,
            'post_content' => wp_slash( $content ),
        ], true );

        if ( is_wp_error( $result ) ) {
            echo "[ERROR] $name (ID $post_id): " . $result->get_error_message() . "\n";
        } else {
            $block_count = substr_count( $content, '<!-- wp:' );
            echo "[OK] $name (ID $post_id): $block_count blocks, 0 custom HTML.\n";
        }
    }

    // Re-enable kses filtering
    kses_init_filters();

    echo "\n=== Block Seeding Complete (v2 - No Custom HTML) ===\n";
    exit;
}
