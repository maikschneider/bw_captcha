<?php

namespace MaikSchneider\Steganography\Encoder;

use MaikSchneider\Steganography\Compressor\CompressorInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

interface EncoderInterface
{
    public function encode(string $data, CompressorInterface $compressor, array $options = []): mixed;

    public function decode(string $data, CompressorInterface $compressor, array $options = []): mixed;

    public function setDefaultOptions(OptionsResolver $resolver): EncoderInterface;
}
