<?php

namespace Blueways\BwCaptcha\Hooks;

use Blueways\BwCaptcha\Utility\CaptchaBuilderUtility;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Crypto\Random;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Form\Domain\Model\Renderable\RootRenderableInterface;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;

class FormElementCaptchaHook
{

    public function beforeRendering(FormRuntime $formRuntime, RootRenderableInterface $renderable)
    {
        if ($renderable->getType() === 'Captcha') {

            // get TypoScript
            $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
            $typoScriptService = GeneralUtility::makeInstance(TypoScriptService::class);
            $typoScript = $typoScriptService->convertTypoScriptArrayToPlainArray($configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_FULL_TYPOSCRIPT));
            $settings = $typoScript['plugin']['tx_bwcaptcha']['settings'];

            // build captcha and add to template
            $width = (int)$settings['width'];
            $height = (int)$settings['height'];
            $font = CaptchaBuilderUtility::getRandomFontFileFromSettings($settings);

            // create new captcha
            $builder = CaptchaBuilderUtility::getBuilderFromSettings($settings);
            $builder->build($width, $height, $font);
            $renderable->setProperty('captcha', $builder->inline());

            // save captcha secret in cache
            $phrase = $builder->getPhrase();
            $cache = GeneralUtility::makeInstance(CacheManager::class)->getCache('bwcaptcha');
            $random = GeneralUtility::makeInstance(Random::class);
            $cacheIdentifier = $random->generateRandomHexString(32);
            $cache->set($cacheIdentifier, $phrase, [], 86400);

            // inject cache identifier for captcha refresh button
            if (isset($typoScript['plugin']['tx_bwcaptcha']) && filter_var($settings['refreshButton'],
                    FILTER_VALIDATE_BOOLEAN)) {
                $renderable->setProperty('cacheIdentifier', $cacheIdentifier);
            }

            // add autocomplete="off"
            $properties = $renderable->getProperties();
            $properties['fluidAdditionalAttributes']['autocomplete'] = 'off';
            $renderable->setProperty('fluidAdditionalAttributes', $properties['fluidAdditionalAttributes']);

            // Add CaptchaId to Array
            $currentCaptchaId = md5($formRuntime->getFormDefinition()->getPersistenceIdentifier() . $renderable->getIdentifier());
            $captchaIds = $GLOBALS['TSFE']->fe_user->getKey('ses', 'captchaIds') ?? [];
            if (!in_array($currentCaptchaId, $captchaIds)) {
                $captchaIds[] = $currentCaptchaId;
                $GLOBALS['TSFE']->fe_user->setKey('ses', 'captchaIds', $captchaIds);
            }

            // write cache identifier to cookie
            $GLOBALS['TSFE']->fe_user->setKey('ses', $currentCaptchaId, $cacheIdentifier);
            $GLOBALS['TSFE']->fe_user->storeSessionData();
        }
    }
}
