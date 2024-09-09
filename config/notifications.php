<?php

return [
    'sandbox' => env('NOTIFICATION_SANDBOX_ENABLE', false),

    'sms' => [
        'default' => env('NOTIFICATION_DEFAULT_SMS_SENDER', 'infobip'),
        'senders' => [
            'twilio' => [
                'transport' => 'twilio',
                'account_sid' => env('TWILIO_ACCOUNT_SID'),
                'auth_token' => env('TWILIO_AUTH_TOKEN'),
                'from' => env('TWILIO_FROM'),
            ],
            'infobip' => [
                'transport' => 'infobip',
                'base_url' => env('INFOBIP_BASE_URL'),
                'api_key' => env('INFOBIP_API_KEY'),
                'from' => env('INFOBIP_FROM'),
                'project_id' => env('INFOBIP_PROJECT_ID', ''),
            ],
        ],
    ],

    'sms2way' => [
        'default' => env('NOTIFICATION_DEFAULT_SMS2WAY_SENDER', 'twilio'),
        'senders' => [
            'twilio' => [
                'transport' => 'twilio',
                'account_sid' => env('TWILIO_ACCOUNT_SID'),
                'auth_token' => env('TWILIO_AUTH_TOKEN'),
                'from' => env('TWILIO_FROM'),
            ],
            'telerivet' => [
                'transport' => 'telerivet',
                'api_key' => env('TELERIVET_API_KEY', ''),
                'project_id' => env('TELERIVET_PROJECT_ID', ''),
                'number' => env('TELERIVET_NUMBER', ''),
            ],
        ],
    ],

    'whatsapp' => [
        'default' => env('NOTIFICATION_DEFAULT_WHATSAPP_SENDER', 'twilio'),
        'senders' => [
            'twilio' => [
                'transport' => 'twilio',
                'account_sid' => env('TWILIO_ACCOUNT_SID'),
                'auth_token' => env('TWILIO_AUTH_TOKEN'),
                'from' => env('TWILIO_WHATSAPP_FROM'),
            ],
        ],
    ],

    'line' => [
        'default' => env('NOTIFICATION_DEFAULT_LINE_SENDER', 'line'),
        'senders' => [
            'line' => [
                'token' => env('LINE_ACCESS_TOKEN'),
                'secret' => env('LINE_CHANNEL_SECRET'),
            ],
        ],
    ],

];
