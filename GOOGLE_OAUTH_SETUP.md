# Google OAuth Setup Guide

## Step-by-Step Instructions

### 1. Go to Google Cloud Console
Visit: https://console.cloud.google.com/

### 2. Create or Select a Project
- Click on the project dropdown at the top
- Click "New Project" or select an existing one
- Give it a name (e.g., "Lost & Found OAuth")

### 3. Enable Google Identity Services
- Go to "APIs & Services" → "Library"
- Search for "Google Identity Services API" or "Google+ API"
- Click "Enable"

### 4. Create OAuth 2.0 Credentials
- Go to "APIs & Services" → "Credentials"
- Click "Create Credentials" → "OAuth 2.0 Client ID"
- If prompted, configure the OAuth consent screen first:
  - Choose "External" (unless you have a Google Workspace)
  - Fill in App name, User support email, Developer contact
  - Add scopes: `email`, `profile`, `openid`
  - Add test users (your email) if in testing mode
  - Save and continue

### 5. Configure OAuth Client
- Application type: **Web application**
- Name: "Lost & Found" (or any name you prefer)
- Authorized JavaScript origins: 
  - `http://localhost` (for local development)
- Authorized redirect URIs: 
  - `http://localhost/lostlink/oauth_google_callback.php`
  - (Add your production URL when deploying)

### 6. Get Your Credentials
- After creating, you'll see:
  - **Client ID** (looks like: `123456789-abcdefg.apps.googleusercontent.com`)
  - **Client Secret** (looks like: `GOCSPX-abcdefghijklmnop`)

### 7. Update config.php
Open `config.php` and replace:
```php
define('GOOGLE_CLIENT_ID', 'your-google-client-id');
define('GOOGLE_CLIENT_SECRET', 'your-google-client-secret');
```

With your actual credentials:
```php
define('GOOGLE_CLIENT_ID', '123456789-abcdefg.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', 'GOCSPX-abcdefghijklmnop');
```

### 8. Test
- Click "Continue with Google" on your login page
- You should be redirected to Google's consent screen
- After authorizing, you'll be logged in

## Troubleshooting

**Error: "The OAuth client was not found"**
- Make sure the Client ID in config.php matches exactly what's in Google Cloud Console
- Check that there are no extra spaces or quotes

**Error: "Redirect URI mismatch"**
- Make sure the redirect URI in Google Cloud Console exactly matches: `http://localhost/lostlink/oauth_google_callback.php`
- Check for trailing slashes or http vs https

**Error: "Access blocked: This app's request is invalid"**
- Make sure you've configured the OAuth consent screen
- Add your email as a test user if the app is in testing mode

## Important Notes
- Keep your Client Secret secure and never commit it to public repositories
- For production, use environment variables instead of hardcoding in config.php
- Update the redirect URI for your production domain when deploying

