<?php

return [
    /**
     * Providers OAUTH 1.0
     * For to authentication with providers who implements oauth 1.0
     */
    'auth'=>[
        'model'=>null, //Model Authenticatable to api, sample: 'model' => '\App\User'
        'social'=> [
            'providers'=> [
                'twitter' => [
                    'consumer_key' => null,
                    'consumer_secret' => null
                ]
            ],
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
    'guards'=>'api', // 'api,driver',
    'appcenter' => [
        'Client' =>[
            'token' => null, //Api token from app center
            'owner' => null, //Owner of project
            'ios' => null, //Name of iOS project
            'droid' => null //Name of Android Project
        ],
        //If you has more notifiables models just add another array like this
        // 'Driver' =>[
        //     'token' => 'drivertok',
        //     'owner' => 'drivown',
        //     'ios' => 'drivios',
        //     'droid' => 'drivdroid'
        //]
    ]
];
