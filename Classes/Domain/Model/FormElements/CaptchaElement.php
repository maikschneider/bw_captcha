<?php

namespace Blueways\BwCaptcha\Domain\Model\FormElements;

use Blueways\BwCaptcha\Validation\Validator\CaptchaValidator;
use Gregwar\Captcha\CaptchaBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Validation\Validator\NotEmptyValidator;
use TYPO3\CMS\Form\Domain\Model\FormElements\AbstractFormElement;

class CaptchaElement extends AbstractFormElement
{

    public function initializeFormElement()
    {
        parent::initializeFormElement();

        // create and add captcha
        $builder = new CaptchaBuilder;
        $builder->build();
        $this->setProperty('captcha', $builder->inline());
        $this->setOptions([
            'validators' => [
                [
                    'identifier' => 'NotEmpty'
                ],
                [
                    'identifier' => 'Captcha',
                    'options' => [
                        'phrase' => $builder->getPhrase()
                    ]
                ]
            ]
        ]);

    }
}
