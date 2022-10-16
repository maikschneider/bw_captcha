<?php

defined('TYPO3') || die();

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

call_user_func(function () {
    /**
     * TypoScript Tempalte
     */
    ExtensionManagementUtility::addStaticFile(
        'bw_captcha',
        'Configuration/TypoScript',
        'Form Captcha'
    );
});
