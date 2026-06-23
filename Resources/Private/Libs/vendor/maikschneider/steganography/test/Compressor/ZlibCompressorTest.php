<?php

namespace MaikSchneider\Steganography\Test\Compressor;

use MaikSchneider\Steganography\Compressor\ZlibCompressor;
use PHPUnit\Framework\TestCase;

class ZlibCompressorTest extends TestCase
{
    public function testName(): void
    {
        $compressor = new ZlibCompressor();
        self::assertEquals('zlib', $compressor->getName());
    }

    public function testEncodeAndDecodeWithDefaultLevel(): void
    {
        $compressor = new ZlibCompressor();
        $compressed = $compressor->compress('test');

        self::assertEquals('test', $compressor->decompress($compressed));
    }

    public function testEncodeAndDecodeWithHigherLevel(): void
    {
        $compressor = new ZlibCompressor(['level' => 6]);
        $compressed = $compressor->compress('test');

        self::assertEquals('test', $compressor->decompress($compressed));
    }
}
