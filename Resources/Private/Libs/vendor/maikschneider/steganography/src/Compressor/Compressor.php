<?php

namespace MaikSchneider\Steganography\Compressor;

use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class Compressor implements CompressorInterface
{
    protected array $options;

    public function __construct(array $options = [])
    {
        $resolver = new OptionsResolver();
        $this->setDefaultOptions($resolver);
        $this->options = $resolver->resolve($options);
    }
}
