<?php

namespace Blueways\BwCaptcha\Middleware;

use Blueways\BwCaptcha\Utility\AudioBuilderUtility;
use Blueways\BwCaptcha\Utility\PhraseSessionUtility;
use GdImage;
use MaikSchneider\Steganography\Processor;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

class Audio implements MiddlewareInterface
{
    protected const DATA_URL_PREFIX = 'data:image/png;base64,';

    protected const MAX_DATA_URL_LENGTH = 4194304;

    protected const MAX_PIXELS = 250000;

    protected ResponseFactoryInterface $responseFactory;

    public function __construct(
        ResponseFactoryInterface $responseFactory
    ) {
        $this->responseFactory = $responseFactory;
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        /** @var PageArguments|null $pageArguments */
        $pageArguments = $request->getAttribute('routing', null);
        if ($pageArguments?->getPageType() !== '3414' || $request->getMethod() !== 'POST' || $request->getAttribute('frontend.user') === null) {
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

        /** @var FrontendUserAuthentication $feUser */
        $feUser = $request->getAttribute('frontend.user');

        // the image references its phrase by handle, the phrase itself is only known to the session
        $phrase = null;
        if ((int)($settings['audioButton'] ?? 1) && is_array($body) && is_string($body['captchaDataUrl'] ?? null)) {
            $handle = $this->extractHandle($body['captchaDataUrl'], $settings);
            if ($handle !== null) {
                $phrase = PhraseSessionUtility::get($feUser, $handle);
            }
        }
        $phrase ??= PhraseSessionUtility::getLatest($feUser);

        if ($phrase === null) {
            return $this->responseFactory->createResponse(404);
        }

        $soundFile = AudioBuilderUtility::createAudioCode($phrase, $languageCode);

        // render captcha audio
        $response = $this->responseFactory->createResponse()
            ->withHeader('Content-Type', 'audio/mp3')
            ->withHeader('Cache-Control', 'no-cache, no-store, private');
        $response->getBody()->write($soundFile);
        return $response;
    }

    /**
     * @param array<string, string> $settings
     */
    protected function extractHandle(string $dataUrl, array $settings): ?string
    {
        $image = $this->createImage($dataUrl, $settings);
        if (!$image instanceof GdImage) {
            return null;
        }

        try {
            // images without a payload make the decompression of the processor fail loudly
            $handle = @(new Processor())->decode($image);
        } catch (\Throwable) {
            return null;
        }

        return is_string($handle) && PhraseSessionUtility::isHandle($handle) ? $handle : null;
    }

    /**
     * Turns the posted data url into an image, as long as it looks exactly like the captcha
     * that was handed out before.
     *
     * @param array<string, string> $settings
     */
    protected function createImage(string $dataUrl, array $settings): ?GdImage
    {
        if (strlen($dataUrl) > self::MAX_DATA_URL_LENGTH || !str_starts_with($dataUrl, self::DATA_URL_PREFIX)) {
            return null;
        }

        $imageData = base64_decode(substr($dataUrl, strlen(self::DATA_URL_PREFIX)), true);
        if ($imageData === false) {
            return null;
        }

        $imageInfo = @getimagesizefromstring($imageData);
        if ($imageInfo === false
            || $imageInfo[2] !== IMAGETYPE_PNG
            || $imageInfo[0] !== (int)($settings['width'] ?? 150)
            || $imageInfo[1] !== (int)($settings['height'] ?? 40)
            || $imageInfo[0] * $imageInfo[1] > self::MAX_PIXELS
        ) {
            return null;
        }

        $image = @imagecreatefromstring($imageData);

        return $image instanceof GdImage ? $image : null;
    }
}
