<?php

use Blueways\BwCaptcha\Controller\CaptchaController;
use Blueways\BwCaptcha\Hooks\FormElementCaptchaHook;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

call_user_func(function () {
    ExtensionManagementUtility::addTypoScriptSetup(trim('
            module.tx_form {
                settings {
                    yamlConfigurations {
                        1623227656 = EXT:bw_captcha/Configuration/Yaml/FormConfiguration.yaml
                    }
                }
            }
        '));

    $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
    $iconRegistry->registerIcon(
        't3-form-captcha-element',
        SvgIconProvider::class,
        ['source' => 'EXT:bw_captcha/Resources/Public/Images/form-captcha-icon.svg']
    );

    // get typo3 version
    $verionNumberUtility = GeneralUtility::makeInstance(VersionNumberUtility::class);
    $version = $verionNumberUtility->convertVersionStringToArray($verionNumberUtility->getNumericTypo3Version());
    $captchaControllerName = $version['version_main'] > 9 ? CaptchaController::class : 'Captcha';
    $extensionName = $version['version_main'] > 11 ? 'BwCaptcha' : 'Blueways.BwCaptcha';

    // register cache table
    if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['bwcaptcha'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['bwcaptcha'] = [];
    }

    // register hook for captcha generation
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['beforeRendering'][1630333427] = FormElementCaptchaHook::class;

    // register plugin for captcha refresh endpoint
    ExtensionUtility::configurePlugin(
        $extensionName,
        'Pi1',
        [
            $captchaControllerName => 'refresh',
        ],
        [
            $captchaControllerName => 'refresh',
        ]
    );
});
