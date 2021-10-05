<?php

namespace Blueways\BwCaptcha\Controller;

use Blueways\BwCaptcha\Utility\CaptchaBuilderUtility;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class CaptchaController extends ActionController
{

    protected $defaultViewObjectName = \TYPO3\CMS\Extbase\Mvc\View\JsonView::class;

    public function initializeRefreshAction()
    {
        $this->defaultViewObjectName = \TYPO3\CMS\Extbase\Mvc\View\JsonView::class;
    }

    public function refreshAction(string $cacheIdentifier)
    {
        // create new captcha
        $builder = CaptchaBuilderUtility::getBuilderFromSettings($this->settings);
        $builder->build((int)$this->settings['width'], (int)$this->settings['height']);
        $captcha = $builder->inline();

        // override captcha secret in cache
        $phrase = $builder->getPhrase();
        $cache = GeneralUtility::makeInstance(CacheManager::class)->getCache('bwcaptcha');
        $cache->set($cacheIdentifier, $phrase, [], 86400);

        // return new inline captcha
        $this->view->setVariablesToRender(['captcha']);
        $this->view->assign('captcha', ['captcha' => $captcha]);
    }
}
