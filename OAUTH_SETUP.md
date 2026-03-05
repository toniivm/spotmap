# 🔐 SpotMap - OAuth Setup & Configuration Guide (2026)

## Overview
This guide provides step-by-step instructions to configure OAuth2 social login for Google, Facebook, Twitter/X, and Instagram.

## ✅ Prerequisites
- SpotMap instance running locally or on server
- HTTPS enabled (required for OAuth)
- Backend and frontend files updated

---

## 1️⃣ Google OAuth Setup

### Step 1: Create Google Cloud Project
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create new project: "spotmap-oauth"
3. Navigate to **APIs & Services > Credentials**
4. Click **Create Credentials > OAuth 2.0 Client ID**
5. Select **Web application**

### Step 2: Configure Redirect URIs
```
Authorized JavaScript origins:
- http://localhost
- http://localhost:8080
- https://spotmap.example.com

Authorized redirect URIs:
- http://localhost/backend/public/api.php?action=oauth_callback&provider=google
- https://spotmap.example.com/backend/public/api.php?action=oauth_callback&provider=google
```

### Step 3: Add to .env
```env
GOOGLE_CLIENT_ID=YOUR_CLIENT_ID.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=YOUR_CLIENT_SECRET
```

---

## 2️⃣ Facebook OAuth Setup

### Step 1: Create Facebook App
1. Go to [Facebook Developers](https://developers.facebook.com/)
2. Click **My Apps > Create App**
3. Choose **Consumer** type
4. Fill in details and create

### Step 2: Configure OAuth
1. In app settings, add **Facebook Login** product
2. Go to **Settings > Basic** and note App ID & App Secret
3. Add **Valid OAuth Redirect URIs**:
```
http://localhost/backend/public/api.php?action=oauth_callback&provider=facebook
https://spotmap.example.com/backend/public/api.php?action=oauth_callback&provider=facebook
```

### Step 3: Add to .env
```env
FACEBOOK_APP_ID=YOUR_APP_ID
FACEBOOK_CLIENT_SECRET=YOUR_APP_SECRET
```

---

## 3️⃣ Twitter/X OAuth 2.0 Setup

### Step 1: Create Twitter App
1. Go to [Twitter Developer Portal](https://developer.twitter.com/)
2. Go to **Projects > Create Project**
3. In **Project Settings**, set **OAuth 2.0 settings**
4. Enable **Authorization Code with PKCE**

### Step 2: Configure Callback URLs
```
Callback URL / Redirect URL:
- http://localhost/backend/public/api.php?action=oauth_callback&provider=twitter
- https://spotmap.example.com/backend/public/api.php?action=oauth_callback&provider=twitter
```

### Step 3: Configure Scopes
Required scopes:
- `tweet.read`
- `users.read`
- `follows.read`
- `follows.write`

### Step 4: Add to .env
```env
TWITTER_CLIENT_ID=YOUR_CLIENT_ID
TWITTER_CLIENT_SECRET=YOUR_CLIENT_SECRET
```

---

## 4️⃣ Instagram OAuth Setup

### Step 1: Create Meta App
1. Go to [Meta Developers](https://developers.facebook.com/)
2. Create new app > Consumer type
3. Add **Instagram Graph API** product

### Step 2: Configure Instagram Settings
1. In **Instagram Basic Display**, fill:
   - Valid OAuth Redirect URIs
   - Deauthorize Callback URL

### Step 3: Redirect URIs
```
http://localhost/backend/public/api.php?action=oauth_callback&provider=instagram
https://spotmap.example.com/backend/public/api.php?action=oauth_callback&provider=instagram
```

### Step 4: Add to .env
```env
INSTAGRAM_APP_ID=YOUR_APP_ID
INSTAGRAM_APP_SECRET=YOUR_APP_SECRET
```

---

## 🔧 Testing OAuth Locally

### Option A: Using ngrok (Recommended)
```bash
# Install ngrok
choco install ngrok

# Start ngrok
ngrok http 80

# Use ngrok URL as redirect (e.g., https://abc123.ngrok.io)
```

### Option B: Local HTTPS
```bash
# Create self-signed certificate
openssl req -x509 -newkey rsa:4096 -keyout key.pem -out cert.pem -days 365 -nodes

# Configure Apache to use certificate
```

---

## 📋 .env Template

```env
# Google
GOOGLE_CLIENT_ID=xxx.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=xxx

# Facebook
FACEBOOK_APP_ID=xxx
FACEBOOK_CLIENT_SECRET=xxx

# Twitter/X
TWITTER_CLIENT_ID=xxx
TWITTER_CLIENT_SECRET=xxx

# Instagram
INSTAGRAM_APP_ID=xxx
INSTAGRAM_APP_SECRET=xxx
```

---

## 🧪 Test Endpoints

### Initiate Google Login
```bash
curl "http://localhost/backend/public/api.php?action=oauth_init&provider=google"
```

### Simulate Callback (Testing)
```bash
curl -X POST "http://localhost/backend/public/api.php?action=oauth_callback" \
  -H "Content-Type: application/json" \
  -d '{"provider":"google","code":"AUTH_CODE"}'
```

---

## 🛡️ Security Best Practices

### DO ✅
- Keep secrets in `.env`, never commit to git
- Use HTTPS in production
- Validate state parameter in OAuth flow
- Rotate secrets quarterly
- Use different credentials per environment
- Log OAuth events for audit trail
- Rate limit OAuth endpoints

### DON'T ❌
- Hardcode secrets in code
- Use redirect_uri in production
- Store tokens in localStorage (use httpOnly cookies)
- Skip CSRF protection
- Log sensitive OAuth data
- Share credentials between apps

---

## 🚨 Troubleshooting

### "Invalid redirect URI"
- Ensure redirect URL matches exactly (case-sensitive)
- Include `/backend/public/api.php` path
- Protocol must match (http vs https)

### "Invalid client secret"
- Double-check .env values
- Copy from provider dashboard carefully
- Check for extra spaces

### "CORS error"
- Add frontend origin to CORS whitelist in Security.php
- Ensure CORS headers are sent correctly

### Tokens expiring quickly
- Check token expiration settings
- Implement refresh token flow
- Validate token validity before use

---

## 📚 References

- [Google OAuth Documentation](https://developers.google.com/identity/protocols/oauth2)
- [Facebook Login Documentation](https://developers.facebook.com/docs/facebook-login)
- [Twitter OAuth 2.0](https://developer.twitter.com/en/docs/authentication/oauth-2-0)
- [Instagram Graph API](https://developers.facebook.com/docs/instagram-api)
- [RFC 6749 - OAuth 2.0 Authorization Framework](https://tools.ietf.org/html/rfc6749)

---

## 📞 Support

For issues, check:
1. Backend logs: `backend/logs/`
2. Browser console: F12 > Console
3. Network tab: F12 > Network
4. API responses for error codes

