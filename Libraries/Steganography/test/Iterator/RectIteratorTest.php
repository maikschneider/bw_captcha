<?php

namespace MaikSchneider\Steganography\Test\Iterator;

use MaikSchneider\Steganography\Iterator\RectIterator;
use PHPUnit\Framework\TestCase;

class RectIteratorTest extends TestCase
{
    public function testIterator(): void
    {
        $iterator = new RectIterator(5, 5);

        $result = iterator_to_array($iterator);

        self::assertEquals([
            [0, 0], [1, 0], [2, 0], [3, 0], [4, 0],
            [0, 1], [1, 1], [2, 1], [3, 1], [4, 1],
            [0, 2], [1, 2], [2, 2], [3, 2], [4, 2],
            [0, 3], [1, 3], [2, 3], [3, 3], [4, 3],
            [0, 4], [1, 4], [2, 4], [3, 4], [4, 4],
        ], $result);
    }
}
