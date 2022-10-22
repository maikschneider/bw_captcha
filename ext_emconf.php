<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Form Captcha',
    'description' => 'Captcha element for the TYPO3 form component. The captcha generation uses Gregwar/Captcha, no Google or 3rd party includes.',
    'category' => 'plugin',
    'author' => 'Maik Schneider',
    'author_company' => 'XIMA Media GmbH',
    'author_email' => 'maik.schneider@xima.de',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => '0',
    'createDirs' => '',
    'clearCacheOnLoad' => 1,
    'version' => '1.2.1',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.0-11.99.99'
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
