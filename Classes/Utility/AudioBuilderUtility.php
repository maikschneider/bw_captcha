<?php

namespace Blueways\BwCaptcha\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class AudioBuilderUtility
{
    protected const SOUND_PATH = 'EXT:bw_captcha/Resources/Private/Sounds/';

    protected const LANGUAGES = ['de', 'en', 'fr'];

    protected const SAMPLE_RATE = 22050;

    /**
     * Reads out a phrase, letter by letter.
     *
     * Every rendering varies in speed, volume, spacing and noise floor, so that the result cannot be
     * matched against the shipped recordings.
     */
    public static function createAudioCode(string $code, string $language): string
    {
        $languageCode = in_array($language, self::LANGUAGES, true) ? $language : 'en';
        $code = (string)preg_replace('/[^A-Za-z0-9]/', '', $code);
        $noiseLevel = random_int(40, 160);
        $data = self::renderNoise(random_int(2000, 9000), $noiseLevel);

        foreach ($code === '' ? [] : str_split($code) as $letter) {
            $letterIdentifier = ctype_upper($letter) ? strtolower($letter) . '-upper' : $letter;
            $samples = self::readSamples(
                GeneralUtility::getFileAbsFileName(self::SOUND_PATH . $languageCode . '/' . $letterIdentifier . '.wav')
            );
            if ($samples === []) {
                continue;
            }

            $samples = self::stretch($samples, random_int(90, 110) / 100);
            $data .= self::renderSamples($samples, random_int(70, 100) / 100, $noiseLevel);
            $data .= self::renderNoise(random_int(7000, 18000), $noiseLevel);
        }

        return self::renderWaveFile($data, self::SAMPLE_RATE + random_int(-800, 800));
    }

    /**
     * Reads the samples of a 16 bit mono PCM wave file.
     *
     * @return int[]
     */
    protected static function readSamples(string $path): array
    {
        if (!is_file($path)) {
            return [];
        }

        $content = (string)file_get_contents($path);
        $length = strlen($content);
        if ($length < 44 || substr($content, 0, 4) !== 'RIFF' || substr($content, 8, 4) !== 'WAVE') {
            return [];
        }

        $offset = 12;
        $isSupported = false;
        while ($offset + 8 <= $length) {
            $chunkId = substr($content, $offset, 4);
            $chunkHeader = unpack('Vsize', substr($content, $offset + 4, 4));
            $chunkSize = $chunkHeader === false ? 0 : (int)$chunkHeader['size'];
            $offset += 8;

            if ($chunkId === 'fmt ') {
                $format = unpack(
                    'vaudioFormat/vnumChannels/VsampleRate/VbyteRate/vblockAlign/vbitsPerSample',
                    substr($content, $offset, 16)
                );
                $isSupported = $format !== false
                    && $format['audioFormat'] === 1
                    && $format['numChannels'] === 1
                    && $format['bitsPerSample'] === 16;
            }

            if ($chunkId === 'data') {
                if (!$isSupported) {
                    return [];
                }
                $dataLength = min($chunkSize, $length - $offset);
                $samples = unpack('v*', substr($content, $offset, $dataLength - ($dataLength % 2)));

                return $samples === false ? [] : array_map(
                    static fn (int $sample): int => $sample >= 32768 ? $sample - 65536 : $sample,
                    array_values($samples)
                );
            }

            $offset += $chunkSize + ($chunkSize % 2);
        }

        return [];
    }

    /**
     * @param int[] $samples
     * @return int[]
     */
    protected static function stretch(array $samples, float $factor): array
    {
        $count = count($samples);
        $targetCount = (int)round($count / $factor);
        $stretched = [];

        for ($i = 0; $i < $targetCount; $i++) {
            $position = $i * $factor;
            $index = (int)$position;
            $current = $samples[$index] ?? 0;
            $next = $samples[$index + 1] ?? $current;
            $stretched[] = (int)round($current + ($next - $current) * ($position - $index));
        }

        return $stretched;
    }

    /**
     * @param int[] $samples
     */
    protected static function renderSamples(array $samples, float $gain, int $noiseLevel): string
    {
        $data = '';
        foreach ($samples as $sample) {
            $data .= self::packSample((int)round($sample * $gain) + mt_rand(-$noiseLevel, $noiseLevel));
        }

        return $data;
    }

    protected static function renderNoise(int $sampleCount, int $noiseLevel): string
    {
        $data = '';
        for ($i = 0; $i < $sampleCount; $i++) {
            $data .= self::packSample(mt_rand(-$noiseLevel, $noiseLevel));
        }

        return $data;
    }

    protected static function packSample(int $sample): string
    {
        return pack('v', max(-32768, min(32767, $sample)) & 0xFFFF);
    }

    protected static function renderWaveFile(string $data, int $sampleRate): string
    {
        return 'RIFF' . pack('V', 36 + strlen($data)) . 'WAVE'
            . 'fmt ' . pack('VvvVVvv', 16, 1, 1, $sampleRate, $sampleRate * 2, 2, 16)
            . 'data' . pack('V', strlen($data)) . $data;
    }
}
