<?php

namespace MaikSchneider\Steganography\Test;

use MaikSchneider\Steganography\Compressor\CompressorInterface;
use MaikSchneider\Steganography\Compressor\ZlibCompressor;
use MaikSchneider\Steganography\Encoder\DefaultEncoder;
use MaikSchneider\Steganography\Encoder\EncoderInterface;
use MaikSchneider\Steganography\Image\Image;
use MaikSchneider\Steganography\Processor;
use MaikSchneider\Steganography\Test\Resources\stub\InvalidCompressor;
use PHPUnit\Framework\TestCase;

class ProcessorTest extends TestCase
{
    public function testCompressor(): void
    {
        $processor = new Processor();
        $processor->setCompressor(new ZlibCompressor());
        self::assertInstanceOf(CompressorInterface::class, $processor->getCompressor());
    }

    public function testEncodeFromFilePath()
    {
        $message = 'a48995845f83ee779c632fd1225224e0e07380fc61da8f495f3e25760fc0e0029034ca41960adb81aeceee4902a1163b';

        $processor = new Processor();
        $image = $processor->encode(__DIR__ . '/Resources/img/koala.jpg', $message);
        self::assertInstanceOf(Image::class, $image);

        $image->write(__DIR__ . '/Resources/out/koala_out.png');
        self::assertFileExists(__DIR__ . '/Resources/out/koala_out.png');

        return $message;
    }

    /**
     * @depends testEncodeFromFilePath
     */
    public function testDecodeFromFilePath(string $expected): void
    {
        $processor = new Processor();
        $message = $processor->decode(__DIR__ . '/Resources/out/koala_out.png');

        self::assertEquals($expected, $message);
    }

    public function testEncodeFromResource()
    {
        $message = 'lorem';
        $resource = imagecreatefromjpeg(__DIR__ . '/Resources/img/koala.jpg');

        $processor = new Processor();
        $image = $processor->encode($resource, $message);
        self::assertInstanceOf(Image::class, $image);

        $image->write(__DIR__ . '/Resources/out/koala_out2.png');
        self::assertFileExists(__DIR__ . '/Resources/out/koala_out2.png');

        return $message;
    }

    /**
     * @depends testEncodeFromResource
     */
    public function testDecodeFromResource(string $expected): void
    {
        $processor = new Processor();
        $resource = imagecreatefrompng(__DIR__ . '/Resources/out/koala_out2.png');
        $message = $processor->decode($resource);

        self::assertEquals($expected, $message);
    }

    public function testTooFewPixels(): void
    {
        self::expectExceptionCode(1693212326);
        $longMessage = str_repeat('a48995845f83ee779c632fd1225224e0e0738', 100);

        $processor = new Processor();
        $processor->encode(__DIR__ . '/Resources/img/3.jpg', $longMessage);
    }

    public function testEncoder(): void
    {
        $processor = new Processor();
        $processor->setEncoder(new DefaultEncoder());

        self::assertInstanceOf(EncoderInterface::class, $processor->getEncoder());
    }

    public function testInvalidCompressor(): void
    {
        $this->expectException(\RuntimeException::class);

        $processor = new Processor();
        $processor->setCompressor(new InvalidCompressor());
    }
}
