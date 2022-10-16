<?php

namespace Blueways\BwCaptcha\Controller;

use Blueways\BwCaptcha\Utility\CaptchaBuilderUtility;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\View\JsonView;

class CaptchaV12Controller extends ActionController
{
    protected $defaultViewObjectName = JsonView::class;

    public function initializeRefreshAction(): void
    {
        $this->defaultViewObjectName = JsonView::class;
    }

    public function refreshAction(string $cacheIdentifier): ResponseInterface
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
        return $this->htmlResponse();
    }
}
