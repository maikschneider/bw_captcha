<?php

return [
    'frontend' => [
        'bw-captcha/captcha' => [
            'target' => Blueways\BwCaptcha\Middleware\Captcha::class,
            'after'  => [
                'typo3-cms/frontend/tsfe',
            ],
        ],
    ],
];
