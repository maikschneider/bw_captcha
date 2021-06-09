<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Form Captcha',
    'description' => 'Captcha for TYPO3 form extension',
    'category' => 'plugin',
    'author' => 'Maik Schneider',
    'author_email' => 'm.schneider@blueways.de',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => '0',
    'createDirs' => '',
    'clearCacheOnLoad' => 1,
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.0-9.9.99'
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
