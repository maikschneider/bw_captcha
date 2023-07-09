<?php

namespace Blueways\BwCaptcha\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class AudioBuilderUtility
{
    public static function createAudioCode(string $code, string $language): string
    {
        $languageCode = in_array($language, ['de']) ? $language : 'de';
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

    public static function joinwavs($wavs): string
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
        $data = '';
        foreach ($wavs as $wav) {
            $fp = fopen($wav, 'rb');
            $header = fread($fp, 36);
            $info = unpack($fields, $header);
            // read optional extra stuff
            if ($info['Subchunk1Size'] > 16) {
                $header .= fread($fp, ($info['Subchunk1Size'] - 16));
            }
            // read SubChunk2ID
            $header .= fread($fp, 4);
            // read Subchunk2Size
            $size = unpack('vsize', fread($fp, 4));
            $size = $size['size'];
            // read data
            $data .= fread($fp, $size);
        }
        return $header . pack('V', strlen($data)) . $data;
    }
}
