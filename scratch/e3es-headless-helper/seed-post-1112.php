<?php
/**
 * Seed Top-Level LED Lighting Service Page (ID 1112)
 * Perfect parity to legacy-html/led-lighting.html + FAQs at the bottom
 */

$wp_load = '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';
if ( ! file_exists( $wp_load ) ) {
    die( "Cannot find wp-load.php at: {$wp_load}\n" );
}
define( 'ABSPATH_SKIP_REDIRECT', true );
require_once $wp_load;

// Set current user to admin and remove KSES filters to prevent escaping of HTML comments in block attributes
wp_set_current_user( 1 );
if ( function_exists( 'kses_remove_filters' ) ) {
    kses_remove_filters();
}

echo "🌱 Seeding LED Lighting Top-Level Service Page (ID 1112)...\n";

// Working paths in WordPress Media Library
$site_url = get_option('siteurl');

$led_hero  = $site_url . '/wp-content/uploads/2026/06/led-crop-1920x1080-1.jpg';
$led_sport = $site_url . '/wp-content/uploads/2026/06/54845357449_7bb9258e8b_k.jpg';
$led_int   = $site_url . '/wp-content/uploads/2026/06/51671231498_f84028afe5_k.jpg';
$led_park  = $site_url . '/wp-content/uploads/2026/06/led-600x400-1.jpg';
$led_gym   = $site_url . '/wp-content/uploads/2026/06/51670228367_9daa14b611_k.jpg';
$led_retro = $site_url . '/wp-content/uploads/2026/06/51466205353_2f6a5de945_k.jpg';
$led_trust = $site_url . '/wp-content/uploads/2026/06/53969622794_b49535a782_k.jpg';

$sup_photo = $site_url . '/wp-content/uploads/2026/06/superintendent.png';
$fac_photo = $site_url . '/wp-content/uploads/2026/06/facilities_director.png';
$bill_photo= $site_url . '/wp-content/uploads/2026/06/dl_B-Savarino-370x280-1.jpg';
$mun_photo = 'https://images.unsplash.com/photo-1560250097-0b93528c311a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=256&q=80';

// Slider projects
$proj_carrizo = $site_url . '/wp-content/uploads/2026/06/dl_Carrizo-Springs-8.jpg';
$proj_edcouch = $site_url . '/wp-content/uploads/2026/06/Edcouch.jpg';
$proj_ricardo = $site_url . '/wp-content/uploads/2026/06/Ricardo.jpg';
$proj_mercedes= $site_url . '/wp-content/uploads/2026/06/MERCEDES.jpg';
$proj_donna   = $site_url . '/wp-content/uploads/2026/06/Donna.jpg';


$banner_html = e3es_make_intro_banner_markup([
    'title'          => 'K-12 <span style="color:#8CC63F">LED</span> Lighting Solutions',
    'bgImageUrl'     => $led_hero,
    'bgOpacity'      => 0.75,
    'bgOverlayColor' => 'blue',
    'bgFadeType'     => 'flat',
    'textAlignment'  => 'center',
    'textCase'       => 'uppercase',
    'textShadow'     => 'subtle'
]);

$content = $banner_html . "\n\n" . <<<BLOCK

<!-- wp:e3es/two-column {"imageUrl":"{$led_sport}","imageAlt":"Sports lighting on football field","reverse":false,"bgStyle":"white","icon":"star"} -->
<section class="wp-block-e3es-two-column db-feature db-feature--white"><div class="db-feature__container"><div class="db-feature__content"><div class="db-feature__icon"></div><!-- wp:heading {"level":2} -->
<h2>Sports Lighting</h2>
<!-- /wp:heading --><!-- wp:paragraph -->
<p>Illuminate your football and soccer fields with professional LED sports lighting. Our solutions provide high-lumen output for even coverage and reduced glare, while optimized light placement eliminates shadows on baseball and softball fields. We ensure long-lasting, high-efficiency fixtures that meet league standards and enhance player safety and the fan experience.</p>
<!-- /wp:paragraph --><!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-e3es-button-inline-green-arrow"} -->
<div class="wp-block-button is-style-e3es-button-inline-green-arrow"><a class="wp-block-button__link wp-element-button" href="/about-us/contact">Learn More About Sports Lighting &rarr;</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div><div class="db-feature__image-wrapper"><img src="{$led_sport}" alt="Sports lighting on football field" class="db-feature__image"></div></div></section>
<!-- /wp:e3es/two-column -->

<!-- wp:e3es/full-width-testimonial {"quote":"The new lighting not only improved visibility but dramatically reduced our utility bills. We realized our annual savings estimate in just over eight months of tracking.","byline":"Texas School District","photoUrl":"{$sup_photo}","bgStyle":"white"} /-->

<!-- wp:e3es/two-column {"imageUrl":"{$led_int}","imageAlt":"Interior classroom LED lighting","reverse":true,"bgStyle":"grey","icon":"layers"} -->
<section class="wp-block-e3es-two-column db-feature db-feature--grey"><div class="db-feature__container db-feature__container--reverse"><div class="db-feature__content"><div class="db-feature__icon"></div><!-- wp:heading {"level":2} -->
<h2>Interior Lighting</h2>
<!-- /wp:heading --><!-- wp:paragraph -->
<p>Enhance the learning and working environment with modern interior LED lighting. We upgrade classrooms, hallways, offices, and common areas with high-efficiency fixtures that improve focus, reduce eye strain, and significantly lower energy costs. Smart dimming and occupancy sensors ensure your lighting works as efficiently as possible.</p>
<!-- /wp:paragraph --><!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-e3es-button-inline-green-arrow"} -->
<div class="wp-block-button is-style-e3es-button-inline-green-arrow"><a class="wp-block-button__link wp-element-button" href="/about-us/contact">Learn More About Interior Lighting &rarr;</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div><div class="db-feature__image-wrapper"><img src="{$led_int}" alt="Interior classroom LED lighting" class="db-feature__image"></div></div></section>
<!-- /wp:e3es/two-column -->

<!-- wp:e3es/two-column {"imageUrl":"{$led_park}","imageAlt":"Parking lot LED lighting","reverse":false,"bgStyle":"white","icon":"shield"} -->
<section class="wp-block-e3es-two-column db-feature db-feature--white"><div class="db-feature__container"><div class="db-feature__content"><div class="db-feature__icon"></div><!-- wp:heading {"level":2} -->
<h2>Parking Lot Lighting</h2>
<!-- /wp:heading --><!-- wp:paragraph -->
<p>Ensure the safety and security of your facilities after dark. We install bright, uniform LED parking lot and exterior lighting that eliminates dark spots and improves visibility for cameras and security personnel. Our durable fixtures reduce maintenance headaches and provide a welcoming environment for students, staff, and visitors.</p>
<!-- /wp:paragraph --><!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-e3es-button-inline-green-arrow"} -->
<div class="wp-block-button is-style-e3es-button-inline-green-arrow"><a class="wp-block-button__link wp-element-button" href="/about-us/contact">Learn More About Parking Lot Lighting &rarr;</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div><div class="db-feature__image-wrapper"><img src="{$led_park}" alt="Parking lot LED lighting" class="db-feature__image"></div></div></section>
<!-- /wp:e3es/two-column -->

<!-- wp:e3es/two-column {"imageUrl":"{$led_gym}","imageAlt":"Indoor gymnasium LED lighting","reverse":true,"bgStyle":"grey","icon":"check-circle"} -->
<section class="wp-block-e3es-two-column db-feature db-feature--grey"><div class="db-feature__container db-feature__container--reverse"><div class="db-feature__content"><div class="db-feature__icon"></div><!-- wp:heading {"level":2} -->
<h2>Indoor Arenas &amp; Gymnasiums</h2>
<!-- /wp:heading --><!-- wp:paragraph -->
<p>Provide uniform lighting for better ball tracking and reduced eye strain on tennis and pickleball courts. Our gymnasium and indoor arena installations feature energy-efficient solutions with dimmable and smart controls, giving you complete command over your facility&#8217;s environment and significantly reducing operational costs.</p>
<!-- /wp:paragraph --><!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-e3es-button-inline-green-arrow"} -->
<div class="wp-block-button is-style-e3es-button-inline-green-arrow"><a class="wp-block-button__link wp-element-button" href="/about-us/contact">Upgrade Your Arena Lighting &rarr;</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div><div class="db-feature__image-wrapper"><img src="{$led_gym}" alt="Indoor gymnasium LED lighting" class="db-feature__image"></div></div></section>
<!-- /wp:e3es/two-column -->

<!-- wp:e3es/full-width-testimonial {"quote":"They came in on nights and weekends so we never disrupted any classes. The LED upgrade was completely invisible to our daily operations.","byline":"Facilities Director","photoUrl":"{$fac_photo}","bgStyle":"light"} /-->

<!-- wp:e3es/two-column {"imageUrl":"{$led_retro}","imageAlt":"Retrofit and upgrade savings","reverse":false,"bgStyle":"green","icon":"dollar"} -->
<section class="wp-block-e3es-two-column db-feature db-feature--green"><div class="db-feature__container"><div class="db-feature__content"><div class="db-feature__icon"></div><!-- wp:heading {"level":2} -->
<h2>Retrofit &amp; Upgrade Savings</h2>
<!-- /wp:heading --><!-- wp:paragraph -->
<p>Upgrade outdated HID or metal halide fixtures to high-performance LED solutions. By partnering with top-tier manufacturers, we deliver precision design, significant cost savings, and superior performance. Whether you need a new installation or a retrofit, our integrated design-build approach ensures fixed costs with no expensive change orders.</p>
<!-- /wp:paragraph --><!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-e3es-button-inline-white-arrow"} -->
<div class="wp-block-button is-style-e3es-button-inline-white-arrow"><a class="wp-block-button__link wp-element-button" href="/about-us/contact">Calculate Your Savings &rarr;</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div><div class="db-feature__image-wrapper"><img src="{$led_retro}" alt="Retrofit and upgrade savings" class="db-feature__image"></div></div></section>
<!-- /wp:e3es/two-column -->

<!-- wp:e3es/full-width-testimonial {"quote":"This was probably the best run project that I’ve been part of. From the initial lighting audit to the final installation, the E3 team was professional and proactive.","byline":"Municipal Manager","photoUrl":"{$mun_photo}","bgStyle":"white"} /-->

<!-- wp:e3es/comparison-table -->
<section class="wp-block-e3es-comparison-table comparison-section"><div class="comparison-container"><table class="comparison-table"><thead><tr><th style="width:20%;border:none;background:transparent"></th><th style="width:40%">Traditional</th><th style="width:40%">E3 Design + Build</th></tr></thead><tbody>
    <!-- wp:e3es/comparison-row {"feature":"Structure","traditional":"Siloed Design & Install","e3":"Integrated Design-Build"} -->
    <tr class="wp-block-e3es-comparison-row"><th scope="row">Structure</th><td>Siloed Design &amp; Install</td><td>Integrated Design-Build</td></tr><!-- /wp:e3es/comparison-row -->
    <!-- wp:e3es/comparison-row {"feature":"Performance","traditional":"Standard HID / Halide","e3":"High-Efficiency LED & Controls"} -->
    <tr class="wp-block-e3es-comparison-row"><th scope="row">Performance</th><td>Standard HID / Halide</td><td>High-Efficiency LED &amp; Controls</td></tr><!-- /wp:e3es/comparison-row -->
    <!-- wp:e3es/comparison-row {"feature":"Savings","traditional":"Fragmented Maintenance","e3":"Precision Energy Savings"} -->
    <tr class="wp-block-e3es-comparison-row"><th scope="row">Savings</th><td>Fragmented Maintenance</td><td>Precision Energy Savings</td></tr><!-- /wp:e3es/comparison-row -->
    <!-- wp:e3es/comparison-row {"feature":"Accountability","traditional":"Multiple Vendors","e3":"Single-Source Provider"} -->
    <tr class="wp-block-e3es-comparison-row"><th scope="row">Accountability</th><td>Multiple Vendors</td><td>Single-Source Provider</td></tr><!-- /wp:e3es/comparison-row -->
</tbody></table></div></section>
<!-- /wp:e3es/comparison-table -->

<!-- wp:e3es/two-column {"imageUrl":"{$led_trust}","imageAlt":"A Partner You Can Trust","reverse":true,"bgStyle":"white","icon":"users"} -->
<section class="wp-block-e3es-two-column db-feature db-feature--white"><div class="db-feature__container db-feature__container--reverse"><div class="db-feature__content"><div class="db-feature__icon"></div><!-- wp:heading {"level":2} -->
<h2>A Partner You Can Trust</h2>
<!-- /wp:heading --><!-- wp:paragraph -->
<p>Unlike massive, traditional ESCOs with high overhead that focus solely on long-term federal projects, we are your local, nimble partner in Texas. We understand energy management means more than simply addressing maintenance issues. It means creating the most sustainable, well-lit, and comfortable environments to free resources for your real mission.</p>
<!-- /wp:paragraph --><!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-e3es-button-inline-green-arrow"} -->
<div class="wp-block-button is-style-e3es-button-inline-green-arrow"><a class="wp-block-button__link wp-element-button" href="/about-us/contact">Connect With Our Team &rarr;</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div><div class="db-feature__image-wrapper"><img src="{$led_trust}" alt="A Partner You Can Trust" class="db-feature__image"></div></div></section>
<!-- /wp:e3es/two-column -->

<!-- wp:e3es/full-width-testimonial {"quote":"You'll never hear us say, 'It's not our problem.' We take ownership of every single fixture we install.","byline":"Bill Savarino, E3 Entegral Solutions","photoUrl":"{$bill_photo}","bgStyle":"white"} /-->

<!-- wp:e3es/region-showcase {"heading":"Texas Project Success","bgStyle":"light"} -->
<!-- wp:group {"className":"region-showcase-card"} -->
<div class="wp-block-group region-showcase-card"><!-- wp:image {"className":"region-showcase-card__img"} --><figure class="wp-block-image region-showcase-card__img"><img src="{$proj_carrizo}" alt="Carrizo Springs"/></figure><!-- /wp:image --><div class="region-showcase-card__body"><h3 class="region-showcase-card__title">Carrizo Springs CISD</h3><p class="region-showcase-card__amount">$14.9M Facility Renewal</p><p class="region-showcase-card__text">A systems-based improvement program including 100 rooftop HVAC units, LED lighting, and building envelope upgrades to enhance indoor air quality and comfort.</p></div></div>
<!-- /wp:group -->
<!-- wp:group {"className":"region-showcase-card"} -->
<div class="wp-block-group region-showcase-card"><!-- wp:image {"className":"region-showcase-card__img"} --><figure class="wp-block-image region-showcase-card__img"><img src="{$proj_edcouch}" alt="Edcouch-Elsa ISD"/></figure><!-- /wp:image --><div class="region-showcase-card__body"><h3 class="region-showcase-card__title">Edcouch-Elsa ISD</h3><p class="region-showcase-card__amount">$15.5M HVAC &amp; Controls Retrofit</p><p class="region-showcase-card__text">A comprehensive project involving redesigning the High School chilled water system, installing new Alerton Automation Systems, and LED retrofits.</p></div></div>
<!-- /wp:group -->
<!-- wp:group {"className":"region-showcase-card"} -->
<div class="wp-block-group region-showcase-card"><!-- wp:image {"className":"region-showcase-card__img"} --><figure class="wp-block-image region-showcase-card__img"><img src="{$proj_ricardo}" alt="Ricardo ISD"/></figure><!-- /wp:image --><div class="region-showcase-card__body"><h3 class="region-showcase-card__title">Ricardo ISD</h3><p class="region-showcase-card__amount">SECO Grant Funded Savings</p><p class="region-showcase-card__text">Utilized ARRA SECO Grants and District funds for a $969K project featuring district-wide controls, 44 new HVAC units, and lighting upgrades.</p></div></div>
<!-- /wp:group -->
<!-- wp:group {"className":"region-showcase-card"} -->
<div class="wp-block-group region-showcase-card"><!-- wp:image {"className":"region-showcase-card__img"} --><figure class="wp-block-image region-showcase-card__img"><img src="{$proj_mercedes}" alt="Mercedes ISD"/></figure><!-- /wp:image --><div class="region-showcase-card__body"><h3 class="region-showcase-card__title">Mercedes ISD</h3><p class="region-showcase-card__amount">$9.5M QZAB Project</p><p class="region-showcase-card__text">An energy-efficiency program retrofitting 10,500 lighting fixtures and major HVAC replacements, avoiding $700K+ in annual utility costs.</p></div></div>
<!-- /wp:group -->
<!-- wp:group {"className":"region-showcase-card"} -->
<div class="wp-block-group region-showcase-card"><!-- wp:image {"className":"region-showcase-card__img"} --><figure class="wp-block-image region-showcase-card__img"><img src="{$proj_donna}" alt="Donna ISD"/></figure><!-- /wp:image --><div class="region-showcase-card__body"><h3 class="region-showcase-card__title">Donna ISD</h3><p class="region-showcase-card__amount">$7.9M Conservation Program</p><p class="region-showcase-card__text">A comprehensive LED retrofit across 20 campuses and mechanical retrofits at 9 campuses, successfully completed with zero change orders.</p></div></div>
<!-- /wp:group -->
<!-- /wp:e3es/region-showcase -->

<!-- wp:e3es/faq-section -->
<section class="wp-block-e3es-faq-section faq-section"><div class="faq-section__container">
    <!-- wp:heading {"level":3} -->
    <h3>What is the typical energy savings from an LED lighting upgrade?</h3>
    <!-- /wp:heading -->
    <!-- wp:paragraph -->
    <p>LED upgrades typically reduce lighting energy consumption by 50% to 70%. When combined with smart controls, occupancy sensors, and dimming systems, overall energy savings can exceed 80% while significantly reducing ongoing maintenance costs.</p>
    <!-- /wp:paragraph -->
    
    <!-- wp:heading {"level":3} -->
    <h3>How does E3 minimize disruptions to daily school or business operations during installation?</h3>
    <!-- /wp:heading -->
    <!-- wp:paragraph -->
    <p>We utilize a "ghost" installation strategy—our crews work evenings, nights, and weekends to complete all upgrades. This ensures that classrooms, offices, and common areas are clean, safe, and ready for use during regular business hours with zero disruption to your daily operations.</p>
    <!-- /wp:paragraph -->
    
    <!-- wp:heading {"level":3} -->
    <h3>Can we upgrade sports field lighting to LED, and what are the benefits?</h3>
    <!-- /wp:heading -->
    <!-- wp:paragraph -->
    <p>Yes. Upgrading sports fields to LED improves player visibility, eliminates shadow zones, and meets athletic league standards. LED fixtures offer instant on/off capabilities (no warm-up time required) and have a lifespan of over 50,000 hours, virtually eliminating the need for bulb replacements on high light poles.</p>
    <!-- /wp:paragraph -->
    
    <!-- wp:heading {"level":3} -->
    <h3>Are there grants or cooperatives available in Texas to help fund these projects?</h3>
    <!-- /wp:heading -->
    <!-- wp:paragraph -->
    <p>Absolutely. E3 helps public entities secure utility rebates, SECO grants, and funding through the state's LoanSTAR program. We are also pre-approved on major purchasing cooperatives like BuyBoard and TIPS, which allows our clients to skip the traditional RFP process and fast-track their project timelines.</p>
    <!-- /wp:paragraph -->
</div></section>
<!-- /wp:e3es/faq-section -->
BLOCK;

$result = wp_update_post( [
    'ID'           => 1112,
    'post_title'   => 'Lighting',
    'post_content' => wp_slash( $content ),
    'post_status'  => 'publish',
    'post_type'    => 'services',
], true );

if ( is_wp_error( $result ) ) {
    echo "❌ ERROR: " . $result->get_error_message() . "\n";
} else {
    echo "✅ Success: Seeded LED Lighting Top-Level Service Page (ID 1112)\n";
}
