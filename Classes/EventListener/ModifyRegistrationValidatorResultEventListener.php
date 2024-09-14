<?php

namespace Blueways\BwCaptcha\EventListener;

use Blueways\BwCaptcha\Validation\Validator\CaptchaValidator;
use DERHANSEN\SfEventMgt\Event\ModifyRegistrationValidatorResultEvent;
use TYPO3\CMS\Extbase\Validation\Validator\ConjunctionValidator;
use TYPO3\CMS\Extbase\Validation\Validator\NotEmptyValidator;

class ModifyRegistrationValidatorResultEventListener
{
    public function __invoke(ModifyRegistrationValidatorResultEvent $event): void
    {
        $captchaSettings = $event->getSettings()['registration']['captcha'] ?? [];
        $isCaptchaEnabled = isset($captchaSettings['enabled']) && $captchaSettings['enabled'];
        $isBwCaptchaEnabled = isset($captchaSettings['type']) && $captchaSettings['type'] === 'bwCaptcha';

        if (!$isCaptchaEnabled || !$isBwCaptchaEnabled) {
            return;
        }

        $validator = new ConjunctionValidator();
        $validator->addValidator(new NotEmptyValidator());
        $validator->addValidator(new CaptchaValidator());

        $result = $validator->validate($event->getRegistration()->getCaptcha());

        $event->getResult()->forProperty('captcha')->merge($result);
    }
}
