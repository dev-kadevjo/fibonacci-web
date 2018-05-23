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
    ]
];