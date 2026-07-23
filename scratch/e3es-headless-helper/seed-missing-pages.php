<?php
/**
 * E3ES Missing Pages Seeder
 *
 * Creates:
 *   - page:    panhandle       (Panhandle region page)
 *   - page:    funding-stories (Funding Stories listing page)
 *   - clients: gwh             (Goodall-Witcher Healthcare)
 *   - clients: houston-cc      (Houston Community College)
 *
 * Run via Local PHP:
 *   "/Users/bryanpaul/Library/Application Support/Local/lightning-services/php-8.2.29+0/bin/darwin-arm64/bin/php" \
 *     "/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-content/plugins/e3es-headless-helper/seed-missing-pages.php"
 */

// ── Bootstrap WordPress ──────────────────────────────────────────────────────
// Use absolute path to handle directory names with spaces
$wp_load = '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';
if ( ! file_exists( $wp_load ) ) {
    // Fallback: relative from plugin dir (4 levels up)
    $wp_load = dirname( __FILE__ ) . '/../../../../wp-load.php';
}
if ( ! file_exists( $wp_load ) ) {
    die( "Cannot find wp-load.php. Checked:\n  /Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php\n" );
}
define( 'ABSPATH_SKIP_REDIRECT', true );
require_once $wp_load;

// Set current user to admin and remove KSES filters to prevent escaping of HTML comments in block attributes
wp_set_current_user( 1 );
if ( function_exists( 'kses_remove_filters' ) ) {
    kses_remove_filters();
}

$site_url = get_option('siteurl');

echo "\n🌱 E3ES Missing Pages Seeder Starting...\n\n";

// Helper to fetch service ID by slug
function e3_get_service_id_by_slug( $slug ) {
    $posts = get_posts( array(
        'post_type' => 'services',
        'name'      => $slug,
        'numberposts' => 1,
        'post_status' => 'any'
    ) );
    return ! empty( $posts ) ? $posts[0]->ID : 0;
}

// ── Helpers ──────────────────────────────────────────────────────────────────
function e3_create_or_update_page( $slug, $title, $content ) {
    $existing = get_page_by_path( $slug, OBJECT, 'page' );
    if ( $existing ) {
        $result = wp_update_post( [
            'ID'           => $existing->ID,
            'post_title'   => $title,
            'post_content' => wp_slash( $content ),
            'post_status'  => 'publish',
        ], true );
        if ( is_wp_error( $result ) ) {
            echo "  ❌ ERROR (page '{$slug}'): " . $result->get_error_message() . "\n";
        } else {
            echo "  ✅ Updated: [page] {$title} (#{$result})\n";
        }
    } else {
        $new_id = wp_insert_post( [
            'post_title'   => $title,
            'post_name'    => $slug,
            'post_content' => wp_slash( $content ),
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ], true );
        if ( is_wp_error( $new_id ) ) {
            echo "  ❌ Could not create page '{$slug}': " . $new_id->get_error_message() . "\n";
        } else {
            echo "  ✅ Created: [page] {$title} (#{$new_id})\n";
        }
    }
}

function e3_create_or_update_client( $slug, $title, $content ) {
    $existing = get_page_by_path( $slug, OBJECT, 'clients' );
    if ( $existing ) {
        $result = wp_update_post( [
            'ID'           => $existing->ID,
            'post_title'   => $title,
            'post_content' => wp_slash( $content ),
            'post_status'  => 'publish',
            'post_type'    => 'clients',
        ], true );
        if ( is_wp_error( $result ) ) {
            echo "  ❌ ERROR (client '{$slug}'): " . $result->get_error_message() . "\n";
        } else {
            echo "  ✅ Updated: [clients] {$title} (#{$result})\n";
        }
    } else {
        $new_id = wp_insert_post( [
            'post_title'   => $title,
            'post_name'    => $slug,
            'post_content' => wp_slash( $content ),
            'post_status'  => 'publish',
            'post_type'    => 'clients',
        ], true );
        if ( is_wp_error( $new_id ) ) {
            echo "  ❌ Could not create client '{$slug}': " . $new_id->get_error_message() . "\n";
        } else {
            echo "  ✅ Created: [clients] {$title} (#{$new_id})\n";
        }
    }
}

// ═══════════════════════════════════════════════════════════════════════════════
// PAGE: Panhandle
// Source: panhandle.html (regional page — same structure as east-texas, south-texas)
// ═══════════════════════════════════════════════════════════════════════════════
echo "📄 Page: Panhandle...\n";

$hero_img    = $site_url . '/wp-content/uploads/2026/06/Texas-Funding-Solutions.jpg';
$bill_photo  = '/images/dl_B-Savarino-370x280-400x400-400x400-400x400-400x400-400x400-400x400.jpg';
$sup_photo   = '/images/superintendent-400x400-400x400-400x400-400x400-400x400-400x400.png';
$fac_photo   = '/images/facilities_director-400x400-400x400-400x400-400x400-400x400-400x400.png';
$fund_img    = '/wp-content/uploads/2026/06/funding-2-600x400.png';

$panhandle_banner = e3es_make_intro_banner_markup([
    'title'          => 'Panhandle Texas',
    'subtitle'       => 'Delivering energy-efficient infrastructure and reliable HVAC systems to schools across the Texas Panhandle. We build facilities that stand up to extreme weather and serve communities built on hard work and big sky values.',
    'bgImageUrl'     => $hero_img,
    'bgOpacity'      => 0.85,
    'bgOverlayColor' => 'green',
    'bgFadeType'     => 'horizontal',
    'textAlignment'  => 'center'
]);

$hvac_id = e3_get_service_id_by_slug( 'hvac-system-upgrades-2' );
$led_id  = e3_get_service_id_by_slug( 'led-lighting-2' );
$envelope_id = e3_get_service_id_by_slug( 'building-envelope' );
$bas_id  = e3_get_service_id_by_slug( 'building-automation-systems-2' );

$selected_ids_arr = array_filter( [ $hvac_id, $led_id, $envelope_id, $bas_id ] );
$selected_ids_json = json_encode( array_map( 'intval', $selected_ids_arr ) );

$panhandle = $panhandle_banner . "\n\n" . <<<BLOCK

<!-- wp:e3es/region-showcase {"heading":"Featured Panhandle Projects","bgStyle":"white"} -->
<!-- wp:group {"className":"region-showcase-card"} --><div class="wp-block-group region-showcase-card"><!-- wp:image {"className":"region-showcase-card__img"} --><figure class="wp-block-image region-showcase-card__img"><img src="/images/dl_Carrizo-Springs-8-400x300-400x300-400x300-400x300-400x300-400x300.jpg" alt="Panhandle ISD"/></figure><!-- /wp:image --><div class="region-showcase-card__body"><h3 class="region-showcase-card__title">Panhandle ISD</h3><p class="region-showcase-card__amount">HVAC &amp; LED Upgrade</p><p class="region-showcase-card__text">A district-wide facility improvement program featuring high-efficiency HVAC replacements and LED lighting retrofits designed to combat the Panhandle's extreme temperature swings.</p></div></div><!-- /wp:group --><!-- wp:group {"className":"region-showcase-card"} --><div class="wp-block-group region-showcase-card"><!-- wp:image {"className":"region-showcase-card__img"} --><figure class="wp-block-image region-showcase-card__img"><img src="/images/Ricardo-400x300-400x300-400x300-400x300-400x300-400x300.jpg" alt="Amarillo ISD"/></figure><!-- /wp:image --><div class="region-showcase-card__body"><h3 class="region-showcase-card__title">Amarillo Area District</h3><p class="region-showcase-card__amount">SECO Grant Funded</p><p class="region-showcase-card__text">Leveraged SECO grant funding for a comprehensive controls and HVAC upgrade across multiple campuses, delivering significant annual energy savings without a bond election.</p></div></div><!-- /wp:group --><!-- wp:group {"className":"region-showcase-card"} --><div class="wp-block-group region-showcase-card"><!-- wp:image {"className":"region-showcase-card__img"} --><figure class="wp-block-image region-showcase-card__img"><img src="/images/MERCEDES-400x300-400x300-400x300-400x300-400x300-400x300.jpg" alt="Lubbock Area Schools"/></figure><!-- /wp:image --><div class="region-showcase-card__body"><h3 class="region-showcase-card__title">Lubbock Area Schools</h3><p class="region-showcase-card__amount">Building Automation Retrofit</p><p class="region-showcase-card__text">Installed modern building automation systems across aging campuses, giving facilities teams centralized monitoring and control over HVAC, lighting, and energy usage.</p></div></div><!-- /wp:group -->
<!-- /wp:e3es/region-showcase -->

<!-- wp:e3es/two-column-cover {"bgStyle":"grey"} -->
<section class="wp-block-e3es-two-column-cover db-feature db-feature--grey"><div class="db-feature__container"><!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column {"width":"66.66%"} -->
<div class="wp-block-column" style="flex-basis:66.66%"><!-- wp:heading {"level":2} -->
<h2>The Design+Build Advantage in the Panhandle</h2>
<!-- /wp:heading --><!-- wp:paragraph -->
<p>For Panhandle schools, traditional construction often means delays, change orders, and finger-pointing — especially when dealing with extreme temperature swings from 110°F summers to ice storms in winter. E3’s integrated Design+Build approach completely flips this script.</p>
<!-- /wp:paragraph --><!-- wp:paragraph -->
<p>By keeping engineering, auditing, and project management under one roof, we guarantee your agreed-upon scope of work at the exact stated price. We fast-track project timelines, ensuring critical upgrades like HVAC overhauls and insulation improvements are completed before the brutal Panhandle summer arrives. The result? Modern, comfortable learning environments delivered on time and on budget.</p>
<!-- /wp:paragraph --><!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/our-approach">Learn More About Design+Build</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column --><!-- wp:column {"width":"33.33%"} -->
<div class="wp-block-column" style="flex-basis:33.33%"><!-- wp:e3es/rep-contact-card {"name":"Bill","role":"Business Development, Panhandle","bio":"Bill is most proud of his beautiful family. All this time we didn't realize we were making memories, we just knew we were having fun!","photoUrl":"{$bill_photo}","emailLabel":"Email Bill","emailHref":"mailto:bill@e3es.com","callLabel":"Schedule a Call","callHref":"tel:+1"} /-->
</div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div></section>
<!-- /wp:e3es/two-column-cover -->

<!-- wp:e3es/full-width-testimonial {"quote":"E3 upgraded our aging facilities into modern learning environments without disrupting the school year. Their process was smooth from start to finish.","byline":"Superintendent, Panhandle ISD","photoUrl":"{$sup_photo}","bgStyle":"light"} /-->

<!-- wp:e3es/two-column {"imageUrl":"{$fund_img}","imageAlt":"Funding success in the Panhandle","reverse":true,"bgStyle":"grey","icon":"dollar"} -->
<section class="wp-block-e3es-two-column db-feature db-feature--grey"><div class="db-feature__container db-feature__container--reverse"><div class="db-feature__content"><div class="db-feature__icon"></div><!-- wp:heading {"level":2} -->
<h2>Securing Funding for Panhandle Schools</h2>
<!-- /wp:heading --><!-- wp:paragraph -->
<p>We know that school budgets are tighter than ever. That’s why we don’t just engineer solutions — we find the money to pay for them. We make it completely <strong>Easy</strong> for our <strong>Local</strong> Texas partners to upgrade facilities by leveraging purchasing cooperatives like BuyBoard and TIPS. Because the co-op has already completed the competitive bidding process, you can <strong>skip the RFP</strong>, bypass traditional procurement hurdles and start transforming your campus sooner. As your <strong>Trusted</strong> advisor, our experts also specialize in navigating complex Texas state grants, including <strong>SECO</strong> and the <strong>LoanSTAR</strong> program.</p>
<!-- /wp:paragraph --><!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/funding">Explore Funding Options</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div><div class="db-feature__image-wrapper"><img src="{$fund_img}" alt="Funding success in the Panhandle" class="db-feature__image"></div></div></section>
<!-- /wp:e3es/two-column -->

<!-- wp:e3es/full-width-testimonial {"quote":"Working with E3 was the easiest procurement process we’ve experienced. They handled everything from engineering to grant applications — we just said yes.","byline":"Facilities Director, Panhandle School District","photoUrl":"{$fac_photo}","bgStyle":"white"} /-->

<!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center">Our K-12 Solutions in the Panhandle</h2>
<!-- /wp:heading -->

<!-- wp:e3es/services-grid {"mode":"manual","selectedIds":{$selected_ids_json}} /-->
BLOCK;

e3_create_or_update_page( 'panhandle', 'Panhandle Texas', $panhandle );

// ═══════════════════════════════════════════════════════════════════════════════
// PAGE: Funding Stories
// Source: funding-stories.html (listing/hub page for funding success stories)
// ═══════════════════════════════════════════════════════════════════════════════
echo "\n📄 Page: Funding Stories...\n";

$fund_hero = $site_url . '/wp-content/uploads/2026/06/Texas-Funding-Solutions.jpg';

$funding_stories_banner = e3es_make_intro_banner_markup([
    'title'          => 'Funding Success Stories',
    'subtitle'       => 'Real Texas schools. Real projects. Real savings. See how E3 has secured millions in grants and cooperative purchasing deals for districts just like yours.',
    'bgImageUrl'     => $fund_hero,
    'bgOpacity'      => 0.85,
    'bgOverlayColor' => 'green',
    'bgFadeType'     => 'flat',
    'textAlignment'  => 'center',
    'textCase'       => 'uppercase'
]);

$funding_stories = $funding_stories_banner . "\n\n" . <<<BLOCK

<!-- wp:paragraph -->
<p></p>
<!-- /wp:paragraph -->

<!-- wp:e3es/two-column {"imageUrl":"/wp-content/uploads/2026/06/funding-2-600x400.png","imageAlt":"SECO Grant Success","reverse":false,"bgStyle":"white","icon":"check-circle"} -->
<section class="wp-block-e3es-two-column db-feature db-feature--white"><div class="db-feature__container"><div class="db-feature__content"><div class="db-feature__icon"></div><!-- wp:heading {"level":2} -->
<h2>$14.9M &#8212; Carrizo Springs CISD</h2>
<!-- /wp:heading --><!-- wp:paragraph -->
<p>A systems-based facility renewal including 100 rooftop HVAC units, LED lighting upgrades, and building envelope improvements. Funded through a combination of SECO grants and district bonds, this project delivered immediate comfort improvements and significant annual utility savings.</p>
<!-- /wp:paragraph --><!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/clients/carrizo-springs-cisd">Read Full Case Study</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div><div class="db-feature__image-wrapper"><img src="/wp-content/uploads/2026/06/funding-2-600x400.png" alt="SECO Grant Success" class="db-feature__image"></div></div></section>
<!-- /wp:e3es/two-column -->

<!-- wp:e3es/two-column {"imageUrl":"/images/MERCEDES-400x300-400x300-400x300-400x300-400x300-400x300.jpg","imageAlt":"Mercedes ISD QZAB","reverse":true,"bgStyle":"grey","icon":"dollar"} -->
<section class="wp-block-e3es-two-column db-feature db-feature--grey"><div class="db-feature__container db-feature__container--reverse"><div class="db-feature__content"><div class="db-feature__icon"></div><!-- wp:heading {"level":2} -->
<h2>$9.5M &#8212; Mercedes ISD (QZAB)</h2>
<!-- /wp:heading --><!-- wp:paragraph -->
<p>An energy-efficiency program retrofitting 10,500 lighting fixtures and performing major HVAC replacements across every campus. Funded through Qualified Zone Academy Bonds (QZAB), this project is projected to avoid $700K+ in annual utility costs while improving student learning environments.</p>
<!-- /wp:paragraph --><!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/clients/mercedes-isd">Read Full Case Study</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div><div class="db-feature__image-wrapper"><img src="/images/MERCEDES-400x300-400x300-400x300-400x300-400x300-400x300.jpg" alt="Mercedes ISD QZAB" class="db-feature__image"></div></div></section>
<!-- /wp:e3es/two-column -->

<!-- wp:e3es/two-column {"imageUrl":"/images/Donna-400x300-400x300-400x300-400x300-400x300-400x300.jpg","imageAlt":"Donna ISD Conservation Program","reverse":false,"bgStyle":"white","icon":"layers"} -->
<section class="wp-block-e3es-two-column db-feature db-feature--white"><div class="db-feature__container"><div class="db-feature__content"><div class="db-feature__icon"></div><!-- wp:heading {"level":2} -->
<h2>$7.9M &#8212; Donna ISD Conservation</h2>
<!-- /wp:heading --><!-- wp:paragraph -->
<p>A comprehensive LED retrofit across 20 campuses and mechanical retrofits at 9 campuses, successfully completed with zero change orders. This project stands as proof that with the right partner, complex multi-campus programs can be delivered exactly as promised — on time and on budget.</p>
<!-- /wp:paragraph --><!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/clients/donna-isd">Read Full Case Study</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div><div class="db-feature__image-wrapper"><img src="/images/Donna-400x300-400x300-400x300-400x300-400x300-400x300.jpg" alt="Donna ISD Conservation Program" class="db-feature__image"></div></div></section>
<!-- /wp:e3es/two-column -->

<!-- wp:e3es/two-column {"imageUrl":"/images/Edcouch-400x300-400x300-400x300-400x300-400x300-400x300.jpg","imageAlt":"Edcouch-Elsa HVAC Retrofit","reverse":true,"bgStyle":"grey","icon":"star"} -->
<section class="wp-block-e3es-two-column db-feature db-feature--grey"><div class="db-feature__container db-feature__container--reverse"><div class="db-feature__content"><div class="db-feature__icon"></div><!-- wp:heading {"level":2} -->
<h2>$15.5M &#8212; Edcouch-Elsa ISD</h2>
<!-- /wp:heading --><!-- wp:paragraph -->
<p>A comprehensive project involving redesigning the High School chilled water system, installing new Alerton Building Automation Systems, and district-wide LED retrofits. This is one of E3’s most complex projects in South Texas, showcasing our ability to manage large-scale, multi-system upgrades seamlessly.</p>
<!-- /wp:paragraph --><!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/clients/edcouch-elsa-isd">Read Full Case Study</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div><div class="db-feature__image-wrapper"><img src="/images/Edcouch-400x300-400x300-400x300-400x300-400x300-400x300.jpg" alt="Edcouch-Elsa HVAC Retrofit" class="db-feature__image"></div></div></section>
<!-- /wp:e3es/two-column -->

<!-- wp:e3es/full-width-testimonial {"quote":"E3 secured over $2 million in matching grants for our district — allowing us to completely overhaul our aging infrastructure without raising local taxes or passing a bond.","byline":"Superintendent, West Texas ISD","photoUrl":"/images/superintendent-400x400-400x400-400x400-400x400-400x400-400x400.png","bgStyle":"green"} /-->

<!-- wp:paragraph -->
<p></p>
<!-- /wp:paragraph -->

<!-- wp:group {"style":{"spacing":{"padding":{"top":"4rem","bottom":"4rem"}}},"className":"alignfull","backgroundColor":"base-2"} -->
<div class="wp-block-group alignfull has-base-2-background-color has-background" style="padding-top:4rem;padding-bottom:4rem"><!-- wp:heading {"textAlign":"center","level":2} -->
<h2 class="has-text-align-center">Ready to Explore Your Funding Options?</h2>
<!-- /wp:heading --><!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">Every district is different. Let our funding experts analyze your situation and identify the best path forward &#8212; at no cost to you.</p>
<!-- /wp:paragraph --><!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/contact">Talk to a Funding Expert</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->
BLOCK;

e3_create_or_update_page( 'funding-stories', 'Funding Success Stories', $funding_stories );

// ═══════════════════════════════════════════════════════════════════════════════
// CLIENT: Goodall-Witcher Healthcare (gwh)
// Source: gwh.html — placeholder page, Healthcare | Central Texas
// ═══════════════════════════════════════════════════════════════════════════════
echo "\n🤝 Client: Goodall-Witcher Healthcare...\n";

$gwh_hero = $site_url . '/wp-content/uploads/2026/06/gwh.jpg';

$gwh_banner = e3es_make_intro_banner_markup([
    'title'          => 'Goodall-Witcher Healthcare',
    'bgImageUrl'     => $gwh_hero,
    'bgOpacity'      => 0.85,
    'bgOverlayColor' => 'green',
    'bgFadeType'     => 'flat',
    'focalPointX'    => 0.5,
    'focalPointY'    => 0.5,
    'region'         => 'Central Texas',
    'industry'       => 'Healthcare'
]);

$gwh = $gwh_banner . "\n\n" . <<<BLOCK

<!-- wp:e3es/two-column {"imageUrl":"/wp-content/uploads/2026/06/funding-2-600x400.png","imageAlt":"Goodall-Witcher Healthcare Facility","reverse":false,"bgStyle":"white","icon":"check-circle"} -->
<section class="wp-block-e3es-two-column db-feature db-feature--white"><div class="db-feature__container"><div class="db-feature__content"><div class="db-feature__icon"></div><!-- wp:heading {"level":2} -->
<h2>Project Overview</h2>
<!-- /wp:heading --><!-- wp:paragraph -->
<p>Goodall-Witcher Healthcare is a long-standing E3 partner in Central Texas. This healthcare facility partnered with E3 to address aging HVAC infrastructure and improve energy efficiency across their campus. E3’s Design+Build approach allowed the project to be completed with minimal disruption to patient care operations.</p>
<!-- /wp:paragraph --><!-- wp:paragraph -->
<p><strong>Industry:</strong> Healthcare<br/><strong>Region:</strong> Central Texas<br/><strong>Services:</strong> HVAC Replacement, Building Automation, LED Lighting</p>
<!-- /wp:paragraph --><!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/contact">Request a Consultation</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div><div class="db-feature__image-wrapper"><img src="/wp-content/uploads/2026/06/funding-2-600x400.png" alt="Goodall-Witcher Healthcare Facility" class="db-feature__image"></div></div></section>
<!-- /wp:e3es/two-column -->

<!-- wp:e3es/full-width-testimonial {"quote":"E3\u2019s team was professional, efficient, and deeply respectful of our need to maintain operations throughout the upgrade process. The results exceeded our expectations.","byline":"Facilities Director, Goodall-Witcher Healthcare","photoUrl":"/images/facilities_director-400x400-400x400-400x400-400x400-400x400-400x400.png","bgStyle":"light"} /-->
BLOCK;

e3_create_or_update_client( 'gwh', 'Goodall-Witcher Healthcare', $gwh );

// ═══════════════════════════════════════════════════════════════════════════════
// CLIENT: Houston Community College (houston-cc)
// Source: houston-cc.html — placeholder page, Higher Education | Coast Texas
// ═══════════════════════════════════════════════════════════════════════════════
echo "\n🤝 Client: Houston Community College...\n";

$hcc_hero = $site_url . '/wp-content/uploads/2026/06/elyson-new-hcc-campus.jpg';

$houston_cc_banner = e3es_make_intro_banner_markup([
    'title'          => 'Houston Community College',
    'bgImageUrl'     => $hcc_hero,
    'bgOpacity'      => 0.85,
    'bgOverlayColor' => 'green',
    'bgFadeType'     => 'flat',
    'focalPointX'    => 0.5,
    'focalPointY'    => 0.5,
    'region'         => 'Gulf Coast Texas',
    'industry'       => 'Higher Education'
]);

$houston_cc = $houston_cc_banner . "\n\n" . <<<BLOCK

<!-- wp:e3es/two-column {"imageUrl":"/wp-content/uploads/2026/06/funding-2-600x400.png","imageAlt":"Houston Community College Campus","reverse":false,"bgStyle":"white","icon":"layers"} -->
<section class="wp-block-e3es-two-column db-feature db-feature--white"><div class="db-feature__container"><div class="db-feature__content"><div class="db-feature__icon"></div><!-- wp:heading {"level":2} -->
<h2>Project Overview</h2>
<!-- /wp:heading --><!-- wp:paragraph -->
<p>Houston Community College (HCC) is one of the largest community college systems in Texas, serving over 70,000 students across multiple campuses in the greater Houston area. E3 partnered with HCC to deliver a comprehensive energy-efficiency and facility upgrade program, leveraging cooperative purchasing to streamline procurement across their sprawling campus network.</p>
<!-- /wp:paragraph --><!-- wp:paragraph -->
<p><strong>Industry:</strong> Higher Education<br/><strong>Region:</strong> Gulf Coast Texas<br/><strong>Services:</strong> HVAC Systems, Building Automation, LED &amp; Exterior Lighting, Energy Auditing</p>
<!-- /wp:paragraph --><!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/contact">Request a Consultation</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div><div class="db-feature__image-wrapper"><img src="/wp-content/uploads/2026/06/funding-2-600x400.png" alt="Houston Community College Campus" class="db-feature__image"></div></div></section>
<!-- /wp:e3es/two-column -->

<!-- wp:e3es/full-width-testimonial {"quote":"E3\u2019s expertise with cooperative purchasing allowed us to bypass months of procurement delays and get our campus upgrades underway far sooner than we anticipated.","byline":"Facilities Manager, Houston Community College","photoUrl":"/images/facilities_director-400x400-400x400-400x400-400x400-400x400-400x400.png","bgStyle":"light"} /-->
BLOCK;

e3_create_or_update_client( 'houston-cc', 'Houston Community College', $houston_cc );


// ── Done ─────────────────────────────────────────────────────────────────────
echo "\n🎉 All missing pages seeded successfully!\n";
echo "   Next steps:\n";
echo "   1. Run 'npm run build' in astro-e3es to rebuild the frontend.\n";
echo "   2. Visit http://e3es2026.local/panhandle to verify the new page.\n";
echo "   3. Visit http://e3es2026.local/funding-stories to verify.\n";
echo "   4. Visit http://e3es2026.local/clients/gwh to verify.\n";
echo "   5. Visit http://e3es2026.local/clients/houston-cc to verify.\n\n";
