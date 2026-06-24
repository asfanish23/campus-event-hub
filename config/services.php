<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // Instagram Graph API configuration
    'instagram' => [
        'token' => env('INSTAGRAM_ACCESS_TOKEN'),
        'user_id' => env('INSTAGRAM_BUSINESS_ACCOUNT_ID'),
        'app_id' => env('INSTAGRAM_APP_ID'),
        'app_secret' => env('INSTAGRAM_APP_SECRET'),
    ],

    // Facebook Page Graph API configuration
    'facebook' => [
        'page_id' => env('FACEBOOK_PAGE_ID'),
        'page_access_token' => env('FACEBOOK_PAGE_ACCESS_TOKEN'),
    ],

    // ImgBB Image Hosting API configuration
    'imgbb' => [
        'key' => env('IMGBB_API_KEY'),
    ],

    /**
     * ToyyibPay Payment Gateway Configuration
     * 
     * Supports both sandbox and production environments:
     * - Sandbox: https://dev.toyyibpay.com (for testing)
     * - Production: https://toyyibpay.com (for live transactions)
     */
    'toyyibpay' => [
        'api_url' => env('TOYYIBPAY_API_URL', 'https://dev.toyyibpay.com'),
        'category_code' => env('TOYYIBPAY_CATEGORY_CODE'),
        'secret_key' => env('TOYYIBPAY_SECRET_KEY'),
        'mode' => env('TOYYIBPAY_MODE', 'sandbox'),
    ],

    // Google Gemini AI API configuration
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
    ],

    // Telegram Bot configuration
    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'chat_id' => env('TELEGRAM_CHAT_ID'),
        'api_url' => env('TELEGRAM_API_URL', 'https://api.telegram.org'),
        'webhook_url' => env('TELEGRAM_WEBHOOK_URL', env('APP_URL') . '/api/telegram/webhook'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

];
