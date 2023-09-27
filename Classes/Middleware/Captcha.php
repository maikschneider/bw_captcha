<?php

namespace Blueways\BwCaptcha\Middleware;

use Blueways\BwCaptcha\Utility\CaptchaBuilderUtility;
use MaikSchneider\Steganography\Processor;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;

class Captcha implements MiddlewareInterface
{
    protected ResponseFactoryInterface $responseFactory;

    protected ConfigurationManager $configurationManager;

    protected Context $context;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        ConfigurationManager $configurationManager,
        Context $context,
    ) {
        $this->responseFactory = $responseFactory;
        $this->configurationManager = $configurationManager;
        $this->context = $context;
    }

    /**
     * @throws InvalidConfigurationTypeException
     */
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

        // encode encrypted phrase into image
        if ((int)$settings['audioButton']) {
            $processor = new Processor();
            $image = $processor->encode($builder->getGd(), $newPhrase);
            $captchaImage = $image->get();
            $mimeType = 'image/png';
        } else {
            $captchaImage = $builder->get();
            $mimeType = 'image/jpeg';
        }

        // remove backend authentication for request (to avoid cache header override)
        $this->context->unsetAspect('backend.user');

        // construct response
        $response = $this->responseFactory->createResponse()
            ->withHeader('Content-Type', $mimeType)
            ->withHeader('Cache-Control', 'no-cache, no-store, private')
            ->withHeader('Cache-Directive', 'no-cache')
            ->withHeader('Pragma-Directive', 'no-cache')
            ->withHeader('Expires', '0');

        // render captcha image
        $response->getBody()->write($captchaImage);
        return $response;
    }

    protected function storePhraseToSession(
        string $newPhrase,
        ServerRequestInterface $request,
        int $lifetime = 3600
    ): void {
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
