<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/oauth_helpers.php';

if (!GOOGLE_CLIENT_ID || !GOOGLE_CLIENT_SECRET || !GOOGLE_REDIRECT_URI) {
    die('Google OAuth is not configured. Please set GOOGLE_CLIENT_ID/SECRET/REDIRECT_URI in config.php');
}

if (!isset($_GET['code'])) {
    header('Location: login.php');
    exit;
}

$code = $_GET['code'];

// Step 2: exchange code for access token
$tokenUrl = 'https://oauth2.googleapis.com/token';
$postData = [
    'code'          => $code,
    'client_id'     => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri'  => GOOGLE_REDIRECT_URI,
    'grant_type'    => 'authorization_code',
];

$ch = curl_init($tokenUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query($postData),
    CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
]);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if (!isset($data['access_token'])) {
    header('Location: login.php');
    exit;
}

$accessToken = $data['access_token'];

// Step 3: fetch user info
$ch = curl_init('https://www.googleapis.com/oauth2/v3/userinfo');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $accessToken],
]);
$userInfoResponse = curl_exec($ch);
curl_close($ch);

$profile = json_decode($userInfoResponse, true);

if (!isset($profile['sub'], $profile['email'])) {
    header('Location: login.php');
    exit;
}

$oauthUser = find_or_create_oauth_user(
    'google',
    $profile['sub'],
    $profile['name'] ?? $profile['email'],
    $profile['email']
);

login_user($oauthUser);

if (in_array($oauthUser['role'], ['admin', 'moderator'], true)) {
    header('Location: admin_dashboard.php');
} else {
    header('Location: user_dashboard.php');
}
exit;


