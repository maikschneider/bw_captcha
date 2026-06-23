<?php

namespace MaikSchneider\Steganography\Compressor;

use LogicException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MultipleCompressor extends Compressor
{
    /**
     * @var CompressorInterface[]
     */
    private array $children = [];

    private ?CompressorInterface $selectedCompressor = null;

    public function attach(CompressorInterface $compressor): void
    {
        $this->children[$compressor->getName()] = $compressor;
    }

    public function compress($data): mixed
    {
        if (!$this->selectCompressor()) {
            throw new LogicException('Attach at least 1 compressor');
        }

        return $this->selectedCompressor->compress($data);
    }

    public function decompress($data): string
    {
        if (!$this->selectCompressor()) {
            throw new LogicException('Attach at least 1 compressor');
        }

        return $this->selectedCompressor->decompress($data);
    }

    public function setDefaultOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'preferred_choice' => null,
        ]);
    }

    public function isSupported(): bool
    {
        return $this->selectCompressor();
    }

    public function getName(): string
    {
        return 'multiple';
    }

    protected function selectCompressor(): bool
    {
        if ($this->selectedCompressor instanceof CompressorInterface) {
            return true;
        }

        if (isset($this->children[$this->options['preferred_choice']])) {
            $this->selectedCompressor = $this->children[$this->options['preferred_choice']];

            if ($this->selectedCompressor->isSupported()) {
                return true;
            }
        }

        foreach ($this->children as $compressor) {
            if ($compressor->isSupported()) {
                $this->selectedCompressor = $compressor;

                return true;
            }
        }

        return false;
    }
}
