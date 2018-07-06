<?php

return [
    /**
     * Providers OAUTH 1.0
     * For to authentication with providers who implements oauth 1.0
     */
    'auth-social'=> [
        'passport' => [
            'token' => null
        ],
        'providers'=> [
            'twitter' => [
                'consumer_key' => null,
                'consumer_secret' => null
            ]
        ]
    ],
    'notification-channel' =>
    [
        'appcenter' => '\Kadevjo\Fibonacci\Channels\AppCenterChannel',
    ],
    'firebase' => [
        'live-model' => false,
        'override' => false

    ],
    'appcenter' => [
        'Client' =>[
            'token' => 'tkappcl',
            'owner' => 'clinetowner',
            'ios' => 'clientios',
            'droid' => 'clientdroid'
        ],
        'Driver' =>[
            'token' => 'drivertok',
            'owner' => 'drivown',
            'ios' => 'drivios',
            'droid' => 'drivdroid'
        ]
    ]
];
