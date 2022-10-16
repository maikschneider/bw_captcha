<?php

namespace Blueways\BwCaptcha\Validation\Validator;

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class CaptchaValidator extends AbstractValidator
{
    /**
     * @var array<string, array<int, string>>
     */
    protected $supportedOptions = [
        'phrase' => ['', 'The phrase of the captcha', 'string'],
    ];

    /**
     * @param mixed $value
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
     */
    protected function isValid($value): void
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
    protected function validateCaptcha(string $captchaId, string $value): bool
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

    protected function displayError(): void
    {
        $this->addError(
            $this->translateErrorMessage(
                'validator.captcha.notvalid',
                'bw_captcha'
            ),
            1623240740
        );
    }
}
