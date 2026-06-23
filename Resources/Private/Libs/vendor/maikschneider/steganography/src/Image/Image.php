<?php

namespace MaikSchneider\Steganography\Image;

use GdImage;
use InvalidArgumentException;
use LimitIterator;
use MaikSchneider\Steganography\Iterator\BinaryIterator;
use MaikSchneider\Steganography\Iterator\RectIterator;
use MaikSchneider\Steganography\Processor;
use MultipleIterator;
use RuntimeException;

final class Image
{
    private string $path;

    private GdImage|bool|null $image = null;

    private int $width = 0;

    private int $height = 0;

    private int $pixels = 0;

    /**
     * @throws InvalidArgumentException
     */
    public static function getFromFilePath(string $path): Image
    {
        if (!file_exists($path)) {
            throw new InvalidArgumentException('File Not Found: ' . $path, 1693212643);
        }

        $image = new Image();
        $image->path = $path;
        $image->initializeImage();

        return $image;
    }

    /**
     * @param GdImage $resource
     */
    public static function getFromResource(mixed $resource): Image
    {
        $image = new Image();
        $image->image = $resource;
        $image->initializeInfo();

        return $image;
    }

    /**
     * @throws RuntimeException
     */
    protected function initializeImage(): void
    {
        $info = getimagesize($this->path);
        $this->width = $info[0];
        $this->height = $info[1];
        $this->pixels = $this->width * $this->height;
        $type = $info[2];

        $this->image = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($this->path),
            IMAGETYPE_GIF => imagecreatefromgif($this->path),
            IMAGETYPE_PNG => imagecreatefrompng($this->path),
            default => throw new RuntimeException('Unsupported image type ' . $type),
        };

        imagealphablending($this->image, false);
    }

    protected function initializeInfo(): void
    {
        $this->width = imagesx($this->image);
        $this->height = imagesy($this->image);
        $this->pixels = $this->width * $this->height;
        imagealphablending($this->image, false);
    }

    public function setBinaryString(BinaryIterator $binary): static
    {
        $iterator = new MultipleIterator(MultipleIterator::MIT_NEED_ALL | MultipleIterator::MIT_KEYS_ASSOC);
        $iterator->attachIterator(new RectIterator($this->width, $this->height), 'rect');
        $iterator->attachIterator($binary, 'bin');

        foreach ($iterator as $current) {
            $this->setPixel($current['rect'][0], $current['rect'][1], $current['bin']);
        }

        return $this;
    }

    public function setPixel($x, $y, array $values): self
    {
        $rgb = $this->getRGB($x, $y);

        foreach ($rgb as $name => $value) {
            $rgb[$name] = bindec(substr(decbin($value), 0, -1) . $values[$name]);
        }

        $color = imagecolorallocate($this->image, $rgb['r'], $rgb['g'], $rgb['b']);
        imagesetpixel($this->image, $x, $y, $color);
        imagecolordeallocate($this->image, $color);

        return $this;
    }

    protected function getRGB($x, $y): array
    {
        $rgb = imagecolorat($this->image, $x, $y);

        return [
            'r' => ($rgb >> 16) & 0xFF,
            'g' => ($rgb >> 8) & 0xFF,
            'b' => $rgb & 0xFF,
        ];
    }

    public function getBinaryString(): string
    {
        $iterator = new RectIterator($this->width, $this->height);
        $length = '';
        $data = '';
        $offset = Processor::LENGTH_BITS / Processor::BITS_PER_PIXEL;

        foreach (new LimitIterator($iterator, 0, $offset) as $value) {
            $length .= $this->getPixel($value[0], $value[1]);
        }

        $bits = (int)bindec($length);
        $length = (int)ceil($bits / Processor::BITS_PER_PIXEL);

        foreach (new LimitIterator($iterator, $offset, $length) as $value) {
            $data .= $this->getPixel($value[0], $value[1]);
        }

        return substr($data, 0, $bits);
    }

    public function getPixel($x, $y): string
    {
        $result = '';
        $rgb = $this->getRGB($x, $y);

        foreach ($rgb as $value) {
            $result .= substr(decbin($value), -1, 1);
        }

        return $result;
    }

    public function write($path): bool
    {
        return imagepng($this->image, $path, 0);
    }

    public function render(): bool
    {
        return imagepng($this->image, null, 0);
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getPixels(): int
    {
        return $this->pixels;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function __destruct()
    {
        if ($this->image) {
            imagedestroy($this->image);
        }
    }

    public function get(): bool|string
    {
        ob_start();
        imagepng($this->image);

        return ob_get_clean();
    }
}
