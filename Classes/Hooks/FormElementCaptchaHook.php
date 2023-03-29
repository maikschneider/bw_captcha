<?php

namespace Blueways\BwCaptcha\Hooks;

use Blueways\BwCaptcha\Utility\CaptchaBuilderUtility;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Crypto\Random;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Form\Domain\Model\Renderable\RootRenderableInterface;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;

class FormElementCaptchaHook
{
    /**
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function beforeRendering(FormRuntime $formRuntime, RootRenderableInterface $renderable): void
    {
        if ($renderable->getType() === 'Captcha') {
            // get TypoScript
            $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
            $typoScriptService = GeneralUtility::makeInstance(TypoScriptService::class);
            $typoScript = $typoScriptService->convertTypoScriptArrayToPlainArray($configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT));
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
            if (isset($typoScript['plugin']['tx_bwcaptcha']) && filter_var(
                $settings['refreshButton'],
                FILTER_VALIDATE_BOOLEAN
            )) {
                $renderable->setProperty('cacheIdentifier', $cacheIdentifier);
            }

            // add autocomplete="off"
            $properties = $renderable->getProperties();
            $properties['fluidAdditionalAttributes']['autocomplete'] = 'off';
            $renderable->setProperty('fluidAdditionalAttributes', $properties['fluidAdditionalAttributes']);

            // Add cache identifier to captchaIds array + write it to cookie
            $tsfe = $GLOBALS['TSFE'] ?? null;
            if ($tsfe) {
                $captchaIds = $GLOBALS['TSFE']->fe_user->getKey('ses', 'captchaIds') ?? [];
                if (!in_array($cacheIdentifier, $captchaIds)) {
                    $captchaIds[] = $cacheIdentifier;
                    $tsfe->fe_user->setKey('ses', 'captchaIds', $captchaIds);
                    $tsfe->fe_user->storeSessionData();
                }
            }

            // add controller name to element
            $verionNumberUtility = GeneralUtility::makeInstance(VersionNumberUtility::class);
            $version = $verionNumberUtility->convertVersionStringToArray($verionNumberUtility->getNumericTypo3Version());
            $controllerName = $version['version_main'] < 12 ? 'Captcha' : 'CaptchaV12';
            $renderable->setProperty('controllerName', $controllerName);
        }
    }
}
