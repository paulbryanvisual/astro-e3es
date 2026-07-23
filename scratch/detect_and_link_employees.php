<?php
require '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';

// Disable KSES for safety
wp_set_current_user(1);
if (function_exists('kses_remove_filters')) {
    kses_remove_filters();
}

$people = get_posts([
    'post_type' => 'people',
    'posts_per_page' => -1,
    'post_status' => 'any'
]);

$employees = get_posts([
    'post_type' => 'employees',
    'posts_per_page' => -1,
    'post_status' => 'any'
]);

function get_clean_words($str) {
    $str = str_replace('-', ' ', $str);
    $str = strtolower($str);
    $words = explode(' ', $str);
    $clean = [];
    foreach ($words as $w) {
        $w = trim($w);
        if (empty($w) || in_array($w, ['pe', 'cem', 'leed', 'ap', 'pmp', 'eit', 'shrm', 'cp'])) {
            continue;
        }
        $clean[] = $w;
    }
    return $clean;
}

// Find matches
$matches = [];
foreach ($people as $p) {
    $p_name = trim($p->post_title);
    $p_words = get_clean_words($p_name);
    if (empty($p_words)) continue;
    
    foreach ($employees as $e) {
        $e_slug = $e->post_name;
        $e_words = get_clean_words($e_slug);
        
        // Exact match of first word, and fuzzy match of second word
        if (count($p_words) >= 1 && count($e_words) >= 1 && $p_words[0] === $e_words[0]) {
            if (count($p_words) >= 2 && count($e_words) >= 2) {
                $dist = levenshtein($p_words[1], $e_words[1]);
                if ($dist <= 2) {
                    $matches[] = [
                        'people_post' => $p,
                        'employee_post' => $e,
                        'reason' => "Fuzzy match on last name: '{$p_words[1]}' vs '{$e_words[1]}'"
                    ];
                    break;
                }
            } elseif (count($p_words) === 1 && count($e_words) === 1) {
                $matches[] = [
                    'people_post' => $p,
                    'employee_post' => $e,
                    'reason' => "Exact single name match"
                ];
                break;
            }
        }
    }
}

echo "Found " . count($matches) . " duplicate people posts that match employees CPT:\n";
foreach ($matches as $m) {
    $p = $m['people_post'];
    $e = $m['employee_post'];
    echo "- '{$p->post_title}' (ID: {$p->ID}) matches Employee '{$e->post_title}' (ID: {$e->ID}, Slug: {$e->post_name}) [{$m['reason']}]\n";
    
    // Find quotes pointing to the duplicate people ID
    $quotes = get_posts([
        'post_type' => 'quotes',
        'posts_per_page' => -1,
        'post_status' => 'any',
        'meta_query' => [
            [
                'key' => '_e3_quote_person_id',
                'value' => $p->ID,
                'compare' => '='
            ]
        ]
    ]);
    
    if (!empty($quotes)) {
        echo "  * Relinking " . count($quotes) . " quotes to Employee ID {$e->ID}...\n";
        foreach ($quotes as $q) {
            update_post_meta($q->ID, '_e3_quote_person_id', intval($e->ID));
        }
    }
    
    // Delete the duplicate people post
    echo "  * Deleting duplicate CPT people post ID {$p->ID}...\n";
    wp_delete_post($p->ID, true);
}

echo "\nDone with cleanup and sync!\n";
