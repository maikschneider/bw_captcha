<?php

namespace Blueways\BwCaptcha\Hooks;

use Gregwar\Captcha\CaptchaBuilder;
use TYPO3\CMS\Core\Crypto\Random;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Form\Domain\Model\Renderable\RootRenderableInterface;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;

class FormElementCaptchaHook
{

    public function beforeRendering(FormRuntime $formRuntime, RootRenderableInterface $renderable)
    {
        if ($renderable->getType() === 'Captcha') {

            $builder = new CaptchaBuilder;
            $builder->build();

            $renderable->setProperty('captcha', $builder->inline());

            $phrase = $builder->getPhrase();
            $cache = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Cache\CacheManager::class)->getCache('bwcaptcha');

            /** @var Random $random */
            $random = GeneralUtility::makeInstance(Random::class);
            $cacheIdentifier = $random->generateRandomHexString(32);
            $cache->set($cacheIdentifier, $phrase, [], 86400);

            // write cache identifier to cookie
            $GLOBALS['TSFE']->fe_user->setKey('ses', 'captchaId', $cacheIdentifier);
            $GLOBALS['TSFE']->fe_user->storeSessionData();
        }
    }
}
