<?php

require_once __DIR__ . '/config.php';

// Step 1: redirect user to Google OAuth consent screen

if (!GOOGLE_CLIENT_ID || !GOOGLE_REDIRECT_URI || GOOGLE_CLIENT_ID === 'your-google-client-id') {
    die('Google OAuth is not configured. Please set GOOGLE_CLIENT_ID and GOOGLE_REDIRECT_URI in config.php<br><br>
         <strong>Setup Instructions:</strong><br>
         1. Go to <a href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a><br>
         2. Create a new project or select an existing one<br>
         3. Enable "Google+ API" or "Google Identity Services"<br>
         4. Go to "Credentials" → "Create Credentials" → "OAuth 2.0 Client ID"<br>
         5. Set Application type to "Web application"<br>
         6. Add authorized redirect URI: ' . htmlspecialchars(GOOGLE_REDIRECT_URI, ENT_QUOTES, 'UTF-8') . '<br>
         7. Copy the Client ID and Client Secret to config.php');
}

$params = [
    'client_id'     => GOOGLE_CLIENT_ID,
    'redirect_uri'  => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope'         => 'openid email profile',
    'access_type'   => 'offline',
    'prompt'        => 'consent',
];

$url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);

header('Location: ' . $url);
exit;


