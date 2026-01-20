<?php

namespace Blueways\BwCaptcha\Middleware;

use Blueways\BwCaptcha\Utility\AudioBuilderUtility;
use MaikSchneider\Steganography\Processor;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;
use TYPO3\CMS\Core\Crypto\PasswordHashing\InvalidPasswordHashException;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;

class Audio implements MiddlewareInterface
{
    protected ResponseFactoryInterface $responseFactory;

    public function __construct(
        ResponseFactoryInterface $responseFactory
    ) {
        $this->responseFactory = $responseFactory;
    }

    /**
     * @throws InvalidPasswordHashException&Throwable
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        /** @var PageArguments $pageArguments */
        $pageArguments = $request->getAttribute('routing', null);
        if ($pageArguments->getPageType() !== '3414' || $request->getMethod() !== 'POST' || $request->getAttribute('frontend.user') === null) {
            // pipe request to other middleware handlers
            return $handler->handle($request);
        }

        $languageCode = $request->getAttribute('language')?->getLocale()->getLanguageCode() ?? '';
        $body = $request->getParsedBody();

        $settings = [];
        try {
            /** @var FrontendTypoScript|null $frontendTypoScript */
            $frontendTypoScript = $request->getAttribute('frontend.typoscript');
            $ts = $frontendTypoScript?->getSetupArray() ?? [];
            $settings = $ts['plugin.']['tx_bwcaptcha.']['settings.'] ?? [];
        } catch (\RuntimeException) {
            // silent skip, fallback values apply; proper dev logging might be helpful in long term
        }

        // get all phrases from session
        $feUser = $request->getAttribute('frontend.user');
        $captchaPhrases = $feUser->getKey('ses', 'captchaPhrases');

        // @TODO: handle error
        if (!$captchaPhrases || !is_array($captchaPhrases)) {
            return $handler->handle($request);
        }

        if ((int)($settings['audioButton'] ?? 1) && is_array($body) && isset($body['captchaDataUrl'])) {
            // get image data from post request
            $dataUrl = $body['captchaDataUrl'];
            [, $dataUrl] = explode(';', $dataUrl);
            [, $dataUrl] = explode(',', $dataUrl);
            $imageData = base64_decode($dataUrl);

            // decode image
            $img = imagecreatefromstring($imageData);
            $processor = new Processor();
            $latestCaptcha = $processor->decode($img);
        } else {
            // use latest captcha
            $latestCaptcha = array_pop($captchaPhrases);
        }

        $soundFile = AudioBuilderUtility::createAudioCode($latestCaptcha, $languageCode);

        // render captcha image
        $response = $this->responseFactory->createResponse()
            ->withHeader('Content-Type', 'audio/mp3');
        $response->getBody()->write($soundFile);
        return $response;
    }
}
