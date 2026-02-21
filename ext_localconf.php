<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

call_user_func(static function () {
    ExtensionManagementUtility::addTypoScriptSetup(trim('
            module.tx_form {
                settings {
                    yamlConfigurations {
                        1623227656 = EXT:bw_captcha/Configuration/Yaml/FormConfiguration.yaml
                    }
                }
            }
        '));

    $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'] ??= [];
    if (!in_array('now', $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'], true)) {
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'now';
    }
});
