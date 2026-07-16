<?php
/**
 * Sync Cleaned Quotes to WordPress CPT 'quotes'
 *
 * This script reads cleaned_merged_quotes.json, updates the primary quote posts with
 * the cleaned copywritten text, updates meta keys, and drafts the remaining merged quotes
 * to avoid duplicate or partial segments.
 *
 * Run with:
 *   php sync_cleaned_quotes.php
 * or for dry-run vs commit:
 *   php sync_cleaned_quotes.php --commit
 */

require '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';

$json_path = '/Users/bryanpaul/Local Sites/astro-e3es/scratch/cleaned_merged_quotes.json';

if (!file_exists($json_path)) {
    die("Error: cleaned_merged_quotes.json not found at $json_path\n");
}

$data = json_decode(file_get_contents($json_path), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    die("Error parsing JSON: " . json_last_error_msg() . "\n");
}

$commit = in_array('--commit', $argv);

echo "==================================================\n";
echo "       SYNC CLEANED QUOTES TO WORDPRESS          \n";
echo "==================================================\n";
echo "Mode: " . ($commit ? "COMMIT (Writing to Database)" : "DRY-RUN (No Database Changes)") . "\n";
echo "Total groups to process: " . count($data) . "\n\n";

$updated_count = 0;
$drafted_count = 0;
$skipped_count = 0;
$errors = [];

foreach ($data as $index => $group) {
    $video = $group['video'];
    $person_id = (int) $group['person_id'];
    $person_name = $group['person_name'];
    $quote_ids = $group['quote_ids'];
    $cleaned_text = $group['cleaned_text'];
    $is_key = !empty($group['is_key']);
    
    if (empty($quote_ids)) {
        echo "⚠️ Group " . ($index + 1) . " (Speaker: {$person_name}, Video: {$video}) has no quote_ids. Skipping.\n";
        $skipped_count++;
        continue;
    }
    
    // The first ID is the primary quote post ID
    $primary_id = (int) $quote_ids[0];
    $secondary_ids = array_slice($quote_ids, 1);
    
    // Check if primary post exists
    $primary_post = get_post($primary_id);
    if (!$primary_post || $primary_post->post_type !== 'quotes') {
        echo "❌ Primary Quote ID {$primary_id} not found or not a 'quotes' post type. Skipping group.\n";
        $errors[] = "Group " . ($index + 1) . ": Primary ID {$primary_id} invalid.";
        $skipped_count++;
        continue;
    }
    
    $proposed_title = $person_name . ' on "' . $video . '"';
    
    echo "📝 Group " . ($index + 1) . ": Speaker: {$person_name} | Video: {$video}\n";
    echo "   Primary Post ID: {$primary_id}\n";
    echo "   Current Title: '{$primary_post->post_title}'\n";
    echo "   Proposed Title: '{$proposed_title}'\n";
    echo "   Cleaned Quote Text: '" . substr($cleaned_text, 0, 80) . "...'\n";
    
    if ($commit) {
        // Update primary post
        $post_data = [
            'ID'           => $primary_id,
            'post_content' => $cleaned_text,
            'post_title'   => $proposed_title,
        ];
        
        $result = wp_update_post($post_data, true);
        if (is_wp_error($result)) {
            echo "   ❌ Failed to update primary post: " . $result->get_error_message() . "\n";
            $errors[] = "Primary ID {$primary_id}: " . $result->get_error_message();
            continue;
        }
        
        // Update meta fields
        update_post_meta($primary_id, '_e3_quote_quote', $cleaned_text);
        update_post_meta($primary_id, '_e3_quote_person_id', $person_id);
        update_post_meta($primary_id, '_e3_quote_video_title', $video);
        update_post_meta($primary_id, '_e3_quote_is_key', $is_key ? 1 : 0);
        
        echo "   ✅ Updated primary quote post and metadata.\n";
        $updated_count++;
    } else {
        $updated_count++;
    }
    
    // Process secondary IDs to draft them
    if (!empty($secondary_ids)) {
        echo "   Secondary IDs to Draft: " . implode(', ', $secondary_ids) . "\n";
        foreach ($secondary_ids as $sec_id) {
            $sec_post = get_post($sec_id);
            if (!$sec_post) {
                echo "      ⚠️ Secondary ID {$sec_id} not found.\n";
                continue;
            }
            
            if ($commit) {
                // Draft secondary post
                $sec_data = [
                    'ID'          => $sec_id,
                    'post_status' => 'draft'
                ];
                wp_update_post($sec_data);
                
                // Track merge relationship in metadata
                update_post_meta($sec_id, '_e3_quote_merged_into', $primary_id);
                echo "      ✅ Drafted ID {$sec_id}.\n";
                $drafted_count++;
            } else {
                $drafted_count++;
            }
        }
    }
    echo "\n";
}

echo "==================================================\n";
echo "               SYNC SUMMARY                       \n";
echo "==================================================\n";
echo "Primary Quotes Updated: {$updated_count}\n";
echo "Secondary Quotes Drafted: {$drafted_count}\n";
echo "Groups Skipped: {$skipped_count}\n";
if (!empty($errors)) {
    echo "Errors encountered: " . count($errors) . "\n";
    foreach ($errors as $err) {
        echo " - {$err}\n";
    }
}
echo "==================================================\n";
