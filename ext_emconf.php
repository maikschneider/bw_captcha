<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Accessible Form Captcha',
    'description' => 'Captcha element with audio support for TYPO3 form components. The captcha generation does not rely on Google or third-party integrations.',
    'category' => 'plugin',
    'author' => 'Maik Schneider',
    'author_company' => 'XIMA Media GmbH',
    'author_email' => 'maik.schneider@xima.de',
    'state' => 'stable',
    'version' => '4.2.1',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-13.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
