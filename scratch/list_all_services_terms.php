<?php
require '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';

$terms = get_terms(array(
    'taxonomy' => 'client-services',
    'hide_empty' => false,
));
foreach ($terms as $t) {
    echo "Slug: {$t->slug} | Name: {$t->name}\n";
}
