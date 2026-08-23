<?php

namespace Blueways\BwCaptcha\Validation\Validator;

use Blueways\BwCaptcha\Utility\PhraseSessionUtility;
use Psr\Http\Message\ServerRequestInterface;
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
        $feUser = $this->getFeUser();

        if ($feUser === null || !is_string($value) || !PhraseSessionUtility::consume($feUser, $value)) {
            $this->displayError();
        }
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

    protected function getFeUser(): ?FrontendUserAuthentication
    {
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        $feUser = $request instanceof ServerRequestInterface ? $request->getAttribute('frontend.user') : null;

        return $feUser instanceof FrontendUserAuthentication ? $feUser : null;
    }
}
