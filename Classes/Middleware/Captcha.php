<?php

namespace Blueways\BwCaptcha\Middleware;

use Blueways\BwCaptcha\Utility\CaptchaBuilderUtility;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

class Captcha implements MiddlewareInterface
{
    protected ResponseFactoryInterface $responseFactory;

    protected ConfigurationManager $configurationManager;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        ConfigurationManager $configurationManager
    ) {
        $this->responseFactory = $responseFactory;
        $this->configurationManager = $configurationManager;
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        /** @var PageArguments $pageArguments */
        $pageArguments = $request->getAttribute('routing', null);
        if ($pageArguments->getPageType() !== '3413') {
            // pipe request to other middleware handlers
            return $handler->handle($request);
        }

        $ts = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
        );
        $settings = $ts['plugin.']['tx_bwcaptcha.']['settings.'];
        $width = (int)$settings['width'];
        $height = (int)$settings['height'];
        $lifetime = (int)$settings['lifetime'];
        $font = CaptchaBuilderUtility::getRandomFontFileFromSettings($settings);

        // create new captcha
        $builder = CaptchaBuilderUtility::getBuilderFromSettings($settings);
        $builder->build($width, $height, $font);
        $newPhrase = $builder->getPhrase() ?? '';
        $this->storePhraseToSession($newPhrase, $request, $lifetime);

        // render captcha image
        $response = $this->responseFactory->createResponse()
            ->withHeader('Content-Type', 'image/jpeg');
        $response->getBody()->write($builder->get());
        return $response;
    }

    protected function storePhraseToSession(string $newPhrase, ServerRequestInterface $request, int $lifetime = 3600): void
    {
        // write data to session
        $tsfe = $request->getAttribute('frontend.controller') ?? $GLOBALS['TSFE'];
        $feUser = $tsfe->fe_user;
        $captchaPhrases = $feUser->getKey('ses', 'captchaPhrases');
        if (empty($captchaPhrases)) {
            $captchaPhrases = [];
        }

        $time = time();
        $captchaPhrases = array_filter(
            $captchaPhrases,
            function ($captchaLifetime) use ($time) {
                return $captchaLifetime > $time;
            },
            ARRAY_FILTER_USE_KEY
        );

        $captchaPhrases[$time + $lifetime] = $newPhrase;
        $feUser->setKey('ses', 'captchaPhrases', $captchaPhrases);
        $feUser->storeSessionData();
    }
}
