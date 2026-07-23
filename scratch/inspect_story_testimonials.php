<?php
require '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';

foreach ([11070, 3803, 3804, 3805, 3806] as $id) {
    $p = get_post($id);
    if ($p) {
        echo "=== Testimonial ID: {$id} (Type: {$p->post_type}) ===\n";
        echo "Title: " . $p->post_title . "\n";
        echo "Meta _e3_quote_quote: " . get_post_meta($id, '_e3_quote_quote', true) . "\n";
        echo "Meta _e3_quote_person_id: " . get_post_meta($id, '_e3_quote_person_id', true) . "\n";
        echo "Content: " . $p->post_content . "\n\n";
    } else {
        echo "=== Testimonial ID: {$id} NOT FOUND ===\n\n";
    }
}
