<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | Configuration for third-party service integrations used in EasyMarket.
    | This includes email services, cloud storage, payment gateways, and
    | OAuth social login providers.
    |
    | ⚠️ Security: Never commit credentials to version control!
    | Always use .env file for sensitive API keys and secrets.
    |
    | 💡 For EasyMarket marketplace:
    | - Email services: Transactional emails (orders, invoices, notifications)
    | - OAuth providers: Social login for buyers and sellers
    | - Cloud services: File storage, backups, analytics
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Mailgun Email Service
    |--------------------------------------------------------------------------
    |
    | Mailgun: Popular transactional email service with great deliverability.
    |
    | Use case for EasyMarket:
    | - Order confirmations
    | - Invoice notifications
    | - Password resets
    | - Product purchase receipts
    |
    | Setup:
    | 1. Sign up at mailgun.com
    | 2. Verify your domain
    | 3. Get API credentials from dashboard
    | 4. Add to .env: MAILGUN_DOMAIN, MAILGUN_SECRET
    |
    | Endpoint options:
    | - api.mailgun.net (US)
    | - api.eu.mailgun.net (EU - GDPR compliant)
    |
    | Pricing: Free tier includes 5,000 emails/month
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Postmark Email Service
    |--------------------------------------------------------------------------
    |
    | Postmark: Premium transactional email service with excellent delivery rates.
    |
    | Use case for EasyMarket:
    | - Time-sensitive transactional emails
    | - Better deliverability than SMTP
    | - Detailed analytics and tracking
    |
    | Setup:
    | 1. Sign up at postmarkapp.com
    | 2. Create a server
    | 3. Get server API token
    | 4. Add to .env: POSTMARK_TOKEN
    |
    | Pricing: Pay-as-you-go, $1.25 per 1,000 emails
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Amazon SES (Simple Email Service)
    |--------------------------------------------------------------------------
    |
    | AWS SES: Cost-effective email service for high-volume sending.
    |
    | Use case for EasyMarket:
    | - High-volume email campaigns
    | - Newsletter to buyers/sellers
    | - Most cost-effective for large marketplaces
    |
    | Setup:
    | 1. Create AWS account
    | 2. Request production access (starts in sandbox)
    | 3. Verify domain/email addresses
    | 4. Create IAM user with SES permissions
    | 5. Add to .env: AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, AWS_DEFAULT_REGION
    |
    | Regions:
    | - us-east-1 (N. Virginia) - Default, lowest latency for US
    | - us-west-2 (Oregon)
    | - eu-west-1 (Ireland) - For European customers
    |
    | Pricing: $0.10 per 1,000 emails (extremely cost-effective)
    |
    */

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Facebook OAuth / Social Login
    |--------------------------------------------------------------------------
    |
    | Facebook Login: Allow buyers/sellers to register using Facebook account.
    |
    | Benefits for EasyMarket:
    | - Faster registration (reduce friction)
    | - Higher conversion rates
    | - Verified email addresses
    | - Access to profile data (with permission)
    |
    | Setup:
    | 1. Create app at developers.facebook.com
    | 2. Add Facebook Login product
    | 3. Configure OAuth redirect URLs
    | 4. Get App ID and App Secret
    | 5. Add to .env: FACEBOOK_CLIENT_ID, FACEBOOK_CLIENT_SECRET
    |
    | ⚠️ Important:
    | - Request only necessary permissions (email, public_profile)
    | - Handle privacy policy requirements
    | - Test with Facebook's App Review before going live
    |
    | Callback URL: {APP_URL}/oauth/facebook/callback
    |
    */

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => rtrim(env('APP_URL'), '/') . '/oauth/facebook/callback',
    ],

    /*
    |--------------------------------------------------------------------------
    | Google OAuth / Social Login
    |--------------------------------------------------------------------------
    |
    | Google Sign-In: Most popular social login option worldwide.
    |
    | Benefits for EasyMarket:
    | - Highest trust factor among users
    | - Works on all devices (mobile, desktop)
    | - Verified Gmail addresses
    | - Fast and reliable authentication
    |
    | Setup:
    | 1. Create project at console.cloud.google.com
    | 2. Enable Google+ API
    | 3. Create OAuth 2.0 credentials
    | 4. Add authorized redirect URIs
    | 5. Add to .env: GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET
    |
    | Scopes needed:
    | - email (required)
    | - profile (basic info)
    |
    | Callback URL: {APP_URL}/oauth/google/callback
    |
    */

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => rtrim(env('APP_URL'), '/') . '/oauth/google/callback',
    ],

    /*
    |--------------------------------------------------------------------------
    | Microsoft OAuth / Social Login
    |--------------------------------------------------------------------------
    |
    | Microsoft Account Login: Good for B2B marketplaces and enterprise users.
    |
    | Benefits for EasyMarket:
    | - Corporate buyers often use Microsoft accounts
    | - Azure Active Directory integration
    | - Professional seller accounts
    |
    | Setup:
    | 1. Register app at portal.azure.com
    | 2. Go to App Registrations
    | 3. Create new registration
    | 4. Add redirect URI
    | 5. Create client secret in Certificates & Secrets
    | 6. Add to .env: MICROSOFT_CLIENT_ID, MICROSOFT_CLIENT_SECRET
    |
    | Use case: Good for digital software/SaaS marketplaces
    |
    | Callback URL: {APP_URL}/oauth/microsoft/callback
    |
    */

    'microsoft' => [
        'client_id' => env('MICROSOFT_CLIENT_ID'),
        'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
        'redirect' => rtrim(env('APP_URL'), '/') . '/oauth/microsoft/callback',
    ],

    /*
    |--------------------------------------------------------------------------
    | VKontakte OAuth / Social Login
    |--------------------------------------------------------------------------
    |
    | VK (VKontakte): Most popular social network in Russia and CIS countries.
    |
    | Benefits for EasyMarket:
    | - Essential for Russian-speaking markets
    | - 600M+ registered users
    | - High penetration in Eastern Europe
    |
    | Setup:
    | 1. Create app at vk.com/apps?act=manage
    | 2. Get Application ID and Secure Key
    | 3. Add redirect URI in settings
    | 4. Add to .env: VKONTAKTE_CLIENT_ID, VKONTAKTE_CLIENT_SECRET
    |
    | Use case: Enable if targeting Russian/Eastern European markets
    |
    | Callback URL: {APP_URL}/oauth/vkontakte/callback
    |
    */

    'vkontakte' => [
        'client_id' => env('VKONTAKTE_CLIENT_ID'),
        'client_secret' => env('VKONTAKTE_CLIENT_SECRET'),
        'redirect' => rtrim(env('APP_URL'), '/') . '/oauth/vkontakte/callback',
    ],

    /*
    |--------------------------------------------------------------------------
    | Envato OAuth / Social Login
    |--------------------------------------------------------------------------
    |
    | Envato: OAuth integration for ThemeForest, CodeCanyon, etc.
    |
    | Benefits for EasyMarket:
    | - Verify Envato purchase codes
    | - Auto-import products from Envato portfolio
    | - Connect existing Envato sellers
    | - Validate licensing for digital products
    |
    | Setup:
    | 1. Create app at build.envato.com/create-app/
    | 2. Request API access
    | 3. Get Client ID and Client Secret
    | 4. Add confirmation URL
    | 5. Add to .env: ENVATO_CLIENT_ID, ENVATO_CLIENT_SECRET
    |
    | Use case: Perfect if EasyMarket sells themes, plugins, or digital assets
    | Allows verification of legitimate Envato authors
    |
    | Callback URL: {APP_URL}/oauth/envato/callback
    |
    */

    'envato' => [
        'client_id' => env('ENVATO_CLIENT_ID'),
        'client_secret' => env('ENVATO_CLIENT_SECRET'),
        'redirect' => rtrim(env('APP_URL'), '/') . '/oauth/envato/callback',
    ],

    /*
    |--------------------------------------------------------------------------
    | GitHub OAuth / Social Login
    |--------------------------------------------------------------------------
    |
    | GitHub: Popular among developers and tech professionals.
    |
    | Benefits for EasyMarket:
    | - Perfect for developer-focused marketplaces
    | - Selling code, themes, plugins, scripts
    | - Verify GitHub repository ownership
    | - Import projects from GitHub
    |
    | Setup:
    | 1. Create OAuth app at github.com/settings/developers
    | 2. Set application name and homepage URL
    | 3. Add authorization callback URL
    | 4. Get Client ID and Client Secret
    | 5. Add to .env: GITHUB_CLIENT_ID, GITHUB_CLIENT_SECRET
    |
    | Use case: Essential for code/software marketplaces
    | Allows sellers to link GitHub repos to their products
    |
    | Callback URL: {APP_URL}/oauth/github/callback
    |
    */

    'github' => [
        'client_id' => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'redirect' => rtrim(env('APP_URL'), '/') . '/oauth/github/callback',
    ],

    /*
    |--------------------------------------------------------------------------
    | Additional Services (Add as needed)
    |--------------------------------------------------------------------------
    |
    | Other services you might integrate with EasyMarket:
    |
    | - Twitter/X OAuth (social login)
    | - LinkedIn OAuth (B2B marketplace)
    | - Stripe API (payment processing)
    | - PayPal API (alternative payments)
    | - Twilio (SMS notifications)
    | - Pusher (real-time notifications)
    | - AWS S3 (file storage - see filesystems.php)
    | - Cloudinary (image optimization)
    | - Google Analytics (tracking)
    | - Facebook Pixel (ads tracking)
    |
    | Example format:
    | 'stripe' => [
    |     'key' => env('STRIPE_KEY'),
    |     'secret' => env('STRIPE_SECRET'),
    |     'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    | ],
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Ably Real-Time Messaging Service
    |--------------------------------------------------------------------------
    |
    | Ably: Real-time messaging platform for WebSocket connections and
    | broadcasting events to connected clients.
    |
    | Use case for EasyMarket:
    | - Real-time notifications (new orders, messages, bids)
    | - Live chat between buyers and sellers
    | - Presence channels (online/offline status)
    | - Live product updates (stock changes, price updates)
    | - Real-time dashboard metrics
    |
    | Features:
    | - WebSocket connections with automatic fallback
    | - Channel authentication for private channels
    | - Presence awareness (who's online)
    | - Message history and persistence
    | - Global edge network for low latency
    |
    | Setup:
    | 1. Sign up at ably.com
    | 2. Create an app in Ably dashboard
    | 3. Get your API key from "API Keys" section
    | 4. Add to .env: ABLY_KEY=your-api-key
    |
    | Pricing: Free tier includes 3M messages/month
    |
    | Documentation: https://ably.com/docs
    |
    */

    'ably' => [
        'key' => env('ABLY_KEY'),
    ],

];


















