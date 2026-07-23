<?php
/**
 * Seed / Update: TORCH Page (ID 6883)
 *
 * Updates the WordPress page content to match the live page structure.
 *
 * Run via Local PHP:
 *   "/Applications/Local.app/Contents/Resources/extraResources/lightning-services/php-8.2.29+0/bin/darwin-arm64/bin/php" \
 *     "/Users/bryanpaul/Dropbox/PaulDropbox/E3/website/wordpress-plugins/e3es-headless-helper/seed-torch.php"
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

echo "\n🔥 TORCH Page Seeder Starting...\n\n";

// ── Image URLs ────────────────────────────────────────────────────────────────
$logo_img = $site_url . '/wp-content/uploads/2026/07/E3_TORCH_2024.png';
$hero_bg  = $site_url . '/wp-content/uploads/2026/07/Operation-room.jpg';

// Helper to clean HTML blocks for Gutenberg to prevent wpautop issues
function clean_gutenberg_html($html) {
    $html = str_replace(array("\r", "\n"), '', $html);
    $html = preg_replace('/>\s+</', '><', $html);
    return trim($html);
}
$main_html = clean_gutenberg_html('
<div class="torch-main">
  <div class="torch-main__header-wrap">
    <h2 class="torch-main__title">E3 AND TORCH</h2>
    <img src="' . $logo_img . '" alt="E3 and TORCH Logo" class="torch-main__logo" />
  </div>
  <div class="torch-main__intro">
    <p>At E3, we are proud to collaborate closely with the Texas Organization of Rural &amp; Community Hospitals (TORCH) as an Endorsed Partner through TORCH Management Services Incorporated (TMSI). This partnership underscores our shared commitment to enhancing healthcare in rural Texas.</p>
    <p>Highlighting our impactful collaboration, view our video case study on the improvements made at Goodall-Witcher Hospital, demonstrating how our projects are tailored to meet the unique needs of community hospitals and enhance patient care.</p>
  </div>
  <div class="torch-main__video-wrapper">
    <iframe title="Goodall-Witcher Healthcare Case Study" src="https://player.vimeo.com/video/740399213?dnt=1" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>
  </div>
  
  <div class="torch-main__section">
    <h3 class="torch-main__section-title">OUR IMPACT IN RURAL HEALTHCARE</h3>
    <p>E3 has spearheaded numerous projects to upgrade and enhance healthcare facilities across rural Texas. Our efforts are tailored to meet the unique needs of community hospitals, ensuring they have access to the best possible care environments.</p>
  </div>

  <div class="torch-main__section">
    <h3 class="torch-main__section-title">SPECIALIZED SERVICES TAILORED TO RURAL NEEDS</h3>
    <p>We recognize the critical role that rural hospitals play in their communities. That’s why we focus on customized solutions that align with TORCH’s goals:</p>
    <ul class="torch-main__list">
      <li class="torch-main__list-item"><strong>Facility Upgrades:</strong> From structural improvements to advanced energy-efficient systems, we ensure hospitals are equipped to serve their communities effectively.</li>
      <li class="torch-main__list-item"><strong>Funding Assistance:</strong> We assist rural hospitals in identifying and securing funding for critical infrastructure upgrades, including accessing grants for LED retrofit projects to enhance lighting efficiency and reduce energy costs.</li>
      <li class="torch-main__list-item"><strong>Energy Efficiency:</strong> Our expertise in energy solutions helps facilities increase case flow and enhance operational efficiency.</li>
      <li class="torch-main__list-item"><strong>Education and Training:</strong> Supporting TORCH’s educational initiatives, we offer training for hospital staff on new systems and technologies, ensuring they can maximize the benefits of updated infrastructure.</li>
    </ul>
  </div>

  <div class="torch-main__section">
    <h3 class="torch-main__section-title">OUR COMMITMENT TO RURAL HEALTH</h3>
    <p>Our mission aligns with that of TORCH—to ensure that rural communities in Texas have access to exceptional healthcare services. We are dedicated to:</p>
    <ul class="torch-main__list">
      <li class="torch-main__list-item"><strong>Preserving Local Healthcare:</strong> By enhancing facility efficiency and functionality, we help keep healthcare local and more accessible.</li>
      <li class="torch-main__list-item"><strong>Advocating for Rural Health:</strong> We support TORCH’s efforts to advocate for policies that benefit rural hospitals and their communities.</li>
      <li class="torch-main__list-item"><strong>Building for the Future:</strong> We are committed to innovating and adapting solutions that anticipate the future needs of rural healthcare.</li>
    </ul>
    <p class="torch-main__footer-text">Together with TORCH, E3 is set to continue transforming rural healthcare landscapes in Texas, ensuring every community has the health resources it needs to thrive.</p>
  </div>
</div>
');

// ── Build New Block Content ───────────────────────────────────────────────────
$content = <<<HTML

<!-- wp:e3es/intro-banner {"title":"TORCH","bgImageUrl":"$hero_bg","subtitle":"ENDORSED PARTNER"} -->
<section class="wp-block-e3es-intro-banner db-page-hero" style="background-image: linear-gradient(rgba(14, 53, 27, 0.7), rgba(14, 53, 27, 0.7)), url('$hero_bg'); background-size: cover; background-position: center;">
<div class="db-page-hero__container">
<h1 class="db-page-hero__title">TORCH</h1>
<div class="db-page-hero__intro">
<p>ENDORSED PARTNER</p>
</div>
</div>
</section>
<!-- /wp:e3es/intro-banner -->

<!-- wp:html -->
$main_html
<!-- /wp:html -->

HTML;

// ── Update the Post ───────────────────────────────────────────────────────────
$result = wp_update_post( [
    'ID'           => 6883,
    'post_title'   => 'TORCH',
    'post_content' => wp_slash( $content ),
    'post_status'  => 'publish',
    'post_type'    => 'page',
], true );

if ( is_wp_error( $result ) ) {
    echo "❌ Error updating post: " . $result->get_error_message() . "\n";
} else {
    echo "✅ TORCH page (ID 6883) updated successfully.\n";
    echo "   Preview: http://e3es2026.local/?page_id=6883\n";
}

echo "\n🏁 Done.\n\n";
