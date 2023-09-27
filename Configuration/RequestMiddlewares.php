<?php

return [
    'frontend' => [
        'bw-captcha/captcha' => [
            'target' => Blueways\BwCaptcha\Middleware\Captcha::class,
            'after' => [
                'typo3/cms-frontend/prepare-tsfe-rendering',
            ],
            'before' => [
                'typo3/cms-frontend/output-compression',
            ],
        ],
        'bw-captcha/audio' => [
            'target' => Blueways\BwCaptcha\Middleware\Audio::class,
            'after' => [
                'typo3/cms-frontend/prepare-tsfe-rendering',
            ],
            'before' => [
                'typo3/cms-frontend/output-compression',
            ],
        ],
    ],
];
