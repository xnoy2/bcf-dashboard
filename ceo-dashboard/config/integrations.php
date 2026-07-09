<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Multi-account configuration (BCF / BGR / BWD)
    |--------------------------------------------------------------------------
    | Mirrors the legacy dist/auth/config.php "ACCOUNTS" structure so the CEO
    | roll-up ("all") and per-account views keep working under Laravel.
    */

    'accounts' => [

        'bcf' => [
            'name' => 'Ballycastle Climbing Frames',
            'ghl'  => [
                'api_key'     => env('BCF_GHL_API_KEY'),
                'location_id' => env('BCF_LOCATION_ID'),
            ],
            'cloudflare' => ['api_token' => env('CF_API_TOKEN')],
        ],

        'bgr' => [
            'name' => 'Bespoke Garden Rooms',
            'ghl'  => [
                'api_key'     => env('BGR_GHL_API_KEY'),
                'location_id' => env('BGR_LOCATION_ID'),
            ],
            'cloudflare' => ['api_token' => env('CF_API_TOKEN')],
        ],

        'rg' => [
            'name' => 'RG Hillview Farms',
            'ghl'  => [
                'api_key'     => env('RG_GHL_API_KEY'),
                'location_id' => env('RG_LOCATION_ID'),
            ],
            'cloudflare' => ['api_token' => env('CF_API_TOKEN')],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Read-only integration API
    |--------------------------------------------------------------------------
    | A single static key (DASHBOARD_API_KEY) authorises external apps to read
    | the same cached dashboard data over JSON at /api/v1/*. Leave the env var
    | empty to disable the API entirely (every request then returns 503).
    */
    'api' => [
        'key' => env('DASHBOARD_API_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Board card attachments
    |--------------------------------------------------------------------------
    | Storage disk for card attachments. 'boards' = local disk (needs a Railway
    | volume). 'r2' = Cloudflare R2 (S3) with presigned direct upload/download,
    | which supports big files without a volume. Max upload size in MB.
    */
    'attachments' => [
        'disk'      => env('ATTACHMENTS_DISK', 'boards'),
        'max_mb'    => (int) env('ATTACHMENTS_MAX_MB', 500),
    ],

    /*
    |--------------------------------------------------------------------------
    | GoHighLevel
    |--------------------------------------------------------------------------
    */
    'ghl' => [
        'base_url' => 'https://services.leadconnectorhq.com',
        'version'  => '2021-07-28',
    ],

    /*
    |--------------------------------------------------------------------------
    | GoHighLevel Agency OAuth app (multi-sub-account integration)
    |--------------------------------------------------------------------------
    | One agency install → access to every sub-account. The agency token is
    | exchanged/refreshed here; per-location tokens are minted on demand.
    */
    'ghl_oauth' => [
        'client_id'     => env('GHL_CLIENT_ID'),
        'client_secret' => env('GHL_CLIENT_SECRET'),
        // Draft apps authorise by version_id (not client_id). Falls back to the
        // app id embedded in the client id (the part before the "-").
        'version_id'    => env('GHL_VERSION_ID'),
        'redirect_uri'  => env('GHL_REDIRECT_URI'), // explicit full URL (recommended in prod)
        'redirect_path' => env('GHL_REDIRECT_PATH', '/integrations/crm/callback'),
        'authorize_url' => 'https://marketplace.gohighlevel.com/oauth/chooselocation',
        'scopes'        => 'contacts.readonly opportunities.readonly locations.readonly '
            . 'calendars.readonly calendars/events.readonly '
            . 'funnels/funnel.readonly funnels/page.readonly funnels/redirect.readonly funnels/pagecount.readonly '
            . 'payments/transactions.readonly payments/orders.readonly users.readonly',
    ],

    /*
    |--------------------------------------------------------------------------
    | Live portals (REST) — validated live 2026-06-08
    |--------------------------------------------------------------------------
    */
    'portals' => [

        // BCF Client Portal — Supabase edge function, x-api-key header
        'bcf_client' => [
            'base_url' => env('BCF_PORTAL_URL'),
            'auth'     => ['type' => 'x-api-key', 'value' => env('BCF_PORTAL_API_KEY')],
        ],

        // BGR Client Portal — Laravel Sanctum, Bearer token
        'bgr_client' => [
            'base_url' => env('BGR_PORTAL_URL'),
            'auth'     => ['type' => 'bearer', 'value' => env('BGR_PORTAL_TOKEN')],
        ],

        // Staff Portal — Laravel Sanctum, Bearer token
        'staff' => [
            'base_url' => env('STAFF_PORTAL_URL'),
            'auth'     => ['type' => 'bearer', 'value' => env('STAFF_PORTAL_TOKEN')],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Domain renewal alerts
    |--------------------------------------------------------------------------
    | Domains expiring within `days` (or already expired) trigger the daily
    | alert email and the dashboard warnings.
    */
    'renewal_alerts' => [
        'days'   => (int) env('RENEWAL_ALERT_DAYS', 10),
        'emails' => array_values(array_filter(array_map('trim', explode(',', (string) env('RENEWAL_ALERT_EMAILS', ''))))),
    ],

    /*
    |--------------------------------------------------------------------------
    | Quick links to the integrated tools (shown in page headers)
    |--------------------------------------------------------------------------
    */
    'tool_links' => [
        'ghl'        => ['label' => 'GoHighLevel', 'url' => 'https://app.gohighlevel.com', 'icon' => 'bi-rocket-takeoff'],
        'staff'      => ['label' => 'Staff Portal', 'url' => 'https://staff.bespokegardenroomsballycastle.co.uk', 'icon' => 'bi-people'],
        'bgr_portal' => ['label' => 'BGR Portal', 'url' => 'https://portal.bespokegardenroomsballycastle.co.uk', 'icon' => 'bi-house-gear'],
        'bcf_portal' => ['label' => 'BCF Portal', 'url' => 'https://portal.ballycastleclimbingframes.co.uk', 'icon' => 'bi-box-seam'],
        'cloudflare' => ['label' => 'Cloudflare', 'url' => 'https://dash.cloudflare.com', 'icon' => 'bi-cloud-fill'],
        'godaddy'    => ['label' => 'GoDaddy', 'url' => 'https://dcc.godaddy.com/control/portfolio', 'icon' => 'bi-globe2'],
    ],

    /*
    |--------------------------------------------------------------------------
    | GoDaddy (domain renewals) — multiple accounts
    |--------------------------------------------------------------------------
    | Auth header: "Authorization: sso-key {KEY}:{SECRET}". Production API.
    */
    'godaddy' => [
        'base_url' => 'https://api.godaddy.com',
        'accounts' => [
            'main' => [
                'label'  => env('GODADDY_1_LABEL', 'Main'),
                'key'    => env('GODADDY_1_KEY'),
                'secret' => env('GODADDY_1_SECRET'),
            ],
            'nicola' => [
                'label'  => env('GODADDY_2_LABEL', 'Nicola'),
                'key'    => env('GODADDY_2_KEY'),
                'secret' => env('GODADDY_2_SECRET'),
            ],
            'nicola_bcf' => [
                'label'  => env('GODADDY_3_LABEL', 'Nicola BCF'),
                'key'    => env('GODADDY_3_KEY'),
                'secret' => env('GODADDY_3_SECRET'),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin login (single user, ported from legacy app)
    |--------------------------------------------------------------------------
    */
    'admin' => [
        'email'         => env('ADMIN_EMAIL'),
        'password_hash' => env('ADMIN_PASSWORD_HASH'),
    ],
];
