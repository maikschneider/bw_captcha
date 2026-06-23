<?php

namespace MaikSchneider\Steganography\Compressor;

use Symfony\Component\OptionsResolver\OptionsResolver;

interface CompressorInterface
{
    public function compress(string $data): mixed;

    public function decompress(mixed $data): string;

    public function setDefaultOptions(OptionsResolver $resolver): void;

    public function isSupported(): bool;

    public function getName(): string;
}
