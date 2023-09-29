<?php

namespace Blueways\BwCaptcha\Domain\Model\FormElements;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Form\Domain\Model\FormElements\AbstractFormElement;

class CaptchaElement extends AbstractFormElement
{
    /**
     * @throws InvalidConfigurationTypeException
     */
    public function initializeFormElement(): void
    {
        parent::initializeFormElement();

        $this->setOptions(
            [
                'validators' => [
                    [
                        'identifier' => 'NotEmpty',
                    ],
                    [
                        'identifier' => 'Captcha',
                    ],
                ],
            ]
        );

        $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
        $ts = $configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
        );
        $settings = $ts['plugin.']['tx_bwcaptcha.']['settings.'];
        $this->setProperty('showRefresh', (bool)$settings['refreshButton']);
        $this->setProperty('showAudio', (bool)$settings['audioButton']);
    }
}
