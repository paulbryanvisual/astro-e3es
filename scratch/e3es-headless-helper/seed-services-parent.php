<?php
/**
 * E3ES Services Parent Pages Seeder
 * 
 * Wipes and rebuilds content with Gutenberg block structures for:
 *   - LED Lighting Upgrades and Sports Lighting (services #1639)
 *   - HVAC System Upgrades and Replacements (services #1641)
 *   - Controls & Automation (services #1637)
 *   - Water & Wastewater (services #1636)
 *   - Financing & Auditing (services #1638)
 * 
 * Run via Local PHP:
 *   "/Users/bryanpaul/Library/Application Support/Local/lightning-services/php-8.2.29+0/bin/darwin-arm64/bin/php" \
 *     "/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-content/plugins/e3es-headless-helper/seed-services-parent.php"
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

echo "\n🌱 E3ES Services Parent Pages Seeder Starting...\n\n";

// Helper to fetch or import media from legacy-html flickr images
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
        // Fallback: check in Astro public folder
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
    
    // Update the mapping in option
    $map = get_option( 'e3_media_url_map', array() );
    $url = wp_get_attachment_url( $id );
    $map[ $filename ] = $url;
    update_option( 'e3_media_url_map', $map );
    
    echo "  📸 Imported media: {$filename} -> ID #{$id}\n";
    return $url;
}

// Helper to make E3 Skewed Two Column block markup
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
    
    return '<!-- wp:e3es/two-column ' . $attrs_json . ' -->
<section class="wp-block-e3es-two-column db-feature db-feature--' . esc_attr( $attr['bgStyle'] ) . '"><div class="db-feature__container' . $reverse_class . '"><div class="db-feature__content"><div class="db-feature__icon"></div><!-- wp:heading {"level":2} -->
<h2>' . esc_html( $attr['heading'] ) . '</h2>
<!-- /wp:heading --><!-- wp:paragraph -->
<p>' . esc_html( $attr['content'] ) . '</p>
<!-- /wp:paragraph --></div><div class="db-feature__image-wrapper"><img src="' . esc_url( $attr['imageUrl'] ) . '" alt="' . esc_attr( $attr['imageAlt'] ) . '" class="db-feature__image">' . $overlay_html . '</div></div></section>
<!-- /wp:e3es/two-column -->';
}

// Helper to make E3 Comparison Table block markup
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

// Helper to make E3 FAQ Section block markup
function e3es_make_faq_section( $faqs ) {
    $inner_html = '';
    foreach ( $faqs as $faq ) {
        $inner_html .= '<!-- wp:heading {"level":3} -->' . "\n";
        $inner_html .= '<h3>' . esc_html( $faq['q'] ) . '</h3>' . "\n";
        $inner_html .= '<!-- /wp:heading -->' . "\n";
        $inner_html .= '<!-- wp:paragraph -->' . "\n";
        $inner_html .= '<p>' . esc_html( $faq['a'] ) . '</p>' . "\n";
        $inner_html .= '<!-- /wp:paragraph -->' . "\n\n";
    }
    
    return '<!-- wp:e3es/faq-section -->' . "\n" . '<section class="wp-block-e3es-faq-section faq-section"><div class="faq-section__container"><h2 class="faq-section__title">Frequently Asked Questions</h2>' . trim( $inner_html ) . '</div></section>' . "\n" . '<!-- /wp:e3es/faq-section -->';
}

// Helper to make E3 CTA Banner block markup
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

// Main seeder function
function e3_seed_service( $id, $title, $content ) {
    $result = wp_update_post( array(
        'ID'           => $id,
        'post_title'   => $title,
        'post_content' => wp_slash( $content ),
        'post_status'  => 'publish',
    ), true );
    
    if ( is_wp_error( $result ) ) {
        echo "  ❌ ERROR seeding service ID #{$id}: " . $result->get_error_message() . "\n";
    } else {
        echo "  ✅ Successfully seeded service ID #{$id}: \"{$title}\"\n";
    }
}

// ── SERVICES DATA ────────────────────────────────────────────────────────────
$services_data = array(
    1639 => array(
        'slug' => 'led-lighting',
        'title' => 'LED Lighting Upgrades and Sports Lighting',
        'meta_desc' => 'Turnkey commercial LED and sports lighting upgrades for Texas K-12 school districts, colleges, cities, and hospitals. High-efficiency retrofits with zero change orders.',
        'overview_title' => 'The Design+Build Lighting Advantage',
        'overview_text' => 'Upgrading outdated, high-maintenance High-Intensity Discharge (HID), metal halide, or fluorescent fixtures to modern high-efficiency Light Emitting Diode (LED) systems is one of the fastest ways for Texas public entities to reduce utility bills and address deferred maintenance. E3 simplifies this process with our integrated Design-Build model, serving as your single source of design and construction accountability. Our crews work nights, weekends, and holidays to ensure classrooms, clinics, and municipal offices remain fully functional during the day with zero disruption. And our guaranteed maximum price means the cost we quote is the cost you pay—no hidden fees or design-omission charges.',
        'overview_photo_caption' => 'Flicker-free classroom LED retrofit completed at Ricardo ISD, improving light levels and learning focus.',
        'overview_photo_link' => '/clients/ricardo-isd',
        'overview_photo_btn' => 'Learn how Ricardo ISD transformed learning',
        'sections' => array(
            array(
                'title' => 'Classroom & Interior Lighting',
                'text' => 'Modernize learning and healing environments. We upgrade classrooms, hospital corridors, offices, and common spaces with dimmable, flicker-free LED systems that mimic natural light. High-quality interior lighting is proven to reduce eye strain, minimize headaches, and improve student focus and productivity. Pairing these with occupancy sensors ensures lights are only active when spaces are occupied.',
                'photo_caption' => 'Modernized interior corridor and classroom lighting installed at Kountze ISD, optimizing brightness and student comfort.',
                'photo_link' => '/clients/kountze-isd',
                'photo_btn' => 'See Kountze classroom lighting upgrades'
            ),
            array(
                'title' => 'Sports & Stadium Lighting',
                'text' => 'Bring professional-grade, glare-free illumination to your football, soccer, baseball, and softball fields. Our high-output LED fixtures provide uniform light levels, eliminate shadow zones, and comply with state and national athletic league requirements (UIL, NCAA). Unlike old metal halide systems that require a 20-minute warm-up, LED sports lights turn on instantly, saving energy and improving safety.',
                'photo_caption' => 'Professional NCAA-compliant stadium sports lighting retrofitted at Greenville ISD under a turnkey energy program.',
                'photo_link' => '/clients/greenville-isd',
                'photo_btn' => 'Explore sports complex retrofits'
            ),
            array(
                'title' => 'Parking Lot & Exterior Security Lighting',
                'text' => 'Enhance safety, eliminate dark spots, and secure your facility perimeter after dark. E3 replaces aging pole-mounted fixtures with high-durability LEDs that provide bright, even coverage, improving camera visibility and security response. By focusing light where it is needed and minimizing light spill, we ensure compliance with dark-sky guidelines while lowering security overhead.',
                'photo_caption' => 'Walkway and exterior security LED retrofits installed at Sanger ISD, improving nighttime visibility.',
                'photo_link' => '/clients/sanger-isd',
                'photo_btn' => 'View exterior security specs'
            )
        ),
        'table_headers' => array('Feature', 'Traditional Bid-Build', 'E3 Turnkey LED Solutions'),
        'table_rows' => array(
            array('Sourcing & Layout', 'Siloed consultants & bidding', 'Integrated audit, design, & install'),
            array('Project Accountability', 'Multiple contractors & vendors', 'Single-source partner (E3)'),
            array('Scheduling', 'Daytime disruptions to operations', 'Ghost night & weekend installation'),
            array('Price Predictability', 'Prone to costly change orders', 'Fixed price with no change orders'),
            array('Utility Rebate Capture', 'Fragmented or missed', 'Fully managed in-house utility applications')
        ),
        'faqs' => array(
            array(
                'q' => 'How much does it cost to install LED sports lights for high school football fields in Texas?',
                'a' => 'The cost of a sports lighting installation varies depending on whether you are retrofitting existing poles or installing a completely new system (including poles, foundations, and electrical wiring). Generally, a turnkey LED sports lighting retrofit for a standard Texas high school football field ranges from $150,000 to $350,000. New installations with new concrete foundations and steel poles range from $300,000 to $600,000. E3 conducts free preliminary audits to provide an exact fixed cost for your specific facility.'
            ),
            array(
                'q' => 'What is the difference between retrofitting old stadium lights and full pole replacement?',
                'a' => 'A retrofit involves removing old, inefficient metal halide fixtures from existing poles and installing new high-output LED arrays using custom-engineered brackets. This saves up to 50% of the cost of a new system. A full pole replacement is only necessary if structural testing reveals that the existing steel or wood poles are fatigued, or if the soil foundation cannot support the wind-load (EPA rating) of the new LED fixtures.'
            ),
            array(
                'q' => 'Are there UIL or athletic standards for sports lighting levels in Texas?',
                'a' => 'Yes. The University Interscholastic League (UIL) and the NCAA specify strict foot-candle requirements for player safety and ball visibility. For example, high school football fields generally require 30 to 50 horizontal foot-candles, while baseball infields require 50 foot-candles and outfield zones require 30 foot-candles. E3\'s engineers provide stamped, UIL-compliant photometric layouts to ensure your field meets all league standards.'
            )
        )
    ),
    1641 => array(
        'slug' => 'hvac',
        'title' => 'HVAC System Upgrades and Replacements',
        'meta_desc' => 'Turnkey commercial HVAC upgrades and mechanical replacements for Texas schools, cities, and hospitals. Reduce energy bills and deferred maintenance.',
        'overview_title' => 'The Design+Build Mechanical Advantage',
        'overview_text' => 'Heating, ventilation, and air conditioning (HVAC) systems are typically the single largest consumers of electricity and natural gas in public and commercial buildings. Outdated chiller plants, failing boilers, and aging rooftop units (RTUs) lead to soaring utility bills, frequent repairs, and poor indoor air quality. E3 eliminates this friction by serving as your single source of design and construction accountability. We combine engineering design and estimation to lock in accurate pricing from day one, absorb any design omissions, and phase installations during school breaks and weekends so systems are fully functional when occupants return.',
        'overview_photo_caption' => 'Major rooftop HVAC replacements and piping upgrades successfully completed at Bryan ISD.',
        'overview_photo_link' => '/clients/bryan-isd',
        'overview_photo_btn' => 'See Bryan ISD mechanical upgrades',
        'sections' => array(
            array(
                'title' => 'Chiller Plant Replacement & Modernization',
                'text' => 'We replace failing, inefficient central chillers with modern, high-efficiency air-cooled or water-cooled units. Optimize cooling efficiency through variable-frequency drives (VFDs), premium refrigerant management, and advanced plant controls. By redesigning constant-volume primary loops to variable-primary setups, we reduce pump energy by up to 50% while improving heat exchange.',
                'photo_caption' => 'New mechanical central plant chiller installation under construction at Donna ISD.',
                'photo_link' => '/clients/donna-isd',
                'photo_btn' => 'Explore Donna ISD chiller plant project details'
            ),
            array(
                'title' => 'Packaged Rooftop Unit (RTU) Upgrades',
                'text' => 'We replace failing, roof-mounted HVAC units with modern high-efficiency packaged RTUs. By integrating dual-stage compressors, multi-speed fans, and economizers, we drastically reduce peak electrical demand. We utilize custom adapter curbs to match the existing roof footprints, reducing structural modifications and securing watertight seals.',
                'photo_caption' => 'High-efficiency packaged rooftop air conditioning unit installed to replace an obsolete unit at Lake Worth ISD.',
                'photo_link' => '/clients/lake-worth-isd',
                'photo_btn' => 'View Lake Worth RTU case study'
            ),
            array(
                'title' => 'Boiler Systems & Heavy Mechanical Lifts',
                'text' => 'Modernize your building\'s heating. We retrofit aging steam and standard water boilers with high-efficiency condensing boilers, advanced hot-water piping, and variable-speed pumping controls to deliver precise, balanced heat. E3 manages the complete installation, coordinating heavy crane lifts and rigging to swap out heavy plant equipment with minimal downtime.',
                'photo_caption' => 'A crane lift placing heavy plant equipment onto the roof of Glen Rose Medical Center.',
                'photo_link' => '/clients/glen-rose-medical-center',
                'photo_btn' => 'Learn how the chiller lift was done'
            )
        ),
        'table_headers' => array('Feature', 'Traditional Bid-Build', 'E3 Design-Build Mechanical'),
        'table_rows' => array(
            array('Design Phase', 'Detached consultant, slow turnaround', 'In-house mechanical engineers, fast design'),
            array('Cost Control', 'Prone to post-bid budget overruns', 'Guaranteed maximum price from day one'),
            array('Change Orders', 'Client pays for errors/omissions', 'Zero change orders for agreed scope'),
            array('Coordination', 'Client manages architect vs. installer', 'Single point of contact (E3)'),
            array('Commissioning', 'Often treated as an afterthought', 'Comprehensive, in-house system commissioning')
        ),
        'faqs' => array(
            array(
                'q' => 'How can school districts fund HVAC replacement without upfront capital in Texas?',
                'a' => 'Texas school districts can fund HVAC upgrades without drawing from capital reserves or passing a voter bond by using Energy Savings Performance Contracts (ESPCs) (governed by Texas Education Code Chapter 44). This legislation allows you to finance energy-efficiency upgrades (such as chillers, boilers, and RTUs) using a tax-exempt lease-purchase agreement. E3 contractually guarantees the energy savings, which are used directly to pay off the financing over time, making the project 100% budget-neutral.'
            ),
            array(
                'q' => 'What are the common signs that a school chiller plant needs replacement vs. repair?',
                'a' => 'Key indicators that a chiller plant requires replacement rather than ongoing repair include: Equipment Age (approaching or exceeding average service life of 15 to 20 years), Refrigerant Phase-Out (utilizing R-22 or other legacy HCFC refrigerants), frequent compressor failures, and Low Delta-T Syndrome (central loop inefficiencies where the returned water temperature is too close to the supplied temperature).'
            ),
            array(
                'q' => 'How do you coordinate heavy mechanical construction during the school year without disruptions?',
                'a' => 'E3 utilizes a phased installation schedule and coordinates major mechanical lifts and shutdowns during planned school holidays, winter and summer breaks, and weekends. For critical areas that require continuous climate control (such as administrative hubs or IT server rooms), we bring in temporary mobile chillers or split systems, ensuring zero downtime for school classes.'
            )
        )
    ),
    1637 => array(
        'slug' => 'controls-automation',
        'title' => 'Controls & Automation',
        'meta_desc' => 'Smart Building Automation Systems (BAS) and Indoor Air Quality (IAQ) controls for Texas public entities. Single-source design-build system integration.',
        'overview_title' => 'Intelligent Building Optimization',
        'overview_text' => 'Modern public facilities require intelligent control systems to operate efficiently. Without a centralized Building Automation System (BAS), heating, cooling, and lighting systems operate in silos—running during unoccupied hours, heating and cooling empty spaces, and failing to maintain stable indoor air quality. E3 designs, installs, and commissions integrated control solutions that connect your mechanical, electrical, and environmental systems. We replace outdated pneumatic controls or proprietary, locked control networks with open-protocol Direct Digital Control (DDC) systems that put you in complete command of your facility\'s energy and air quality, maximizing occupant comfort and saving utility dollars.',
        'overview_photo_caption' => 'Web-accessible building controls and DDC panel installation completed at Edcouch-Elsa ISD.',
        'overview_photo_link' => '/clients/edcouch-elsa-isd',
        'overview_photo_btn' => 'See Edcouch-Elsa BAS integration',
        'sections' => array(
            array(
                'title' => 'Building Automation Systems (BAS) & HVAC Controls',
                'text' => 'Optimize your facility operations. We install DDC panels, electronic actuators, variable-frequency drives (VFDs), and smart thermostats, integrating them into a centralized, user-friendly software platform. Schedule setbacks, monitor real-time energy draw, and receive automated maintenance alerts. With our open-protocol systems, you remain independent of specific vendor servicing contracts.',
                'photo_caption' => 'Turnkey direct digital controls installed in the primary mechanical equipment room at Royal ISD.',
                'photo_link' => '/clients/royal-isd',
                'photo_btn' => 'Explore Royal ISD DDC panels'
            ),
            array(
                'title' => 'Indoor Air Quality (IAQ) Analysis & Improvements',
                'text' => 'Improve building health and visual comfort. We assess facility environments, install carbon dioxide (CO2) and particulate sensors, and integrate air purification, filtration, and ventilation controls to ensure a continuous stream of fresh, clean air. By coordinating ventilation with CO2 levels, we ensure classrooms and offices are healthy without over-ventilating and wasting energy.',
                'photo_caption' => 'Air quality improvements and mechanical dampers linked to building automation at Port Neches-Groves ISD.',
                'photo_link' => '/clients/port-neches-groves-isd',
                'photo_btn' => 'View PNG IAQ damper installations'
            ),
            array(
                'title' => 'SCADA Telemetry & Remote Monitoring Systems',
                'text' => 'Centralize visibility of your building systems. We integrate SCADA systems, remote telemetry networks, tank level sensors, and pressure monitors. Receive instant alerts on your mobile device for critical alarms, preventing breakdowns and optimizing maintenance routing.',
                'photo_caption' => 'Hospital-wide remote telemetry and building energy management screens at Goodall Witcher Hospital.',
                'photo_link' => '/clients/gwh',
                'photo_btn' => 'Learn more about Hospital SCADA'
            )
        ),
        'table_headers' => array('Feature', 'Legacy Control Contractors', 'E3 Open-Protocol Solutions'),
        'table_rows' => array(
            array('Protocol Openness', 'Proprietary & locked protocols', 'Open-protocol BACnet / LonWorks'),
            array('Vendor Lock-In', 'High; requires proprietary techs', 'Low; compatible with any vendor'),
            array('System Integration', 'Lighting, HVAC, and IAQ run in silos', 'Unified control dashboard'),
            array('User Interface', 'Outdated, text-heavy consoles', 'Modern, graphical web & mobile apps'),
            array('Procurement', 'Prone to slow, complex bidding', 'Pre-approved via Texas cooperatives')
        ),
        'faqs' => array(
            array(
                'q' => 'What is open-protocol BACnet and why should schools avoid proprietary controls?',
                'a' => 'BACnet is an ANSI/ASHRAE Standard communication protocol that allows building control hardware from different manufacturers to communicate. Public entities should avoid proprietary control systems because they create \'vendor lock-in.\' With a proprietary system, you are contractually forced to hire that specific manufacturer for every software update, replacement sensor, or expansion. An open-protocol BACnet system ensures that any certified controls technician can service, expand, or modify your building controls, keeping maintenance costs competitive.'
            ),
            array(
                'q' => 'Can building automation systems (BAS) help detect water leaks or monitor air quality?',
                'a' => 'Yes. Modern Building Automation Systems can serve as unified monitoring platforms. By integrating digital water flow meters, the BAS can identify abnormal, continuous water draws (indicating a hidden pipe leak or running toilet) and immediately send text alerts to maintenance staff. Similarly, by integrating inline indoor air quality sensors, the BAS can monitor carbon dioxide (CO2), temperature, relative humidity, and volatile organic compounds (VOCs), automatically opening mechanical fresh-air dampers when pollutant levels rise.'
            ),
            array(
                'q' => 'What are the ventilation requirements for school indoor air quality under ASHRAE 62.1?',
                'a' => 'ASHRAE Standard 62.1 defines minimum outdoor fresh air ventilation rates based on space type and expected occupancy. For classroom areas, this typically translates to roughly 10 to 15 cubic feet per minute (CFM) of outdoor air per student. Additionally, the standard recommends keeping indoor CO2 levels below 1,000 parts per million (ppm) to prevent drowsiness and focus issues. E3 designs ventilation systems using carbon dioxide sensors to dynamically adjust fresh air volumes.'
            )
        )
    ),
    1636 => array(
        'slug' => 'water-wastewater',
        'title' => 'Water & Wastewater',
        'meta_desc' => 'Turnkey water and wastewater infrastructure upgrades for Texas municipalities and public entities. Innovative, budget-neutral treatment solutions.',
        'overview_title' => 'Sustainable, Budget-Neutral Infrastructure Modernization',
        'overview_text' => 'Managing municipal water networks and wastewater treatment plants is a major financial and regulatory challenge for Texas cities and public entities. Aging pipe networks suffer from mineral scale buildup that constricts water flow and harbors bacteria, wastewater lagoons accumulate thick sludge that reduces holding capacity, and inaccurate mechanical water meters lead to lost utility revenue. E3 delivers a smarter, budget-neutral approach through our Design-Build model. We introduce innovative, eco-friendly, and pre-approved technologies that resolve your infrastructure bottlenecks, guarantee regulatory compliance, and pay for themselves through operational savings and increased utility revenue.',
        'overview_photo_caption' => 'Water conservation retrofits and building utility upgrades completed for Cooke County municipal properties.',
        'overview_photo_link' => '/clients/cooke-county',
        'overview_photo_btn' => 'See Cooke County courthouse retrofits',
        'sections' => array(
            array(
                'title' => 'Municipal Water Treatment (Bicarbus Descaling)',
                'text' => 'Descale aging water mains and eliminate biofilms. Our Bicarbus chemical descaling technology naturally removes calcium carbonate scale and microbial growth, improving water pressure and reducing carcinogens (TTHMs) by lowering chlorine demands. This is completed in-place while the water system remains online, preventing service shutdowns.',
                'photo_caption' => 'High-efficiency water piping and mechanical controls under construction in South Texas.',
                'photo_link' => '/clients/donna-isd',
                'photo_btn' => 'Learn more about water line descaling'
            ),
            array(
                'title' => 'Water Meter Installation & SCADA Integration',
                'text' => 'Recover lost utility revenue and secure lift station reliability. We install smart solid-state water meters (AMR/AMI) that accurately capture low flows, replacing mechanical meters that slip out of calibration. We integrate these with SCADA telemetry systems to monitor tank levels, flow rates, and chlorine residuals remotely, ensuring TCEQ compliance.',
                'photo_caption' => 'Turnkey smart water meter and billing integration piping at Bryan ISD.',
                'photo_link' => '/clients/bryan-isd',
                'photo_btn' => 'Explore smart meter installations'
            ),
            array(
                'title' => 'Water Conservation & Low-Flow Upgrades',
                'text' => 'Reduce water consumption by up to 40% in public facilities. We replace or retrofit high-water-consumption plumbing fixtures with low-flow toilets (1.28 GPF), pint-flush urinals, and automatic sensor faucets. We install smart irrigation controllers with integrated leak-detection monitoring to secure water billing credits.',
                'photo_caption' => 'Low-flow plumbing upgrades and fixture retrofits completed at Ricardo ISD.',
                'photo_link' => '/clients/ricardo-isd',
                'photo_btn' => 'View low-flow plumbing savings'
            )
        ),
        'table_headers' => array('Feature', 'Traditional Civil Engineering', 'E3 Design-Build Infrastructure'),
        'table_rows' => array(
            array('Project Funding', 'Requires tax increases or bond votes', 'Funded through utility savings & revenue'),
            array('Sludge Removal', 'Expensive dredging & landfill hauling', 'In-place biological digestion (Nanobubbles)'),
            array('Pipe Rehabilitation', 'Costly pipe excavation & replacement', 'Chemical descaling in-place (Bicarbus)'),
            array('Accountability', 'Siloed engineers and contractors', 'Single-source partner (E3)'),
            array('Regulatory Risk', 'Client manages TCEQ relations', 'E3 guarantees compliant design & performance')
        ),
        'faqs' => array(
            array(
                'q' => 'Does biological sludge digestion using nanobubbles replace physical dredging for wastewater lagoons?',
                'a' => 'Yes. E3\'s nanobubble technology serves as a biological alternative to physical dredging. By continuously infusing millions of microscopic oxygen bubbles into the bottom sludge layer, we stimulate native aerobic bacteria that consume and digest organic sludge biologically in-place. This can reduce sludge depths by 20% to 50% within a year, saving municipalities hundreds of thousands of dollars in dredging costs.'
            ),
            array(
                'q' => 'How does Bicarbus chemical descaling work, and does it help lower municipal TTHM levels?',
                'a' => 'Bicarbus is an NSF/ANSI Standard 60 certified food-grade chemical treatment introduced into the water supply in precise, low concentrations. It dissolves calcium carbonate scale inside water mains, clearing the pipes and stripping away biofilms that shelter bacteria. Because the pipe network is cleared of organic biofilm and scale, the water’s overall chlorine demand drops. Reducing the amount of chlorine used directly prevents the formation of Total Trihalomethanes (TTHMs)—harmful, carcinogenic disinfection byproducts—ensuring full compliance with TCEQ drinking water standards.'
            ),
            array(
                'q' => 'How do AMI smart water meters recover lost utility billing revenue for Texas municipalities?',
                'a' => 'Mechanical water meters degrade over time, losing calibration and failing to record low flow rates (such as slow leaks or trickling faucets). E3 replaces old mechanical meters with solid-state electromagnetic or ultrasonic smart meters (AMR/AMI) that maintain 100% accuracy for their 20-year lifespan. Capturing this previously unrecorded water flow typically increases municipal water billing revenue by 5% to 15% immediately, helping to self-finance the infrastructure project.'
            )
        )
    ),
    1638 => array(
        'slug' => 'financing-auditing',
        'title' => 'Financing & Auditing',
        'meta_desc' => 'Turnkey energy audits, funding procurement, and cooperative purchasing for Texas school districts, cities, and hospitals. Capital-free upgrades.',
        'overview_title' => 'Overcoming Funding Barriers for Infrastructure Upgrades',
        'overview_text' => 'Public entities in Texas face a persistent challenge: a growing backlog of deferred maintenance coupled with limited capital budgets. Funding massive upgrades typically requires raising taxes, proposing bond elections, or depleting reserve funds. E3 resolves this barrier by offering complete, turnkey Financing, Auditing, and Procurement solutions. We help you identify energy waste, secure non-taxpayer funding (grants, low-interest state loans, utility rebates), and utilize state-approved cooperative purchasing to deliver facility modernizations with zero upfront capital.',
        'overview_photo_caption' => 'Turnkey ESPC improvements fully funded by guaranteed energy savings at Boyd ISD.',
        'overview_photo_link' => '/clients/boyd-isd',
        'overview_photo_btn' => 'Learn how Boyd ISD self-funded updates',
        'sections' => array(
            array(
                'title' => 'Energy Auditing & Investment Grade Audit (IGA)',
                'text' => 'Identify energy waste and compile a board-ready investment case. We perform free preliminary facility studies and detailed Investment Grade Audits (IGA) to inventory equipment, analyze utility tariffs, and calculate exact payback periods. The IGA forms the engineering baseline for our performance contract guarantee.',
                'photo_caption' => 'Turnkey facility auditing and LED sports lighting retrofit completed at Carrizo Springs CISD.',
                'photo_link' => '/clients/carrizo-springs-consolidated-isd',
                'photo_btn' => 'See Investment Grade Audit details'
            ),
            array(
                'title' => 'Cooperative Purchasing Agreements',
                'text' => 'Skip the public bidding process legally. E3 is an approved vendor on major Texas cooperatives, including BuyBoard, TIPS, and TASB. Because we have already gone through a competitive RFP process to become pre-approved on these co-ops, school boards and city councils can bypass the traditional 6-to-12-month design-bid-build procurement process and contract directly with E3.',
                'photo_caption' => 'Fast-track mechanical upgrades procured through pre-approved purchasing co-ops at Sanger ISD.',
                'photo_link' => '/clients/sanger-isd',
                'photo_btn' => 'Learn how to bypass RFP delays'
            ),
            array(
                'title' => 'Grant Procurement & Alternative Financing',
                'text' => 'Secure non-taxpayer funding for your facility. E3’s funding experts navigate the application process for State Energy Conservation Office (SECO) grants, the Texas LoanSTAR program, Qualified Zone Academy Bonds (QZAB), and federal energy tax credits. E3’s in-house engineering team provides the required stamped drawings to successfully secure this competitive funding, an advantage that many specialized competitors lack.',
                'photo_caption' => 'Comprehensive energy conservation program retrofitting 20 campuses funded through alternative financing at Donna ISD.',
                'photo_link' => '/clients/donna-isd',
                'photo_btn' => 'See funding & QZAB procurement details'
            )
        ),
        'table_headers' => array('Feature', 'Traditional Bid-Build', 'E3 Turnkey Financing & Procurement'),
        'table_rows' => array(
            array('Funding Strategy', 'Capital budget or voter bond required', 'Funded through utility savings & grants'),
            array('Audit Cost', 'Upfront fee paid to consultants', 'Free preliminary energy study'),
            array('Procurement Cycle', '6 to 12 months (Design-Bid-Build)', 'Accelerated via pre-approved co-ops'),
            array('Financial Guarantee', 'None; client bears performance risk', 'E3 guarantees energy savings'),
            array('Incentives Coordination', 'Client manages rebate applications', 'Fully handled in-house by E3')
        ),
        'faqs' => array(
            array(
                'q' => 'How does an Energy Savings Performance Contract (ESPC) work under Texas law?',
                'a' => 'An ESPC is a turnkey procurement and financing mechanism governed by Texas Education Code § 44.901 (for school districts) and Texas Local Government Code Chapter 302 (for cities, counties, and hospital districts). It allows public entities to pay for facility energy, water, and operational upgrades using the utility savings generated by the new equipment. Under Texas law, the ESCO (like E3) must contractually guarantee that the savings will meet or exceed the annual finance payments. If there is a savings shortfall, E3 pays the difference, making the project budget-neutral.'
            ),
            array(
                'q' => 'Can school districts skip competitive bidding using BuyBoard or TIPS for construction?',
                'a' => 'Yes. Under Texas Education Code § 44.031 and Government Code § 791 (Interlocal Cooperation Act), public entities can legally satisfy competitive bidding requirements by procuring services through state-approved purchasing cooperatives such as BuyBoard, TIPS, or TASB. Because E3 has already gone through a competitive RFP process to become a pre-approved vendor on these co-ops, school boards and city councils can bypass the traditional 6-to-12-month design-bid-build procurement process and contract directly with E3, saving time and administrative costs.'
            ),
            array(
                'q' => 'What is the difference between a free preliminary energy study and an Investment Grade Audit (IGA)?',
                'a' => 'A Preliminary Energy Study (PES) is a high-level facility walkthrough (similar to an ASHRAE Level 1 audit) that E3 performs at no cost. It reviews utility bills, inventories equipment, and identifies potential conservation measures to determine if an ESPC is feasible. An Investment Grade Audit (IGA) is a comprehensive, engineering-intensive study (ASHRAE Level 3) that details exactly how the upgrades will be executed, the precise equipment models, and the guaranteed energy savings. The IGA forms the baseline for the ESPC\'s financial guarantee and is typically funded as part of the overall project once approved.'
            )
        )
    )
);

// Run the seeder
foreach ( $services_data as $pid => $s ) {
    $slug = $s['slug'];
    $title = $s['title'];
    echo "Processing Service: {$title} (#{$pid})...\n";
    
    // Import and map media files
    $hero_img = e3_get_or_import_media( "{$slug}_hero.jpg" );
    $overview_img = e3_get_or_import_media( "{$slug}_overview.jpg" );
    
    $sections_html = array();
    foreach ( $s['sections'] as $idx => $sect ) {
        $img_name = "{$slug}_section_" . ($idx + 1) . ".jpg";
        $sect_img = e3_get_or_import_media( $img_name );
        
        $sections_html[] = e3es_make_two_column_block( array(
            'imageUrl'        => $sect_img,
            'imageAlt'        => $sect['title'],
            'reverse'         => ( $idx % 2 === 1 ), // Alternate alignment
            'bgStyle'         => ( $idx % 2 === 1 ) ? 'grey' : 'white',
            'icon'            => ( $idx === 0 ) ? 'layers' : ( ( $idx === 1 ) ? 'star' : 'shield' ),
            'overlayHeadline' => $sect['title'],
            'overlayText'     => $sect['photo_caption'],
            'overlayBtnText'  => $sect['photo_btn'],
            'overlayBtnUrl'   => $sect['photo_link'],
            'heading'         => $sect['title'],
            'content'         => $sect['text']
        ) );
    }
    
    // Build Intro Banner
    $banner = e3es_make_intro_banner_markup( array(
        'title'          => $title,
        'subtitle'       => $s['meta_desc'],
        'bgImageUrl'     => $hero_img,
        'bgOpacity'      => 0.85,
        'bgOverlayColor' => 'green',
        'bgFadeType'     => 'flat',
        'textAlignment'  => 'center',
        'textCase'       => 'uppercase'
    ) );
    
    // Build Overview Section
    $overview_block = e3es_make_two_column_block( array(
        'imageUrl'        => $overview_img,
        'imageAlt'        => $s['overview_title'],
        'reverse'         => false,
        'bgStyle'         => 'white',
        'icon'            => 'check-circle',
        'overlayHeadline' => $s['overview_title'],
        'overlayText'     => $s['overview_photo_caption'],
        'overlayBtnText'  => $s['overview_photo_btn'],
        'overlayBtnUrl'   => $s['overview_photo_link'],
        'heading'         => $s['overview_title'],
        'content'         => $s['overview_text']
    ) );
    
    // Build Comparison Table
    $table_block = e3es_make_comparison_table( $s['table_headers'], $s['table_rows'] );
    
    // Build FAQs Section
    $faq_block = e3es_make_faq_section( $s['faqs'] );
    
    // Build CTA Banner
    $cta_block = e3es_make_cta_banner(
        "Ready to Optimize Your Facility?",
        "Contact our Texas energy experts to schedule a free Preliminary Energy Study. Learn how you can fund modern upgrades using guaranteed utility savings.",
        "Request a Free Assessment",
        "/contact"
    );
    
    // Combine blocks into post content
    $post_content = implode( "\n\n", array(
        $banner,
        $overview_block,
        implode( "\n\n", $sections_html ),
        $table_block,
        $faq_block,
        $cta_block
    ) );
    
    // Seed WordPress post
    e3_seed_service( $pid, $title, $post_content );
    
    // Sync featured media ID so that the REST API exposes it properly too
    global $wpdb;
    $attachment_id = $wpdb->get_var( $wpdb->prepare(
        "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s",
        '%' . "{$slug}_hero.jpg"
    ) );
    if ( $attachment_id ) {
        update_post_meta( $pid, '_thumbnail_id', $attachment_id );
    }
}

echo "\n🎉 Parent services seeded successfully inside WordPress database using custom blocks!\n\n";
