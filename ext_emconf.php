<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Form Captcha',
    'description' => 'Captcha element for the TYPO3 form component. The captcha generation uses Gregwar/Captcha, no Google or 3rd party includes.',
    'category' => 'plugin',
    'author' => 'Maik Schneider',
    'author_company' => 'XIMA Media GmbH',
    'author_email' => 'maik.schneider@xima.de',
    'state' => 'stable',
    'clearCacheOnLoad' => true,
    'version' => '2.0.5',
    'constraints' => [
        'depends' => [
            'typo3' => '10.0.0-12.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
