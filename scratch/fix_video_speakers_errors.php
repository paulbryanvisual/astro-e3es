<?php
require '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';

// Disable KSES for safety
wp_set_current_user(1);
if (function_exists('kses_remove_filters')) {
    kses_remove_filters();
}

// 1. Map target person/employee posts by name to their real WordPress Post ID
$people_cache = [];
$people_posts = get_posts([
    'post_type' => ['people', 'employees'],
    'posts_per_page' => -1,
    'post_status' => 'any'
]);
foreach ($people_posts as $p) {
    $title_lower = strtolower(trim($p->post_title));
    $slug_lower = strtolower(trim($p->post_name));
    
    // Cache by title
    $people_cache[$title_lower] = $p->ID;
    
    // For employees, cache by clean name from slug as well
    if ($p->post_type === 'employees') {
        $parts = explode('-', $p->post_name);
        $clean_parts = [];
        foreach ($parts as $part) {
            if (in_array($part, ['pe', 'cem', 'leed', 'ap', 'pmp', 'eit', 'shrm', 'cp'])) continue;
            $clean_parts[] = $part;
        }
        $clean_name = implode(' ', array_slice($clean_parts, 0, 2));
        $people_cache[strtolower($clean_name)] = $p->ID;
    }
}

// Make sure we have the specific IDs we need
$target_speakers = [
    'Dr. Theresa Williams' => 'people',
    'Judd Marshall' => 'people',
    'Klip Weaver' => 'employees', // slug klip-weaver
    'Steve Schliesing' => 'employees', // slug steve-schliesing
    'Dr. James Largent' => 'people',
    'Jerry Pickett' => 'people',
    'Paul Buckner' => 'people',
    'Andrew Peters' => 'people',
    'Sonny Fletcher' => 'people',
    'Josh Combs' => 'employees', // slug josh-combs-pe-cem
    'Jeff Freeman' => 'employees', // slug jeff-freeman
    'Dan Meyer' => 'employees', // slug dan-meyer
    'Lance Wyatt' => 'employees', // slug lance-wyatt
    'Adam Anders' => 'employees', // slug adam-anders
    'Jason Brinkley' => 'employees', // slug jason-brinkley
    'Award Winner' => 'people',
    'James Hartman' => 'people',
    'Mike Craft' => 'people',
    'Effie Morris' => 'people',
    'Tim Evans' => 'people',
    'Marcus Crispin' => 'employees' // slug marcus-crispin
];

$speaker_ids = [];
foreach ($target_speakers as $name => $type) {
    $key = strtolower($name);
    if (isset($people_cache[$key])) {
        $speaker_ids[$name] = $people_cache[$key];
    } else {
        // If missing, try to find by title in WP
        $found = get_posts([
            'post_type' => ['people', 'employees'],
            'title' => $name,
            'numberposts' => 1,
            'post_status' => 'any'
        ]);
        if (!empty($found)) {
            $speaker_ids[$name] = $found[0]->ID;
        } else {
            // Create a people post if not found
            $new_id = wp_insert_post([
                'post_type' => 'people',
                'post_title' => $name,
                'post_status' => 'publish'
            ]);
            $speaker_ids[$name] = $new_id;
            echo "🆕 Created missing speaker profile: {$name} (ID: {$new_id})\n";
        }
    }
}

// 2. Query all quotes
$quotes = get_posts([
    'post_type' => 'quotes',
    'posts_per_page' => -1,
    'post_status' => 'any'
]);

echo "Processing " . count($quotes) . " quotes...\n";

$relinked_count = 0;
$garbage_people_to_delete = [];

foreach ($quotes as $q) {
    $video = get_post_meta($q->ID, '_e3_quote_video_title', true);
    
    // Parse current speaker name from title
    $parts = explode(' on "', $q->post_title, 2);
    $current_speaker = trim($parts[0]);
    
    $new_speaker = null;
    
    // Video-specific correction rules
    switch ($video) {
        case 'Lessons in Learning - Dr. Theresa Williams, Superintendent, Plano ISD':
            $new_speaker = 'Dr. Theresa Williams';
            break;
            
        case 'Lessons In Learning - Judd Marshall, Superintendent,  Mount Pleasant ISD':
            $new_speaker = 'Judd Marshall';
            break;
            
        case 'How to Get the Most Out of Vendor Relationships':
            $new_speaker = 'Klip Weaver';
            break;
            
        case 'E3 in 3D - Steve Schliesing':
            $new_speaker = 'Steve Schliesing';
            break;
            
        case 'Granbury ISD, case study':
            if ($current_speaker === 'Johnny Perkins Field') {
                $new_speaker = 'Dr. James Largent';
            }
            break;
            
        case 'Goodall-Witcher Healthcare, case study':
            if (in_array($current_speaker, ['General Und Posted', 'Dasta Type'])) {
                $new_speaker = 'Jerry Pickett';
            }
            break;
            
        case 'Bryan ISD SECO LoanSTAR Project, case study':
            if ($current_speaker === 'BAS Alarm') {
                $new_speaker = 'Paul Buckner';
            }
            break;
            
        case 'Caldwell ISD Bond Video':
            $new_speaker = 'Andrew Peters';
            break;
            
        case 'Caldwell ISD, case study':
            if ($current_speaker === 'Weather Expert') {
                $new_speaker = 'Andrew Peters';
            }
            break;
            
        case 'E3 TASB SLI 2020 Presentation':
            $new_speaker = 'Sonny Fletcher';
            break;
            
        case 'R454b Discussion':
            if (in_array($current_speaker, ['Josh Cambs', 'Josh Combs'])) {
                $new_speaker = 'Josh Combs';
            } elseif (in_array($current_speaker, ['Jeff Freeran', 'Jeft Freeran', 'Jefl Freeman', 'Jeif Freernan'])) {
                $new_speaker = 'Jeff Freeman';
            } elseif (in_array($current_speaker, ['Dansl Mever', 'Danwl Mever'])) {
                $new_speaker = 'Dan Meyer';
            }
            break;
            
        case 'E3 at ARCIT - project delivery.mp4':
            $new_speaker = 'Lance Wyatt';
            break;
            
        case 'E3 at ARCIT - funding':
            $new_speaker = 'Jason Brinkley';
            break;
            
        case 'E3 at ARCIT - who we are':
            $new_speaker = 'Adam Anders';
            break;
            
        case 'E3 is a small business...or not!':
            $new_speaker = 'Klip Weaver';
            break;
            
        case 'DaVinci Award.mp4':
            $new_speaker = 'Award Winner';
            break;
            
        case 'E3 Lighting Video':
            if (in_array($current_speaker, ['Hawkins Iso', 'Were Haddens'])) {
                $new_speaker = 'James Hartman';
            } elseif ($current_speaker === 'Cooke Coul') {
                $new_speaker = 'Mike Craft';
            }
            break;
            
        case 'Lake Worth, case study':
            if ($current_speaker === 'Lake Worth') {
                $new_speaker = 'Effie Morris';
            }
            break;
            
        case 'Lead Incentive Program Announcement':
            $new_speaker = 'Tim Evans';
            break;
            
        case 'Highland Park ISD - Central plant upgrades':
            if ($current_speaker === 'Central Plant Upgrad') {
                $new_speaker = 'Marcus Crispin';
            }
            break;
    }
    
    if ($new_speaker !== null) {
        $target_id = $speaker_ids[$new_speaker];
        $current_linked_id = intval(get_post_meta($q->ID, '_e3_quote_person_id', true));
        
        // Save the old linked ID so we can potentially delete it if it's garbage
        if ($current_linked_id && $current_linked_id !== $target_id) {
            $garbage_people_to_delete[$current_linked_id] = true;
        }
        
        // Update meta
        update_post_meta($q->ID, '_e3_quote_person_id', intval($target_id));
        
        // Also update post title to reflect the correct speaker name
        $title_parts = explode(' on "', $q->post_title, 2);
        if (count($title_parts) === 2) {
            $new_title = $new_speaker . ' on "' . $title_parts[1];
            if ($q->post_title !== $new_title) {
                wp_update_post([
                    'ID' => $q->ID,
                    'post_title' => $new_title
                ]);
            }
        }
        
        $relinked_count++;
    }
}

echo "Relinked and updated {$relinked_count} quotes.\n";

// Delete garbage people posts
$deleted_count = 0;
foreach (array_keys($garbage_people_to_delete) as $p_id) {
    $post = get_post($p_id);
    if ($post && $post->post_type === 'people') {
        // Double check no other quotes use this person ID
        $others = get_posts([
            'post_type' => 'quotes',
            'posts_per_page' => 1,
            'post_status' => 'any',
            'meta_query' => [
                [
                    'key' => '_e3_quote_person_id',
                    'value' => $p_id,
                    'compare' => '='
                ]
            ]
        ]);
        
        if (empty($others)) {
            echo "🗑️ Deleting garbage CPT people profile: '{$post->post_title}' (ID: {$p_id})\n";
            wp_delete_post($p_id, true);
            $deleted_count++;
        }
    }
}

echo "Deleted {$deleted_count} garbage people profiles.\n";
echo "Sync & Clean up complete!\n";
