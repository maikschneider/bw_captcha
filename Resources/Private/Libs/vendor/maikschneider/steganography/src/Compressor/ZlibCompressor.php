<?php

namespace MaikSchneider\Steganography\Compressor;

use Symfony\Component\OptionsResolver\OptionsResolver;

class ZlibCompressor extends Compressor
{
    public function compress($data): string|bool
    {
        return gzcompress((string)$data, $this->options['level']);
    }

    public function decompress($data): string
    {
        return gzuncompress($data);
    }

    public function isSupported(): bool
    {
        return function_exists('gzcompress');
    }

    public function setDefaultOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'level' => -1,
        ]);
    }

    public function getName(): string
    {
        return 'zlib';
    }
}
