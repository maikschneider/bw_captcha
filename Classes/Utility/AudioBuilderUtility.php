<?php

namespace Blueways\BwCaptcha\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class AudioBuilderUtility
{
    public static function createAudioCode(string $code, string $language): string
    {
        $languageCode = in_array($language, ['de', 'en', 'fr']) ? $language : 'en';
        $silentSecond = GeneralUtility::getFileAbsFileName('EXT:bw_captcha/Resources/Private/Sounds/silence.wav');
        $letterAudioPaths = [];

        foreach (str_split($code) as $letter) {
            $letterIdentifier = ctype_upper($letter) ? strtolower($letter) . '-upper' : $letter;
            $letterAudioPath = 'EXT:bw_captcha/Resources/Private/Sounds/' . $languageCode . '/' . $letterIdentifier . '.wav';
            $letterAudioPaths[] = GeneralUtility::getFileAbsFileName($letterAudioPath);
            $letterAudioPaths[] = $silentSecond;
        }

        return self::joinwavs($letterAudioPaths);
    }

    /**
     * @param string[] $wavs
     */
    public static function joinwavs(array $wavs): string
    {
        $fields = implode('/', [
            'H8ChunkID',
            'VChunkSize',
            'H8Format',
            'H8Subchunk1ID',
            'VSubchunk1Size',
            'vAudioFormat',
            'vNumChannels',
            'VSampleRate',
            'VByteRate',
            'vBlockAlign',
            'vBitsPerSample',
        ]);
        $data = $header = '';
        foreach ($wavs as $wav) {
            $fp = fopen($wav, 'rb');
            if (!$fp) {
                continue;
            }
            $header = fread($fp, 36) ?: '';
            $info = unpack($fields, $header);
            // read optional extra stuff
            if (isset($info['Subchunk1Size']) && $info['Subchunk1Size'] > 16) {
                $header .= fread($fp, max(0, (int)$info['Subchunk1Size'] - 16));
            }
            // read SubChunk2ID
            $header .= fread($fp, 4);
            // read Subchunk2Size
            $size = unpack('vsize', fread($fp, 4) ?: '') ?: [];
            $size = $size['size'];
            // dirty hack to fix problems with some german capital letters: obtained size is way too small
            if (strpos($wav, '-upper.wav')) {
                $size = 80000;
            }
            // read data
            $data .= fread($fp, $size);
        }
        return $header . pack('V', strlen($data)) . $data;
    }
}
