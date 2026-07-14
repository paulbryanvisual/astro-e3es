<?php
require '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';

// 1. Load legacy clients JSON mapping
$legacy_json = file_get_contents('/Users/bryanpaul/Local Sites/astro-e3es/src/data/legacy_clients.json');
$legacy_clients = json_decode($legacy_json, true);

// 2. Fetch all clients posts
$posts = get_posts(array(
    'post_type' => 'clients',
    'posts_per_page' => -1,
));

$term_slug_map = array(
    'HVAC' => 'hvac',
    'Lighting' => 'lighting',
    'Water & Plumbing' => 'water-plumbing',
    'Building Controls' => 'building-controls',
    'Building Envelope' => 'building-envelope',
    'Energy Infrastructure' => 'energy-infrastructure'
);

echo "Syncing client services terms...\n";

foreach ($posts as $p) {
    $title = $p->post_title;
    $content = $p->post_content;
    $services_to_assign = array();
    
    // Check if it is a legacy client
    $found_legacy = false;
    foreach ($legacy_clients as $lc) {
        if (strcasecmp($lc['name'], $title) === 0 || strcasecmp(str_replace(' ISD', '', $lc['name']), str_replace(' ISD', '', $title)) === 0) {
            $services_to_assign = $lc['services'];
            $found_legacy = true;
            break;
        }
    }
    
    // If not found in legacy_clients, auto-detect from content keywords
    if (!$found_legacy) {
        // Detect HVAC
        if (preg_match('/\b(HVAC|air conditioning|heating|chiller|cooling|boiler|ventilation|mechanical|RTU|AHU|furnace)\b/i', $content)) {
            $services_to_assign[] = 'HVAC';
        }
        // Detect Lighting
        if (preg_match('/\b(lighting|LED|retrofit|fixture|fixtures|lamps|sports-lighting)\b/i', $content)) {
            $services_to_assign[] = 'Lighting';
        }
        // Detect Water & Plumbing
        if (preg_match('/\b(water|plumbing|low-flow|faucet|toilet|irrigation|conservation|aerator|flush)\b/i', $content)) {
            $services_to_assign[] = 'Water & Plumbing';
        }
        // Detect Building Controls
        if (preg_match('/\b(building automation|BAS|building controls|controls|EMS|energy management system|thermostat)\b/i', $content)) {
            $services_to_assign[] = 'Building Controls';
        }
        // Detect Building Envelope
        if (preg_match('/\b(envelope|weatherization|window|sealing|roofing|roof|insulation)\b/i', $content)) {
            $services_to_assign[] = 'Building Envelope';
        }
        // Detect Energy Infrastructure
        if (preg_match('/\b(infrastructure|solar|generator|microgrid|utility|cogeneration|substation)\b/i', $content)) {
            $services_to_assign[] = 'Energy Infrastructure';
        }
    }
    
    // Map service names to term IDs/slugs and set them
    $term_ids = array();
    foreach ($services_to_assign as $s) {
        $slug = isset($term_slug_map[$s]) ? $term_slug_map[$s] : sanitize_title($s);
        $term = get_term_by('slug', $slug, 'client-services');
        if ($term) {
            $term_ids[] = (int)$term->term_id;
        } else {
            // Create the term if it doesn't exist
            $new_term = wp_insert_term($s, 'client-services', array('slug' => $slug));
            if (!is_wp_error($new_term)) {
                $term_ids[] = (int)$new_term['term_id'];
            }
        }
    }
    
    // Set the terms on the post
    wp_set_object_terms($p->ID, $term_ids, 'client-services');
    
    $assigned_names = wp_get_post_terms($p->ID, 'client-services', array('fields' => 'names'));
    echo "- Assigned to \"{$title}\" (ID: {$p->ID}): " . implode(', ', $assigned_names) . "\n";
}
echo "Sync complete!\n";
