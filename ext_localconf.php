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

    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \TYPO3\CMS\Core\Imaging\IconRegistry::class
    );
    $iconRegistry->registerIcon(
        't3-form-captcha-element',
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:bw_captcha/Resources/Public/Images/form-captcha-icon.svg']
    );

    // get typo3 version
    $verionNumberUtility = TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Utility\VersionNumberUtility::class);
    $version = $verionNumberUtility->convertVersionStringToArray($verionNumberUtility->getNumericTypo3Version());
    $captchaControllerName = $version['version_main'] > 9 ? \Blueways\BwCaptcha\Controller\CaptchaController::class : 'Captcha';

    // register cache table
    if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['bwcaptcha'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['bwcaptcha'] = array();
    }

    // register hook for captcha generation
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['beforeRendering'][1630333427]
        = \Blueways\BwCaptcha\Hooks\FormElementCaptchaHook::class;

    // register plugin for captcha refresh endpoint
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Blueways.BwCaptcha',
        'Pi1',
        [
            $captchaControllerName => 'refresh'
        ],
        [
            $captchaControllerName => 'refresh'
        ]
    );
});
