<?php

namespace MaikSchneider\Steganography\Iterator;

use Iterator;
use MaikSchneider\Steganography\Processor;

/**
 * @author Kazuyuki Hayashi
 */
class BinaryIterator implements Iterator
{
    private readonly string $string;

    private int $index = 0;

    private readonly int $length;

    private int $count = Processor::BITS_PER_PIXEL;

    public function __construct(string $string, int $count = Processor::BITS_PER_PIXEL)
    {
        $this->count  = $count;
        $this->string = sprintf('%048b', strlen($string)) . $string;
        $this->length = strlen($this->string);
    }

    /**
     * Return the current element
     *
     * @return array Can return any type.
     */
    public function current(): array
    {
        $part  = substr($this->string, ($this->index * $this->count), $this->count);
        $chars = array_pad(str_split($part), $this->count, 0);

        return [
            'r' => $chars[0],
            'g' => $chars[1],
            'b' => $chars[2],
        ];
    }

    /**
     * Move forward to next element
     */
    public function next(): void
    {
        ++$this->index;
    }

    /**
     * Return the key of the current element
     *
     * @return int scalar on success, or null on failure.
     */
    public function key(): int
    {
        return $this->index;
    }

    /**
     * Checks if current position is valid
     *
     * @return bool The return value will be casted to boolean and then evaluated.
     *       Returns true on success or false on failure.
     */
    public function valid(): bool
    {
        return $this->index * $this->count < $this->length;
    }

    /**
     * Rewind the Iterator to the first element
     */
    public function rewind(): void
    {
        $this->index = 0;
    }
}
