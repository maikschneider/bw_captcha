<?php

namespace Blueways\BwCaptcha\EventListener;

use Blueways\BwCaptcha\Validation\Validator\CaptchaValidator;
use DERHANSEN\SfEventMgt\Event\ModifyRegistrationValidatorResultEvent;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Extbase\Validation\Validator\ConjunctionValidator;
use TYPO3\CMS\Extbase\Validation\Validator\NotEmptyValidator;

class ModifyRegistrationValidatorResultEventListener
{
    public function __construct(
        private readonly ExtensionConfiguration $extensionConfiguration,
    ) {
    }

    public function __invoke(ModifyRegistrationValidatorResultEvent $event): void
    {
        $isBwCaptchaUsed = $this->extensionConfiguration->get('bw_captcha', 'sfEventMgt') ?? false;

        if (!$isBwCaptchaUsed) {
            return;
        }

        $validator = new ConjunctionValidator();
        $validator->addValidator(new NotEmptyValidator());
        $validator->addValidator(new CaptchaValidator());

        $result = $validator->validate($event->getRegistration()->getCaptcha());

        $event->getResult()->forProperty('captcha')->merge($result);
    }
}
