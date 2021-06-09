<?php
defined('TYPO3_MODE') or die();

call_user_func(function () {
    if (TYPO3_MODE === 'BE') {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup(trim('
            module.tx_form {
                settings {
                    yamlConfigurations {
                        1623227656 = EXT:bw_captcha/Configuration/Yaml/FormConfiguration.yaml
                    }
                }
            }
        '));

        $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Imaging\IconRegistry::class
        );
        $iconRegistry->registerIcon(
            't3-form-captcha-element',
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:bw_captcha/Resources/Public/Images/form-captcha-icon.svg']
        );
    }

    // register cache table
    if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['bwcaptcha'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['bwcaptcha'] = array();
    }

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['beforeRendering'][1571076908]
        = \Blueways\BwCaptcha\Hooks\FormElementCaptchaHook::class;
});
