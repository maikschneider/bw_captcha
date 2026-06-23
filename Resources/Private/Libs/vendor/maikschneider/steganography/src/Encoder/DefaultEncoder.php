<?php

namespace MaikSchneider\Steganography\Encoder;

use MaikSchneider\Steganography\Compressor\CompressorInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DefaultEncoder implements EncoderInterface
{
    public function encode($data, CompressorInterface $compressor, array $options = []): string
    {
        $compressed = base64_encode((string)$compressor->compress($data));
        $bin        = '';
        $length     = strlen($compressed);

        for ($i = 0; $i < $length; ++$i) {
            $bin .= sprintf('%08b', ord($compressed[$i]));
        }

        return $bin;
    }

    public function decode($data, CompressorInterface $compressor, array $options = []): string
    {
        $chars  = str_split((string)$data, 8);
        $compressed = '';

        foreach ($chars as $char) {
            $compressed .= chr(bindec($char));
        }

        return $compressor->decompress(base64_decode($compressed));
    }

    public function setDefaultOptions(OptionsResolver $resolver): self
    {
        return $this;
    }
}
