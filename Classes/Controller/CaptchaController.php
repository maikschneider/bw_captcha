<?php

namespace Blueways\BwCaptcha\Controller;

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
        $this->view->setVariablesToRender(['captcha']);
        $this->view->assign('captcha', ['captcha' => $cacheIdentifier]);
    }
}
