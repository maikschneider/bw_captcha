<?php

namespace Blueways\BwCaptcha\Middleware;

use Blueways\BwCaptcha\Utility\AudioBuilderUtility;
use MaikSchneider\Steganography\Processor;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Crypto\PasswordHashing\InvalidPasswordHashException;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;

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

    /**
     * @throws InvalidConfigurationTypeException
     * @throws InvalidPasswordHashException
     */
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

        $languageCode = $request->getAttribute('language')->getTwoLetterIsoCode();
        $body = $request->getParsedBody();

        $ts = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
        );
        $settings = $ts['plugin.']['tx_bwcaptcha.']['settings.'];

        // get all phrases from session
        $tsfe = $request->getAttribute('frontend.controller') ?? $GLOBALS['TSFE'];
        $feUser = $tsfe->fe_user;
        $captchaPhrases = $feUser->getKey('ses', 'captchaPhrases');

        // @TODO: handle error
        if (!$captchaPhrases || !is_array($captchaPhrases)) {
            return $handler->handle($request);
        }

        $latestCaptcha = '';

        if ($settings['useSteganography'] && isset($body['captchaDataUrl'])) {
            // get image data from post request
            $dataUrl = $body['captchaDataUrl'];
            [, $dataUrl] = explode(';', $dataUrl);
            [, $dataUrl] = explode(',', $dataUrl);
            $imageData = base64_decode($dataUrl);

            // decode image
            $img = imagecreatefromstring($imageData);
            $processor = new Processor();
            $encryptedCaptchaPhrase = $processor->decode($img);

            /** @var PasswordHashFactory $hashFactory */
            $hashFactory = GeneralUtility::makeInstance(PasswordHashFactory::class);
            $hashInstance = $hashFactory->getDefaultHashInstance('FE');
            foreach (array_reverse($captchaPhrases) as $captchaPhrase) {
                if ($hashInstance->checkPassword($captchaPhrase, $encryptedCaptchaPhrase)) {
                    $latestCaptcha = $captchaPhrase;
                    break;
                }
            }
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
