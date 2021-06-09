<?php

namespace Blueways\BwCaptcha\Validation\Validator;

use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class CaptchaValidator extends AbstractValidator
{

    protected $supportedOptions = [
        'phrase' => ['', 'The phrase of the captcha', 'string']
    ];

    protected function isValid($value)
    {
        if (!is_string($value) || $this->options['phrase'] !== $value) {
            $this->addError(
                $this->translateErrorMessage(
                    'validator.captcha.notvalid',
                    'bw_captcha'
                ) ?? '',
                1623240740
            );
        }
    }
}
