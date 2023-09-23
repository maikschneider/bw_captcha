<?php

return [
    'frontend' => [
        'bw-captcha/captcha' => [
            'target' => Blueways\BwCaptcha\Middleware\Captcha::class,
            'after'  => [
                'typo3-cms/frontend/tsfe',
            ],
        ],
        'bw-captcha/audio' => [
            'target' => Blueways\BwCaptcha\Middleware\Audio::class,
            'after'  => [
                'typo3-cms/frontend/tsfe',
            ],
        ],
    ],
];
