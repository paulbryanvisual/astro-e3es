<?php
require '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';

foreach ([23, 177] as $id) {
    $p = get_post($id);
    if ($p) {
        echo "=== Story Page ID: {$id} ===\n";
        echo $p->post_content . "\n\n";
    }
}
