<?php

// Database configuration - replace with your own credentials
define('DB_HOST', '127.0.0.1');   // Use IP to avoid IPv6 socket issues
define('DB_PORT', 3306);          // Change if XAMPP/MySQL runs on another port
define('DB_NAME', 'lost_found');
define('DB_USER', 'root');
define('DB_PASS', '');

// OAuth: pull from environment if available, otherwise set the fallback values below.
// Replace the placeholders with your live credentials from the provider console.
$oauthBase = 'http://localhost/lostlink';

define('GOOGLE_CLIENT_ID', getenv('GOOGLE_CLIENT_ID') ?: 'your-google-client-id');
define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET') ?: 'your-google-client-secret');
define('GOOGLE_REDIRECT_URI', getenv('GOOGLE_REDIRECT_URI') ?: $oauthBase . '/oauth_google_callback.php');

define('FACEBOOK_CLIENT_ID', getenv('FACEBOOK_CLIENT_ID') ?: 'your-facebook-client-id');
define('FACEBOOK_CLIENT_SECRET', getenv('FACEBOOK_CLIENT_SECRET') ?: 'your-facebook-client-secret');
define('FACEBOOK_REDIRECT_URI', getenv('FACEBOOK_REDIRECT_URI') ?: $oauthBase . '/oauth_facebook_callback.php');


