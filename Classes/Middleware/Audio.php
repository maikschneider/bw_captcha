<?php

namespace Blueways\BwCaptcha\Middleware;

use Blueways\BwCaptcha\Utility\AudioBuilderUtility;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

class Audio implements MiddlewareInterface
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
        if ($pageArguments->getPageType() !== '3414') {
            // pipe request to other middleware handlers
            return $handler->handle($request);
        }

        $ts = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
        );
        $settings = $ts['plugin.']['tx_bwcaptcha.']['settings.'];

        $tsfe = $request->getAttribute('frontend.controller') ?? $GLOBALS['TSFE'];
        $feUser = $tsfe->fe_user;
        $captchaPhrases = $feUser->getKey('ses', 'captchaPhrases');

        if (!$captchaPhrases || !is_array($captchaPhrases)) {
            // @TODO: handle error
            return $handler->handle($request);
        }

        $languageCode = $request->getAttribute('language')->getTwoLetterIsoCode();
        $latestCaptcha = array_pop($captchaPhrases);

        $soundFile = AudioBuilderUtility::createAudioCode($latestCaptcha, $languageCode);

        //$soundFile = file_get_contents(GeneralUtility::getFileAbsFileName('EXT:bw_captcha/Resources/Private/Sounds/de/a.mp3'));

        // render captcha image
        $response = $this->responseFactory->createResponse()
            ->withHeader('Content-Type', 'audio/mp3');
        $response->getBody()->write($soundFile);
        return $response;
    }
}
