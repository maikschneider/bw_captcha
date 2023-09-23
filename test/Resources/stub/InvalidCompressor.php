<?php

namespace MaikSchneider\Steganography\Test\Resources\stub;

use MaikSchneider\Steganography\Compressor\Compressor;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvalidCompressor extends Compressor
{
    public function compress(string $data): mixed
    {
        return $data;
    }

    public function decompress($data): string
    {
        return $data;
    }

    public function setDefaultOptions(OptionsResolver $resolver): void
    {
    }

    public function isSupported(): bool
    {
        return false;
    }

    public function getName(): string
    {
        return 'invalid';
    }
}
