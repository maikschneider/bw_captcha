<?php

namespace Blueways\BwCaptcha\Utility;

use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

class CaptchaBuilderUtility
{
    /**
     * @param array<string, string> $settings
     * @return \Gregwar\Captcha\CaptchaBuilder
     */
    public static function getBuilderFromSettings(array $settings): CaptchaBuilder
    {
        $length = $settings['length'] ?: 5;
        $charset = $settings['charset'] ?: 'abcdefghijklmnpqrstuvwxyz123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $textColor = $settings['textColor'] ?: null;
        $lineColor = $settings['lineColor'] ?: null;
        $backgroundColor = $settings['backgroundColor'] ?: null;
        $distortion = $settings['distortion'] ?: null;
        $maxFrontLines = $settings['maxFrontLines'] ?: null;
        $maxBehindLines = $settings['maxBehindLines'] ?: null;
        $maxAngle = $settings['maxAngle'] ?: null;
        $maxOffset = $settings['maxOffset'] ?: null;
        $interpolation = $settings['interpolation'] ?: null;
        $ignoreAllEffects = $settings['ignoreAllEffects'] ?: null;

        $phraseBuilder = new PhraseBuilder($length, $charset);
        $captchaBuilder = new CaptchaBuilder(null, $phraseBuilder);

        if ($textColor) {
            $textColor = GeneralUtility::intExplode(',', $textColor);
            $captchaBuilder->setTextColor($textColor[0], $textColor[1], $textColor[2]);
        }
        if ($lineColor) {
            $lineColor = GeneralUtility::intExplode(',', $lineColor);
            $captchaBuilder->setLineColor($lineColor[0], $lineColor[1], $lineColor[2]);
        }
        if ($backgroundColor) {
            $backgroundColor = GeneralUtility::intExplode(',', $backgroundColor);
            $captchaBuilder->setBackgroundColor($backgroundColor[0], $backgroundColor[1], $backgroundColor[2]);
        }
        if ($distortion) {
            $captchaBuilder->setDistortion(filter_var($distortion, FILTER_VALIDATE_BOOLEAN));
        }
        if ($maxFrontLines) {
            $captchaBuilder->setMaxFrontLines((int)$maxFrontLines);
        }
        if ($maxBehindLines) {
            $captchaBuilder->setMaxBehindLines((int)$maxBehindLines);
        }
        if ($maxAngle) {
            $captchaBuilder->setMaxAngle((int)$maxAngle);
        }
        if ($maxOffset) {
            $captchaBuilder->setMaxOffset((int)$maxOffset);
        }
        if ($interpolation) {
            $captchaBuilder->setInterpolation(filter_var($interpolation, FILTER_VALIDATE_BOOLEAN));
        }
        if ($ignoreAllEffects) {
            $captchaBuilder->setIgnoreAllEffects(filter_var($ignoreAllEffects, FILTER_VALIDATE_BOOLEAN));
        }

        return $captchaBuilder;
    }

    /**
     * @param array<string, string> $settings
     * @return string|null
     */
    public static function getRandomFontFileFromSettings(array $settings): ?string
    {
        $fontFiles = GeneralUtility::trimExplode(',', $settings['fontFiles'] ?? '', true);
        shuffle($fontFiles);

        if (!count($fontFiles)) {
            return null;
        }

        // check for file extension
        $filePathInfo = PathUtility::pathinfo($fontFiles[0]);
        if (isset($filePathInfo['extension']) && $filePathInfo['extension'] !== 'ttf') {
            return null;
        }

        // check EXT: path
        $randomFontFile = GeneralUtility::getFileAbsFileName($fontFiles[0]);
        if (file_exists($randomFontFile)) {
            return $randomFontFile;
        }

        // check 1: file storage path
        $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
        try {
            $randomFontFile = $resourceFactory->retrieveFileOrFolderObject($fontFiles[0])?->getPublicUrl() ?? '';
            $randomFontFile = Environment::getPublicPath() . $randomFontFile;
        } catch (\Exception $e) {
        }

        // check for existence
        if ($randomFontFile && file_exists($randomFontFile)) {
            return $randomFontFile;
        }

        return null;
    }
}
