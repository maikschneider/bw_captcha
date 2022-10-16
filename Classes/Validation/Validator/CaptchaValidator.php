<?php

namespace Blueways\BwCaptcha\Validation\Validator;

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class CaptchaValidator extends AbstractValidator
{

    protected $supportedOptions = [
        'phrase' => ['', 'The phrase of the captcha', 'string']
    ];

    /**
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
     */
    protected function isValid(mixed $value): void
    {
        $captchaIds = $GLOBALS['TSFE']->fe_user->getKey('ses', 'captchaIds');

        if (!$captchaIds || !is_array($captchaIds) || !is_string($value)) {
            $this->displayError();
            return;
        }

        foreach ($captchaIds as $captchaId) {
            $isValid = $this->validateCaptcha($captchaId, $value);
            if ($isValid) {
                return;
            }
        }

        $this->displayError();
    }

    /**
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
     */
    protected function validateCaptcha($captchaId, $value): bool
    {
        $cacheIdentifier = $GLOBALS['TSFE']->fe_user->getKey('ses', $captchaId);

        if (!$cacheIdentifier) {
            return false;
        }

        // get captcha secret from cache and compare
        $cache = GeneralUtility::makeInstance(CacheManager::class)->getCache('bwcaptcha');
        $phrase = $cache->get($cacheIdentifier);

        if ($phrase && $phrase === $value) {
            return true;
        }

        return false;
    }

    protected function displayError()
    {
        $this->addError(
            $this->translateErrorMessage(
                'validator.captcha.notvalid',
                'bw_captcha'
            ) ?? '',
            1623240740
        );
    }
}
