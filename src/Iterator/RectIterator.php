<?php

namespace MaikSchneider\Steganography\Iterator;

use Iterator;

/**
 * @author Kazuyuki Hayashi
 */
class RectIterator implements Iterator
{
    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $height;

    private int $index = 0;

    private int $x = 0;

    private int $y = 0;

    /**
     * @param int $width
     * @param int $height
     */
    public function __construct($width = 0, $height = 0)
    {
        $this->width  = $width;
        $this->height = $height;
    }

    /**
     * Return the current element
     *
     * @return mixed Can return any type.
     */
    public function current(): mixed
    {
        return [$this->x, $this->y];
    }

    /**
     * Move forward to next element
     */
    public function next(): void
    {
        if ($this->x + 1 < $this->width) {
            ++$this->x;
        } else {
            $this->x = 0;
            ++$this->y;
        }

        ++$this->index;
    }

    /**
     * Return the key of the current element
     *
     * @return mixed scalar on success, or null on failure.
     */
    public function key(): mixed
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
        return $this->x < $this->width && $this->y < $this->height;
    }

    /**
     * Rewind the Iterator to the first element
     */
    public function rewind(): void
    {
        $this->index = 0;
        $this->x     = 0;
        $this->y     = 0;
    }
}
