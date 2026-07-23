<?php
/**
 * E3ES Comprehensive Services Seeder (Parent and Child Pages)
 * 
 * Rebuilds all parent and child service posts using custom Gutenberg blocks
 * populated with copy parsed from the Markdown files under:
 *   E3/docs/services-copy/
 * 
 * Run via Local PHP:
 *   "/Users/bryanpaul/Library/Application Support/Local/lightning-services/php-8.2.29+0/bin/darwin-arm64/bin/php" \
 *     "/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-content/plugins/e3es-headless-helper/seed-all-services.php"
 */

// ── Bootstrap WordPress ──────────────────────────────────────────────────────
$wp_load = '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';
if ( ! file_exists( $wp_load ) ) {
    $wp_load = dirname( __FILE__ ) . '/../../../../wp-load.php';
}
if ( ! file_exists( $wp_load ) ) {
    die( "Cannot find wp-load.php.\n" );
}
define( 'ABSPATH_SKIP_REDIRECT', true );
require_once $wp_load;

// Set current user to admin and remove KSES filters
wp_set_current_user( 1 );
if ( function_exists( 'kses_remove_filters' ) ) {
    kses_remove_filters();
}

echo "\n🌱 E3ES Comprehensive Services Seeder Starting...\n\n";

global $wpdb;

// ── 1. Database Slug Cleanup and Swaps ───────────────────────────────────────
echo "🧹 Performing slug cleanup and canonicalization...\n";

// A. LED Lighting parent page: canonical ID should be 1112 (slug 'lighting')
// ID 739 is 'led-lighting'. We will swap ID 739 to slug 'led-lighting-old' and ID 1112 to slug 'lighting'.
$wpdb->query( "UPDATE {$wpdb->posts} SET post_name = 'led-lighting-old' WHERE ID = 739" );
$wpdb->query( "UPDATE {$wpdb->posts} SET post_name = 'lighting' WHERE ID = 1112" );

// B. HVAC parent page: canonical ID should be 1111 (slug 'hvac-2') or 102 (slug 'hvac')
// Let's use ID 102 as the top-level parent HVAC service (slug 'hvac').
// Let's make sure ID 102 has slug 'hvac' and parent 0.
$wpdb->query( "UPDATE {$wpdb->posts} SET post_name = 'hvac' WHERE ID = 102" );
$wpdb->query( "UPDATE {$wpdb->posts} SET post_parent = 0 WHERE ID = 102" );

// Rename any other top-level 'hvac' posts to avoid conflicts
$wpdb->query( "UPDATE {$wpdb->posts} SET post_name = 'hvac-old-1131' WHERE ID = 1131" );
$wpdb->query( "UPDATE {$wpdb->posts} SET post_name = 'hvac-old-1145' WHERE ID = 1145" );
$wpdb->query( "UPDATE {$wpdb->posts} SET post_name = 'hvac-old-1159' WHERE ID = 1159" );
$wpdb->query( "UPDATE {$wpdb->posts} SET post_name = 'hvac-old-1165' WHERE ID = 1165" );

// C. Controls & Automation parent page: canonical ID should be 1637 (slug 'controls-automation')
$wpdb->query( "UPDATE {$wpdb->posts} SET post_name = 'controls-automation', post_parent = 0 WHERE ID = 1637" );

// E. Water & Wastewater parent page: canonical ID should be 1636 (slug 'water-wastewater')
$wpdb->query( "UPDATE {$wpdb->posts} SET post_name = 'water-wastewater', post_parent = 0 WHERE ID = 1636" );

// F. Financing & Auditing parent page: canonical ID should be 1638 (slug 'financing-auditing')
$wpdb->query( "UPDATE {$wpdb->posts} SET post_name = 'financing-auditing', post_parent = 0 WHERE ID = 1638" );

// G. Trash temporary/duplicate parent pages generated during previous testing to avoid REST API pollution
$wpdb->query( "UPDATE {$wpdb->posts} SET post_status = 'trash' WHERE ID IN (1639, 1641)" );

echo "  ✅ Cleaned up conflicting slugs and set top-level parents.\n";

// ── 2. Image Import Helper ──────────────────────────────────────────────────
function e3_get_or_import_media( $filename ) {
    global $wpdb;
    
    // Check if attachment already exists in the library
    $attachment_id = $wpdb->get_var( $wpdb->prepare(
        "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s",
        '%' . $filename
    ) );
    
    if ( $attachment_id ) {
        return wp_get_attachment_url( $attachment_id );
    }
    
    // Path to legacy-html flickr assets
    $src_path = "/Users/bryanpaul/Dropbox/PaulDropbox/E3/website/legacy-html/images/flickr/" . $filename;
    if ( ! file_exists( $src_path ) ) {
        $src_path = "/Users/bryanpaul/Local Sites/astro-e3es/public/images/flickr/" . $filename;
    }
    
    if ( ! file_exists( $src_path ) ) {
        echo "  ⚠️ Source image not found: {$filename}. Using Taj Mahal placeholder.\n";
        return 'http://e3es2026.local/wp-content/uploads/2026/06/taj-mahal-placeholder.png';
    }
    
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    
    $tmp_file = wp_tempnam( $filename );
    copy( $src_path, $tmp_file );
    
    $file_array = array(
        'name'     => $filename,
        'tmp_name' => $tmp_file,
        'type'     => 'image/jpeg',
        'error'    => 0,
        'size'     => filesize( $tmp_file ),
    );
    
    $desc = ucwords( str_replace( array( '-', '_' ), ' ', pathinfo( $filename, PATHINFO_FILENAME ) ) );
    $id = media_handle_sideload( $file_array, 0, $desc );
    
    if ( is_wp_error( $id ) ) {
        echo "  ❌ Sideload failed for {$filename}: " . $id->get_error_message() . "\n";
        @unlink( $tmp_file );
        return '';
    }
    
    // Update mapping option
    $map = get_option( 'e3_media_url_map', array() );
    $url = wp_get_attachment_url( $id );
    $map[ $filename ] = $url;
    update_option( 'e3_media_url_map', $map );
    
    echo "  📸 Imported media: {$filename} -> ID #{$id}\n";
    return $url;
}

// ── 3. Gutenberg Block Helpers ───────────────────────────────────────────────
function e3es_make_two_column_block( $args ) {
    $defaults = array(
        'imageUrl'        => '',
        'imageAlt'        => '',
        'reverse'         => false,
        'bgStyle'         => 'white',
        'icon'            => '',
        'overlayHeadline' => '',
        'overlayText'     => '',
        'overlayBtnText'  => '',
        'overlayBtnUrl'   => '',
        'heading'         => '',
        'content'         => ''
    );
    $attr = array_merge( $defaults, $args );
    
    $attrs_json = json_encode( array(
        'imageUrl'        => $attr['imageUrl'],
        'imageAlt'        => $attr['imageAlt'],
        'reverse'         => $attr['reverse'],
        'bgStyle'         => $attr['bgStyle'],
        'icon'            => $attr['icon'],
        'overlayHeadline' => $attr['overlayHeadline'],
        'overlayText'     => $attr['overlayText'],
        'overlayBtnText'  => $attr['overlayBtnText'],
        'overlayBtnUrl'   => $attr['overlayBtnUrl']
    ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
    
    $reverse_class = $attr['reverse'] ? ' db-feature__container--reverse' : '';
    
    $overlay_html = '';
    if ( ! empty( $attr['overlayHeadline'] ) || ! empty( $attr['overlayText'] ) || ! empty( $attr['overlayBtnText'] ) ) {
        $headline_html = $attr['overlayHeadline'] ? '<h3 class="db-feature__overlay-headline">' . esc_html( $attr['overlayHeadline'] ) . '</h3>' : '';
        $text_html = $attr['overlayText'] ? '<p class="db-feature__overlay-text">' . esc_html( $attr['overlayText'] ) . '</p>' : '';
        $btn_html = ( $attr['overlayBtnText'] && $attr['overlayBtnUrl'] ) ? '<a href="' . esc_url( $attr['overlayBtnUrl'] ) . '" class="btn btn--outline-white db-feature__overlay-button">' . esc_html( $attr['overlayBtnText'] ) . '</a>' : '';
        
        $overlay_html = '<div class="db-feature__image-overlay"><div class="db-feature__overlay-content">' . $headline_html . $text_html . $btn_html . '</div></div>';
    }
    
    $content_blocks = $attr['content'];
    if ( strpos( $content_blocks, '<!-- wp:' ) === false ) {
        $content_blocks = '<!-- wp:paragraph -->' . "\n" . '<p>' . wp_kses_post( $attr['content'] ) . '</p>' . "\n" . '<!-- /wp:paragraph -->';
    }
    
    $heading_html = '<!-- wp:heading {"level":2} -->' . "\n" . '<h2>' . wp_kses_post( $attr['heading'] ) . '</h2>' . "\n" . '<!-- /wp:heading -->';
    
    return '<!-- wp:e3es/two-column ' . $attrs_json . ' -->
<section class="wp-block-e3es-two-column db-feature db-feature--' . esc_attr( $attr['bgStyle'] ) . '"><div class="db-feature__container' . $reverse_class . '"><div class="db-feature__content"><div class="db-feature__icon"></div>' . $heading_html . "\n" . $content_blocks . '</div><div class="db-feature__image-wrapper"><img src="' . esc_url( $attr['imageUrl'] ) . '" alt="' . esc_attr( $attr['imageAlt'] ) . '" class="db-feature__image">' . $overlay_html . '</div></div></section>
<!-- /wp:e3es/two-column -->';
}

function e3es_make_comparison_table( $headers, $rows ) {
    $tbody = '';
    foreach ( $rows as $row ) {
        $feature = $row[0];
        $trad = $row[1];
        $e3 = $row[2];
        
        $attrs = json_encode( array(
            'feature'     => $feature,
            'traditional' => $trad,
            'e3'          => $e3
        ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
        
        $tbody .= '    <!-- wp:e3es/comparison-row ' . $attrs . ' -->
    <tr class="wp-block-e3es-comparison-row"><th scope="row">' . esc_html( $feature ) . '</th><td>' . esc_html( $trad ) . '</td><td>' . esc_html( $e3 ) . '</td></tr><!-- /wp:e3es/comparison-row -->' . "\n";
    }
    
    return '<!-- wp:e3es/comparison-table -->
<section class="wp-block-e3es-comparison-table comparison-section"><div class="comparison-container"><table class="comparison-table"><thead><tr><th style="width:20%;border:none;background:transparent"></th><th style="width:40%">' . esc_html( $headers[1] ) . '</th><th style="width:40%">' . esc_html( $headers[2] ) . '</th></tr></thead><tbody>
' . $tbody . '</tbody></table></div></section>
<!-- /wp:e3es/comparison-table -->';
}

function e3es_make_faq_section( $faqs ) {
    $inner_html = '';
    foreach ( $faqs as $faq ) {
        $inner_html .= '<!-- wp:heading {"level":3} -->' . "\n";
        $inner_html .= '<h3>' . esc_html( $faq['q'] ) . '</h3>' . "\n";
        $inner_html .= '<!-- /wp:heading -->' . "\n";
        $inner_html .= '<!-- wp:paragraph -->' . "\n";
        $inner_html .= '<p>' . wp_kses_post( $faq['a'] ) . '</p>' . "\n";
        $inner_html .= '<!-- /wp:paragraph -->' . "\n\n";
    }
    
    return "<!-- wp:e3es/faq-section -->\n" . 
           "<section class=\"wp-block-e3es-faq-section faq-section\"><div class=\"faq-section__container\"><h2 class=\"faq-section__title\">Frequently Asked Questions</h2>" . 
           trim( $inner_html ) . 
           "</div></section>\n<!-- /wp:e3es/faq-section -->";
}

function e3es_make_cta_banner( $title, $text, $btnText, $btnUrl ) {
    $attrs = json_encode( array(
        'title'   => $title,
        'text'    => $text,
        'btnText' => $btnText,
        'btnUrl'  => $btnUrl
    ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
    
    return '<!-- wp:e3es/cta-banner ' . $attrs . ' -->
<section class="cta-banner"><div class="cta-banner__container"><h2 class="cta-banner__title">' . esc_html( $title ) . '</h2><p class="cta-banner__text">' . esc_html( $text ) . '</p><a href="' . esc_url( $btnUrl ) . '" class="btn btn--primary cta-banner__btn">' . esc_html( $btnText ) . '</a></div></section>
<!-- /wp:e3es/cta-banner -->';
}

// ── 4. Markdown Parser & HTML Helpers ─────────────────────────────────────────

function e3es_map_child_slug( $slug ) {
    $slug = basename( $slug, '.md' );
    if ( $slug === 'lighting' ) return 'lighting';
    if ( $slug === 'hvac' ) return 'hvac';
    if ( $slug === 'scada-tceq' ) return 'scada';
    if ( $slug === 'grant-procurement' ) return 'grant-and-funding-procurement';
    if ( $slug === 'decarbonization-solar-storage' ) return 'solar';
    if ( $slug === 'roofing-envelope' ) return 'roofing';
    if ( $slug === 'commissioning-rcx' ) return 'retro-commissioning';
    return $slug;
}

function e3es_markdown_to_html( $text, $parent_slug = '' ) {
    // Convert bold **text** to <strong>text</strong>
    $text = preg_replace( '/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $text );
    
    // Convert italic *text* to <em>text</em>
    $text = preg_replace( '/\*([^*]+)\*/', '<em>$1</em>', $text );
    
    // Convert local markdown links: [Link Title](target.md)
    $text = preg_replace_callback( '/\[([^\]]+)\]\(([^)]+)\.md\)/', function( $m ) use ( $parent_slug ) {
        $title = $m[1];
        $target_slug = e3es_map_child_slug( $m[2] );
        if ( $parent_slug ) {
            return '<a href="/services/' . $parent_slug . '/' . $target_slug . '">' . $title . '</a>';
        } else {
            return '<a href="/services/' . $target_slug . '">' . $title . '</a>';
        }
    }, $text );
    
    // Convert generic markdown links: [Link Title](url)
    $text = preg_replace( '/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $text );
    
    return $text;
}

function e3es_parse_markdown_blocks( $text, $parent_slug = '' ) {
    $lines = explode( "\n", $text );
    $blocks = array();
    
    $current_type = ''; // 'paragraph', 'list', 'table'
    $current_block_lines = array();
    
    $flush_block = function() use ( &$blocks, &$current_type, &$current_block_lines, $parent_slug ) {
        if ( empty( $current_block_lines ) ) return;
        
        $block_text = implode( "\n", $current_block_lines );
        $html = '';
        
        if ( $current_type === 'list' ) {
            $html = "<ul>\n";
            foreach ( $current_block_lines as $line ) {
                $line_content = preg_replace( '/^[\*\-\+]\s+/', '', trim( $line ) );
                $html .= "  <li>" . e3es_markdown_to_html( $line_content, $parent_slug ) . "</li>\n";
            }
            $html .= "</ul>";
        } elseif ( $current_type === 'paragraph' ) {
            $html = "<p>" . e3es_markdown_to_html( $block_text, $parent_slug ) . "</p>";
        } elseif ( $current_type === 'table' ) {
            $html = '<!-- table -->';
        }
        
        if ( ! empty( $html ) ) {
            $blocks[] = array(
                'type' => $current_type,
                'html' => $html,
                'raw'  => $block_text
            );
        }
        
        $current_block_lines = array();
        $current_type = '';
    };
    
    foreach ( $lines as $line ) {
        $trimmed = trim( $line );
        
        if ( $trimmed === '---' || strpos( $trimmed, 'Meta Description:' ) !== false || strpos( $trimmed, 'Keywords:' ) !== false ) {
            continue;
        }
        
        if ( empty( $trimmed ) ) {
            $flush_block();
            continue;
        }
        
        $is_list_item = ( preg_match( '/^[\*\-\+]\s+/', $trimmed ) );
        $is_table_line = ( $trimmed[0] === '|' );
        
        if ( $is_list_item ) {
            if ( $current_type !== 'list' ) {
                $flush_block();
                $current_type = 'list';
            }
            $current_block_lines[] = $line;
        } elseif ( $is_table_line ) {
            if ( $current_type !== 'table' ) {
                $flush_block();
                $current_type = 'table';
            }
            $current_block_lines[] = $line;
        } else {
            if ( $current_type !== 'paragraph' && $current_type !== '' ) {
                $flush_block();
            }
            $current_type = 'paragraph';
            $current_block_lines[] = $trimmed;
        }
    }
    
    $flush_block();
    return $blocks;
}

function e3es_parse_markdown_copy( $filepath, $parent_slug = '' ) {
    $content = @file_get_contents( $filepath );
    if ( ! $content ) return null;
    
    $content = str_replace( "\r\n", "\n", $content );
    
    $result = array(
        'title'              => '',
        'meta_desc'          => '',
        'primary_keywords'   => '',
        'secondary_keywords' => '',
        'intro_elements'     => array(),
        'h2_sections'        => array(),
        'faqs'               => array()
    );
    
    if ( preg_match( '/^#\s+(.+)$/m', $content, $m ) ) {
        $result['title'] = trim( $m[1] );
    }
    if ( preg_match( '/\*\*Meta Description:\*\*\s*(.+)$/m', $content, $m ) ) {
        $result['meta_desc'] = trim( $m[1] );
    }
    if ( preg_match( '/\*\*Primary Keywords:\*\*\s*(.+)$/m', $content, $m ) ) {
        $result['primary_keywords'] = trim( $m[1] );
    }
    if ( preg_match( '/\*\*Secondary Keywords:\*\*\s*(.+)$/m', $content, $m ) ) {
        $result['secondary_keywords'] = trim( $m[1] );
    }
    
    $parts = preg_split( '/^##\s+/m', $content );
    $intro_part = array_shift( $parts );
    $result['intro_elements'] = e3es_parse_markdown_blocks( $intro_part, $parent_slug );
    
    foreach ( $parts as $part ) {
        $lines = explode( "\n", $part );
        $section_title = trim( array_shift( $lines ) );
        $section_body = implode( "\n", $lines );
        
        $is_faq = ( stripos( $section_title, 'Frequently Asked Questions' ) !== false || stripos( $section_title, 'FAQ' ) !== false );
        
        if ( $is_faq ) {
            $faq_parts = preg_split( '/^###\s+/m', $section_body );
            array_shift( $faq_parts );
            foreach ( $faq_parts as $faq_part ) {
                $faq_lines = explode( "\n", $faq_part );
                $q = trim( array_shift( $faq_lines ) );
                $a_body = implode( "\n", $faq_lines );
                $a_blocks = e3es_parse_markdown_blocks( $a_body, $parent_slug );
                $a_html = '';
                foreach ( $a_blocks as $block ) {
                    $a_html .= $block['html'] . "\n";
                }
                $result['faqs'][] = array( 'q' => $q, 'a' => trim( $a_html ) );
            }
        } else {
            $blocks = e3es_parse_markdown_blocks( $section_body, $parent_slug );
            $sub_sections = array();
            
            $h3_parts = preg_split( '/^###\s+/m', $section_body );
            $section_intro_body = array_shift( $h3_parts );
            $section_intro_blocks = e3es_parse_markdown_blocks( $section_intro_body, $parent_slug );
            
            if ( ! empty( $h3_parts ) ) {
                foreach ( $h3_parts as $h3_part ) {
                    $h3_lines = explode( "\n", $h3_part );
                    $h3_header = trim( array_shift( $h3_lines ) );
                    $h3_body = implode( "\n", $h3_lines );
                    $h3_blocks = e3es_parse_markdown_blocks( $h3_body, $parent_slug );
                    
                    $sub_title = $h3_header;
                    $target_slug = '';
                    if ( preg_match( '/^\[([^\]]+)\]\(([^)]+)\.md\)/', $h3_header, $hm ) ) {
                        $sub_title = $hm[1];
                        $target_slug = $hm[2];
                    }
                    
                    $content_html = '';
                    $learn_more_text = '';
                    $learn_more_link = '';
                    
                    foreach ( $h3_blocks as $block ) {
                        // Check if it's the learn more link
                        if ( $block['type'] === 'paragraph' && preg_match( '/Learn more/i', $block['raw'] ) ) {
                            if ( preg_match( '/\[([^\]]+)\]\(([^)]+)\.md\)/', $block['raw'], $lm ) ) {
                                $learn_more_text = $lm[1];
                                $learn_more_link = $lm[2];
                            }
                        } else {
                            $content_html .= $block['html'] . "\n";
                        }
                    }
                    
                    $sub_sections[] = array(
                        'title'           => $sub_title,
                        'target_slug'     => $target_slug,
                        'content_html'    => trim( $content_html ),
                        'learn_more_text' => $learn_more_text,
                        'learn_more_link' => $learn_more_link
                    );
                }
            }
            
            $result['h2_sections'][] = array(
                'title'        => $section_title,
                'blocks'       => $blocks,
                'intro_blocks' => $section_intro_blocks,
                'sub_sections' => $sub_sections
            );
        }
    }
    
    return $result;
}

// ── 5. Setup Mappings ────────────────────────────────────────────────────────

$child_images_map = array(
    'interior-lighting'             => 'kountze-isd.jpg',
    'sports-lighting'               => 'sanger-isd.jpg',
    'exterior-lighting'             => 'greenville-isd.jpg',
    'parking-lot-lighting'          => 'prosper-isd.jpg',
    'gym-lighting'                  => 'boyd-isd.jpg',
    'smart-lighting-controls'       => 'cooke-county.jpg',
    'led-retrofit'                  => 'ricardo-isd.jpg',
    
    'chiller-replacement'           => 'gwh.jpg',
    'boiler-systems'                => 'glen-rose-medical-center.jpg',
    'rtu-replacement'               => 'donna-isd.jpg',
    'vrf-systems'                   => 'lake-worth-isd.jpg',
    'ventilation-upgrades'          => 'rio-hondo-isd.jpg',
    'dehumidification'              => 'royal-isd.jpg',
    'mechanical-modernization'      => 'bryan-isd.jpg',
    
    'building-automation'           => 'edcouch-elsa-isd.jpg',
    'indoor-air-quality'            => 'port-neches-groves-isd.jpg',
    
    'municipal-water-treatment'     => 'cooke-county.jpg',
    'wastewater-sludge-remediation' => 'ricardo-isd.jpg',
    'water-meter-installation'      => 'donna-isd.jpg',
    'water-conservation'            => 'bryan-isd.jpg',
    'water-wells'                   => 'cooke-county.jpg',
    'scada'                         => 'ricardo-isd.jpg',
    
    'energy-auditing'               => 'carrizo-springs-consolidated-isd.jpg',
    'grant-and-funding-procurement' => 'donna-isd.jpg',
    'cooperative-purchasing'        => 'sanger-isd.jpg',
    
    'solar'                         => 'led-lighting_hero.jpg',
    'roofing'                       => 'port-neches-groves-isd.jpg',
    'retro-commissioning'           => 'controls-automation_hero.jpg'
);

function e3es_get_unique_button_text( $slug ) {
    $ctas = array(
        'interior-lighting'             => 'See Classroom Lighting Upgrades &rarr;',
        'sports-lighting'               => 'See Stadium Lighting Solutions &rarr;',
        'exterior-lighting'             => 'View Security Lighting Projects &rarr;',
        'parking-lot-lighting'          => 'View Parking Lot Retrofits &rarr;',
        'gym-lighting'                  => 'View Gymnasium Lighting Projects &rarr;',
        'smart-lighting-controls'       => 'See Smart Controls Integration &rarr;',
        'led-retrofit'                  => 'View Turnkey Retrofit Results &rarr;',
        
        'chiller-replacement'           => 'View Chiller Plant Upgrades &rarr;',
        'boiler-systems'                => 'See Boiler Systems Retrofits &rarr;',
        'rtu-replacement'               => 'See Packaged RTU Upgrades &rarr;',
        'vrf-systems'                   => 'View VRF Zoning Installations &rarr;',
        'ventilation-upgrades'          => 'See Ventilation Upgrades &rarr;',
        'dehumidification'              => 'View Dehumidification Projects &rarr;',
        'mechanical-modernization'      => 'See Mechanical Modernizations &rarr;',
        
        'building-automation'           => 'See BAS & Controls Upgrades &rarr;',
        'indoor-air-quality'            => 'View IAQ Improvements &rarr;',
        
        'municipal-water-treatment'     => 'See Water Treatment Descaling &rarr;',
        'wastewater-sludge-remediation' => 'View Lagoon Sludge Remediation &rarr;',
        'water-meter-installation'      => 'See AMI Metering Upgrades &rarr;',
        'water-conservation'            => 'View Water Conservation Projects &rarr;',
        'water-wells'                   => 'See Well Rehabilitation Projects &rarr;',
        'scada'                         => 'View SCADA & Telemetry Upgrades &rarr;',
        
        'energy-auditing'               => 'Request Energy Audit &rarr;',
        'grant-and-funding-procurement' => 'See Funding & Grants Won &rarr;',
        'cooperative-purchasing'        => 'View Cooperative Contracts &rarr;',
        
        'solar'                         => 'View Decarbonization Projects &rarr;',
        'roofing'                       => 'See Building Envelope Projects &rarr;',
        'retro-commissioning'           => 'See Commissioning Projects &rarr;'
    );
    return isset( $ctas[$slug] ) ? $ctas[$slug] : 'See project details &rarr;';
}

function e3es_get_unique_overlay_text( $slug ) {
    $texts = array(
        'interior-lighting'             => 'Explore flicker-free indoor LED fixtures that improve student comfort and concentration.',
        'sports-lighting'               => 'Discover glare-free, high-output stadium illumination compliant with NCAA and UIL guidelines.',
        'exterior-lighting'             => 'See how E3 removes blind spots and secures perimeters with high-efficiency security lights.',
        'parking-lot-lighting'          => 'Upgrade pole lights to weather-resistant LEDs for bright parking lots and clear cameras.',
        'gym-lighting'                  => 'Check out impact-resistant, controllable gymnasium lights for sports and events.',
        'smart-lighting-controls'       => 'Integrate occupancy sensors and daylight harvesting to optimize utility savings.',
        'led-retrofit'                  => 'Learn how E3 manages the complete lighting upgrade, from audit to installation.',
        
        'chiller-replacement'           => 'Optimize central plants by replacing old chillers with energy-efficient systems.',
        'boiler-systems'                => 'Install condensing hot-water boiler retrofits to deliver comfortable, balanced heat.',
        'rtu-replacement'               => 'Swap out worn rooftop air conditioners with multi-stage RTUs that reduce peak demand.',
        'vrf-systems'                   => 'Enjoy energy-efficient, zoned heating and cooling without complex duct routing.',
        'ventilation-upgrades'          => 'Ensure fresh air compliance with low-maintenance energy recovery ventilators.',
        'dehumidification'              => 'Protect facility structures and prevent mold growth in pool natatoriums and locker rooms.',
        'mechanical-modernization'      => 'See full building mechanical system optimization, duct balances, and insulation upgrades.',
        
        'building-automation'           => 'Centralize mechanical system monitoring with open-protocol DDC controllers.',
        'indoor-air-quality'            => 'Improve occupant health and wellness with active filtration and environmental monitoring.',
        
        'municipal-water-treatment'     => 'Clean municipal water mains and remove biofilms with budget-neutral Bicarbus descaling.',
        'wastewater-sludge-remediation' => 'Eliminate lagoon sludge organically without the disruption of physical dredging.',
        'water-meter-installation'      => 'Upgrade mechanical water meters to AMI systems to recover lost billing revenue.',
        'water-conservation'            => 'Reduce building water consumption by up to 40% with smart plumbing retrofits.',
        'water-wells'                   => 'Restore drawdown yields and serviceability of municipal water well infrastructure.',
        'scada'                         => 'Gain real-time visibility into tank levels, pressure, and chemical residuals.',
        
        'energy-auditing'               => 'Analyze building operations and equipment health with professional energy audits.',
        'grant-and-funding-procurement' => 'Secure non-taxpayer funding from state SECO grants and Texas LoanSTAR programs.',
        'cooperative-purchasing'        => 'Legally fast-track your project and skip bidding using approved co-op contracts.',
        
        'solar'                         => 'Install clean commercial solar arrays and batteries to hedge against rising rates.',
        'roofing'                       => 'Prevent leaks and lower cooling costs with premium cool roofs and envelope seals.',
        'retro-commissioning'           => 'Fine-tune existing mechanical plants to recover efficiency with minimal capital expense.'
    );
    return isset( $texts[$slug] ) ? $texts[$slug] : 'Learn how E3 delivers turnkey design-build mechanical and energy solutions.';
}

// ── 6. Setup Categories & Folders ────────────────────────────────────────────
$base_copy_dir = '/Users/bryanpaul/Dropbox/PaulDropbox/E3/docs/services-copy';
$folders_map = array(
    'lighting' => array(
        'parent_id'   => 1112,
        'parent_slug' => 'lighting',
        'images'      => array( 'kountze-isd.jpg', 'sanger-isd.jpg', 'greenville-isd.jpg', 'prosper-isd.jpg', 'ricardo-isd.jpg', 'boyd-isd.jpg', 'cooke-county.jpg' )
    ),
    'hvac' => array(
        'parent_id'   => 102,
        'parent_slug' => 'hvac',
        'images'      => array( 'donna-isd.jpg', 'lake-worth-isd.jpg', 'glen-rose-medical-center.jpg', 'gwh.jpg', 'rio-hondo-isd.jpg', 'royal-isd.jpg', 'bryan-isd.jpg' )
    ),
    'controls' => array(
        'parent_id'   => 1637,
        'parent_slug' => 'controls-automation',
        'images'      => array( 'edcouch-elsa-isd.jpg', 'royal-isd.jpg', 'port-neches-groves-isd.jpg', 'gwh.jpg' )
    ),
    'water' => array(
        'parent_id'   => 1636,
        'parent_slug' => 'water-wastewater',
        'images'      => array( 'cooke-county.jpg', 'ricardo-isd.jpg', 'donna-isd.jpg', 'bryan-isd.jpg' )
    ),
    'finance' => array(
        'parent_id'   => 1638,
        'parent_slug' => 'financing-auditing',
        'images'      => array( 'carrizo-springs-consolidated-isd.jpg', 'donna-isd.jpg', 'sanger-isd.jpg', 'boyd-isd.jpg' )
    ),
    'special' => array(
        'parent_id'   => 0,
        'parent_slug' => '',
        'images'      => array( 'led-lighting_hero.jpg', 'port-neches-groves-isd.jpg', 'controls-automation_hero.jpg' )
    )
);

// ── 7. Seeding Loop ──────────────────────────────────────────────────────────
foreach ( $folders_map as $folder => $meta ) {
    $dir_path = $base_copy_dir . '/' . $folder;
    if ( ! is_dir( $dir_path ) ) continue;
    
    echo "\n📂 Processing folder: [{$folder}]...\n";
    
    $files = scandir( $dir_path );
    $img_index = 0;
    
    foreach ( $files as $file ) {
        if ( $file === '.' || $file === '..' || ! str_ends_with( $file, '.md' ) ) continue;
        
        $filepath = $dir_path . '/' . $file;
        $slug = basename( $file, '.md' );
        
        // Map slug corrections
        if ( $slug === 'lighting' ) $slug = 'lighting';
        if ( $slug === 'hvac' ) $slug = 'hvac';
        if ( $slug === 'scada-tceq' ) $slug = 'scada';
        if ( $slug === 'grant-procurement' ) $slug = 'grant-and-funding-procurement';
        if ( $slug === 'decarbonization-solar-storage' ) $slug = 'solar';
        if ( $slug === 'roofing-envelope' ) $slug = 'roofing';
        if ( $slug === 'commissioning-rcx' ) $slug = 'retro-commissioning';
        
        $parsed = e3es_parse_markdown_copy( $filepath, $meta['parent_slug'] );
        if ( ! $parsed ) continue;
        
        $is_parent = ( $slug === $meta['parent_slug'] );
        $parent_id = $is_parent ? 0 : $meta['parent_id'];
        
        echo "  📄 File: {$file} -> Slug: {$slug} " . ( $is_parent ? "[PARENT]" : "[CHILD]" ) . "\n";
        
        $img_name = $meta['images'][$img_index % count( $meta['images'] )];
        $img_url = e3_get_or_import_media( $img_name );
        $img_index++;
        
        // A. BUILD INTRO BANNER
        $banner_html = e3es_make_intro_banner_markup( array(
            'title'          => $parsed['title'],
            'subtitle'       => $parsed['meta_desc'],
            'bgImageUrl'     => $img_url,
            'bgOpacity'      => 0.85,
            'bgOverlayColor' => ( $folder === 'lighting' ) ? 'blue' : ( ( $folder === 'water' ) ? 'black' : 'green' ),
            'bgFadeType'     => 'flat',
            'textAlignment'  => 'center',
            'textCase'       => 'uppercase'
        ) );
        
        $blocks_array = array( $banner_html );
        
        // B. BUILD BODY SECTIONS
        if ( $is_parent ) {
            foreach ( $parsed['h2_sections'] as $idx => $section ) {
                $section_title = $section['title'];
                
                if ( ! empty( $section['sub_sections'] ) ) {
                    // Offerings section: render standard H2 heading block, then separate two-column blocks
                    $blocks_array[] = '<!-- wp:heading {"level":2} -->' . "\n" . '<h2>' . esc_html( $section_title ) . '</h2>' . "\n" . '<!-- /wp:heading -->';
                    
                    foreach ( $section['sub_sections'] as $sub_idx => $sub ) {
                        $child_slug = e3es_map_child_slug( $sub['target_slug'] );
                        
                        global $child_images_map;
                        $child_img_name = isset( $child_images_map[$child_slug] ) ? $child_images_map[$child_slug] : '';
                        $child_img = e3_get_or_import_media( $child_img_name );
                        if ( ! $child_img ) {
                            $child_img = e3_get_or_import_media( $meta['images'][$sub_idx % count( $meta['images'] )] );
                        }
                        
                        $btn_text = e3es_get_unique_button_text( $child_slug );
                        $btn_url = "/services/{$slug}/{$child_slug}";
                        $overlay_text = e3es_get_unique_overlay_text( $child_slug );
                        $clean_content = preg_replace( '/^<p>|<\/p>$/i', '', $sub['content_html'] );
                        
                        $blocks_array[] = e3es_make_two_column_block( array(
                            'imageUrl'        => $child_img,
                            'imageAlt'        => $sub['title'],
                            'reverse'         => ( $sub_idx % 2 === 1 ),
                            'bgStyle'         => ( $sub_idx % 2 === 1 ) ? 'grey' : 'white',
                            'icon'            => ( $sub_idx % 3 === 0 ) ? 'layers' : ( ( $sub_idx % 3 === 1 ) ? 'star' : 'shield' ),
                            'overlayHeadline' => $sub['title'],
                            'overlayText'     => $overlay_text,
                            'overlayBtnText'  => $btn_text,
                            'overlayBtnUrl'   => $btn_url,
                            'heading'         => $sub['title'],
                            'content'         => $clean_content
                        ) );
                    }
                } else {
                    // Standard section: render standard H2 heading block, then standard paragraph/list blocks
                    $blocks_array[] = '<!-- wp:heading {"level":2} -->' . "\n" . '<h2>' . esc_html( $section_title ) . '</h2>' . "\n" . '<!-- /wp:heading -->';
                    
                    foreach ( $section['blocks'] as $element ) {
                        if ( $element['type'] === 'paragraph' ) {
                            $blocks_array[] = "<!-- wp:paragraph -->\n" . $element['html'] . "\n<!-- /wp:paragraph -->";
                        } elseif ( $element['type'] === 'list' ) {
                            $blocks_array[] = "<!-- wp:list -->\n" . $element['html'] . "\n<!-- /wp:list -->";
                        }
                    }
                }
            }
        } else {
            // Child Page Layout: one two-column block for the overview, followed by remaining H2 sections as standard blocks
            $main_blocks_html = '';
            foreach ( $parsed['intro_elements'] as $element ) {
                if ( $element['type'] === 'paragraph' ) {
                    $main_blocks_html .= "<!-- wp:paragraph -->\n" . $element['html'] . "\n<!-- /wp:paragraph -->\n";
                } elseif ( $element['type'] === 'list' ) {
                    $main_blocks_html .= "<!-- wp:list -->\n" . $element['html'] . "\n<!-- /wp:list -->\n";
                }
            }
            
            if ( empty( $main_blocks_html ) && ! empty( $parsed['h2_sections'] ) ) {
                $first_sec = array_shift( $parsed['h2_sections'] );
                foreach ( $first_sec['blocks'] as $element ) {
                    if ( $element['type'] === 'paragraph' ) {
                        $main_blocks_html .= "<!-- wp:paragraph -->\n" . $element['html'] . "\n<!-- /wp:paragraph -->\n";
                    } elseif ( $element['type'] === 'list' ) {
                        $main_blocks_html .= "<!-- wp:list -->\n" . $element['html'] . "\n<!-- /wp:list -->\n";
                    }
                }
            }
            
            $blocks_array[] = e3es_make_two_column_block( array(
                'imageUrl'        => $img_url,
                'imageAlt'        => $parsed['title'],
                'reverse'         => false,
                'bgStyle'         => 'white',
                'icon'            => 'check-circle',
                'overlayHeadline' => $parsed['title'],
                'overlayText'     => "Learn more about " . $parsed['title'] . ".",
                'overlayBtnText'  => "Request assessment",
                'overlayBtnUrl'   => "/contact",
                'heading'         => $parsed['title'],
                'content'         => $main_blocks_html
            ) );
            
            foreach ( $parsed['h2_sections'] as $section ) {
                $blocks_array[] = "<!-- wp:heading {\"level\":2} -->\n<h2>" . esc_html( $section['title'] ) . "</h2>\n<!-- /wp:heading -->";
                foreach ( $section['blocks'] as $element ) {
                    if ( $element['type'] === 'paragraph' ) {
                        $blocks_array[] = "<!-- wp:paragraph -->\n" . $element['html'] . "\n<!-- /wp:paragraph -->";
                    } elseif ( $element['type'] === 'list' ) {
                        $blocks_array[] = "<!-- wp:list -->\n" . $element['html'] . "\n<!-- /wp:list -->";
                    }
                }
            }
        }
        
        // C. APPEND FAQS
        if ( ! empty( $parsed['faqs'] ) ) {
            $blocks_array[] = e3es_make_faq_section( $parsed['faqs'] );
        }
        
        // D. APPEND CTA BANNER
        $blocks_array[] = e3es_make_cta_banner(
            "Ready to Optimize Your Facility?",
            "Contact our Texas energy experts to schedule a free Preliminary Energy Study. Learn how you can fund modern upgrades using guaranteed utility savings.",
            "Request a Free Assessment",
            "/contact"
        );
        
        $post_content = implode( "\n\n", $blocks_array );
        
        // Seed or update the post
        $existing_posts = get_posts( array(
            'name'        => $slug,
            'post_type'   => 'services',
            'post_status' => 'any',
            'numberposts' => 1,
        ) );
        $post_id = ! empty( $existing_posts ) ? $existing_posts[0]->ID : 0;
        if ( $post_id ) {
            $result = wp_update_post( array(
                'ID'           => $post_id,
                'post_title'   => $parsed['title'],
                'post_content' => wp_slash( $post_content ),
                'post_parent'  => $parent_id,
                'post_status'  => 'publish'
            ), true );
            
            if ( is_wp_error( $result ) ) {
                echo "  ❌ ERROR updating post #{$post_id}: " . $result->get_error_message() . "\n";
            } else {
                echo "  ✅ Updated: service post #{$post_id} slug '{$slug}'\n";
            }
        } else {
            $new_id = wp_insert_post( array(
                'post_type'    => 'services',
                'post_title'   => $parsed['title'],
                'post_name'    => $slug,
                'post_content' => wp_slash( $post_content ),
                'post_parent'  => $parent_id,
                'post_status'  => 'publish'
            ), true );
            
            if ( is_wp_error( $new_id ) ) {
                echo "  ❌ ERROR creating post for slug '{$slug}': " . $new_id->get_error_message() . "\n";
            } else {
                echo "  ✅ Created: service post #{$new_id} slug '{$slug}'\n";
                $post_id = $new_id;
            }
        }
        
        // Sync thumbnail ID and meta fields
        if ( $post_id ) {
            // Find attachment ID of the main image
            $attachment_id = $wpdb->get_var( $wpdb->prepare(
                "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s",
                '%' . $img_name
            ) );
            if ( $attachment_id ) {
                update_post_meta( $post_id, '_thumbnail_id', $attachment_id );
            }
            
            // Set service_excerpt and meta description custom fields
            update_post_meta( $post_id, 'service_excerpt', $parsed['meta_desc'] );
            update_post_meta( $post_id, 'meta_description', $parsed['meta_desc'] );
            update_post_meta( $post_id, 'primary_keywords', $parsed['primary_keywords'] );
            update_post_meta( $post_id, 'secondary_keywords', $parsed['secondary_keywords'] );
        }
        
        // Duplicate mappings: if 'roofing' is seeded, also seed the 'building-envelope' post
        if ( $slug === 'roofing' ) {
            $envelope_post = get_page_by_path( 'building-envelope', OBJECT, 'services' );
            if ( $envelope_post ) {
                wp_update_post( array(
                    'ID'           => $envelope_post->ID,
                    'post_title'   => 'Building Envelope & Thermal Insulation',
                    'post_content' => wp_slash( $post_content ),
                    'post_parent'  => 0,
                    'post_status'  => 'publish'
                ) );
                if ( $attachment_id ) {
                    update_post_meta( $envelope_post->ID, '_thumbnail_id', $attachment_id );
                }
                echo "  ✅ Synced 'building-envelope' duplicate page using 'roofing' blocks.\n";
            }
        }
    }
}

// ── 7. Flush Caches ──────────────────────────────────────────────────────────
wp_cache_flush();
echo "\n🎉 E3ES Services Seeder completed successfully!\n\n";
