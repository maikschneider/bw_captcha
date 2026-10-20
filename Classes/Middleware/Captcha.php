<?php

namespace Blueways\BwCaptcha\Middleware;

use Blueways\BwCaptcha\Utility\CaptchaBuilderUtility;
use Blueways\BwCaptcha\Utility\PhraseSessionUtility;
use MaikSchneider\Steganography\Processor;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

class Captcha implements MiddlewareInterface
{
    protected ResponseFactoryInterface $responseFactory;

    protected Context $context;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        Context $context,
    ) {
        $this->responseFactory = $responseFactory;
        $this->context = $context;
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        /** @var PageArguments|null $pageArguments */
        $pageArguments = $request->getAttribute('routing', null);
        if ($pageArguments?->getPageType() !== '3413') {
            // pipe request to other middleware handlers
            return $handler->handle($request);
        }

        $settings = [];
        try {
            /** @var FrontendTypoScript|null $frontendTypoScript */
            $frontendTypoScript = $request->getAttribute('frontend.typoscript');
            $ts = $frontendTypoScript?->getSetupArray() ?? [];
            $settings = $ts['plugin.']['tx_bwcaptcha.']['settings.'] ?? [];
        } catch (\RuntimeException) {
            // silent skip, fallback values apply; proper dev logging might be helpful in long term
        }
        $width = (int)($settings['width'] ?? 150);
        $height = (int)($settings['height'] ?? 40);
        $lifetime = (int)($settings['lifetime'] ?? 3600);
        $font = CaptchaBuilderUtility::getRandomFontFileFromSettings($settings);

        // create new captcha
        $builder = CaptchaBuilderUtility::getBuilderFromSettings($settings);
        $builder->build($width, $height, $font);
        $newPhrase = $builder->getPhrase() ?? '';

        /** @var FrontendUserAuthentication|null $feUser */
        $feUser = $request->getAttribute('frontend.user');
        $handle = null;
        if ($feUser !== null) {
            $handle = PhraseSessionUtility::createHandle();
            PhraseSessionUtility::store($feUser, $handle, $newPhrase, $lifetime);
        }

        // encode the handle of the phrase into the image, so that the audio middleware
        // can look up the solution of exactly this image in the session
        $gd = $builder->getGd();
        $captchaImage = null;
        $mimeType = 'image/jpeg';
        if ($handle !== null && (int)($settings['audioButton'] ?? 1) && $gd !== null) {
            try {
                $captchaImage = (new Processor())->encode($gd, $handle)->get();
                $mimeType = 'image/png';
            } catch (\Throwable) {
                // image too small to carry the handle: the audio middleware falls back to the latest phrase
                $captchaImage = null;
            }
        }
        if ($captchaImage === null || $captchaImage === false) {
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
        $response->getBody()->write((string)$captchaImage);
        return $response;
    }
}
