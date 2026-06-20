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

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    | conceptnote als OIDC-Identity-Provider (SSO Phase B).
    | rootsfactory ist der Client; nur Mitglieder des conceptnote-Teams
    | "Free-Spirits" (Claim is_team_member) dürfen in den Workspace.
    */
    'conceptnote' => [
        'issuer' => rtrim(env('CONCEPTNOTE_OIDC_ISSUER', 'https://conceptnote.eu'), '/'),
        'client_id' => env('CONCEPTNOTE_OIDC_CLIENT_ID'),
        'client_secret' => env('CONCEPTNOTE_OIDC_CLIENT_SECRET'),
        'redirect' => env('CONCEPTNOTE_OIDC_REDIRECT_URI'),
    ],

];
