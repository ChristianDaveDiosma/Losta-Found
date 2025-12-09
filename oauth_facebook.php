<?php

require_once __DIR__ . '/config.php';

// Step 1: redirect user to Facebook OAuth dialog

if (!FACEBOOK_CLIENT_ID || !FACEBOOK_REDIRECT_URI) {
    die('Facebook OAuth is not configured. Please set FACEBOOK_CLIENT_ID and FACEBOOK_REDIRECT_URI in config.php');
}

$params = [
    'client_id'     => FACEBOOK_CLIENT_ID,
    'redirect_uri'  => FACEBOOK_REDIRECT_URI,
    'response_type' => 'code',
    'scope'         => 'email,public_profile',
];

$url = 'https://www.facebook.com/v18.0/dialog/oauth?' . http_build_query($params);

header('Location: ' . $url);
exit;


