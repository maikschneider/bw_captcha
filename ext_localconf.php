<?php

defined('TYPO3') or die();

call_user_func(function () {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup(trim('
            module.tx_form {
                settings {
                    yamlConfigurations {
                        1623227656 = EXT:bw_captcha/Configuration/Yaml/FormConfiguration.yaml
                    }
                }
            }
        '));

    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    $iconRegistry->registerIcon(
        't3-form-captcha-element',
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:bw_captcha/Resources/Public/Images/form-captcha-icon.svg']
    );

    $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'] ??= [];
    \TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule(
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'],
        ['now']
    );
});
