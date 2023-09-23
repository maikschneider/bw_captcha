<?php

namespace Blueways\BwCaptcha\Validation\Validator;

use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

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
     */
    protected function isValid($value): void
    {
        $captchaPhrases = $this->getFeUser()->getKey('ses', 'captchaPhrases');

        if (!$captchaPhrases || !is_array($captchaPhrases) || !is_string($value)) {
            $this->displayError();
            return;
        }

        $time = time();
        $captchaPhrases = array_filter(
            $captchaPhrases,
            function ($captchaLifetime) use ($time) {
                return $captchaLifetime > $time;
            },
            ARRAY_FILTER_USE_KEY
        );

        foreach ($captchaPhrases as $lifetime => $captchaPhrase) {
            $isValid = !empty($captchaPhrase) && $captchaPhrase === $value;
            if ($isValid) {
                // remove solved captcha
                unset($captchaPhrases[$lifetime]);
                $this->getFeUser()->setKey('ses', 'captchaPhrases', $captchaPhrases);
                $this->getFeUser()->storeSessionData();
                return;
            }
        }

        $this->displayError();
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

    protected function getFeUser(): FrontendUserAuthentication
    {
        return $GLOBALS['TSFE']->fe_user;
    }
}
