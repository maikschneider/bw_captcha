<?php

namespace Blueways\BwCaptcha\Domain\Model\FormElements;

use TYPO3\CMS\Form\Domain\Model\FormElements\AbstractFormElement;

class CaptchaElement extends AbstractFormElement
{
    public function initializeFormElement(): void
    {
        parent::initializeFormElement();

        $this->setOptions([
            'validators' => [
                [
                    'identifier' => 'NotEmpty',
                ],
                [
                    'identifier' => 'Captcha',
                ],
            ],
        ]);
    }
}
