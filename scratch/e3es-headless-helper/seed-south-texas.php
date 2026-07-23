<?php
/**
 * Seed / Update: South Texas Page (ID 1660)
 *
 * Updates the WordPress page content to match the reference layout at:
 * https://www.e3es.com/next/south-texas.html
 *
 * Run via Local PHP:
 *   "/Users/bryanpaul/Library/Application Support/Local/lightning-services/php-8.2.29+0/bin/darwin-arm64/bin/php" \
 *     "/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-content/plugins/e3es-headless-helper/seed-south-texas.php"
 */

// ── Bootstrap WordPress ───────────────────────────────────────────────────────
$wp_load = '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';
if ( ! file_exists( $wp_load ) ) {
    die( "Cannot find wp-load.php at: $wp_load\n" );
}
define( 'ABSPATH_SKIP_REDIRECT', true );
require_once $wp_load;

wp_set_current_user( 1 );
if ( function_exists( 'kses_remove_filters' ) ) {
    kses_remove_filters();
}

$site_url = rtrim( get_option( 'siteurl' ), '/' );

echo "\n🌱 South Texas Page Seeder Starting...\n\n";

// ── Image URLs ────────────────────────────────────────────────────────────────
$hero_bg                    = $site_url . '/wp-content/uploads/2026/06/dl_Carrizo-Springs-8-1920x1080-2.jpg';
$carrizo_img                = $site_url . '/wp-content/uploads/2026/06/dl_Carrizo-Springs-8-400x300-400x300-400x300-400x300-2.jpg';
$edcouch_img                = $site_url . '/wp-content/uploads/2026/06/Edcouch-400x300-400x300-400x300-400x300-2.jpg';
$ricardo_img                = $site_url . '/wp-content/uploads/2026/06/Ricardo-400x300-400x300-400x300-400x300-400x300-2.jpg';
$mercedes_img               = $site_url . '/wp-content/uploads/2026/06/MERCEDES-400x300-400x300-400x300-400x300-400x300-400x300-2.jpg';
$donna_img                  = $site_url . '/wp-content/uploads/2026/06/Donna-400x300-400x300-400x300-400x300-400x300-2.jpg';
$funding_img                = $site_url . '/wp-content/uploads/2026/06/funding-2-600x400-600x400-600x400-600x400-600x400-2.png';
$hvac_img                   = $site_url . '/wp-content/uploads/2026/06/hvac-500x220-500x220-500x220-500x220-500x220-500x220-2.jpg';
$led_img                    = $site_url . '/wp-content/uploads/2026/06/led-500x220-500x220-500x220-500x220-500x220-500x220-2.jpg';
$iaq_img                    = $site_url . '/wp-content/uploads/2026/06/air-500x220-500x220-500x220-500x220-500x220-2.jpg';
$bas_img                    = $site_url . '/wp-content/uploads/2026/06/automation-500x220-500x220-500x220-500x220-500x220-2.jpg';
$bill_photo                 = $site_url . '/wp-content/uploads/2026/06/dl_B-Savarino-370x280-400x400-400x400.jpg';
$superintendent_avatar      = $site_url . '/wp-content/uploads/2026/06/superintendent-400x400-400x400-400x400-400x400-400x400-400x400-2.png';
$facilities_director_avatar = $site_url . '/wp-content/uploads/2026/06/facilities_director-400x400-400x400-400x400-400x400-400x400-400x400-2.png';
$coop_logos_img             = $site_url . '/wp-content/uploads/2026/06/dl_images-800x600-800x600-800x600-800x600-800x600-800x600-2.png';
$tips_logo_img              = $site_url . '/wp-content/uploads/2026/06/dl_TIPS-TC-Logo-300x132-1-800x600-800x600-800x600-800x600-800x600-800x600-2.png';

// ── Build New Block Content ───────────────────────────────────────────────────
$content = <<<HTML

<!-- wp:e3es/intro-banner {"bgColor":"rgba(17,36,19,0.85)","bgImage":"$hero_bg","title":"South Texas \u0026 Coast"} -->
<section class="wp-block-e3es-intro-banner db-page-hero" style="background-image: linear-gradient(rgba(14, 53, 27, 0.7), rgba(14, 53, 27, 0.7)), url('$hero_bg'); background-size: cover; background-position: center;">
<div class="db-page-hero__container">
<h1 class="db-page-hero__title">South Texas &amp; Coast</h1>
<div class="db-page-hero__intro">
<p>Delivering resilient, hurricane-ready infrastructure and efficient cooling systems to schools across South Texas. We build facilities that reflect the deep cultural pride and spirit of coastal communities.</p>
</div>
</div>
</section>
<!-- /wp:e3es/intro-banner -->

<!-- wp:e3es/region-showcase {"heading":"Featured South Texas Projects","variant":"white"} -->
<section class="wp-block-e3es-region-showcase region-showcase region-showcase--white">
<div class="region-showcase__container">
<h2 class="region-showcase__heading section-title">Featured South Texas Projects</h2>
<div class="region-showcase__slider-wrap">
<div class="region-showcase__track">

<!-- wp:e3es/region-showcase-card {"title":"Carrizo Springs CISD","value":"\$14.9M Facility Renewal","image":"$carrizo_img","imageAlt":"Carrizo Springs CISD"} -->
<div class="region-showcase__card">
<img src="$carrizo_img" alt="Carrizo Springs CISD" class="region-showcase__card-img" />
<div class="region-showcase__card-content">
<h3 class="region-showcase__card-title">Carrizo Springs CISD</h3>
<p class="region-showcase__card-value">\$14.9M Facility Renewal</p>
<p class="region-showcase__card-text">A systems-based improvement program including 100 rooftop HVAC units, LED lighting, and building envelope upgrades to enhance indoor air quality and comfort.</p>
</div>
</div>
<!-- /wp:e3es/region-showcase-card -->

<!-- wp:e3es/region-showcase-card {"title":"Edcouch-Elsa ISD","value":"\$15.5M HVAC &amp; Controls Retrofit","image":"$edcouch_img","imageAlt":"Edcouch-Elsa ISD"} -->
<div class="region-showcase__card">
<img src="$edcouch_img" alt="Edcouch-Elsa ISD" class="region-showcase__card-img" />
<div class="region-showcase__card-content">
<h3 class="region-showcase__card-title">Edcouch-Elsa ISD</h3>
<p class="region-showcase__card-value">\$15.5M HVAC &amp; Controls Retrofit</p>
<p class="region-showcase__card-text">A comprehensive project involving redesigning the High School chilled water system, installing new Alerton Automation Systems, and LED retrofits.</p>
</div>
</div>
<!-- /wp:e3es/region-showcase-card -->

<!-- wp:e3es/region-showcase-card {"title":"Ricardo ISD","value":"SECO Grant Funded Savings","image":"$ricardo_img","imageAlt":"Ricardo ISD"} -->
<div class="region-showcase__card">
<img src="$ricardo_img" alt="Ricardo ISD" class="region-showcase__card-img" />
<div class="region-showcase__card-content">
<h3 class="region-showcase__card-title">Ricardo ISD</h3>
<p class="region-showcase__card-value">SECO Grant Funded Savings</p>
<p class="region-showcase__card-text">Utilized ARRA SECO Grants and District funds for a \$969K project featuring district-wide controls, 44 new HVAC units, and lighting upgrades.</p>
</div>
</div>
<!-- /wp:e3es/region-showcase-card -->

<!-- wp:e3es/region-showcase-card {"title":"Mercedes ISD","value":"\$9.5M QZAB Project","image":"$mercedes_img","imageAlt":"Mercedes ISD"} -->
<div class="region-showcase__card">
<img src="$mercedes_img" alt="Mercedes ISD" class="region-showcase__card-img" />
<div class="region-showcase__card-content">
<h3 class="region-showcase__card-title">Mercedes ISD</h3>
<p class="region-showcase__card-value">\$9.5M QZAB Project</p>
<p class="region-showcase__card-text">An energy-efficiency program retrofitting 10,500 lighting fixtures and major HVAC replacements, avoiding \$700K+ in annual utility costs.</p>
</div>
</div>
<!-- /wp:e3es/region-showcase-card -->

<!-- wp:e3es/region-showcase-card {"title":"Donna ISD","value":"\$7.9M Conservation Program","image":"$donna_img","imageAlt":"Donna ISD"} -->
<div class="region-showcase__card">
<img src="$donna_img" alt="Donna ISD" class="region-showcase__card-img" />
<div class="region-showcase__card-content">
<h3 class="region-showcase__card-title">Donna ISD</h3>
<p class="region-showcase__card-value">\$7.9M Conservation Program</p>
<p class="region-showcase__card-text">A comprehensive LED retrofit across 20 campuses and mechanical retrofits at 9 campuses, successfully completed with zero change orders.</p>
</div>
</div>
<!-- /wp:e3es/region-showcase-card -->

</div>
</div>
</div>
</section>
<!-- /wp:e3es/region-showcase -->

<!-- wp:group {"tagName":"section","align":"full","className":"db-feature db-feature--grey","layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull db-feature db-feature--grey">

<!-- wp:columns -->
<div class="wp-block-columns is-layout-flex wp-block-columns-is-layout-flex">

<!-- wp:column {"width":"66.66%"} -->
<div class="wp-block-column is-layout-flow wp-block-column-is-layout-flow" style="flex-basis:66.66%">

<!-- wp:heading -->
<h2 class="wp-block-heading">The Design+Build Advantage in South Texas</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p class="wp-block-paragraph">For South Texas schools, traditional construction often means delays, change orders, and finger-pointing — especially when dealing with the unique climate and rapid growth of the region. E3's integrated Design+Build approach completely flips this script.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p class="wp-block-paragraph">By keeping engineering, auditing, and project management under one roof, we guarantee your agreed-upon scope of work at the exact stated price. We fast-track project timelines, ensuring critical upgrades like HVAC overhauls and weather-resilient roofing are completed before the start of the new school year. The result? Modern, comfortable learning environments delivered on time and on budget.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons is-layout-flex wp-block-buttons-is-layout-flex">
<!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/our-approach">Learn More About Design+Build</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->

</div>
<!-- /wp:column -->

<!-- wp:column {"width":"33.33%"} -->
<div class="wp-block-column is-layout-flow wp-block-column-is-layout-flow" style="flex-basis:33.33%">
<div class="bill-sidebar">
<div class="rep-contact-card">
<div class="rep-contact-card__photo-wrap"><img src="$bill_photo" alt="Bill Savarino" class="rep-contact-card__photo" /></div>
<h3 class="rep-contact-card__name">Meet Bill</h3>
<p class="rep-contact-card__role">Business Development, South Texas</p>
<blockquote class="rep-contact-card__bio"><p>&#8220;Bill is most proud of his beautiful family. &#8216;All this time we didn&#8217;t realize we were making memories, we just knew we were having fun!&#8217;&#8221;</p></blockquote>
<p class="rep-contact-card__subtitle">Have questions about how Design+Build or creative funding can transform your district? Reach out directly to Bill.</p>
<div class="rep-contact-card__buttons"><a href="mailto:bill@e3es.com" class="btn btn--primary">Email Bill</a><a href="tel:+1" class="btn btn--outline">Schedule a Call</a></div>
</div>
</div>
</div>
<!-- /wp:column -->

</div>
<!-- /wp:columns -->

<!-- wp:html -->
<div class="db-feature__testimonial-wrap">
    <blockquote class="full-width-testimony bg-white">
        <div class="full-width-testimony__avatar-wrap">
            <img src="$superintendent_avatar" alt="Superintendent" class="full-width-testimony__avatar" />
        </div>
        <div style="flex: 1; font-style: italic; color: var(--color-text-main); font-size: 1.1rem; line-height: 1.6;">
            "E3 upgraded our aging facilities into modern learning environments without disrupting the school year. Their process was smooth from start to finish."
        </div>
        <div class="full-width-testimony-author">
            <div style="margin-bottom: 0.2rem;">- Superintendent, South Texas ISD</div>
            <a href="#" style="color: var(--color-primary-dark); font-weight: 700; font-size: 0.9rem; text-decoration: underline; font-style: normal;">Read Case Study</a>
        </div>
    </blockquote>
</div>
<!-- /wp:html -->

</section>
<!-- /wp:group -->

<!-- wp:e3es/two-column {"bgStyle":"grey","reverse":true} -->
<section class="wp-block-e3es-two-column db-feature db-feature--grey">
<div class="db-feature__container db-feature__container--reverse">
<div class="db-feature__content">
<div class="db-feature__icon">
<svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
</div>
<!-- wp:heading -->
<h2 class="wp-block-heading">Securing Funding for South Texas Schools</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p class="wp-block-paragraph">We know that school budgets are tighter than ever. That&#8217;s why we don&#8217;t just engineer solutions &#8212; we find the money to pay for them. We make it completely <strong>Easy</strong> for our <strong>Local</strong> Texas partners to upgrade facilities by leveraging purchasing cooperatives like BuyBoard and TIPS. Because the co-op has already completed the competitive bidding process, you can <strong>skip the RFP</strong>, bypass traditional procurement hurdles and start transforming your campus sooner. As your <strong>Trusted</strong> advisor, our experts also specialize in navigating complex Texas state grants, including <strong>SECO</strong> and the <strong>LoanSTAR</strong> program.</p>
<!-- /wp:paragraph -->
<!-- wp:buttons -->
<div class="wp-block-buttons is-layout-flex wp-block-buttons-is-layout-flex">
<!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/funding-stories">Explore Funding Stories</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->

<!-- wp:html -->
<div class="coop-logos">
    <div class="coop-logos__item">
        <img src="$coop_logos_img" alt="Purchasing Cooperatives" class="coop-logos__img" />
    </div>
    <div class="coop-logos__item">
        <img src="$tips_logo_img" alt="TIPS Purchasing Cooperative" class="coop-logos__img coop-logos__img--tips" />
    </div>
</div>
<!-- /wp:html -->

</div>
<div class="db-feature__image-wrapper"><img decoding="async" src="$funding_img" alt="Funding success in South Texas" class="db-feature__image" /></div>
</div>

<!-- wp:html -->
<div class="db-feature__testimonial-wrap">
    <blockquote class="full-width-testimony bg-white">
        <div class="full-width-testimony__avatar-wrap">
            <img src="$facilities_director_avatar" alt="Facilities Director" class="full-width-testimony__avatar" />
        </div>
        <div style="flex: 1; font-style: italic; color: var(--color-text-main); font-size: 1.1rem; line-height: 1.6;">
            "When the hurricane hit, our E3-upgraded roofs and backup systems kept our community safe. They truly understand coastal resilience."
        </div>
        <div class="full-width-testimony-author">
            <div style="margin-bottom: 0.2rem;">- Facilities Director, Coastal Bend Schools</div>
            <a href="#" style="color: var(--color-primary-dark); font-weight: 700; font-size: 0.9rem; text-decoration: underline; font-style: normal;">Read Case Study</a>
        </div>
    </blockquote>
</div>
<!-- /wp:html -->

</section>
<!-- /wp:e3es/two-column -->

<!-- wp:group {"className":"services","style":{"spacing":{"padding":{"top":"5rem","right":"2rem","bottom":"5rem","left":"2rem"}}}} -->
<div class="wp-block-group services is-layout-flow wp-block-group-is-layout-flow" style="padding-top:5rem;padding-right:2rem;padding-bottom:5rem;padding-left:2rem">

<!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center">Our K-12 Solutions in South Texas</h2>
<!-- /wp:heading -->

<div class="services__grid">

<!-- Service Card: HVAC -->
<div class="services__card">
<img src="$hvac_img" alt="HVAC System Upgrades and Replacements" class="services__card-image" />
<div class="services__card-content">
<h3 class="services__card-title">HVAC System Upgrades and Replacements</h3>
<p class="services__card-text">E3 designs, replaces, and upgrades outdated heating, ventilation, and air conditioning systems to create comfortable indoor environments and improve operational efficiency.</p>
</div>
</div>

<!-- Service Card: LED Lighting -->
<div class="services__card">
<img src="$led_img" alt="LED Lighting Upgrades and Sports Lighting" class="services__card-image" />
<div class="services__card-content">
<h3 class="services__card-title">LED Lighting Upgrades and Sports Lighting</h3>
<p class="services__card-text">E3 provides true turnkey lighting solutions &#8212; including auditing, design, procurement, and installation &#8212; for building interiors, exteriors, and specialized sports complexes. They utilize specialized retrofit labor and frequently deploy a &#8220;ghost&#8221; strategy, working nights and weekends to complete upgrades in occupied spaces without disrupting students or staff.</p>
</div>
</div>

<!-- Service Card: IAQ -->
<div class="services__card">
<img src="$iaq_img" alt="Indoor Air Quality Analysis and Improvements" class="services__card-image" />
<div class="services__card-content">
<h3 class="services__card-title">Indoor Air Quality Analysis and Improvements</h3>
<p class="services__card-text">E3 assesses and upgrades facility environments to ensure safe, healthy, and high-quality indoor air for building occupants.</p>
</div>
</div>

<!-- Service Card: BAS -->
<div class="services__card">
<img src="$bas_img" alt="Building Automation Systems (BAS)" class="services__card-image" />
<div class="services__card-content">
<h3 class="services__card-title">Building Automation Systems (BAS)</h3>
<p class="services__card-text">E3 optimizes and commissions intelligent building control systems, allowing facilities to efficiently monitor and manage their energy usage.</p>
</div>
</div>

</div>

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons is-content-justification-center is-layout-flex wp-block-buttons-is-layout-flex">
<!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/services">View All Solutions</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->

</div>
<!-- /wp:group -->

HTML;

// ── Update the Post ───────────────────────────────────────────────────────────
$result = wp_update_post( [
    'ID'           => 1660,
    'post_title'   => 'South Texas',
    'post_content' => wp_slash( $content ),
    'post_status'  => 'publish',
    'post_type'    => 'page',
], true );

if ( is_wp_error( $result ) ) {
    echo "❌ Error updating post: " . $result->get_error_message() . "\n";
} else {
    echo "✅ South Texas page (ID 1660) updated successfully.\n";
    echo "   Preview: http://e3es2026.local/?page_id=1660\n";
}

echo "\n🏁 Done.\n\n";
