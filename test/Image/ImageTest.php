<?php

namespace MaikSchneider\Steganography\Test\Image;

use GdImage;
use InvalidArgumentException;
use MaikSchneider\Steganography\Image\Image;
use PHPUnit\Framework\TestCase;

class ImageTest extends TestCase
{
    public function testSize(): void
    {
        $image = Image::getFromFilePath(__DIR__ . '/../Resources/img/3.jpg');
        self::assertEquals(3, $image->getWidth());
        self::assertEquals(3, $image->getHeight());
    }

    public function testInvalidPath(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Image::getFromFilePath(__DIR__ . '/foo.jpg');
    }

    public function testRender(): void
    {
        $image = Image::getFromFilePath(__DIR__ . '/../Resources/img/3.jpg');
        self::assertTrue($image->render());

        $resource = $image->get();
        self::assertInstanceOf(GdImage::class, imagecreatefromstring($resource));
    }
}
