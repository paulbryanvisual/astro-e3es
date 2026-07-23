<?php
define('WP_USE_THEMES', false);
require_once('/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php');

$user_id = 1; // Default Admin
$user = get_user_by('id', $user_id);

if ($user) {
    // Generate the authentication cookie
    $expiration = time() + 3600; // 1 hour
    
    // Set cookie headers in PHP global so we can grab them
    wp_set_auth_cookie($user_id, true);
    
    $cookies = [];
    foreach (headers_list() as $header) {
        if (stripos($header, 'Set-Cookie:') === 0) {
            $cookie_str = substr($header, 12);
            // Parse cookie string
            $parts = explode(';', $cookie_str);
            $name_val = explode('=', trim($parts[0]), 2);
            if (count($name_val) === 2) {
                $cookies[] = [
                    'name' => $name_val[0],
                    'value' => urldecode($name_val[1]),
                    'domain' => 'e3es2026.local',
                    'path' => '/'
                ];
            }
        }
    }
    
    echo json_encode($cookies, JSON_PRETTY_PRINT);
} else {
    echo json_encode(['error' => 'User not found']);
}
