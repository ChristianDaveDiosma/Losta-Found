<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/oauth_helpers.php';

if (!FACEBOOK_CLIENT_ID || !FACEBOOK_CLIENT_SECRET || !FACEBOOK_REDIRECT_URI) {
    die('Facebook OAuth is not configured. Please set FACEBOOK_CLIENT_ID/SECRET/REDIRECT_URI in config.php');
}

if (!isset($_GET['code'])) {
    header('Location: login.php');
    exit;
}

$code = $_GET['code'];

// Step 2: exchange code for access token
$tokenUrl = 'https://graph.facebook.com/v18.0/oauth/access_token';
$params = [
    'client_id'     => FACEBOOK_CLIENT_ID,
    'redirect_uri'  => FACEBOOK_REDIRECT_URI,
    'client_secret' => FACEBOOK_CLIENT_SECRET,
    'code'          => $code,
];

$ch = curl_init($tokenUrl . '?' . http_build_query($params));
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
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
$fields = 'id,name,email';
$ch = curl_init('https://graph.facebook.com/me?fields=' . urlencode($fields));
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $accessToken],
]);
$userInfoResponse = curl_exec($ch);
curl_close($ch);

$profile = json_decode($userInfoResponse, true);

if (!isset($profile['id'])) {
    header('Location: login.php');
    exit;
}

$email = $profile['email'] ?? ($profile['id'] . '@facebook.local'); // fallback if email not provided

$oauthUser = find_or_create_oauth_user(
    'facebook',
    $profile['id'],
    $profile['name'] ?? $email,
    $email
);

login_user($oauthUser);

if (in_array($oauthUser['role'], ['admin', 'moderator'], true)) {
    header('Location: admin_dashboard.php');
} else {
    header('Location: user_dashboard.php');
}
exit;


