<?php
define('WP_USE_THEMES', false);
require_once('/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php');

$dry_run = in_array('--dry-run', $argv) || !in_array('--execute', $argv);

if ($dry_run) {
    echo "=== DRY RUN MODE: No database changes will be saved ===\n";
    echo "To execute, run with: php add_missing_subtitles.php --execute\n\n";
} else {
    echo "=== EXECUTE MODE: Saving changes to the database ===\n\n";
}

$posts = get_posts(array(
    'post_type' => array('page', 'services'),
    'posts_per_page' => -1,
    'post_status' => 'publish',
));

function get_suggested_subtitle($title) {
    $title = trim(strip_tags($title));
    
    $mapping = array(
        'HVAC Systems and Upgrades' => 'High-efficiency commercial heating, ventilation, and air conditioning solutions for Texas public facilities.',
        'About Us' => 'Partnering with Texas school districts, cities, and counties to build energy-efficient, sustainable infrastructure.',
        'Our Story and State-Wide Impact' => 'Delivering guaranteed energy savings and modernized infrastructure across the state of Texas.',
        'Our Approach' => 'A proven, budget-neutral design-build process to modernize your facilities with zero capital outlay.',
        'The Design+Build Advantage' => 'Single-source accountability, guaranteed pricing, and turnkey engineering from concept to completion.',
        'South Texas' => 'Delivering modernized, energy-efficient school facilities and infrastructure across South Texas communities.',
        'Water and Wastewater Solutions' => 'Turnkey engineering, conservation, and descaling upgrades for Texas public water systems.',
        'Water Conservation and Low-Flow Upgrades' => 'Commercial water conservation upgrades and low-flow plumbing retrofits for Texas schools and public buildings.',
        'Water Wells and Pump Systems' => 'Water well rehabilitation, submersible pump replacement, and well testing services for Texas municipalities.',
        'Exterior and Security LED Lighting Solutions' => 'Turnkey exterior LED lighting upgrades to improve safety and eliminate dark spots on public facility grounds.',
        'Classroom and Interior LED Lighting Solutions' => 'High-performance interior LED lighting retrofits to improve focus, lower maintenance, and reduce energy consumption.',
        'LED Retrofit and Lighting Assessments' => 'Turnkey commercial LED retrofit services and data-driven lighting energy audits for Texas public entities.',
        'Sports and Stadium LED Lighting Solutions' => 'UIL/NCAA compliant LED sports and stadium lighting systems with instant on/off controls for schools and cities.',
        'Boiler Systems and Hydronic Heating Modernization' => 'High-efficiency commercial boiler replacements and steam-to-hot-water retrofits for Texas public buildings.',
        'Indoor Air Quality (IAQ) Analysis and Improvements' => 'Pathogen mitigation, advanced filtration, and smart IAQ monitoring for healthier public environments.',
        'SCADA Telemetry and TCEQ Compliance' => 'Smart SCADA telemetry system integration and remote monitoring solutions for Texas water utilities.',
    );

    if (isset($mapping[$title])) {
        return $mapping[$title];
    }
    
    if (stripos($title, 'lighting') !== false || stripos($title, 'led') !== false) {
        return 'Turnkey commercial LED lighting and sports lighting upgrades for Texas public facilities.';
    }
    if (stripos($title, 'water') !== false || stripos($title, 'wastewater') !== false) {
        return 'Turnkey water and wastewater infrastructure upgrades for Texas municipalities and public entities.';
    }
    if (stripos($title, 'hvac') !== false || stripos($title, 'heating') !== false || stripos($title, 'cooling') !== false) {
        return 'Turnkey commercial HVAC upgrades and mechanical replacements for Texas schools, cities, and hospitals.';
    }
    
    return 'Turnkey engineering and design-build solutions for Texas school districts, cities, and public facilities.';
}

$updated_count = 0;

foreach ($posts as $post) {
    if (!has_block('e3es/intro-banner', $post->post_content)) {
        continue;
    }

    $blocks = parse_blocks($post->post_content);
    $post_updated = false;

    foreach ($blocks as &$block) {
        if ($block['blockName'] !== 'e3es/intro-banner') {
            continue;
        }

        $attrs = $block['attrs'];
        $subtitle = isset($attrs['subtitle']) ? trim($attrs['subtitle']) : '';

        if (empty($subtitle)) {
            $suggested = get_suggested_subtitle($post->post_title);
            echo "ID: {$post->ID} | Title: \"{$post->post_title}\"\n";
            echo "  -> Adding Subtitle: \"{$suggested}\"\n";

            // Update block attributes
            $block['attrs']['subtitle'] = $suggested;

            // Rebuild innerHTML: insert the subtitle markup if not already present
            if (strpos($block['innerHTML'], 'db-page-hero__intro') === false) {
                $subtitle_html = '<div class="db-page-hero__intro"><p>' . esc_html($suggested) . '</p></div>';
                
                // Replace closing </h1> with </h1> + subtitle HTML
                if (strpos($block['innerHTML'], '</h1>') !== false) {
                    $block['innerHTML'] = str_replace('</h1>', '</h1>' . $subtitle_html, $block['innerHTML']);
                } else {
                    // Fallback insertion before closing section
                    $block['innerHTML'] = str_replace('</section>', $subtitle_html . '</section>', $block['innerHTML']);
                }
                
                $block['innerContent'] = array($block['innerHTML']);
            }

            $post_updated = true;
        }
    }
    unset($block); // Break references

    if ($post_updated) {
        $updated_count++;
        $new_content = serialize_blocks($blocks);

        if (!$dry_run) {
            wp_update_post(array(
                'ID' => $post->ID,
                'post_content' => $new_content,
            ));
            echo "  [SAVED]\n";
        } else {
            echo "  [WOULD SAVE]\n";
        }
        echo "\n";
    }
}

echo "Total posts updated: {$updated_count}\n";
