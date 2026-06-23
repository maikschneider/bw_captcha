<?php

namespace MaikSchneider\Steganography\Test\Compressor;

use LogicException;
use MaikSchneider\Steganography\Compressor\MultipleCompressor;
use MaikSchneider\Steganography\Compressor\ZlibCompressor;
use MaikSchneider\Steganography\Test\Resources\stub\InvalidCompressor;
use PHPUnit\Framework\TestCase;

class MultipleCompressorTest extends TestCase
{
    public function testName(): void
    {
        $compressor = new MultipleCompressor();
        $compressor->attach(new ZlibCompressor());
        self::assertEquals('multiple', $compressor->getName());
    }

    public function testIsSupported(): void
    {
        $compressor = new MultipleCompressor();
        $compressor->attach(new ZlibCompressor());
        self::assertTrue($compressor->isSupported());

        $compressor = new MultipleCompressor();
        self::assertFalse($compressor->isSupported());
    }

    public function testEncodeAndDecode(): void
    {
        $compressor = new MultipleCompressor();
        $compressor->attach(new ZlibCompressor());

        $compressed = $compressor->compress('test');

        self::assertEquals('test', $compressor->decompress($compressed));
    }

    public function testEncodeBeforeAttach(): void
    {
        $this->expectException(LogicException::class);

        $compressor = new MultipleCompressor();
        $compressor->compress('test');
    }

    public function testDecodeBeforeAttach(): void
    {
        $this->expectException(LogicException::class);
        $compressor = new MultipleCompressor();
        $compressor->decompress('test');
    }

    public function testPreferredChoice(): void
    {
        $compressor = new MultipleCompressor(['preferred_choice' => 'zlib']);
        $compressor->attach(new InvalidCompressor());
        $compressor->attach(new ZlibCompressor());
        $data = $compressor->compress('test');
        self::assertEquals('test', $compressor->decompress($data));

        $compressor = new MultipleCompressor();
        $compressor->attach(new ZlibCompressor());
        $compressor->attach(new InvalidCompressor());
        $data = $compressor->compress('test');
        self::assertEquals('test', $compressor->decompress($data));
    }

    public function testInvalidPreferredChoice(): void
    {
        $compressor = new MultipleCompressor(['preferred_choice' => 'invalid']);
        $compressor->attach(new ZlibCompressor());
        $compressor->attach(new InvalidCompressor());
        $compressor->compress('test');
        $data = $compressor->compress('test');

        self::assertEquals('test', $compressor->decompress($data));
    }
}
