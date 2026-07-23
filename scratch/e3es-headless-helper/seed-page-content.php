<?php
/**
 * E3ES Comprehensive Page Content Seeder
 * 
 * Wipes and rebuilds content for:
 *   Pages: Partners in Funding (9), Our Approach (6), K-12 Schools (10), Services (11)
 *   Services post type: K-12 Lighting (1132)
 *   Clients post type: South Texas regional — create if missing
 * 
 * Run via Local PHP:
 *   "/Users/bryanpaul/Library/Application Support/Local/lightning-services/php-8.2.29+0/bin/darwin-arm64/bin/php" \
 *     "/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-content/plugins/e3es-headless-helper/seed-page-content.php"
 */

// ── Bootstrap WordPress ──────────────────────────────────────────────────────
// Plugin lives at: wp-content/plugins/e3es-headless-helper/
// wp-load.php is at: ../../../../wp-load.php  (4 levels up)
$wp_load = dirname( __FILE__ ) . '/../../../../wp-load.php';
if ( ! file_exists( $wp_load ) ) {
    $wp_load = dirname( __FILE__ ) . '/../../../wp-load.php';
}
if ( ! file_exists( $wp_load ) ) {
    $wp_load = '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';
}
if ( ! file_exists( $wp_load ) ) {
    die( "Cannot find wp-load.php. Checked:\n  " . dirname( __FILE__ ) . "/../../../../wp-load.php\n  " . dirname( __FILE__ ) . "/../../../wp-load.php\n" );
}
define( 'ABSPATH_SKIP_REDIRECT', true );
require_once $wp_load;

// Set current user to admin and remove KSES filters to prevent escaping of HTML comments in block attributes
wp_set_current_user( 1 );
if ( function_exists( 'kses_remove_filters' ) ) {
    kses_remove_filters();
}

$site_url = get_option('siteurl');

echo "\n🌱 E3ES Content Seeder Starting...\n\n";

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

// Helper 
function e3_seed( $id, $title, $content, $post_type = 'page' ) {
    $result = wp_update_post( [
        'ID'           => $id,
        'post_title'   => $title,
        'post_content' => wp_slash( $content ),
        'post_status'  => 'publish',
        'post_type'    => $post_type,
    ], true );
    if ( is_wp_error( $result ) ) {
        echo "  ❌ ERROR ({$post_type} #{$id}): " . $result->get_error_message() . "\n";
    } else {
        echo "  ✅ Seeded: [{$post_type}] {$title} (#{$id})\n";
    }
}

function e3_create_or_update_client( $slug, $title, $content ) {
    $existing = get_page_by_path( $slug, OBJECT, 'clients' );
    if ( $existing ) {
        e3_seed( $existing->ID, $title, $content, 'clients' );
    } else {
        $new_id = wp_insert_post( [
            'post_title'   => $title,
            'post_name'    => $slug,
            'post_content' => wp_slash( $content ),
            'post_status'  => 'publish',
            'post_type'    => 'clients',
        ], true );
        if ( is_wp_error( $new_id ) ) {
            echo "  ❌ Could not create client '{$title}': " . $new_id->get_error_message() . "\n";
        } else {
            echo "  ✅ Created: [clients] {$title} (#{$new_id})\n";
        }
    }
}

// ═══════════════════════════════════════════════════════════════════════════════
// PAGE 9: Partners in Funding
// Matches: funding.html
// ═══════════════════════════════════════════════════════════════════════════════
echo "📄 Partners in Funding...\n";

$hero_img = $site_url . '/wp-content/uploads/2026/06/Texas-Funding-Solutions.jpg';
$what_img = '/wp-content/uploads/2026/06/What-Do-We-Do.jpg';
$why_img  = '/wp-content/uploads/2026/06/funding-2-600x400.png';
$map_img  = '/wp-content/uploads/2026/06/static-map-600x400.png';
$exp_img  = '/wp-content/uploads/2026/06/52493178480_84996b8e0c_k.jpg';

$funding_banner = e3es_make_intro_banner_markup([
    'title'          => 'Securing Your Funding',
    'subtitle'       => "We don't just upgrade your facility’s infrastructure — we are the Texas-based experts in securing the money to pay for it.",
    'bgImageUrl'     => $hero_img,
    'bgOpacity'      => 0.85,
    'bgOverlayColor' => 'green',
    'bgFadeType'     => 'flat',
    'textAlignment'  => 'center',
    'textCase'       => 'uppercase'
]);

$funding = $funding_banner . "\n\n" . <<<BLOCK

<!-- wp:e3es/two-column {"imageUrl":"{$what_img}","imageAlt":"Funding documents and paperwork","reverse":false,"bgStyle":"white","icon":"layers"} -->
<section class="wp-block-e3es-two-column db-feature db-feature--white"><div class="db-feature__container"><div class="db-feature__content"><div class="db-feature__icon"></div><!-- wp:heading {"level":2} -->
<h2>What Do We Do?</h2>
<!-- /wp:heading --><!-- wp:paragraph -->
<p>As an industry leader, we offer complete, turnkey design+build solutions that include providing the stamped engineering drawings required to secure highly competitive funding like SECO grants and LoanSTAR programs. By designing, auditing, and securing your funding simultaneously, we dramatically accelerate your project timeline.</p>
<!-- /wp:paragraph --></div><div class="db-feature__image-wrapper"><img src="{$what_img}" alt="Funding documents and paperwork" class="db-feature__image"></div></div></section>
<!-- /wp:e3es/two-column -->

<!-- wp:e3es/two-column {"imageUrl":"{$why_img}","imageAlt":"Aging infrastructure","reverse":true,"bgStyle":"grey","icon":"check-circle"} -->
<section class="wp-block-e3es-two-column db-feature db-feature--grey"><div class="db-feature__container db-feature__container--reverse"><div class="db-feature__content"><div class="db-feature__icon"></div><!-- wp:heading {"level":2} -->
<h2>Why Do We Do It?</h2>
<!-- /wp:heading --><!-- wp:paragraph -->
<p>We know that public schools, hospitals, and municipalities are often severely underfunded and lack the internal expertise to piece together complex financing. Navigating these government grants is a complicated, miserable process that public entities simply do not want to tackle on their own. We do it because it secures &#8220;almost free money&#8221; for our clients, allowing them to modernize failing, decades-old infrastructure without draining their local budgets.</p>
<!-- /wp:paragraph --></div><div class="db-feature__image-wrapper"><img src="{$why_img}" alt="Aging infrastructure" class="db-feature__image"></div></div></section>
<!-- /wp:e3es/two-column -->

<!-- wp:e3es/two-column {"imageUrl":"{$map_img}","imageAlt":"Map of Texas showing client locations","reverse":false,"bgStyle":"green","mapSpill":true,"icon":"users"} -->
<section class="wp-block-e3es-two-column db-feature db-feature--green db-feature--map-spill"><div class="db-feature__container"><div class="db-feature__content"><div class="db-feature__icon"></div><!-- wp:heading {"level":2} -->
<h2>We&#8217;ve Helped So Many</h2>
<!-- /wp:heading --><!-- wp:paragraph -->
<p>We are the trusted, local powerhouse in Texas, having successfully partnered with over 400 clients to complete more than 375 projects. Our dominance in this space is undeniable: during a recent round of SECO funding, we captured roughly 90% of the available grant money for hospitals. We have successfully helped dozens of school districts and healthcare facilities secure the financial backing they need to transform their buildings.</p>
<!-- /wp:paragraph --></div><div class="db-feature__image-wrapper"><img src="{$map_img}" alt="Map of Texas showing client locations" class="db-feature__image"></div></div></section>
<!-- /wp:e3es/two-column -->

<!-- wp:e3es/two-column {"imageUrl":"{$exp_img}","imageAlt":"E3 expert team at work","reverse":true,"bgStyle":"white","icon":"star"} -->
<section class="wp-block-e3es-two-column db-feature db-feature--white"><div class="db-feature__container db-feature__container--reverse"><div class="db-feature__content"><div class="db-feature__icon"></div><!-- wp:heading {"level":2} -->
<h2>We Are The Experts So You Don&#8217;t Have To Be</h2>
<!-- /wp:heading --><!-- wp:paragraph -->
<p>Your team shouldn&#8217;t have to beat their heads against the wall dealing with complex government paperwork and procurement hurdles. We make purchasing completely <strong>Easy</strong> for our <strong>Local</strong> Texas partners by utilizing purchasing cooperatives like BuyBoard, TIPS, and Job Order Contracting (JOC). Because the co-op has already completed the competitive bidding process, you can <strong>skip the RFP</strong> &#8212; no traditional procurement required. Our in-house experts navigate this highly competitive system to bring the funding, the technical strategy, and the solutions directly to you. As your <strong>Trusted</strong> advisor, we serve as the ultimate &#8220;easy button&#8221; so you can stay focused on your community.</p>
<!-- /wp:paragraph --></div><div class="db-feature__image-wrapper"><img src="{$exp_img}" alt="E3 expert team at work" class="db-feature__image"></div></div></section>
<!-- /wp:e3es/two-column -->
BLOCK;

e3_seed( 9, 'Partners in Funding', $funding, 'page' );


// ═══════════════════════════════════════════════════════════════════════════════
// PAGE 6: Our Approach (Design+Build Advantage)
// Matches: design-build.html
// ═══════════════════════════════════════════════════════════════════════════════
echo "📄 Our Approach (Design+Build)...\n";

$db_hero  = $site_url . '/wp-content/uploads/2026/06/E3-background-layered-2-scaled.jpg';
$db_fast  = '/images/dl_caldwell-dark-1-600x400-600x400-600x400-600x400-600x400-600x400.jpg';
$db_acct  = '/images/54811959968_9493f28880_k-600x400-600x400-600x400-600x400-600x400-600x400.jpg';
$db_budg  = '/images/Donna-600x400-600x400-600x400-600x400-600x400-600x400.jpg';
$db_nimb  = '/images/dl_53969622794_b49535a782_k-3-1024x694-600x400-600x400-600x400-600x400-600x400-600x400.jpg';

$approach_banner = e3es_make_intro_banner_markup([
    'title'          => 'The Design+Build Advantage',
    'bgImageUrl'     => $db_hero,
    'bgOpacity'      => 0.85,
    'bgOverlayColor' => 'green',
    'bgFadeType'     => 'flat',
    'textAlignment'  => 'center',
    'textCase'       => 'uppercase'
]);

$approach = $approach_banner . "\n\n" . <<<BLOCK

<!-- wp:e3es/core-pillars -->
<section class="wp-block-e3es-core-pillars db-pillars" style="background-color:var(--color-bg-light);padding:5rem 2rem"><div style="max-width:1200px;margin:0 auto;display:grid;grid-template-columns:repeat(auto-fit, minmax(300px, 1fr));gap:3rem">
    <!-- wp:e3es/core-pillar {"title":"A Collaborative Workflow","text":"As a true Design-Build firm, E3 offers a single point of accountability on all of our projects. Our in-house engineers and project managers work hand-in-hand, from the initial design to project completion, for a simultaneous, collaborative workflow. E3 engineers develop the design while working with our in-house construction team to quickly and accurately determine project cost."} -->
    <div class="wp-block-e3es-core-pillar" style="background:white;padding:2.5rem;box-shadow:0 10px 30px rgba(0,0,0,0.05);border-top:4px solid var(--color-primary-green)"><h3 style="color:var(--color-primary-green);font-size:1.25rem;margin-bottom:1rem;text-transform:uppercase;letter-spacing:1px;line-height:1.3">A Collaborative Workflow</h3><p style="margin-bottom:0">As a true Design-Build firm, E3 offers a single point of accountability on all of our projects. Our in-house engineers and project managers work hand-in-hand, from the initial design to project completion, for a simultaneous, collaborative workflow. E3 engineers develop the design while working with our in-house construction team to quickly and accurately determine project cost.</p></div>
    <!-- /wp:e3es/core-pillar -->
    <!-- wp:e3es/core-pillar {"title":"Guaranteed Timelines &amp; Budgets","text":"Serving as both project designer and contractor, we ensure our projects are installed on-time and within budget, and we commit to our clients that there will be no change orders for the agreed-upon scope of work."} -->
    <div class="wp-block-e3es-core-pillar" style="background:white;padding:2.5rem;box-shadow:0 10px 30px rgba(0,0,0,0.05);border-top:4px solid var(--color-primary-green)"><h3 style="color:var(--color-primary-green);font-size:1.25rem;margin-bottom:1rem;text-transform:uppercase;letter-spacing:1px;line-height:1.3">Guaranteed Timelines &amp; Budgets</h3><p style="margin-bottom:0">Serving as both project designer and contractor, we ensure our projects are installed on-time and within budget, and we commit to our clients that there will be no change orders for the agreed-upon scope of work.</p></div>
    <!-- /wp:e3es/core-pillar -->
    <!-- wp:e3es/core-pillar {"title":"The Streamlined Choice","text":"The efficiency the Design-Build methodology offers matters especially during times of economic uncertainty, when project delays can result in dramatic price increases and loss of funding. Design-Build is the most streamlined and effective process for retrofit projects."} -->
    <div class="wp-block-e3es-core-pillar" style="background:white;padding:2.5rem;box-shadow:0 10px 30px rgba(0,0,0,0.05);border-top:4px solid var(--color-primary-green)"><h3 style="color:var(--color-primary-green);font-size:1.25rem;margin-bottom:1rem;text-transform:uppercase;letter-spacing:1px;line-height:1.3">The Streamlined Choice</h3><p style="margin-bottom:0">The efficiency the Design-Build methodology offers matters especially during times of economic uncertainty, when project delays can result in dramatic price increases and loss of funding. Design-Build is the most streamlined and effective process for retrofit projects.</p></div>
    <!-- /wp:e3es/core-pillar -->
</div></section>
<!-- /wp:e3es/core-pillars -->

<!-- wp:e3es/comparison-table -->
<section class="wp-block-e3es-comparison-table comparison-section"><div class="comparison-container"><table class="comparison-table"><thead><tr><th style="width:20%;border:none;background:transparent"></th><th style="width:40%">Traditional</th><th style="width:40%">E3 Design + Build</th></tr></thead><tbody>
    <!-- wp:e3es/comparison-row {"feature":"Structure","traditional":"Siloed","e3":"Integrated (Under One Roof)"} -->
    <tr class="wp-block-e3es-comparison-row"><th scope="row">Structure</th><td>Siloed</td><td>Integrated (Under One Roof)</td></tr><!-- /wp:e3es/comparison-row -->
    <!-- wp:e3es/comparison-row {"feature":"Timeline","traditional":"Sequential &amp; Slow","e3":"Accelerated Delivery"} -->
    <tr class="wp-block-e3es-comparison-row"><th scope="row">Timeline</th><td>Sequential &amp; Slow</td><td>Accelerated Delivery</td></tr><!-- /wp:e3es/comparison-row -->
    <!-- wp:e3es/comparison-row {"feature":"Risk Profile","traditional":"Fragmented Finger-Pointing","e3":"Single-Source Accountability"} -->
    <tr class="wp-block-e3es-comparison-row"><th scope="row">Risk Profile</th><td>Fragmented Finger-Pointing</td><td>Single-Source Accountability</td></tr><!-- /wp:e3es/comparison-row -->
    <!-- wp:e3es/comparison-row {"feature":"Cost Control","traditional":"Variable &amp; Unpredictable","e3":"Fixed Responsibility"} -->
    <tr class="wp-block-e3es-comparison-row"><th scope="row">Cost Control</th><td>Variable &amp; Unpredictable</td><td>Fixed Responsibility</td></tr><!-- /wp:e3es/comparison-row -->
</tbody></table></div></section>
<!-- /wp:e3es/comparison-table -->

<!-- wp:e3es/two-column {"imageUrl":"{$db_fast}","imageAlt":"Faster project timelines","reverse":false,"bgStyle":"white","icon":"clock"} -->
<section class="wp-block-e3es-two-column db-feature db-feature--white"><div class="db-feature__container"><div class="db-feature__content"><div class="db-feature__icon"></div><!-- wp:heading {"level":2} -->
<h2>Up to 2x Faster Timelines</h2>
<!-- /wp:heading --><!-- wp:paragraph -->
<p>Because we serve as both your designer and your contractor, our in-house engineers and project managers work hand-in-hand with you from day one. We design, audit, and secure funding simultaneously, making your project delivery up to twice as fast as traditional, linear bidding methods.</p>
<!-- /wp:paragraph --></div><div class="db-feature__image-wrapper"><img src="{$db_fast}" alt="Faster project timelines" class="db-feature__image"></div></div></section>
<!-- /wp:e3es/two-column -->

<!-- wp:e3es/two-column {"imageUrl":"{$db_acct}","imageAlt":"Single point of accountability","reverse":true,"bgStyle":"grey","icon":"shield"} -->
<section class="wp-block-e3es-two-column db-feature db-feature--grey"><div class="db-feature__container db-feature__container--reverse"><div class="db-feature__content"><div class="db-feature__icon"></div><!-- wp:heading {"level":2} -->
<h2>A Single Point of Accountability</h2>
<!-- /wp:heading --><!-- wp:paragraph -->
<p>In standard Texas construction projects, finger-pointing between architects and contractors is often a &#8220;feature, not a bug.&#8221; By consolidating all roles into one entity, we provide you a single point of accountability, completely eliminating the finger-pointing that slows down your projects.</p>
<!-- /wp:paragraph --></div><div class="db-feature__image-wrapper"><img src="{$db_acct}" alt="Single point of accountability" class="db-feature__image"></div></div></section>
<!-- /wp:e3es/two-column -->

<!-- wp:e3es/two-column {"imageUrl":"{$db_budg}","imageAlt":"No change orders","reverse":false,"bgStyle":"green","icon":"dollar"} -->
<section class="wp-block-e3es-two-column db-feature db-feature--green"><div class="db-feature__container"><div class="db-feature__content"><div class="db-feature__icon"></div><!-- wp:heading {"level":2} -->
<h2>Forget About Change Orders</h2>
<!-- /wp:heading --><!-- wp:paragraph -->
<p>Traditional finger-pointing almost always leads to expensive change orders for you. Our contractual obligation eliminates this risk: our teams collaborate proactively to find solutions, guaranteeing you no change orders for the agreed-upon scope of work. We deliver your projects at the stated price, and if there is an oversight, we cover the cost ourselves.</p>
<!-- /wp:paragraph --></div><div class="db-feature__image-wrapper"><img src="{$db_budg}" alt="No change orders" class="db-feature__image"></div></div></section>
<!-- /wp:e3es/two-column -->

<!-- wp:e3es/two-column {"imageUrl":"{$db_nimb}","imageAlt":"Nimble and local team","reverse":true,"bgStyle":"white","icon":"map-pin"} -->
<section class="wp-block-e3es-two-column db-feature db-feature--white"><div class="db-feature__container db-feature__container--reverse"><div class="db-feature__content"><div class="db-feature__icon"></div><!-- wp:heading {"level":2} -->
<h2>Nimble and Local</h2>
<!-- /wp:heading --><!-- wp:paragraph -->
<p>Unlike massive, traditional Energy Services Companies (ESCOs) with high overhead that focus solely on long-term federal projects, we are your local, nimble partner. We offer a streamlined, high-quality process specifically tailored to your municipal, school, or hospital facility needs.</p>
<!-- /wp:paragraph --></div><div class="db-feature__image-wrapper"><img src="{$db_nimb}" alt="Nimble and local team" class="db-feature__image"></div></div></section>
<!-- /wp:e3es/two-column -->
BLOCK;

e3_seed( 6, 'Our Approach', $approach, 'page' );


// ═══════════════════════════════════════════════════════════════════════════════
// PAGE 10: K-12 Schools
// Matches: k12.html — intro-banner + interactive map + services grid
// ═══════════════════════════════════════════════════════════════════════════════
echo "📄 K-12 Schools...\n";

$k12_hero = $site_url . '/wp-content/uploads/2026/06/54401120128_a10df8e7eb_o-scaled.jpg';

$k12_banner = e3es_make_intro_banner_markup([
    'title'          => 'K-12 Schools',
    'subtitle'       => 'For over two decades, we have been empowering Texas school districts with modernized, energy-efficient infrastructure so educators can focus on what matters most: the students.',
    'bgImageUrl'     => $k12_hero,
    'bgOpacity'      => 0.85,
    'bgOverlayColor' => 'green',
    'bgFadeType'     => 'flat',
    'textAlignment'  => 'center',
    'textCase'       => 'uppercase'
]);

$hvac_id = e3_get_service_id_by_slug( 'hvac-system-upgrades-2' );
$led_id  = e3_get_service_id_by_slug( 'led-lighting-2' );
$iaq_id  = e3_get_service_id_by_slug( 'indoor-air-quality-2' );
$bas_id  = e3_get_service_id_by_slug( 'building-automation-systems-2' );

$selected_ids_arr = array_filter( [ $hvac_id, $led_id, $iaq_id, $bas_id ] );
$selected_ids_json = json_encode( array_map( 'intval', $selected_ids_arr ) );

$k12 = $k12_banner . "\n\n" . <<<BLOCK

<!-- wp:e3es/texas-interactive-map /-->

<!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center">Our K-12 Infrastructure Solutions</h2>
<!-- /wp:heading -->

<!-- wp:e3es/services-grid {"mode":"manual","selectedIds":{$selected_ids_json}} /-->
BLOCK;

e3_seed( 10, 'K-12 Schools', $k12, 'page' );


// ═══════════════════════════════════════════════════════════════════════════════
// PAGE 11: Services (Our Turnkey Services)
// Matches: services.html — 10-card grid
// ═══════════════════════════════════════════════════════════════════════════════
echo "📄 Services (Our Turnkey Services)...\n";

$svcs_hero = $site_url . '/wp-content/uploads/2026/06/E3-background-layered-2-scaled.jpg';

$services_banner = e3es_make_intro_banner_markup([
    'title'          => 'Our Turnkey Services',
    'subtitle'       => 'We provide comprehensive, start-to-finish solutions designed to modernize your facilities, reduce energy consumption, and eliminate deferred maintenance.',
    'bgImageUrl'     => $svcs_hero,
    'bgOpacity'      => 0.85,
    'bgOverlayColor' => 'black',
    'bgFadeType'     => 'flat',
    'textAlignment'  => 'center',
    'textCase'       => 'uppercase'
]);

$services_page = $services_banner . "\n\n" . <<<BLOCK

<!-- wp:e3es/services-grid {"mode":"auto","limit":12} /-->
BLOCK;

e3_seed( 11, 'Services', $services_page, 'page' );


// ═══════════════════════════════════════════════════════════════════════════════
// SERVICE 1132: K-12 LED & Sports Lighting (/services/k-12/lighting/)
// Matches: led-lighting.html
// ═══════════════════════════════════════════════════════════════════════════════
echo "🔧 Service: K-12 LED & Sports Lighting...\n";

$led_hero  = $site_url . '/wp-content/uploads/2026/06/led-crop-1920x1080-1.jpg';
$led_sport = '/wp-content/uploads/2026/06/54845357449_7bb9258e8b_k-600x400-600x400-600x400-600x400.jpg';
$led_int   = '/wp-content/uploads/2026/06/51671231498_f84028afe5_k-600x400-600x400-600x400-600x400-600x400-600x400.jpg';
$led_park  = '/wp-content/uploads/2026/06/led-600x400-600x400-600x400-600x400.jpg';
$led_gym   = '/wp-content/uploads/2026/06/51670228367_9daa14b611_k-600x400-600x400-600x400-600x400-600x400-600x400.jpg';
$led_retro = '/wp-content/uploads/2026/06/51466205353_2f6a5de945_k-600x400-600x400-600x400-600x400.jpg';
$led_trust = '/images/53969622794_b49535a782_k-600x400-600x400-600x400-600x400-600x400-600x400.jpg';
$sup_photo = '/images/superintendent-400x400-400x400-400x400-400x400-400x400-400x400.png';
$fac_photo = '/images/facilities_director-400x400-400x400-400x400-400x400-400x400-400x400.png';
$bill_photo= '/images/dl_B-Savarino-370x280-400x400-400x400-400x400-400x400-400x400-400x400.jpg';

$led_banner = e3es_make_intro_banner_markup([
    'title'          => 'K-12 <span style="color:#8CC63F">LED</span> Lighting Solutions',
    'subtitle'       => 'Upgrading outdated HID or metal halide fixtures to high-efficiency LED lighting not only improves safety and aesthetics but also yields significant energy and maintenance cost savings for your school, hospital, or municipal facility.',
    'bgImageUrl'     => $led_hero,
    'bgOpacity'      => 0.75,
    'bgOverlayColor' => 'blue',
    'bgFadeType'     => 'flat',
    'textAlignment'  => 'center',
    'textCase'       => 'uppercase',
    'textShadow'     => 'subtle'
]);

$led_lighting = $led_banner . "\n\n" . <<<BLOCK

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

<!-- wp:e3es/cta-banner {"title":"Ready to Light Up Your Campus?","text":"Talk to our lighting experts about a free facility assessment and custom LED upgrade plan.","btnText":"Request a Free Assessment","btnUrl":"/about-us/contact"} -->
<section class="wp-block-e3es-cta-banner cta-banner"><div class="cta-banner__container"><h2 class="cta-banner__title">Ready to Light Up Your Campus?</h2><p class="cta-banner__text">Talk to our lighting experts about a free facility assessment and custom LED upgrade plan.</p><a href="/about-us/contact" class="btn btn--primary cta-banner__btn">Request a Free Assessment</a></div></section>
<!-- /wp:e3es/cta-banner -->
BLOCK;

e3_seed( 1132, 'K-12 LED &amp; Sports Lighting', $led_lighting, 'services' );


// ═══════════════════════════════════════════════════════════════════════════════
// CLIENT: South Texas Regional Page (clients post type)
// Matches: south-texas.html
// ═══════════════════════════════════════════════════════════════════════════════
echo "🤝 Client: South Texas & Coast Regional Page...\n";

$st_hero   = $site_url . '/wp-content/uploads/2026/06/dl_Carrizo-Springs-8.jpg';
$st_db     = '/images/54811959968_9493f28880_k-600x400-600x400-600x400-600x400-600x400-600x400.jpg';
$st_fund   = '/wp-content/uploads/2026/06/funding-2-600x400.png';
$bill_photo = '/images/dl_B-Savarino-370x280-400x400-400x400-400x400-400x400-400x400-400x400.jpg';
$sup_photo  = '/images/superintendent-400x400-400x400-400x400-400x400-400x400-400x400.png';
$fac_photo  = '/images/facilities_director-400x400-400x400-400x400-400x400-400x400-400x400.png';

$south_texas_banner = e3es_make_intro_banner_markup([
    'title'          => 'South Texas & Coast',
    'subtitle'       => 'Delivering resilient, hurricane-ready infrastructure and efficient cooling systems to schools across South Texas. We build facilities that reflect the deep cultural pride and spirit of coastal communities.',
    'bgImageUrl'     => $st_hero,
    'bgOpacity'      => 0.85,
    'bgOverlayColor' => 'green',
    'bgFadeType'     => 'horizontal',
    'textAlignment'  => 'center'
]);

$south_texas = $south_texas_banner . "\n\n" . <<<BLOCK

<!-- wp:e3es/region-showcase {"heading":"Featured South Texas Projects","bgStyle":"white"} -->
<!-- wp:group {"className":"region-showcase-card"} --><div class="wp-block-group region-showcase-card"><!-- wp:image {"className":"region-showcase-card__img"} --><figure class="wp-block-image region-showcase-card__img"><img src="/images/dl_Carrizo-Springs-8-400x300-400x300-400x300-400x300-400x300-400x300.jpg" alt="Carrizo Springs"/></figure><!-- /wp:image --><div class="region-showcase-card__body"><h3 class="region-showcase-card__title">Carrizo Springs CISD</h3><p class="region-showcase-card__amount">$14.9M Facility Renewal</p><p class="region-showcase-card__text">A systems-based improvement program including 100 rooftop HVAC units, LED lighting, and building envelope upgrades to enhance indoor air quality and comfort.</p></div></div><!-- /wp:group --><!-- wp:group {"className":"region-showcase-card"} --><div class="wp-block-group region-showcase-card"><!-- wp:image {"className":"region-showcase-card__img"} --><figure class="wp-block-image region-showcase-card__img"><img src="/images/Edcouch-400x300-400x300-400x300-400x300-400x300-400x300.jpg" alt="Edcouch-Elsa ISD"/></figure><!-- /wp:image --><div class="region-showcase-card__body"><h3 class="region-showcase-card__title">Edcouch-Elsa ISD</h3><p class="region-showcase-card__amount">$15.5M HVAC &amp; Controls Retrofit</p><p class="region-showcase-card__text">A comprehensive project involving redesigning the High School chilled water system, installing new Alerton Automation Systems, and LED retrofits.</p></div></div><!-- /wp:group --><!-- wp:group {"className":"region-showcase-card"} --><div class="wp-block-group region-showcase-card"><!-- wp:image {"className":"region-showcase-card__img"} --><figure class="wp-block-image region-showcase-card__img"><img src="/images/Ricardo-400x300-400x300-400x300-400x300-400x300-400x300.jpg" alt="Ricardo ISD"/></figure><!-- /wp:image --><div class="region-showcase-card__body"><h3 class="region-showcase-card__title">Ricardo ISD</h3><p class="region-showcase-card__amount">SECO Grant Funded Savings</p><p class="region-showcase-card__text">Utilized ARRA SECO Grants and District funds for a $969K project featuring district-wide controls, 44 new HVAC units, and lighting upgrades.</p></div></div><!-- /wp:group --><!-- wp:group {"className":"region-showcase-card"} --><div class="wp-block-group region-showcase-card"><!-- wp:image {"className":"region-showcase-card__img"} --><figure class="wp-block-image region-showcase-card__img"><img src="/images/MERCEDES-400x300-400x300-400x300-400x300-400x300-400x300.jpg" alt="Mercedes ISD"/></figure><!-- /wp:image --><div class="region-showcase-card__body"><h3 class="region-showcase-card__title">Mercedes ISD</h3><p class="region-showcase-card__amount">$9.5M QZAB Project</p><p class="region-showcase-card__text">An energy-efficiency program retrofitting 10,500 lighting fixtures and major HVAC replacements, avoiding $700K+ in annual utility costs.</p></div></div><!-- /wp:group --><!-- wp:group {"className":"region-showcase-card"} --><div class="wp-block-group region-showcase-card"><!-- wp:image {"className":"region-showcase-card__img"} --><figure class="wp-block-image region-showcase-card__img"><img src="/images/Donna-400x300-400x300-400x300-400x300-400x300-400x300.jpg" alt="Donna ISD"/></figure><!-- /wp:image --><div class="region-showcase-card__body"><h3 class="region-showcase-card__title">Donna ISD</h3><p class="region-showcase-card__amount">$7.9M Conservation Program</p><p class="region-showcase-card__text">A comprehensive LED retrofit across 20 campuses and mechanical retrofits at 9 campuses, successfully completed with zero change orders.</p></div></div><!-- /wp:group -->
<!-- /wp:e3es/region-showcase -->

<!-- wp:e3es/two-column-cover {"bgStyle":"grey","reverse":false} -->
<section class="wp-block-e3es-two-column-cover db-feature db-feature--grey"><div class="db-feature__container"><!-- wp:columns {"isStackedOnMobile":true} -->
<div class="wp-block-columns"><!-- wp:column {"width":"66.66%"} -->
<div class="wp-block-column" style="flex-basis:66.66%"><!-- wp:heading {"level":2} -->
<h2>The Design+Build Advantage in South Texas</h2>
<!-- /wp:heading --><!-- wp:paragraph -->
<p>For South Texas schools, traditional construction often means delays, change orders, and finger-pointing — especially when dealing with the unique climate and rapid growth of the region. E3’s integrated Design+Build approach completely flips this script.</p>
<!-- /wp:paragraph --><!-- wp:paragraph -->
<p>By keeping engineering, auditing, and project management under one roof, we guarantee your agreed-upon scope of work at the exact stated price. We fast-track project timelines, ensuring critical upgrades like HVAC overhauls and weather-resilient roofing are completed before the start of the new school year. The result? Modern, comfortable learning environments delivered on time and on budget.</p>
<!-- /wp:paragraph --><!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/our-approach">Learn More About Design+Build</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column --><!-- wp:column {"width":"33.33%"} -->
<div class="wp-block-column" style="flex-basis:33.33%"><!-- wp:e3es/rep-contact-card {"name":"Bill","role":"Business Development, South Texas","bio":"Bill is most proud of his beautiful family. All this time we didn't realize we were making memories, we just knew we were having fun!","photoUrl":"{$bill_photo}","emailLabel":"Email Bill","emailHref":"mailto:bill@e3es.com","callLabel":"Schedule a Call","callHref":"tel:+1"} /-->
</div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div></section>
<!-- /wp:e3es/two-column-cover -->

<!-- wp:e3es/full-width-testimonial {"quote":"E3 upgraded our aging facilities into modern learning environments without disrupting the school year. Their process was smooth from start to finish.","byline":"Superintendent, South Texas ISD","photoUrl":"{$sup_photo}","bgStyle":"light"} /-->

<!-- wp:e3es/two-column {"imageUrl":"{$st_fund}","imageAlt":"Funding success in South Texas","reverse":true,"bgStyle":"grey","icon":"dollar"} -->
<section class="wp-block-e3es-two-column db-feature db-feature--grey"><div class="db-feature__container db-feature__container--reverse"><div class="db-feature__content"><div class="db-feature__icon"></div><!-- wp:heading {"level":2} -->
<h2>Securing Funding for South Texas Schools</h2>
<!-- /wp:heading --><!-- wp:paragraph -->
<p>We know that school budgets are tighter than ever. That’s why we don’t just engineer solutions — we find the money to pay for them. We make it completely <strong>Easy</strong> for our <strong>Local</strong> Texas partners to upgrade facilities by leveraging purchasing cooperatives like BuyBoard and TIPS. Because the co-op has already completed the competitive bidding process, you can <strong>skip the RFP</strong>, bypass traditional procurement hurdles and start transforming your campus sooner. As your <strong>Trusted</strong> advisor, our experts also specialize in navigating complex Texas state grants, including <strong>SECO</strong> and the <strong>LoanSTAR</strong> program.</p>
<!-- /wp:paragraph --><!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/our-approach/funding">Explore Funding Options</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div><div class="db-feature__image-wrapper"><img src="{$st_fund}" alt="Funding success in South Texas" class="db-feature__image"></div></div></section>
<!-- /wp:e3es/two-column -->

<!-- wp:e3es/full-width-testimonial {"quote":"When the hurricane hit, our E3-upgraded roofs and backup systems kept our community safe. They truly understand coastal resilience.","byline":"Facilities Director, Coastal Bend Schools","photoUrl":"{$fac_photo}","bgStyle":"white"} /-->

<!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center">Our K-12 Solutions in South Texas</h2>
<!-- /wp:heading -->

<!-- wp:e3es/services-grid {"mode":"manual","selectedIds":{$selected_ids_json}} /-->
BLOCK;

e3_create_or_update_client( 'south-texas', 'South Texas & Coast', $south_texas );


// ── Done ─────────────────────────────────────────────────────────────────────
echo "\n🎉 All content seeded successfully!\n";
echo "   Next: Run 'npm run build' in astro-e3es to rebuild the frontend.\n\n";
