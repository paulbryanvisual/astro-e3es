<?php
/**
 * Seed / Update: Industries Pages (Municipalities 1651, Healthcare 1652, Higher Education 1226)
 * Uses native Gutenberg blocks rather than HTML blocks.
 *
 * Run via Local PHP:
 *   "/Applications/Local.app/Contents/Resources/extraResources/lightning-services/php-8.2.29+0/bin/darwin-arm64/bin/php" \
 *     "/Users/bryanpaul/Dropbox/PaulDropbox/E3/website/wordpress-plugins/e3es-headless-helper/seed-industries.php"
 */

// ── Bootstrap WordPress ───────────────────────────────────────────────────────
$wp_load = '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';
if ( ! file_exists( $wp_load ) ) {
    die( "Cannot find wp-load.php at: $wp_load\n" );
}
define( 'ABSPATH_SKIP_REDIRECT', true );
require_once $wp_load;

// Bootstrap admin files for media upload handling
require_once( ABSPATH . 'wp-admin/includes/image.php' );
require_once( ABSPATH . 'wp-admin/includes/file.php' );
require_once( ABSPATH . 'wp-admin/includes/media.php' );

wp_set_current_user( 1 );
if ( function_exists( 'kses_remove_filters' ) ) {
    kses_remove_filters();
}

$site_url = rtrim( get_option( 'siteurl' ), '/' );

echo "\n🏢 Industries Page Seeder Starting...\n\n";

// Helper to find attachment ID by filename
function get_attachment_id_by_filename($filename) {
    global $wpdb;
    $query = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s";
    $attachment_id = $wpdb->get_var($wpdb->prepare($query, '%' . $filename));
    return $attachment_id ? intval($attachment_id) : 0;
}

// Helper to upload file programmatically
function upload_local_file_to_media($local_path, $title = '') {
    if (!file_exists($local_path)) {
        echo "  Error: Local file does not exist at $local_path\n";
        return '';
    }
    
    $filename = basename($local_path);
    $existing_id = get_attachment_id_by_filename($filename);
    if ($existing_id) {
        echo "  Asset already exists in media library: $filename (ID: $existing_id)\n";
        return wp_get_attachment_url($existing_id);
    }
    
    // Copy file to temp dir for sideloading
    $tmp_dir = ini_get('upload_tmp_dir') ? ini_get('upload_tmp_dir') : sys_get_temp_dir();
    $tmp_path = rtrim($tmp_dir, '/') . '/' . $filename;
    copy($local_path, $tmp_path);
    
    $file_array = [
        'name'     => $filename,
        'tmp_name' => $tmp_path,
    ];
    
    // Upload file
    $id = media_handle_sideload($file_array, 0, $title);
    if (is_wp_error($id)) {
        echo "  Error uploading $filename: " . $id->get_error_message() . "\n";
        return '';
    }
    
    $url = wp_get_attachment_url($id);
    echo "  Uploaded new asset: $filename (ID: $id) -> $url\n";
    return $url;
}

// ── Upload Assets ─────────────────────────────────────────────────────────────

// Water page assets
$water_bg      = upload_local_file_to_media('/Users/bryanpaul/Local Sites/astro-e3es/scratch/water_images/stockdale_bg.jpg', 'Stockdale Header Background');
$larry_jones   = upload_local_file_to_media('/Users/bryanpaul/Local Sites/astro-e3es/scratch/water_images/larry_jones.jpg', 'Larry Jones Photo');
$rich_gibbens  = upload_local_file_to_media('/Users/bryanpaul/Local Sites/astro-e3es/scratch/water_images/rich_gibbens.jpg', 'Rich Gibbens Photo');
$timothy_davis = upload_local_file_to_media('/Users/bryanpaul/Local Sites/astro-e3es/scratch/water_images/timothy_davis.jpg', 'Timothy Davis Photo');
$ron_mcvey     = upload_local_file_to_media('/Users/bryanpaul/Local Sites/astro-e3es/scratch/water_images/ron_mcvey.jpg', 'Ron McVey Photo');
$weimar_well   = upload_local_file_to_media('/Users/bryanpaul/Local Sites/astro-e3es/scratch/water_images/weimar_well.jpg', 'Weimar Well Before and After');

// Healthcare page assets
$health_bg     = upload_local_file_to_media('/Users/bryanpaul/Local Sites/astro-e3es/scratch/torch_images/operation_room.jpg', 'Operation Room Background');
$torch_logo    = upload_local_file_to_media('/Users/bryanpaul/Local Sites/astro-e3es/scratch/torch_images/e3_torch_logo.png', 'E3 and TORCH Logo');

// Higher Education page assets
$highered_bg   = upload_local_file_to_media('/Users/bryanpaul/Local Sites/astro-e3es/scratch/highered_images/highered_bg.jpg', 'Higher Education Header Background');
$b_mund        = upload_local_file_to_media('/Users/bryanpaul/Local Sites/astro-e3es/scratch/highered_images/b_mund.jpg', 'Ben Mund Photo');
$dan_schmitz   = upload_local_file_to_media('/Users/bryanpaul/Local Sites/astro-e3es/scratch/highered_images/dan_schmitz.jpg', 'Dan Schmitz Photo');
$bca_img       = upload_local_file_to_media('/Users/bryanpaul/Local Sites/astro-e3es/scratch/highered_images/bca.jpg', 'Business Case Analysis');
$buyboard      = upload_local_file_to_media('/Users/bryanpaul/Local Sites/astro-e3es/scratch/highered_images/buyboard.png', 'BuyBoard Logo');
$tips          = upload_local_file_to_media('/Users/bryanpaul/Local Sites/astro-e3es/scratch/highered_images/tips.png', 'TIPS Logo');

// PDFs
$bicarbus_pdf  = upload_local_file_to_media('/Users/bryanpaul/Local Sites/astro-e3es/scratch/BiCARBUS2026.pdf', 'BiCARBUS Flyer PDF');
$stockdale_pdf = upload_local_file_to_media('/Users/bryanpaul/Local Sites/astro-e3es/scratch/Stockdale-Case-Study.pdf', 'Stockdale Case Study PDF');
$city_county   = upload_local_file_to_media('/Users/bryanpaul/Local Sites/astro-e3es/scratch/City-County-Flier-2026.pdf', 'City and County Flyer PDF');
$health_pdf    = upload_local_file_to_media('/Users/bryanpaul/Local Sites/astro-e3es/scratch/Healthcare-and-Design-Build.pdf', 'Healthcare and Design Build PDF');
$highered_pdf  = upload_local_file_to_media('/Users/bryanpaul/Local Sites/astro-e3es/scratch/Higher-Ed-2026.pdf', 'Higher Education PDF');


// ── 1. Seeding Municipalities (ID 1651) ───────────────────────────────────────
echo "\nSeeding Municipalities page...\n";

$muni_content = <<<GUTENBERG
<!-- wp:e3es/intro-banner {"title":"Municipalities","bgImageUrl":"$water_bg","subtitle":"Helping Texas cities optimize their infrastructure and resources."} -->
<section class="wp-block-e3es-intro-banner db-page-hero" style="background-image: linear-gradient(rgba(14, 53, 27, 0.7), rgba(14, 53, 27, 0.7)), url('$water_bg'); background-size: cover; background-position: center;">
<div class="db-page-hero__container">
<h1 class="db-page-hero__title">Municipalities</h1>
<div class="db-page-hero__intro">
<p>Helping Texas cities optimize their infrastructure and resources.</p>
</div>
</div>
</section>
<!-- /wp:e3es/intro-banner -->

<!-- wp:group {"className":"industry-layout","layout":{"type":"constrained"}} -->
<div class="wp-block-group industry-layout"><!-- wp:group {"className":"industry-layout__container","layout":{"type":"constrained"}} -->
<div class="wp-block-group industry-layout__container"><!-- wp:group {"className":"industry-layout__main","layout":{"type":"constrained"}} -->
<div class="wp-block-group industry-layout__main">
      <!-- wp:heading {"level":3,"className":"industry-layout__main-title"} -->
      <h3 class="wp-block-heading industry-layout__main-title">Cleaner, more efficient systems with zero upfront cost</h3>
      <!-- /wp:heading -->

      <!-- wp:paragraph -->
      <p>Across Texas, cities are faced with the challenges of aging infrastructure. Repair, maintenance, and operating costs are increasing while at the same time revenues and system efficiencies are decreasing. At E3 Entegral Solutions, we help municipalities take control of their systems with unique and tailored solutions developed and designed specifically for each city. The 2 key elements E3 brings are technical expertise and capital funding generation.</p>
      <!-- /wp:paragraph -->

      <!-- wp:paragraph -->
      <p>E3 delivers applicable and powerful technology and our projects have included measures ensuring safe and reliable access to quality water all the way to wastewater treatment safeguarding the environment.</p>
      <!-- /wp:paragraph -->

      <!-- wp:heading {"level":4,"className":"industry-layout__sub-title"} -->
      <h4 class="wp-block-heading industry-layout__sub-title">Mobile. Automated. Scalable.</h4>
      <!-- /wp:heading -->

      <!-- wp:paragraph -->
      <p><strong>Metering-as-a-Service (MaaS)</strong><br>A fully managed solution for upgrading to advanced metering infrastructure (AMI):</p>
      <!-- /wp:paragraph -->

      <!-- wp:list {"className":"industry-layout__list"} -->
      <ul class="wp-block-list industry-layout__list">
<!-- wp:list-item {"className":"industry-layout__list-item"} --><li class="industry-layout__list-item">100% accurate meter reads (within 3 days)</li><!-- /wp:list-item -->
<!-- wp:list-item {"className":"industry-layout__list-item"} --><li class="industry-layout__list-item">Daily leak detection reports</li><!-- /wp:list-item -->
<!-- wp:list-item {"className":"industry-layout__list-item"} --><li class="industry-layout__list-item">Reduced re-reads by up to 98%</li><!-- /wp:list-item -->
<!-- wp:list-item {"className":"industry-layout__list-item"} --><li class="industry-layout__list-item">Locked-in pricing for up to 20 years</li><!-- /wp:list-item -->
<!-- wp:list-item {"className":"industry-layout__list-item"} --><li class="industry-layout__list-item">No upfront cost—only pay for meters that work</li><!-- /wp:list-item -->
      </ul>
      <!-- /wp:list -->

      <!-- wp:heading {"level":4,"className":"industry-layout__sub-title"} -->
      <h4 class="wp-block-heading industry-layout__sub-title">Advanced Water Treatment Solutions</h4>
      <!-- /wp:heading -->

      <!-- wp:paragraph -->
      <p><strong>Nano-Bubble Technology</strong><br>Using patented nano-bubble infusion, this process saturates water with microscopic oxygen bubbles that purify, oxygenate, and break down contaminants at the molecular level. For municipal lagoon systems, Nano-bubble technology improves sludge reduction without the need for dredging.</p>
      <!-- /wp:paragraph -->

      <!-- wp:paragraph -->
      <p><strong>BiCARBUS</strong><br>BiCARBUS is a neutral-pH sodium hypochlorite solution designed to work with existing chlorination systems. It boosts disinfection effectiveness, eliminates biofilm and scale, and dramatically reduces chemical budgets. Certified to NSF/ANSI/CAN 60 and backed by University of Houston research, BiCARBUS is already proving its value in Texas water systems.</p>
      <!-- /wp:paragraph -->

      <!-- wp:heading {"level":4,"className":"industry-layout__sub-title"} -->
      <h4 class="wp-block-heading industry-layout__sub-title">City of Stockdale Case Study</h4>
      <!-- /wp:heading -->

      <!-- wp:paragraph -->
      <p>The City of Stockdale’s nine-acre wastewater lagoon system had accumulated more than 30 years of sludge, causing elevated TSS, BOD5, and ammonia levels and threatening regulatory compliance. Traditional dredging was estimated to cost nearly $10 million and would have significantly disrupted operations.</p>
      <!-- /wp:paragraph -->

      <!-- wp:paragraph -->
      <p>E3 implemented a Nano-Bubble bioremediation solution that agitated compacted sludge, increased oxygen levels, and accelerated microbial digestion. The results reduced sludge depths by up to 80 percent, improved water quality dramatically, restored full system capacity, and brought the lagoons back into compliance, all while avoiding thousands of tons of sludge hauling and millions in dredging costs.</p>
      <!-- /wp:paragraph -->

      <!-- wp:paragraph -->
      <p><a class="industry-layout__file-link" href="$stockdale_pdf" target="_blank"><span class="far fa-file-pdf"></span> PDF: Case study one-pager.</a></p>
      <!-- /wp:paragraph -->

      <!-- wp:heading {"level":4,"className":"industry-layout__sub-title"} -->
      <h4 class="wp-block-heading industry-layout__sub-title">City of Weimar</h4>
      <!-- /wp:heading -->

      <!-- wp:paragraph -->
      <p>When Weimar’s Water Well #9 was taken offline due to discoloration, foul taste, and odor, the city faced a costly choice: risk a TCEQ violation or fund new well construction. E3 avoided both by implementing a controlled BiCARBUS treatment—an NSF/ANSI Standard 60 certified solution that eliminated biofilm and mineral scale from the inside out. Field monitoring of Oxidation Reduction Potential, Total Dissolved Solids, and conductivity confirmed the well was being restored at every stage, returning it to full operational status without aggressive chemicals or capital replacement.</p>
      <!-- /wp:paragraph -->

      <!-- wp:image {"className":"industry-layout__img-wrap"} -->
      <figure class="wp-block-image industry-layout__img-wrap"><img src="$weimar_well" alt="Weimar Well Before and after"/></figure>
      <!-- /wp:image -->

      <!-- wp:heading {"level":4,"className":"industry-layout__sub-title"} -->
      <h4 class="wp-block-heading industry-layout__sub-title">City of Timpson</h4>
      <!-- /wp:heading -->

      <!-- wp:paragraph -->
      <p>In partnership with the city, E3 is deploying its cutting-edge BiCARBUS technology to eliminate TTHMs (chemical byproducts formed during water treatment) at the source, establishing microbial control in groundwater before harmful byproducts can form. Combined with a high-efficiency air stripper and advanced ultrasonic water metering, this nearly $1 million infrastructure upgrade delivers cleaner water, smarter data, and long-term system reliability.</p>
      <!-- /wp:paragraph -->

      <!-- wp:paragraph -->
      <p>Funded through a Performance Rebuilding Program, this project is a model for forward-thinking water infrastructure, extending system lifespan and giving communities the confidence that comes with safe, high-quality water.</p>
      <!-- /wp:paragraph -->

      <!-- wp:heading {"level":4,"className":"industry-layout__sub-title"} -->
      <h4 class="wp-block-heading industry-layout__sub-title">Why Texas Municipalities Choose E3</h4>
      <!-- /wp:heading -->

      <!-- wp:list {"className":"industry-layout__list"} -->
      <ul class="wp-block-list industry-layout__list">
<!-- wp:list-item {"className":"industry-layout__list-item"} --><li class="industry-layout__list-item">Experts in public-sector infrastructure (schools, hospitals, and cities)</li><!-- /wp:list-item -->
<!-- wp:list-item {"className":"industry-layout__list-item"} --><li class="industry-layout__list-item">Turnkey service: from audit to implementation and long-term support</li><!-- /wp:list-item -->
<!-- wp:list-item {"className":"industry-layout__list-item"} --><li class="industry-layout__list-item">Transparent pricing and performance guarantees</li><!-- /wp:list-item -->
<!-- wp:list-item {"className":"industry-layout__list-item"} --><li class="industry-layout__list-item">Access to $100M+ bonding capacity and decades of experience</li><!-- /wp:list-item -->
      </ul>
      <!-- /wp:list -->
    </div><!-- /wp:group --></div><!-- /wp:group --></div><!-- /wp:group -->
GUTENBERG;

wp_update_post([
    'ID'           => 1651,
    'post_title'   => 'Municipalities',
    'post_content' => wp_slash( $muni_content ),
    'post_status'  => 'publish',
], true);
echo "  Success seeding Municipalities (ID 1651)!\n";


// ── 2. Seeding Healthcare (ID 1652) ───────────────────────────────────────────
echo "\nSeeding Healthcare page...\n";

$health_content = <<<GUTENBERG
<!-- wp:e3es/intro-banner {"title":"Healthcare","bgImageUrl":"$health_bg","subtitle":"Providing healthy and efficient facilities for patients and staff."} -->
<section class="wp-block-e3es-intro-banner db-page-hero" style="background-image: linear-gradient(rgba(14, 53, 27, 0.7), rgba(14, 53, 27, 0.7)), url('$health_bg'); background-size: cover; background-position: center;">
<div class="db-page-hero__container">
<h1 class="db-page-hero__title">Healthcare</h1>
<div class="db-page-hero__intro">
<p>Providing healthy and efficient facilities for patients and staff.</p>
</div>
</div>
</section>
<!-- /wp:e3es/intro-banner -->

<!-- wp:group {"className":"industry-layout","layout":{"type":"constrained"}} -->
<div class="wp-block-group industry-layout"><!-- wp:group {"className":"industry-layout__container","layout":{"type":"constrained"}} -->
<div class="wp-block-group industry-layout__container"><!-- wp:group {"className":"industry-layout__main","layout":{"type":"constrained"}} -->
<div class="wp-block-group industry-layout__main">
      <!-- wp:group {"className":"industry-layout__header-wrap","layout":{"type":"constrained"}} -->
      <div class="wp-block-group industry-layout__header-wrap">
        <!-- wp:heading {"level":3,"className":"industry-layout__main-title"} -->
        <h3 class="wp-block-heading industry-layout__main-title">E3 AND TORCH</h3>
        <!-- /wp:heading -->

        <!-- wp:image {"className":"industry-layout__logo"} -->
      <figure class="wp-block-image industry-layout__logo"><img src="$torch_logo" alt="E3 and TORCH Logo"/></figure>
      <!-- /wp:image -->
      </div>
      <!-- /wp:group -->

      <!-- wp:group {"className":"industry-layout__intro","layout":{"type":"constrained"}} -->
      <div class="wp-block-group industry-layout__intro">
        <!-- wp:paragraph -->
        <p>At E3, we are proud to collaborate closely with the Texas Organization of Rural &amp; Community Hospitals (TORCH) as an Endorsed Partner through TORCH Management Services Incorporated (TMSI). This partnership underscores our shared commitment to enhancing healthcare in rural Texas.</p>
        <!-- /wp:paragraph -->

        <!-- wp:paragraph -->
        <p>Highlighting our impactful collaboration, view our video case study on the improvements made at Goodall-Witcher Hospital, demonstrating how our projects are tailored to meet the unique needs of community hospitals and enhance patient care.</p>
        <!-- /wp:paragraph -->
      </div>
      <!-- /wp:group -->

      <!-- wp:e3es/video-embed {"title":"Goodall-Witcher Healthcare Case Study","videoUrl":"https://player.vimeo.com/video/740399213?dnt=1"} -->
      <section class="wp-block-e3es-video-embed db-video-section"><h3 class="db-video-section__title">Goodall-Witcher Healthcare Case Study</h3><div class="db-video-wrapper"><iframe src="https://player.vimeo.com/video/740399213?dnt=1" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen title="Goodall-Witcher Healthcare Case Study"></iframe></div></section>
      <!-- /wp:e3es/video-embed -->
      
      <!-- wp:group {"className":"industry-layout__section","layout":{"type":"constrained"}} -->
      <div class="wp-block-group industry-layout__section">
        <!-- wp:heading {"level":4,"className":"industry-layout__section-title"} -->
        <h4 class="wp-block-heading industry-layout__section-title">OUR IMPACT IN RURAL HEALTHCARE</h4>
        <!-- /wp:heading -->

        <!-- wp:paragraph -->
        <p>E3 has spearheaded numerous projects to upgrade and enhance healthcare facilities across rural Texas. Our efforts are tailored to meet the unique needs of community hospitals, ensuring they have access to the best possible care environments.</p>
        <!-- /wp:paragraph -->
      </div>
      <!-- /wp:group -->
      
      <!-- wp:group {"className":"industry-layout__section","layout":{"type":"constrained"}} -->
      <div class="wp-block-group industry-layout__section">
        <!-- wp:heading {"level":4,"className":"industry-layout__section-title"} -->
        <h4 class="wp-block-heading industry-layout__section-title">SPECIALIZED SERVICES TAILORED TO RURAL NEEDS</h4>
        <!-- /wp:heading -->

        <!-- wp:paragraph -->
        <p>We recognize the critical role that rural hospitals play in their communities. That’s why we focus on customized solutions that align with TORCH’s goals:</p>
        <!-- /wp:paragraph -->

        <!-- wp:list {"className":"industry-layout__list"} -->
        <ul class="wp-block-list industry-layout__list">
<!-- wp:list-item {"className":"industry-layout__list-item"} --><li class="industry-layout__list-item"><strong>Facility Upgrades:</strong> From structural improvements to advanced energy-efficient systems, we ensure hospitals are equipped to serve their communities effectively.</li><!-- /wp:list-item -->
<!-- wp:list-item {"className":"industry-layout__list-item"} --><li class="industry-layout__list-item"><strong>Funding Assistance:</strong> We assist rural hospitals in identifying and securing funding for critical infrastructure upgrades, including accessing grants for LED retrofit projects to enhance lighting efficiency and reduce energy costs.</li><!-- /wp:list-item -->
<!-- wp:list-item {"className":"industry-layout__list-item"} --><li class="industry-layout__list-item"><strong>Energy Efficiency:</strong> Our expertise in energy solutions helps facilities increase case flow and enhance operational efficiency.</li><!-- /wp:list-item -->
<!-- wp:list-item {"className":"industry-layout__list-item"} --><li class="industry-layout__list-item"><strong>Education and Training:</strong> Supporting TORCH’s educational initiatives, we offer training for hospital staff on new systems and technologies, ensuring they can maximize the benefits of updated infrastructure.</li><!-- /wp:list-item -->
        </ul>
        <!-- /wp:list -->
      </div>
      <!-- /wp:group -->
      
      <!-- wp:group {"className":"industry-layout__section","layout":{"type":"constrained"}} -->
      <div class="wp-block-group industry-layout__section">
        <!-- wp:heading {"level":4,"className":"industry-layout__section-title"} -->
        <h4 class="wp-block-heading industry-layout__section-title">OUR COMMITMENT TO RURAL HEALTH</h4>
        <!-- /wp:heading -->

        <!-- wp:paragraph -->
        <p>Our mission aligns with that of TORCH—to ensure that rural communities in Texas have access to exceptional healthcare services. We are dedicated to:</p>
        <!-- /wp:paragraph -->

        <!-- wp:list {"className":"industry-layout__list"} -->
        <ul class="wp-block-list industry-layout__list">
<!-- wp:list-item {"className":"industry-layout__list-item"} --><li class="industry-layout__list-item"><strong>Preserving Local Healthcare:</strong> By enhancing facility efficiency and functionality, we help keep healthcare local and more accessible.</li><!-- /wp:list-item -->
<!-- wp:list-item {"className":"industry-layout__list-item"} --><li class="industry-layout__list-item"><strong>Advocating for Rural Health:</strong> We support TORCH’s efforts to advocate for policies that benefit rural hospitals and their communities.</li><!-- /wp:list-item -->
<!-- wp:list-item {"className":"industry-layout__list-item"} --><li class="industry-layout__list-item"><strong>Building for the Future:</strong> We are committed to innovating and adapting solutions that anticipate the future needs of rural healthcare.</li><!-- /wp:list-item -->
        </ul>
        <!-- /wp:list -->

        <!-- wp:paragraph {"className":"industry-layout__footer-text"} -->
        <p class="industry-layout__footer-text">Together with TORCH, E3 is set to continue transforming rural healthcare landscapes in Texas, ensuring every community has the health resources it needs to thrive.</p>
        <!-- /wp:paragraph -->
      </div>
      <!-- /wp:group -->
    </div><!-- /wp:group --></div><!-- /wp:group --></div><!-- /wp:group -->
GUTENBERG;

wp_update_post([
    'ID'           => 1652,
    'post_title'   => 'Healthcare',
    'post_content' => wp_slash( $health_content ),
    'post_status'  => 'publish',
], true);
echo "  Success seeding Healthcare (ID 1652)!\n";


// ── 3. Seeding Higher Education (ID 1226) ─────────────────────────────────────
echo "\nSeeding Higher Education page...\n";

$highered_content = <<<GUTENBERG
<!-- wp:e3es/intro-banner {"title":"Higher Education","bgImageUrl":"$highered_bg","subtitle":"Modernizing campus facilities to maximize energy efficiency."} -->
<section class="wp-block-e3es-intro-banner db-page-hero" style="background-image: linear-gradient(rgba(14, 53, 27, 0.7), rgba(14, 53, 27, 0.7)), url('$highered_bg'); background-size: cover; background-position: center;">
<div class="db-page-hero__container">
<h1 class="db-page-hero__title">Higher Education</h1>
<div class="db-page-hero__intro">
<p>Modernizing campus facilities to maximize energy efficiency.</p>
</div>
</div>
</section>
<!-- /wp:e3es/intro-banner -->

<!-- wp:group {"className":"industry-layout","layout":{"type":"constrained"}} -->
<div class="wp-block-group industry-layout"><!-- wp:group {"className":"industry-layout__container","layout":{"type":"constrained"}} -->
<div class="wp-block-group industry-layout__container"><!-- wp:group {"className":"industry-layout__main","layout":{"type":"constrained"}} -->
<div class="wp-block-group industry-layout__main">
      <!-- wp:heading {"level":3,"className":"industry-layout__main-title"} -->
      <h3 class="wp-block-heading industry-layout__main-title">Higher Education</h3>
      <!-- /wp:heading -->

      <!-- wp:paragraph -->
      <p>E3 brings decades of design-build experience to college and university campuses across Texas. From community colleges serving tight regional budgets to state university systems managing complex, aging infrastructure, we build solutions that reduce energy costs, improve comfort, and free up resources for your real mission: educating students.</p>
      <!-- /wp:paragraph -->

      <!-- wp:paragraph -->
      <p>Campus infrastructure is complex. Aging mechanical systems, mixed-use buildings, 24/7 operational demands, and the expectation of zero disruption require a contractor who knows what they’re doing. E3 designs and installs high-efficiency HVAC systems, LED lighting, building automation, and infrastructure improvements on a schedule that keeps classrooms, labs, and residence halls running. We do what needs doing at night, on weekends, and over breaks because that’s how you run a project on a live campus.</p>
      <!-- /wp:paragraph -->
      
      <!-- wp:heading {"level":4,"className":"industry-layout__sub-title"} -->
      <h4 class="wp-block-heading industry-layout__sub-title">San Jacinto College Case Study | Lighting Modernization &amp; Energy Efficiency Improvements</h4>
      <!-- /wp:heading -->

      <!-- wp:paragraph -->
      <p>When San Jacinto College needed to address aging lighting infrastructure, inconsistent classroom lighting, and rising energy concerns, they turned to E3 for a comprehensive upgrade.</p>
      <!-- /wp:paragraph -->

      <!-- wp:paragraph -->
      <p>This project focused on replacing outdated fixtures with modern LED lighting, implementing occupancy sensors, and improving lighting controls across key campus facilities, including classrooms and library spaces. The result was improved energy efficiency, enhanced safety, reduced user error, and a more comfortable learning environment for students and faculty.</p>
      <!-- /wp:paragraph -->

      <!-- wp:paragraph -->
      <p>In this case study, Joshua Delgado, Energy Management Coordinator for San Jacinto College, shares how E3 delivered the project with minimal disruption to daily operations by performing much of the work after hours, allowing classes to continue uninterrupted. The project was completed on schedule, within budget, and without complaints from building occupants.</p>
      <!-- /wp:paragraph -->

      <!-- wp:paragraph -->
      <p>Watch to hear firsthand how thoughtful planning, energy-efficient technology, and a collaborative approach helped San Jacinto College improve campus facilities while creating a better experience for everyone who uses them.</p>
      <!-- /wp:paragraph -->
      
      <!-- wp:heading {"level":4,"className":"industry-layout__sub-title"} -->
      <h4 class="wp-block-heading industry-layout__sub-title">Featured Solutions</h4>
      <!-- /wp:heading -->

      <!-- wp:list {"className":"industry-layout__list"} -->
      <ul class="wp-block-list industry-layout__list">
<!-- wp:list-item {"className":"industry-layout__list-item"} --><li class="industry-layout__list-item">LED Lighting Upgrades</li><!-- /wp:list-item -->
<!-- wp:list-item {"className":"industry-layout__list-item"} --><li class="industry-layout__list-item">Occupancy Sensors &amp; Lighting Controls</li><!-- /wp:list-item -->
<!-- wp:list-item {"className":"industry-layout__list-item"} --><li class="industry-layout__list-item">Energy Efficiency Improvements</li><!-- /wp:list-item -->
<!-- wp:list-item {"className":"industry-layout__list-item"} --><li class="industry-layout__list-item">Campus Facility Modernization</li><!-- /wp:list-item -->
<!-- wp:list-item {"className":"industry-layout__list-item"} --><li class="industry-layout__list-item">Educational Facility Upgrades</li><!-- /wp:list-item -->
      </ul>
      <!-- /wp:list -->
      
      <!-- wp:heading {"level":4,"className":"industry-layout__sub-title"} -->
      <h4 class="wp-block-heading industry-layout__sub-title">START WITH A FREE BUSINESS CASE ANALYSIS</h4>
      <!-- /wp:heading -->

      <!-- wp:paragraph -->
      <p>E3 offers a complimentary Business Case Analysis (BCA) for your campus, no cost, no obligation. Our team will assess the current state of your facilities, identify the highest-impact upgrade opportunities, and model the projected savings. You’ll walk away with a clear picture of where your facilities stand and what’s possible. Straight answers, not a sales pitch.</p>
      <!-- /wp:paragraph -->

      <!-- wp:image {"className":"industry-layout__img-wrap"} -->
      <figure class="wp-block-image industry-layout__img-wrap"><img src="$bca_img" alt="Business Case Analysis"/></figure>
      <!-- /wp:image -->
      
      <!-- wp:heading {"level":4,"className":"industry-layout__sub-title"} -->
      <h4 class="wp-block-heading industry-layout__sub-title">Procurement</h4>
      <!-- /wp:heading -->

      <!-- wp:paragraph -->
      <p>E3 is a member of BuyBoard, TIPS, and other co-ops, so Texas colleges and universities can procure our services without the time and expense of a full competitive bid process. If your institution uses a co-op to purchase, we’re already on contract and ready to go.</p>
      <!-- /wp:paragraph -->

      <!-- wp:group {"className":"industry-layout__logos","layout":{"type":"constrained"}} -->
      <div class="wp-block-group industry-layout__logos">
        <!-- wp:image {"className":"industry-layout__logo-img"} -->
      <figure class="wp-block-image industry-layout__logo-img"><img src="$buyboard" alt="BuyBoard"/></figure>
      <!-- /wp:image -->

        <!-- wp:image {"className":"industry-layout__logo-img"} -->
      <figure class="wp-block-image industry-layout__logo-img"><img src="$tips" alt="TIPS"/></figure>
      <!-- /wp:image -->
      </div>
      <!-- /wp:group -->
      
      <!-- wp:heading {"level":4,"className":"industry-layout__sub-title"} -->
      <h4 class="wp-block-heading industry-layout__sub-title">Financing</h4>
      <!-- /wp:heading -->

      <!-- wp:paragraph -->
      <p>Getting a project funded shouldn’t be the hard part. E3 navigates the financing landscape so you don’t have to, including:</p>
      <!-- /wp:paragraph -->

      <!-- wp:list {"className":"industry-layout__list"} -->
      <ul class="wp-block-list industry-layout__list">
<!-- wp:list-item {"className":"industry-layout__list-item"} --><li class="industry-layout__list-item"><strong>New Market Tax Credits (NMTCs):</strong> can significantly reduce net project costs for institutions serving economically distressed communities.</li><!-- /wp:list-item -->
<!-- wp:list-item {"className":"industry-layout__list-item"} --><li class="industry-layout__list-item"><strong>SECO LED Retrofit Programs:</strong> State Energy Conservation Office incentives designed specifically for Texas public institutions.</li><!-- /wp:list-item -->
<!-- wp:list-item {"className":"industry-layout__list-item"} --><li class="industry-layout__list-item"><strong>Utility incentives and federal grant programs:</strong> we identify and stack every available dollar before a project breaks ground.</li><!-- /wp:list-item -->
      </ul>
      <!-- /wp:list -->

      <!-- wp:paragraph -->
      <p>E3 has helped public institutions access millions in grant and incentive dollars. We’ll help you understand the full financial picture before a single decision is made.</p>
      <!-- /wp:paragraph -->
    </div><!-- /wp:group --></div><!-- /wp:group --></div><!-- /wp:group -->
GUTENBERG;

wp_update_post([
    'ID'           => 1226,
    'post_title'   => 'Higher Education',
    'post_content' => wp_slash( $highered_content ),
    'post_status'  => 'publish',
], true);
echo "  Success seeding Higher Education (ID 1226)!\n";

wp_cache_flush();
echo "\n🏁 Industries Page Seeding Complete!\n\n";
